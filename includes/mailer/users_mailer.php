<?php

use Engelsystem\Models\User\User;

/**
 * @param User $user
 * @return bool
 */
function mail_user_delete($user)
{
    return engelsystem_email_to_user(
        $user,
        __('Your account has been deleted'),
        __(
            'Your %s account has been deleted. If you have any questions regarding your account deletion, please contact heaven.',
            [config('app_name')]
        )
    );
}

function mail_user_unlocked($user)
{
    return engelsystem_email_to_user(
        $user,
        __('Your account has been unlocked'),
        __(
            'Your %s account has been unlocked. You can now start using your account.',
            [config('app_name')]
        )
    );
}

function mail_user_locked($user)
{
    return engelsystem_email_to_user(
        $user,
        __('Your account has been locked'),
        __(
            'Your %s account has been locked. If you have any questions regarding your account locking, please contact us.'
        )
    );
}
