<?php
// Temporary file for import data from mysql to dynamodb
require_once __DIR__ . '/../app/autoload.php';

$_SERVER['HTTP_HOST'] = 'localhost';

$connection = new PDO(
	'mysql:host=127.0.0.1;dbname=huge;port=3306;charset=utf8',
	'root', '12345678',
	[PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]
);

$query = $connection->prepare('SELECT * FROM users');
$query->execute();
DatabaseFactory::getFactory()->getConnection();
\Kettle\ORM::factory(model\DynamoDb\User::class)->getClient()->deleteTable(['TableName'=>'user']);
while ($user = $query->fetch(PDO::FETCH_ASSOC)) {
	$userModel = \Kettle\ORM::factory(model\DynamoDb\User::class)->create($user);
	$userModel->save();
}

