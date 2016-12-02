<?php

/**
 * Class DatabaseFactory
 *
 * Use it like this:
 * $database = DatabaseFactory::getFactory()->getConnection();
 *
 * That's my personal favourite when creating a database connection.
 * It's a slightly modified version of Jon Raphaelson's excellent answer on StackOverflow:
 * http://stackoverflow.com/questions/130878/global-or-singleton-for-database-connection
 *
 * Full quote from the answer:
 *
 * "Then, in 6 months when your app is super famous and getting dugg and slashdotted and you decide you need more than
 * a single connection, all you have to do is implement some pooling in the getConnection() method. Or if you decide
 * that you want a wrapper that implements SQL logging, you can pass a PDO subclass. Or if you decide you want a new
 * connection on every invocation, you can do do that. It's flexible, instead of rigid."
 *
 * Thanks! Big up, mate!
 */
class DatabaseFactory
{
    private static $factory;
    private $database;

    public static function getFactory()
    {
        if (!self::$factory) {
            self::$factory = new DatabaseFactory();
        }
        return self::$factory;
    }

    public function getConnection() {
        switch(Config::get('DB_TYPE')) {
            case 'mysql':
                return $this->getPDOConnection();
                break;
            case 'dynamodb':
                $this->getDynamoDBConnection();
                break;
            default:
                $this->exitConnectionDoNotAvailable(null, 'Unknown Database type:' . Config::get('DB_MYSQL_TYPE') . '.<br> Now supported dynamodb or mysql');
        }
    }

    /**
     * @return PDO
     *
     * Check DB connection in try/catch block. Also when PDO is not constructed properly,
     * prevent to exposing database host, username and password in plain text as:
     * PDO->__construct('mysql:host=127....', 'root', '12345678', Array)
     * by throwing custom error message
     */
    private function getPDOConnection() {
        if (!$this->database) {
            try {
                $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);
                $this->database = new PDO(
                    'mysql:host=' . Config::get('DB_MYSQL_HOST') . ';dbname=' .
                    Config::get('DB_MYSQL_NAME') . ';port=' . Config::get('DB_MYSQL_PORT') . ';charset=' . Config::get('DB_MYSQL_CHARSET'),
                    Config::get('DB_MYSQL_USER'), Config::get('DB_MYSQL_PASS'),
                    $options
                );
            } catch (PDOException $e) {
                $this->exitConnectionDoNotAvailable($e);
            }
        }
        return $this->database;
    }

    private function getDynamoDBConnection() {
        try {
            putenv('HOME=/home/vagrant');
            \Kettle\ORM::configure("profile", 'vagrant');
            \Kettle\ORM::configure("version", Config::get('DB_DYNAMODB_AWS_VERSION'));
            \Kettle\ORM::configure("region", Config::get('DB_DYNAMODB_AWS_REGION'));
            if(Config::get('DB_DYNAMODB_IS_LOCAL')) {
                \Kettle\ORM::configure(
                    "base_url",
                    'http://' . Config::get('DB_DYNAMODB_LOCAL_HOST') . ':' . Config::get('DB_DYNAMODB_LOCAL_PORT') . '/'
                );
                \Kettle\ORM::configure(
                    "endpoint",
                    'http://' . Config::get('DB_DYNAMODB_LOCAL_HOST') . ':' . Config::get('DB_DYNAMODB_LOCAL_PORT') . '/'
                );
            }
            else {
                \Kettle\ORM::configure("key", Config::get('DB_DYNAMODB_AWS_KEY'));
                \Kettle\ORM::configure("secret", Config::get('DB_DYNAMODB_AWS_SECRET'));
            }
        } catch(\Exception $e) {
            $this->exitConnectionDoNotAvailable($e);
        }
    }

    private function exitConnectionDoNotAvailable(\Exception $e = null, $message = null) {
        // Echo custom message. Echo error code gives you some info.
        echo 'Database connection can not be estabilished. Please try again later.' . '<br>';

        if($e) {
            echo 'Error code: ' . $e->getCode() . '<br>';
            echo $e->getMessage();
        }
        elseif($message) {
            echo $message;
        }

        // Stop application :(
        // No connection, reached limit connections etc. so no point to keep it running
        exit(1);

    }
}
