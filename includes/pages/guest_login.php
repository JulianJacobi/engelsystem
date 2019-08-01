<?php

use Carbon\Carbon;
use Engelsystem\Database\DB;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function login_title()
{
    return __('Login');
}

/**
 * @return string
 */
function register_title()
{
    return __('Register');
}

/**
 * Engel registrieren
 *
 * @return string
 */
function guest_register()
{
    $authUser = auth()->user();
    $tshirt_sizes = config('tshirt_sizes');
    $enable_tshirt_size = config('enable_tshirt_size');
    $enable_dect = config('enable_dect');
    $enable_planned_arrival = config('enable_planned_arrival');
    $min_password_length = config('min_password_length');
    $config = config();
    $request = request();
    $session = session();

    $msg = '';
    $nick = '';
    $lastName = '';
    $preName = '';
    $dect = '';
    $mobile = '';
    $mail = '';
    $email_shiftinfo = true;
    $email_by_human_allowed = true;
    $tshirt_size = '';
    $password_hash = '';
    $selected_angel_types = [];
    $planned_arrival_date = null;

    $street = '';
    $hometown = '';
    $zip_code = '';
    $emergency_contact = '';
    $emergency_contact_phone = '';
    $allergies = '';
    $medicines = '';
    $date_of_birth = '';

    $angel_types_source = AngelTypes();
    $angel_types = [];
    foreach ($angel_types_source as $angel_type) {
        $angel_types[$angel_type['id']] = $angel_type['name'] . ($angel_type['restricted'] ? ' (restricted)' : '');
        if (!$angel_type['restricted']) {
            $selected_angel_types[] = $angel_type['id'];
        }
    }

    if (!auth()->can('register') || (!$authUser && !config('registration_enabled'))) {
        error(__('Registration is disabled.'));

        return page_with_title(register_title(), [
            msg(),
        ]);
    }

    if ($request->hasPostData('submit')) {
        $valid = true;

        if ($request->has('nick')) {
            $nickValidation = User_validate_Nick($request->input('nick'));
            $nick = $nickValidation->getValue();

            if (!$nickValidation->isValid()) {
                $valid = false;
                $msg .= error(sprintf(__('Please enter a valid nick.') . ' ' . __('Use up to 23 letters, numbers, connecting punctuations or spaces for your nickname.'),
                    $nick), true);
            }
            if (User::whereName($nick)->count() > 0) {
                $valid = false;
                $msg .= error(sprintf(__('Your nick &quot;%s&quot; already exists.'), $nick), true);
            }
        } else {
            $valid = false;
            $msg .= error(__('Please enter a nickname.'), true);
        }

        if ($request->has('mail') && strlen(strip_request_item('mail')) > 0) {
            $mail = strip_request_item('mail');
            if (!check_email($mail)) {
                $valid = false;
                $msg .= error(__('E-mail address is not correct.'), true);
            }
            if (User::whereEmail($mail)->first()) {
                $valid = false;
                $msg .= error(__('E-mail address is already used by another user.'), true);
            }
        } else {
            $valid = false;
            $msg .= error(__('Please enter your e-mail.'), true);
        }

        if ($request->has('email_shiftinfo')) {
            $email_shiftinfo = true;
        }

        if ($request->has('email_by_human_allowed')) {
            $email_by_human_allowed = true;
        }

        if ($enable_tshirt_size) {
            if ($request->has('tshirt_size') && isset($tshirt_sizes[$request->input('tshirt_size')])) {
                $tshirt_size = $request->input('tshirt_size');
            } else {
                $valid = false;
                $msg .= error(__('Please select your shirt size.'), true);
            }
        }

        if ($request->has('password') && strlen($request->postData('password')) >= $min_password_length) {
            if ($request->postData('password') != $request->postData('password2')) {
                $valid = false;
                $msg .= error(__('Your passwords don\'t match.'), true);
            }
        } else {
            $valid = false;
            $msg .= error(sprintf(
                __('Your password is too short (please use at least %s characters).'),
                $min_password_length
            ), true);
        }

        if ($request->has('planned_arrival_date') && $enable_planned_arrival) {
            $tmp = parse_date('Y-m-d H:i', $request->input('planned_arrival_date') . ' 00:00');
            $result = User_validate_planned_arrival_date($tmp);
            $planned_arrival_date = $result->getValue();
            if (!$result->isValid()) {
                $valid = false;
                error(__('Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date.'));
            }
        } elseif ($enable_planned_arrival) {
            $valid = false;
            error(__('Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date.'));
        }

        $selected_angel_types = [];
        foreach (array_keys($angel_types) as $angel_type_id) {
            if ($request->has('angel_types_' . $angel_type_id)) {
                $selected_angel_types[] = $angel_type_id;
            }
        }

        if ($request->has('street') && strlen(User_validate_Nick($request->input('street'))) > 1) {
            $street = strip_request_item('street');
        } else {
            $valid = false;
            error(__('Please enter your full address.'));
        }

        if ($request->has('zip_code') && strlen(User_validate_Nick($request->input('zip_code'))) > 1) {
            $zip_code = strip_request_item('zip_code');
        } else {
            $valid = false;
            error(__('Please enter your full address.'));
        }

        if ($request->has('hometown') && strlen(User_validate_Nick($request->input('hometown'))) > 1) {
            $hometown = strip_request_item('hometown');
        } else {
            $valid = false;
            error(__('Please enter your full address.'));
        }

        if ($request->has('emergency_contact') && strlen(User_validate_Nick($request->input('emergency_contact'))) > 1) {
            $emergency_contact = strip_request_item('emergency_contact');
        } else {
            $valid = false;
            error(__('Please enter an emergency contact.'));
        }

        if ($request->has('emergency_contact_phone') && strlen(User_validate_Nick($request->input('emergency_contact_phone'))) > 1) {
            $emergency_contact_phone = strip_request_item('emergency_contact_phone');
        } else {
            $valid = false;
            error(__('Please enter an emergency contact.'));
        }

        if ($request->has('date_of_birth')) {
            $date_of_birth = parse_date('Y-m-d H:i', $request->input('date_of_birth') . ' 00:00');
        } else {
            $valid = false;
            error(__('Please enter your birthday.'));
        }


        if ($request->has('lastname') && strlen(User_validate_Nick($request->input('lastname'))) > 1) {
            $lastName = strip_request_item('lastname');
        } else {
            $valid = false;
            error(__('Please enter your last name.'));
        }
        if ($request->has('prename') && strlen(User_validate_Nick($request->input('prename'))) > 1) {
            $preName = strip_request_item('prename');
        } else {
            $valid = false;
            error(__('Please enter your first name.'));
        }
        if ($enable_dect && $request->has('dect')) {
            if (strlen(strip_request_item('dect')) <= 40) {
                $dect = strip_request_item('dect');
            } else {
                $valid = false;
                error(__('For dect numbers are only 40 digits allowed.'));
            }
        }
        if ($request->has('mobile')) {
            $mobile = strip_request_item('mobile');
        } else {
            $valid = false;
            error(__('Please enter your mobile number.'));
        }

        // Trivia
        if ($request->has('allergies')) {
            $allergies = strip_request_item_nl('allergies');
        }
        if ($request->has('medicines')) {
            $medicines = strip_request_item_nl('medicines');
        }

        if ($valid) {
            $user = new User([
                'name'          => $nick,
                'password'      => $password_hash,
                'email'         => $mail,
                'api_key'       => '',
                'last_login_at' => null,
            ]);
            $user->save();

            $contact = new Contact([
                'dect'   => $dect,
                'mobile' => $mobile,
                'hometown'                => $hometown,
                'street'                  => $street,
                'zip_code'                => $zip_code,
                'emergency_contact'       => $emergency_contact,
                'emergency_contact_phone' => $emergency_contact_phone,
            ]);
            $contact->user()
                ->associate($user)
                ->save();

            $personalData = new PersonalData([
                'first_name'           => $preName,
                'last_name'            => $lastName,
                'shirt_size'           => $tshirt_size,
                'planned_arrival_date' => $enable_planned_arrival ? Carbon::createFromTimestamp($planned_arrival_date) : null,
                'date_of_birth'        => Carbon::createFromTimestamp($date_of_birth),
                'allergies'            => $allergies,
                'medicines'            => $medicines,
            ]);
            $personalData->user()
                ->associate($user)
                ->save();

            $settings = new Settings([
                'language'        => $session->get('locale'),
                'theme'           => config('theme'),
                'email_human'     => $email_by_human_allowed,
                'email_shiftinfo' => $email_shiftinfo,
            ]);
            $settings->user()
                ->associate($user)
                ->save();

            $state = new State([]);
            if (config('autoarrive')) {
                $state->arrived = true;
                $state->arrival_date = new Carbon();
            }
            $state->user()
                ->associate($user)
                ->save();

            // Assign user-group and set password
            DB::insert('INSERT INTO `UserGroups` (`uid`, `group_id`) VALUES (?, -20)', [$user->id]);
            set_password($user->id, $request->postData('password'));

            // Assign angel-types
            $user_angel_types_info = [];
            foreach ($selected_angel_types as $selected_angel_type_id) {
                DB::insert(
                    'INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`, `supporter`) VALUES (?, ?, FALSE)',
                    [$user->id, $selected_angel_type_id]
                );
                $user_angel_types_info[] = $angel_types[$selected_angel_type_id];
            }

            engelsystem_log(
                'User ' . User_Nick_render($user, true)
                . ' signed up as: ' . join(', ', $user_angel_types_info)
            );
            success(__('Angel registration successful!'));

            // User is already logged in - that means a supporter has registered an angel. Return to register page.
            if ($authUser) {
                redirect(page_link_to('register'));
            }

            // If a welcome message is present, display registration success page.
            if ($message = $config->get('welcome_msg')) {
                return User_registration_success_view($message);
            }

            redirect(page_link_to('/'));
        }
    }

    $buildup_start_date = time();
    $teardown_end_date = null;
    if ($buildup = $config->get('buildup_start')) {
        /** @var Carbon $buildup */
        $buildup_start_date = $buildup->getTimestamp();
    }

    if ($teardown = $config->get('teardown_end')) {
        /** @var Carbon $teardown */
        $teardown_end_date = $teardown->getTimestamp();
    }

    return page_with_title(register_title(), [
        __('By completing this form you\'re registering as a NiSa-Con volunteer. This script will create you an account in the angel task scheduler.'),
        $msg,
        msg(),
        form([
            div('row', [
                div('col-md-6', [
                    div('row', [
                        div('col-sm-4', [
                            form_text('nick', __('Nick') . ' ' . entry_required(), $nick),
                            form_info('',
                                __('Use up to 23 letters, numbers, connecting punctuations or spaces for your nickname.'))
                        ]),
                        div('col-sm-8', [
                            form_email('mail', __('E-Mail') . ' ' . entry_required(), $mail),
                            form_checkbox(
                                'email_shiftinfo',
                                __(
                                    'The %s is allowed to send me an email (e.g. when my shifts change)',
                                    [config('app_name')]
                                ),
                                $email_shiftinfo
                            ),
                            form_checkbox(
                                'email_by_human_allowed',
                                __('Humans are allowed to send me an email (e.g. for ticket vouchers)'),
                                $email_by_human_allowed
                            )
                        ])
                    ]),
                    div('row', [
                        $enable_planned_arrival ? div('col-sm-6', [
                            form_date(
                                'planned_arrival_date',
                                __('Planned date of arrival') . ' ' . entry_required(),
                                $planned_arrival_date, $buildup_start_date, $teardown_end_date
                            )
                        ]) : '',
                        div('col-sm-6', [
                            $enable_tshirt_size ? form_select('tshirt_size',
                                __('Shirt size') . ' ' . entry_required(),
                                $tshirt_sizes, $tshirt_size, __('Please select...')) : ''
                        ])
                    ]),
                    div('row', [
                        div('col-sm-6', [
                            form_password('password', __('Password') . ' ' . entry_required())
                        ]),
                        div('col-sm-6', [
                            form_password('password2', __('Confirm password') . ' ' . entry_required())
                        ])
                    ]),
                    form_checkboxes(
                        'angel_types',
                        __('What do you want to do?') . sprintf(
                            ' (<a href="%s">%s</a>)',
                            page_link_to('angeltypes', ['action' => 'about']),
                            __('Description of job types')
                        ),
                        $angel_types,
                        $selected_angel_types
                    ),
                    form_info(
                        '',
                        __('Restricted angel types need will be confirmed later by a supporter. You can change your selection in the options section.')
                    )
                ]),
                div('col-md-6', [
                    div('row', [
                        $enable_dect ? div('col-sm-4', [
                            form_text('dect', __('DECT'), $dect)
                        ]) : '',
                        div($enable_dect ? 'col-sm-4' : 'col-sm-12', [
                            form_text('mobile', __('Mobile'), $mobile)
                        ]),
                    ]),
                    div('row', [
                        div('col-sm-6', [
                            form_text('prename', __('First name') . ' ' . entry_required(), $preName)
                        ]),
                        div('col-sm-6', [
                            form_text('lastname', __('Last name') . ' ' . entry_required(), $lastName)
                        ])
                    ]),
                    div('row', [
                        div('col-sm-12', [
                            form_text('street', __('Street + Nr.') . ' ' . entry_required(), $street)
                        ]),
                    ]),
                    div('row', [
                        div('col-sm-3', [
                            form_text('zip_code', __('Zip Code') . ' ' . entry_required(), $zip_code)
                        ]),
                        div('col-sm-9', [
                            form_text('hometown', __('Town') . ' ' . entry_required(), $hometown)
                        ]),
                    ]),
                    div('row', [
                        div('col-sm-6', [
                            form_textarea('allergies', __('Allergies'), $allergies)
                        ]),
                        div('col-sm-6', [
                            form_textarea('medicines', __('Medicines'), $medicines)
                        ]),
                    ]),
                    form_info(__("Emergency contact")),
                    div('row', [
                        div('col-sm-12', [
                            form_text('emergency_contact', __('Name') . ' ' . entry_required(), $emergency_contact)
                        ]),
                        div( 'col-sm-12', [
                            form_text('emergency_contact_phone', __('Phone') . ' ' . entry_required(), $emergency_contact_phone)
                        ]),
                    ]),
                    form_info(entry_required() . ' = ' . __('Entry required!'))
                ])
            ]),
            form_submit('submit', __('Register'))
        ])
    ]);
}

/**
 * @return string
 */
function entry_required()
{
    return '<span class="text-info glyphicon glyphicon-warning-sign"></span>';
}

/**
 * @return string
 */
function guest_login()
{
    $nick = '';
    $request = request();
    $session = session();
    $valid = true;

    $session->remove('uid');

    if ($request->hasPostData('submit')) {
        if ($request->has('nick') && !empty($request->input('nick'))) {
            $nickValidation = User_validate_Nick($request->input('nick'));
            $nick = $nickValidation->getValue();
            /** @var User $login_user */
            $login_user = User::whereName($nickValidation->getValue())->first();
            if ($login_user) {
                if ($request->has('password')) {
                    if (!verify_password($request->postData('password'), $login_user->password, $login_user->id)) {
                        $valid = false;
                        error(__('Your password is incorrect.  Please try it again.'));
                    }
                } else {
                    $valid = false;
                    error(__('Please enter a password.'));
                }

                if (!$login_user->state->unlocked) {
                    $valid = false;
                    error(__('Your account is currently locked, before you can login to the system you must be unlocked by one of our admins.'));
                }
            } else {
                $valid = false;
                error(__('No user was found with that Nickname. Please try again. If you are still having problems, ask a Dispatcher.'));
            }

        } else {
            $valid = false;
            error(__('Please enter a nickname.'));
        }

        if ($valid && $login_user) {
            $session->set('uid', $login_user->id);
            $session->set('locale', $login_user->settings->language);

            redirect(page_link_to(config('home_site')));
        }
    }

    return page([
        div('col-md-12', [
            div('row', [
                EventConfig_countdown_page()
            ]),
            div('row', [
                div('col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4', [
                    div('panel panel-primary first', [
                        div('panel-heading', [
                            '<span class="icon-icon_angel"></span> ' . __('Login')
                        ]),
                        div('panel-body', [
                            msg(),
                            form([
                                form_text_placeholder('nick', __('Nick'), $nick),
                                form_password_placeholder('password', __('Password')),
                                form_submit('submit', __('Login')),
                                !$valid ? buttons([
                                    button(page_link_to('user_password_recovery'), __('I forgot my password'))
                                ]) : ''
                            ])
                        ]),
                        div('panel-footer', [
                            glyph('info-sign') . __('Please note: You have to activate cookies!')
                        ])
                    ])
                ])
            ]),
            div('row', [
                div('col-sm-6 text-center', [
                    heading(register_title(), 2),
                    get_register_hint()
                ]),
                div('col-sm-6 text-center', [
                    heading(__('What can I do?'), 2),
                    '<p>' . __('Please read about the jobs you can do to help us.') . '</p>',
                    buttons([
                        button(
                            page_link_to('angeltypes', ['action' => 'about']),
                            __('Teams/Job description') . ' &raquo;'
                        )
                    ])
                ])
            ])
        ])
    ]);
}

/**
 * @return string
 */
function get_register_hint()
{
    if (auth()->can('register') && config('registration_enabled')) {
        return join('', [
            '<p>' . __('Please sign up, if you want to help us!') . '</p>',
            buttons([
                button(page_link_to('register'), register_title() . ' &raquo;')
            ])
        ]);
    }

    return error(__('Registration is disabled.'), true);
}
