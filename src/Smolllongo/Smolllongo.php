<?php
namespace Smolllongo;

class Smollongo
{
    /**
     * @var \MongoClient
     */
    protected $_connection;
    /**
     * @var \MongoDB
     */
    protected $_db;
    /**
     * @var MongoCollection[]
     */
    protected $_collections = [];

    public function getParams()
    {
        return ['host'=>'localhost', 'database' => 'test', 'port'=>27017];
    }

    public function getConnectionString()
    {
        return 'mongodb://' . $this->getParams()['host'] . ':' . $this->getParams()['port'];
    }

    /**
     * @return \Mongo
     */
    public function getConnection()
    {
        if (is_null($this->_connection)) {
            $this->_connection = new \MongoClient($this->getConnectionString());
        }
        return $this->_connection;
    }

    /**
     * @return \MongoDB
     */
    public function getDb()
    {
        if (is_null($this->_db)) {
            $this->_db = $this->getConnection()->selectDB($this->getParams()['database']);
        }
        return $this->_db;
    }

    /**
     * @param $name name collection
     * @return MongoCollection
     */
    public function getCollection($name)
    {
        if (!is_null($this->_collections)) {
            $this->_collections = [];
        }
        if (!isset($this->_collections[$name])) {
            $this->_collections[$name] = $this->getDb()->selectCollection($name);
        }
        return $this->_collections[$name];
    }

    /**
     * @return callable
     */
    public function getFiletrDataFunction()
    {
        $filter = function (&$value/*, $key*/) {
            if ($value instanceof \DateTime) {
                $value = new \MongoDate($value->format('U'));
            }
        };
        return $filter;
    }

    public function filterData(array $data)
    {
        array_walk_recursive($data, $this->getFiletrDataFunction());
        return $data;
    }

    /**
     * @param \MongoCollection|string $collection
     * @param array $data
     */
    public function insert($collection, array $data)
    {
        if (!$collection instanceof \MongoCollection) {
            $collection = $this->getCollection($collection);
        }
        $data = $this->filterData($data);
        return $collection->insert($data);
    }
}