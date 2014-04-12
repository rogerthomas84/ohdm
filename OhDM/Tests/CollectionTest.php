<?php
namespace OhDM\Tests;

use OhDM\Config;
use OhDM\Collection;
use OhDM\Tests\TestCollections\FooBar;

class CollectionTest extends OhDMTestBase
{
    public function testBasicMethods()
    {
        $collection = new Collection();
        $this->assertEquals('collection', $collection->getSource());
    }

    public function testCleanSourceName()
    {
        $sources = array(
            'Foo' => 'foo',
            'foo' => 'foo',
            'Barfoo' => 'barfoo',
            'Bar_foo' => 'barfoo',
            'Bar_Foo' => 'bar_foo',
            'BarFoo' => 'bar_foo',
        );
        $collection = new Collection();
        foreach ($sources as $k => $v) {
            $this->assertEquals($v, $collection->cleanSource($k));
        }
    }

    public function testBasicMethodsOfExtendedCollection()
    {
        $collection = new FooBar();
        $this->assertEquals('foo_bar', $collection->getSource());
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

    public function testMongoConnectsAfterInitialisedAndNotConnectedByDefault()
    {
        $config = $this->initConfig(array('connect' => false));
        $this->assertFalse(
            Config::getInstance()->mongo->connected
        );
        $collection = new FooBar();
        $this->assertEquals('foo_bar', $collection->getSource());
        $collection->save();
        $collection->delete();
        $this->assertTrue(
            Config::getInstance()->mongo->connected
        );
    }

    public function testSavingCollectionWorksAsExpected()
    {
        $this->initConfig();
        $collection = new FooBar();
        $this->assertEquals('foo_bar', $collection->getSource());
        $collection->save();
        $this->assertInstanceOf('\MongoId', $collection->_id);
        $collectionTwo = clone $collection;
        $collectionTwo->name = 'bar';
        $collectionTwo->save();
        $this->assertEquals($collection->_id, $collectionTwo->_id);
        $this->assertSavedDocumentIsFound($collection->_id);
        $this->assertSavedDocumentIsFound($collectionTwo->_id);

        $collection->delete();
        $collectionTwo->delete();
    }

    public function testSavingCollectionWorksAsExpectedWhenOptionsAreSpecified()
    {
        $this->initConfig();
        $collection = new FooBar();
        $collection->save(array('w' => 1));
        $this->assertInstanceOf('\MongoId', $collection->_id);
        $collectionTwo = clone $collection;
        $collectionTwo->name = 'bar';
        $collectionTwo->save(array('w' => 1));
        $this->assertEquals($collection->_id, $collectionTwo->_id);
        $this->assertSavedDocumentIsFound($collection->_id);
        $this->assertSavedDocumentIsFound($collectionTwo->_id);

        $collection->delete();
        $collectionTwo->delete();
    }

    public function testDeletionWorksAsExpected()
    {
        $this->initConfig();
        $collection = new FooBar();
        $collection->save();
        $collection->delete();

        $collection = new FooBar();
        $collection->save();
        $collection->delete(array('justOne' => true));

        $collection = new FooBar(); // not saved.
        $this->assertFalse($collection->delete());
    }

    public function testGetAndSetIdWorkAsExpected()
    {
        $id = new \MongoId();
        $instance = new FooBar();
        $instance->setId($id);
        $this->assertEquals($id->__toString(), $instance->getId()->__toString());

        $idTwo = new \MongoId();
        $instance->setId($idTwo);
        $this->assertEquals($idTwo->__toString(), $instance->getId()->__toString());
    }

    public function assertSavedDocumentIsFound(\MongoId $id)
    {
        $result = FooBar::findById($id);
        $this->assertInstanceOf('\OhDM\Tests\TestCollections\FooBar', $result);
    }
}
