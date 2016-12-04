<?php

/**
 * Handles all data manipulation of the admin part
 */
class AdminModel
{
    /**
     * Sets the deletion and suspension values
     *
     * @param $suspensionInDays
     * @param $softDelete
     * @param $userId
     */
    public static function setAccountSuspensionAndDeletionStatus($suspensionInDays, $softDelete, $userId)
    {

        // Prevent to suspend or delete own account.
        // If admin suspend or delete own account will not be able to do any action.
        if ($userId == Session::get('user_id')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CANT_DELETE_SUSPEND_OWN'));
            return false;
        }

        if ($suspensionInDays > 0) {
            $suspensionTime = time() + ($suspensionInDays * 60 * 60 * 24);
        } else {
            $suspensionTime = null;
        }

        // FYI "on" is what a checkbox delivers by default when submitted. Didn't know that for a long time :)
        if ($softDelete == "on") {
            $delete = 1;
        } else {
            $delete = 0;
        }

        // write the above info to the database
        self::writeDeleteAndSuspensionInfoToDatabase($userId, $suspensionTime, $delete);

        // if suspension or deletion should happen, then also kick user out of the application instantly by resetting
        // the user's session :)
        if ($suspensionTime != null OR $delete = 1) {
            self::resetUserSession($userId);
        }
    }

    /**
     * Simply write the deletion and suspension info for the user into the database, also puts feedback into session
     *
     * @param $userId
     * @param $suspensionTime
     * @param $delete
     * @return bool
     */
    private static function writeDeleteAndSuspensionInfoToDatabase($userId, $suspensionTime, $delete)
    {
        /** @var \model\DynamoDb\User $user */
        $user = \Kettle\ORM::factory(model\DynamoDb\User::class)->findOne($userId);
        if(is_null($user)) {
            return false;
        }
        $user->user_suspension_timestamp = $suspensionTime;
        $user->user_deleted = $delete;

        if ($user->save()) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUSPENSION_DELETION_STATUS'));
            return true;
        }
        return false;
    }

    /**
     * Kicks the selected user out of the system instantly by resetting the user's session.
     * This means, the user will be "logged out".
     *
     * @param $userId
     * @return bool
     */
    private static function resetUserSession($userId)
    {
        /** @var \model\DynamoDb\User $user */
        $user = \Kettle\ORM::factory(model\DynamoDb\User::class)->findOne($userId);

        if(is_null($user)) {
            return false;
        }

        $user->session_id = null;

        if ($user->save()) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_USER_SUCCESSFULLY_KICKED'));
            return true;
        }
        return false;
    }
}
