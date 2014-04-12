![Travis](https://travis-ci.org/rogerthomas84/ohdm.png)

[View on Packagist](https://packagist.org/packages/rogerthomas84/ohdm)

OhDM
====

OhDM is a an ODM which is a simple PHP library that eases the pain of interacting with MongoDB.


Stability
---------

OhDM is a new project, so some things won't work as expected. It's classified right now as in the early stages, and there
is a version 1.0.0 which we're classing as stable.

Saying that, things will be introduced to help enhance the library further but it'll be versioned accordingly.


Help Out
--------

The easiest way to help is by forking and raising a PR. All I ask is that you contribute changes back to this repository.

Changes won't be merged unless you've tested (and written tests) for the changes made. We also strive for high test coverage.

Right now, we're sitting at 99.33% coverage, and we would really like to stay around there.


Quick Start
-----------

To get started quickly, first you're going to need to setup the configuration object. It's a singleton.

```php
<?php
Config::init(
    new \MongoClient(
        'mongodb://127.0.0.1',
        array('connect' => false)
    ),
    'pets' // The database name
);
```

Now you'll need a Collection model. Something like this:

```php
<?php
namespace My\Namespace\Name;

use OhDM\Collection;

class Cats extends Collection
{
    public $name = null;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

```

Introduction to Queries
-----------------------


### Create a document

```php
<?php
$record = new Cats();
$record->setName('fuzzy');
$record->save();
```

### Find By ID

The quickest form of find is when you know an objects `_id`. OhDM exploits this and implements a quick and easy way to locate them.

```php
<?php
$record = Cats::findById(
    new \MongoId('53466f2978f7a8bc5c1ae933')
);
if ($record) {
    // $record is instance of Cats
    echo 'My cats name is: ' . $record->getName();
} else {
    // couldn't find it!
}
```

### Find By Query

OhDM supports finding documents by advanced querying which will also return the item in object form.
Spoiler alert: it's as easy as it looks below!

```php
<?php
$command = array(
    'query' => array('name' => 'kitty'),
    'fields' => array(),
    'skip' => 0,
    'limit' => 100,
    'sort' => array(
        'name' => 1
    )
);
$ohDmCursor = Cats::find($command);
while ($ohDmCursor->hasNext()) {
    $thisCat = $ohDmCursor->getNext(); // it's an instance of Cats
}
```

### Delete

Delete can be called on any saved document and it will be removed from the database.

```php
<?php
$record = Cats::findById(
    new \MongoId('53466f2978f7a8bc5c1ae933')
);
$record->delete();
```

### Update

As it's an object, just alter the variables and call `save()` against it.

```php
<?php
$record = Cats::findById(
    new \MongoId('53466f2978f7a8bc5c1ae933')
);
if ($record) {
    // $record is instance of Cats
    echo 'My cats name was: ' . $record->getName();

    $record->setName('kitty');
    $record->save();

    echo 'My cats name is now: ' . $record->getName();

} else {
    // couldn't find it!
}
```

