<?php
namespace OhDM;

use OhDM\Exception\ConfigException;
class Config
{
    /**
     * @var \MongoClient|null
     */
    public $mongo = null;

    /**
     * @var string
     */
    public $dbName = 'test';

    /**
     * @var Config|null
     */
    protected static $instance = null;

    /**
     * Protected constructor.
     * @param \MongoClient $db
     * @param string $dbName
     */
    protected function __construct(\MongoClient $mongo, $dbName)
    {
        $this->mongo = $mongo;
        $this->dbName = $dbName;
    }

    /**
     * Initialise the configuration
     * @param \MongoClient $db
     * @param string $dbName
     * @return boolean
     */
    public static function init(\MongoClient $mongo, $dbName)
    {
        static::$instance = new self($mongo, $dbName);

        return true;
    }

    /**
     * Destroy the configuration
     * @return void
     */
    public static function destroyInstance()
    {
        if (static::$instance instanceof Config && static::$instance->mongo instanceof \MongoClient) {
            static::$instance->mongo->close();
        }
        static::$instance = null;

        return true;
    }

    /**
     * Get the instance of Config
     * @throws \Exception if init is not called first
     * @return \OhDM\Config
     */
    public static function getInstance()
    {
        if (!static::$instance instanceof Config) {
            throw new ConfigException('Call ::init prior to get instance.');
        }

        return static::$instance;
    }
}
