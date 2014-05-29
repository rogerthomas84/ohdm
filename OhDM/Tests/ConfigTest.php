<?php
namespace OhDM\Tests;

use OhDM\Config;

class ConfigTest extends OhDMTestBase
{
    public function testConfigConstructionInitialisationWhenValidReturnsTrue()
    {
        $config = $this->initConfig();
        $this->assertTrue($config);
        $this->assertInstanceOf('\OhDM\Config', Config::getInstance());
    }

    public function testGettingConfigInstanceThrowsExceptionWhenInitIsNotCalledFirst()
    {
        try {
            Config::destroyInstance();
            Config::getInstance();
        } catch (\Exception $e) {
            $this->assertInstanceOf('\OhDM\Exception\ConfigException', $e);
            return;
        }
        $this->fail('expected exception');
    }

    public function testConfigStoresNotConnectedMongoClientWhenOptionsArePassed()
    {
        $config = $this->initConfig(array('connect' => false));
        $this->assertFalse(
            @Config::getInstance()->mongo->connected
        );
    }
}
