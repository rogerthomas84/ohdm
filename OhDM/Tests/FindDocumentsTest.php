<?php
namespace OhDM\Tests;

use OhDM\Tests\TestCollections\FooBar;
use OhDM\Tests\TestCollections\AlphabeticalCollection;

class FindDocumentsTest extends OhDMTestBase
{
    public function testBasicCreateThenSave()
    {
        $this->initConfig();
        $collection = new FooBar();
        $this->assertEquals('foo_bar', $collection->getSource());
        $collection->save();
        $this->assertInstanceOf('\MongoId', $collection->_id);
        $collectionTwo = clone $collection;
        $collectionTwo->name = md5(microtime(true));
        $collectionTwo->save();
        $this->assertEquals($collection->_id, $collectionTwo->_id);
        $this->assertSavedDocumentIsFound($collection->_id);
        $this->assertSavedDocumentIsFound($collectionTwo->_id);
        $collection->delete();
        $collectionTwo->delete();
    }

    public function testFindByInvalidIdFindsNothing()
    {
        $this->assertFalse(FooBar::findById(new \MongoId()));
    }

    public function testInvalidFindArrayReturnsFalse()
    {
        $invalid = array();
        $invalid[] = array(
            'query' => 'foo' // should be an array
        );

        $invalid[] = array(
                'query' => array(),
                'fields' => 'foo' // should be an array
        );

        $invalid[] = array(
                'query' => array(),
                'fields' => array(),
                'skip' => 'foo' // should be an integer
        );

        $invalid[] = array(
                'query' => array(),
                'fields' => array(),
                'skip' => 1,
                'limit' => 'foo' // should be an integer
        );

        $invalid[] = array(
                'query' => array(),
                'fields' => array(),
                'skip' => 0,
                'limit' => 0,
                'sort' => 'foo' // should be an array
        );

        foreach ($invalid as $command) {
            $this->assertFalse(FooBar::find($command));
        }
    }

    public function testCreateThenFind()
    {
        // Start clean.
        $this->dropCollectionByObject(new FooBar());

        $item = new FooBar();
        $item->name = 'createandfind';
        $item->save();

        $command = array(
            'query' => array('name' => 'createandfind'),
            'fields' => array(),
            'skip' => 0,
            'limit' => 100,
            'sort' => array(
                'name' => 1
            )
        );

        $result = FooBar::find($command);
        $this->assertInstanceOf('\OhDM\Cursor', $result);
        $this->assertInstanceOf('\MongoCursor', $result->getRawCursor());
        $this->assertGreaterThan(0, $result->getCount());
        while ($result->hasNext()) {
            $obj = $result->getNext();
            $this->assertInstanceOf('\OhDM\Tests\TestCollections\FooBar', $obj);
            $this->assertInstanceOf('\MongoId', $obj->getId());
            $this->assertEquals('createandfind', $obj->name);
            $obj->delete();
        }
    }

    public function testCreateThenFindWithSort()
    {
        // Start clean.
        $this->dropCollectionByObject(new AlphabeticalCollection());

        $range = range('a', 'z');
        $originalRange = $range;
        rsort($range);
        foreach ($range as $alpha) {
            $item = new AlphabeticalCollection();
            $item->name = $alpha;
            $item->save();
        }

        $command = array(
            'sort' => array(
                'name' => 1
            )
        );

        $result = AlphabeticalCollection::find($command);
        $this->assertInstanceOf('\OhDM\Cursor', $result);
        $this->assertInstanceOf('\MongoCursor', $result->getRawCursor());
        $this->assertEquals(26, $result->getCount());
        $i = 0;
        while ($result->hasNext()) {
            $obj = $result->getNext();
            $this->assertEquals($originalRange[$i], $obj->name);
            $obj->delete();
            $i++;
        }
    }

    public function testCreateThenFindWithSkip()
    {
        // Start clean.
        $this->dropCollectionByObject(new AlphabeticalCollection());

        $range = range('a', 'z');
        $originalRange = $range;
        rsort($range);
        foreach ($range as $alpha) {
            $item = new AlphabeticalCollection();
            $item->name = $alpha;
            $item->save();
        }

        $command = array(
            'sort' => array(
                'name' => 1
            ),
            'skip' => 13,
            'limit' => 13,
        );

        $result = AlphabeticalCollection::find($command);
        $this->assertInstanceOf('\OhDM\Cursor', $result);
        $this->assertInstanceOf('\MongoCursor', $result->getRawCursor());
        $this->assertEquals(13, $result->getCount());
        $i = 13;
        while ($result->hasNext()) {
            $obj = $result->getNext();
            $this->assertEquals($originalRange[$i], $obj->name);
            $obj->delete();
            $i++;
        }
    }

    public function assertSavedDocumentIsFound(\MongoId $id)
    {
        $result = FooBar::findById($id);
        $this->assertInstanceOf('\OhDM\Tests\TestCollections\FooBar', $result);
    }
}
