<?php

namespace model\DynamoDb;
/**
 * Class User
 *
 * @property string user_id
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
class UserModel extends PhoORM {
    protected $_table_name = 'user';
    protected $_hash_key = 'user_id';

    protected $_schema = [
        'user_id'                       => 'S',
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
                'attribute_hash' => 'user_name',
                'type' =>'INCLUDE',
                'non_key_attributes' => ['user_provider_type']
            ],
            'idx_user_email' => [
                'attribute_hash' => 'user_email',
                'type' =>'INCLUDE',
                'non_key_attributes' => ['user_provider_type']
            ],
            'idx_user_provider_type' => [
                'attribute_hash' => 'user_provider_type',
                'type' =>'KEYS_ONLY',
            ],
        ];
    }

    /** {@inheritdoc}
     * @todo It's need to understand how exactly indexes are working in DynamoDB
     */
    protected function getLocalSecondaryIndexesKeys() {
        return [];
    }

    /**
     * Getting user by user_name and user_provider_type
     *
     * @param $userName string
     * @param null|string $providerType
     *
     * @return UserModel|null
     */
    public function getByUserName($userName, $providerType = null) {
        $this->resetConditions();
        $this->where('user_name', 'EQ', $userName);
        $this->index('idx_user_name');
        $user = $this->findFirst(['Limit' => 1]);
        if(
            $user
            && !is_null($providerType)
            && $user->user_provider_type != $providerType
        ) {
            return null;
        }

        return $user;
    }

    /**
     * Getting user by user_email and user_provider_type
     *
     * @param $userEmail string
     * @param null|string $providerType
     *
     * @return UserModel|null
     */
    public function getByUserEmail($userEmail, $providerType = null) {
        $this->resetConditions();
        $this->where('user_email', 'EQ', $userEmail);
        $this->index('idx_user_email');
        $user = $this->findFirst(['Limit' => 1]);
        if(
            $user
            && !is_null($providerType)
            && $user->user_provider_type != $providerType
        ) {
            return null;
        }

        return $user;
    }
}
