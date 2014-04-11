<?php
namespace OhDM;

class Collection
{
    /**
     * @var \MongoId|null
     */
    public $_id = null;

    /**
     * Final method for future constructor logic.
     */
    final public function __construct()
    {
        $this->init();
    }

    /**
     * Called in the __construct, this can be overriden.
     */
    public function init()
    {
    }

    /**
     * Get the source (collection) name
     * @return string
     */
    public function getSource()
    {
        $fqcn = get_class($this);
        $pieces = explode('\\', $fqcn);
        return $this->cleanSource($pieces[(count($pieces) - 1)]);
    }

    /**
     * Retrieve a single document
     * @param \MongoId|string $id
     * @param array $fields
     * @return Collection|boolean false
     */
    public static function findById($id, array $fields = array())
    {
        $id = $id instanceof \MongoId ? $id : new \MongoId($id);
        $fqcn = get_called_class();
        $instance = new $fqcn();
        $connection = $instance->getConnection();
        /* @var $connection \MongoCollection */
        $result = $connection->findOne(
            array('_id' => $id),
            $fields
        );

        if ($result) {
            $instance->populate($result);

            return $instance;
        }

        return false;
    }

    /**
     * Populate the values from an existing array
     * @param array $result
     * @return void
     */
    final public function populate(array $result)
    {
        foreach ($result as $key => $values) {
            if (property_exists($this, $key)) {
                $this->$key = $values;
            }
        }

        return;
    }

    /**
     * Find items from the collection and retrieve the Cursor object
     * @param array $command
     * @return \OhDM\Cursor|boolean false
     */
    public static function find(array $command = array())
    {
        $query = array();
        if (array_key_exists('query', $command)) {
            $query = $command['query'];
            if (!is_array($query)) {
                return false;
            }
        }
        $fields = array();
        if (array_key_exists('fields', $command)) {
            $fields = $command['fields'];
            if (!is_array($fields)) {
                return false;
            }
        }
        $sort = array();
        if (array_key_exists('sort', $command)) {
            $sort = $command['sort'];
            if (!is_array($sort)) {
                return false;
            }
        }
        $skip = null;
        if (array_key_exists('skip', $command)) {
            $skip = $command['skip'];
            if (!is_integer($skip)) {
                return false;
            }
        }
        $limit = null;
        if (array_key_exists('limit', $command)) {
            $limit = $command['limit'];
            if (!is_integer($limit)) {
                return false;
            }
        }

        $fqcn = get_called_class();
        $instance = new $fqcn();
        $connection = $instance->getConnection();
        /* @var $connection \MongoCollection */

        $cursor = $connection->find($query, $fields);
        if (!empty($sort)) {
            $cursor->sort($sort);
        }
        if (null != $skip) {
            $cursor->skip($skip);
        }
        if (null != $limit) {
            $cursor->limit($limit);
        }

        $ohDmCursor = new Cursor($cursor, $instance);

        return $ohDmCursor;
    }

    /**
     * Delete the current document
     * @param array $options
     * @return boolean|mixed
     * @todo this needs to change. It needs to be a boolean only return
     */
    final public function delete(array $options = array())
    {
        $collection = $this->getConnection();
        if (!$this->_id instanceof \MongoId) {
            return false;
        }

        if (!empty($options)) {
            return $collection->remove(
                array('_id' => $this->_id),
                $options
            );
        }

        return $collection->remove(
            array('_id' => $this->_id)
        );
    }

    /**
     * Save / Update the current document, specifying save / update options if required.
     * @param array $options
     * @return boolean|mixed
     * @todo this needs to change. It needs to be a boolean only return
     */
    final public function save(array $options = array())
    {
        $vals = get_object_vars($this);
        $collection = $this->getConnection();
        if (!$vals['_id'] instanceof \MongoId) {
            unset($vals['_id']);
            $this->preInsert();
            if (!empty($options)) {
                $collection->save($vals, $options);
            } else {
                $collection->save($vals);
            }
            if (array_key_exists('_id', $vals)) {
                $this->_id = $vals['_id'];
                $this->postInsert();
                return true;
            }

            return false;
        }

        $updateVals = $vals;
        unset($updateVals['_id']);

        $this->preUpdate();
        if (!empty($options)) {
            $update = $collection->update(
                array('_id' => $vals['_id']),
                array('$set' => $updateVals),
                $options
            );
            $this->postUpdate();

            return $update;
        }

        $update = $collection->update(
            array('_id' => $vals['_id']),
            array('$set' => $updateVals)
        );
        $this->postUpdate();

        return $update;
    }

    /**
     * Get a \MongoCollection connection for this source
     * @return \MongoCollection
     */
    final public function getConnection()
    {
        $config = Config::getInstance();
        return $config->mongo->selectCollection(
            $config->dbName,
            $this->getSource()
        );
    }

    /**
     * Retrieve a normalised name for a collection from a string
     * @param string $name
     * @return string
     */
    final public function cleanSource($name)
    {
        $name = preg_replace('/[^a-zA-Z]/', '', $name);
        $name = preg_replace('/[A-Z]/', '_$0', $name);
        $name = trim($name, '_');
        $name = strtolower($name);

        return $name;
    }

    /**
     * Set the ID
     * @param \MongoId|string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id instanceof \MongoId ? $id : new \MongoId($id);

        return $this;
    }

    /**
     * Get the document ID, or null if not set.
     * @return \MongoId|null
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Called before inserting a new document
     */
    protected function preInsert()
    {
    }

    /**
     * Called after inserting a new document
     */
    protected function postInsert()
    {
    }

    /**
     * Called before updating an existing document
     */
    protected function preUpdate()
    {
    }

    /**
     * Called after updating an existing document
     */
    protected function postUpdate()
    {
    }
}
