<?php

namespace model\DynamoDb;
/**
 * Class User
 *
 * @property integer user_id
 * @property integer session_id
 * @property string user_password_hash
 * @property string user_email
 * @property string user_name
 * @property integer user_active
 * @property boolean user_deleted
 * @property integer user_account_type
 * @property boolean user_has_avatar
 * @property integer user_remember_me_token
 * @property integer user_creation_timestamp
 * @property integer user_suspension_timestamp
 * @property integer user_last_login_timestamp
 * @property integer user_failed_logins
 * @property integer user_last_failed_login
 * @property string user_activation_hash
 * @property string user_password_reset_hash
 * @property integer user_password_reset_timestamp
 * @property string user_provider_type
 *
 * @package model\DynamoDb
 */
class User extends PhoORM {
    protected $_table_name = 'user';
    protected $_hash_key = 'user_id';

    protected $_schema = [
        'user_id'                       => 'N',
        'session_id'                    => 'N',
        'user_name'                     => 'S',
        'user_password_hash'            => 'S',
        'user_email'                    => 'S',
        'user_active'                   => 'N',
        'user_deleted'                  => 'N',
        'user_account_type'             => 'N',
        'user_has_avatar'               => 'N',
        'user_remember_me_token'        => 'N',
        'user_creation_timestamp'       => 'N',
        'user_suspension_timestamp'     => 'N',
        'user_last_login_timestamp'     => 'N',
        'user_failed_logins'            => 'N',
        'user_last_failed_login'        => 'N',
        'user_activation_hash'          => 'S',
        'user_password_reset_hash'      => 'S',
        'user_password_reset_timestamp' => 'N',
        'user_provider_type'            => 'S',
    ];

    /** {@inheritdoc} */
    protected function getGlobalSecondaryIndexKeys() {
        return [
            'idx_user_name' => [
                'attribute' => 'user_name',
                'type' =>'INCLUDE',
            ],
        ];
    }

    /** {@inheritdoc}
     * @todo It's need to understand how exactly indexes are working in DynamoDB
     */
    protected function getLocalSecondaryIndexesKeys() {
        return [];
    }
}