<?php
namespace OhDM\Tests\TestCollections;

use OhDM\Collection;

class FooBar extends Collection
{
    public $name = 'foobar';

    public function init()
    {
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
