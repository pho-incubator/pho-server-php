<?php

namespace model\DynamoDb;
/**
 * Class User
 *
 * @property string note_id
 * @property string note_text
 * @property string user_id
 *
 * @package model\DynamoDb
 */
class Note extends PhoORM {
	protected $_table_name = 'note';
	protected $_hash_key = 'note_id';

	protected $_schema = [
		'note_id'   => 'S',
		'note_text' => 'S',
		'user_id'   => 'S',
	];

	/** {@inheritdoc} */
	protected function getGlobalSecondaryIndexKeys() {
		return [
			'idx_user_id'          => [
				'attribute_hash'     => 'user_id',
				'type'               => 'INCLUDE',
				'non_key_attributes' => ['note_text'],
			],
		];
	}

	/** {@inheritdoc} */
	protected function getLocalSecondaryIndexesKeys() {
		return [];
	}
}