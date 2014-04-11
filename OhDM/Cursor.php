<?php
namespace OhDM;

use OhDM\Collection;

class Cursor
{
    /**
     * @var \MongoCursor|null
     */
    private $cursor;

    /**
     * @var integer
     */
    private $count = 0;

    /**
     * @var integer
     */
    private $position = 0;

    /**
     * @var Collection|null
     */
    private $collection = null;

    /**
     * Construct by passing in the \MongoCursor and the object to populate
     * @param \MongoCursor $cursor
     */
    public function __construct(\MongoCursor $cursor, Collection $collection)
    {
        $this->cursor = $cursor;
        $this->count = $cursor->count(true);
        $this->position = 0;
        $this->collection = $collection;
    }

    /**
     * Get the count of items in the result set
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Whether there is a next result.
     * @return boolean
     */
    public function hasNext()
    {
        return $this->cursor->hasNext();
    }

    /**
     * Retrieve the collection object (as provided by x::find())
     * @return Collection
     */
    public function getNext()
    {
        $item = $this->cursor->getNext();
        $obj = clone $this->collection;
        $obj->populate($item);

        return $obj;
    }

    /**
     * Retrieve the \MongoCursor
     * @return \MongoCursor|null
     */
    public function getRawCursor()
    {
        return $this->cursor;
    }
}
