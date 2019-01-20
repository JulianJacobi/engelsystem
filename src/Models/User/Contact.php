<?php

namespace Engelsystem\Models\User;

/**
 * @property string|null $dect
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $hometown
 * @property string|null $street
 * @property string|null $zip_code
 * @property string|null $emergency_contact
 * @property string|null $emergency_contact_phone
 *
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereDect($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereMobile($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereHometown($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereStreet($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereZipCode($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereEmergencyContact($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereEmergencyContactPhone($value)
 */
class Contact extends HasUserModel
{
    /** @var string The table associated with the model */
    protected $table = 'users_contact';

    /** The attributes that are mass assignable */
    protected $fillable = [
        'user_id',
        'dect',
        'email',
        'mobile',
        'hometown',
        'street',
        'zip_code',
        'emergency_contact',
        'emergency_contact_phone',
    ];
}
