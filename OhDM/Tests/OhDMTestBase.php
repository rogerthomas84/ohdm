<?php
namespace OhDM\Tests;

use OhDM\Config;
use OhDM\Collection;

class OhDMTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $options (optional)
     * @return boolean
     */
    protected function initConfig(array $options = array())
    {
        Config::destroyInstance();
        if (!empty($options)) {
            return Config::init(
                    new \MongoClient(
                        'mongodb://127.0.0.1',
                        $options
                    ),
                    'ohdm-tests'
            );
        }

        return Config::init(
            new \MongoClient(
                'mongodb://127.0.0.1',
                array('connect' => false)
            ),
            'ohdm-tests'
        );
    }

    /**
     * Drop a collection by the object
     * @param Collection $object
     * @return multitype:
     */
    protected function dropCollectionByObject(Collection $object)
    {
        return $object->getConnection()->drop();
    }
}
