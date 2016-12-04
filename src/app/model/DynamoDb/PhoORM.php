<?php

namespace model\DynamoDb;


/**
 * Class PhoORM
 * @package model\DynamoDb
 *
 * Abstract class
 * All DynamoDB models should be inherited of this class
 */
abstract class PhoORM extends \Kettle\ORM {

	public function __construct() {
		$this->createTableIfNeed();
	}

	public function findByParams() {
		$this->findFirst();
	}

	/** {@inheritdoc} */
	public function save(array $options = []) {
		if(empty($this->{$this->_hash_key})) {
			$this->{$this->_hash_key} = $this->getUniqueId();
		}
		return parent::save($options);
	}

	/**
	 * Returns unique id for new row.
	 * If first return value of uniqid() exists in db recursive calls to itself
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function getUniqueId() {
		$uuid = uniqid();
		if($this->findOne($uuid)) {
			return $this->getUniqueId();
		}
		return $uuid;
	}

	/**
	 * Checking of existance if the model table and creating it if it's need
	 */
	protected function createTableIfNeed() {
		// If table exists do nothing
		if (in_array($this->getTableName(), $this->getClient()->listTables()->get('TableNames'))) {
			return;
		}
		$primaryKeySchema = [
			[ // Required HASH type attribute
				'AttributeName' => $this->getHashKey(),
				'KeyType'       => 'HASH',
			]
		];
		if($this->getRangeKey()) {
			$primaryKeySchema[] = [ // Optional RANGE key type for HASH + RANGE tables
				'AttributeName' => $this->getRangeKey(),
				'KeyType'       => 'RANGE',
			];
		}
		$params = [
			'TableName'              => $this->getTableName(),
			'KeySchema'              => $primaryKeySchema,
			'AttributeDefinitions'   => [],
			'ProvisionedThroughput'  => [ // required provisioned throughput for the table
				'ReadCapacityUnits'  => 1,
				'WriteCapacityUnits' => 1,
			],
			'GlobalSecondaryIndexes' => [], // optional (list of GlobalSecondaryIndex)
//			'LocalSecondaryIndexes'  => [], // optional (list of LocalSecondaryIndex)
		];

		$params['AttributeDefinitions'][] = [
			'AttributeName' => $this->getHashKey(),
			'AttributeType' => $this->_schema[$this->getHashKey()],
		];
		if($this->getRangeKey()) {
			$params['AttributeDefinitions'][] = [
				'AttributeName' => $this->getRangeKey(),
				'AttributeType' => $this->_schema[$this->getRangeKey()],
			];
		}

		$params['GlobalSecondaryIndexes'][] = [
			'IndexName'             => 'index_' . $this->getTableName(),
			'KeySchema'             => $primaryKeySchema,
			'Projection'            => [ // required
				'ProjectionType'   => 'ALL', // (ALL | KEYS_ONLY | INCLUDE)
//				'NonKeyAttributes' => array_keys($this->_schema),
			],
			'ProvisionedThroughput' => [ // throughput to provision to the index
				'ReadCapacityUnits'  => 1,
				'WriteCapacityUnits' => 1,
			],
		];
		foreach ($this->getGlobalSecondaryIndexKeys() as $indexKey => $index) {
			$params['AttributeDefinitions'][] = [
				'AttributeName' => $index['attribute_hash'],
				'AttributeType' => $this->_schema[$index['attribute_hash']],
			];

			$keySchema = [
				[ // Required HASH type attribute
					'AttributeName' => $index['attribute_hash'],
					'KeyType'       => 'HASH',
				]
			];
			if(isset($index['attribute_range'])) {
				$params['AttributeDefinitions'][] = [
					'AttributeName' => $index['attribute_range'],
					'AttributeType' => $this->_schema[$index['attribute_range']],
				];

				$keySchema[] = [ // Optional RANGE key type for HASH + RANGE tables
					'AttributeName' => $index['attribute_range'],
					'KeyType'       => 'RANGE',
				];
			}

			$globalSecondaryIndex = [
				'IndexName'             => $indexKey,
				'KeySchema'             => $keySchema,
				'Projection'            => [ // required
					'ProjectionType'   => $index['type'], // (ALL | KEYS_ONLY | INCLUDE)
				],
				'ProvisionedThroughput' => [ // throughput to provision to the index
					'ReadCapacityUnits'  => 1,
					'WriteCapacityUnits' => 1,
				],
			];
			if($index['type'] == 'INCLUDE') {
				if(empty($index['non_key_attributes'])) {
					echo "In model User index `{$indexKey}` should contain non_key_attributes because it's INCLUDE type";
					exit(1);
				}
				$globalSecondaryIndex['Projection']['NonKeyAttributes'] = $index['non_key_attributes'];
			}
			$params['GlobalSecondaryIndexes'][] = $globalSecondaryIndex;
		}
		//todo LocalSecondaryIndexes
		$this->getClient()->createTable($params);
	}

	/**
	 * Get local indexes for model
	 * @return array
	 */
	abstract protected function getLocalSecondaryIndexesKeys();

	/**
	 * Get global indexes for model
	 * @return array
	 */
	abstract protected function getGlobalSecondaryIndexKeys();
}