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

	/**
	 * Checking of existance if the model table and creating it if it's need
	 */
	protected function createTableIfNeed() {
		// If table exists do nothing
		if (in_array($this->getTableName(), $this->getClient()->listTables()->get('TableNames'))) {
			return;
		}
		$keySchema = [
			[ // Required HASH type attribute
				'AttributeName' => $this->getHashKey(),
				'KeyType'       => 'HASH',
			]
		];
		if($this->getRangeKey()) {
			$keySchema[] = [ // Optional RANGE key type for HASH + RANGE tables
				'AttributeName' => $this->getRangeKey(),
				'KeyType'       => 'RANGE',
			];

		}
		$params = [
			'TableName'              => $this->getTableName(),
			'KeySchema'              => $keySchema,
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
			'KeySchema'             => $keySchema,
			'Projection'            => [ // required
				'ProjectionType'   => 'INCLUDE', // (ALL | KEYS_ONLY | INCLUDE)
				'NonKeyAttributes' => array_keys($this->_schema),
			],
			'ProvisionedThroughput' => [ // throughput to provision to the index
				'ReadCapacityUnits'  => 1,
				'WriteCapacityUnits' => 1,
			],

		];
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