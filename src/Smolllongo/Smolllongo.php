<?php
namespace Smolllongo;

class Smolllongo
{

    const CONNECTION_DEFAULT = 'default';

    /**
     * @var Connection[]
     */
    protected $connections = [];

    /**
     * @var \MongoDB
     */
    protected $databases = [];

    /**
     * @var \MongoCollection[]
     */
    protected $collections = [];


    protected $conOptions = [];



    public function setConOptions(array $options, $conName = self::CONNECTION_DEFAULT)
    {
        $this->conOptions[$conName] = $options;
    }

    public function getConOptions($conName)
    {
        if (!isset($this->conOptions[$conName])) {
            return null;
        }
        return $this->conOptions[$conName];
    }

    public function getOptDbName($conName)
    {
        $options = $this->getConOptions($conName);
        if (is_null($options) or !isset($options['dbName'])) {
            throw new Exception('Db name not set in options for ' . $conName);
        }
        return $options['dbName'];
    }

    public function getConnection($conName = self::CONNECTION_DEFAULT, array $options = null)
    {
        if (!isset($this->connections[$conName])) {
            if (!is_null($options)) {
                $this->setConOptions($conName, $options);
            }
            $this->connections[$conName] = $this->createConnection($conName);
        }
        return $this->connections[$conName];
    }

    public function getMongoClient($conName = self::CONNECTION_DEFAULT, array $options = null)
    {
        return $this->getConnection($conName, $options)->getMongoClient();
    }

    public function createConnection($conName = self::CONNECTION_DEFAULT)
    {
        $options  = $this->getConOptions($conName);
        if (is_null($options) and $conName!==self::CONNECTION_DEFAULT) {
            throw new Exception('Not set options for connection ' . $conName);
        }
        return new Connection($options);
    }

    public function closeConnectionDbs($conName)
    {
        if (isset($this->databases[$conName])) {
            foreach ($this->databases[$conName] as $dbName=>$db) {
                $this->closeDbCollections($dbName, $conName);
            }
            unset($this->databases[$conName]);
        }
    }

    public function closeDbCollections($dbName, $conName)
    {
        if (isset($this->collections[$conName][$dbName])) {
            unset($this->collections[$conName][$dbName]);
        }
    }

    /**
     * @param null $dbName
     * @param string $conName
     * @return \MongoDB
     */
    public function getDb($dbName = null, $conName = self::CONNECTION_DEFAULT)
    {
        if (is_null($dbName)) {
            $dbName = $this->getOptDbName($conName);
        }

        if (!isset($this->databases[$conName][$dbName])) {
            $this->databases[$conName][$dbName] = $this->getMongoClient($conName)->selectDB($dbName);
        }
        return $this->databases[$conName][$dbName];
    }

    /**
     * @param $collectName
     * @param null $dbName
     * @param string $conName
     * @return \MongoCollection
     */
    public function getCollection($collectName, $dbName = null, $conName = self::CONNECTION_DEFAULT)
    {
        if (is_null($dbName)) {
            $dbName = $this->getOptDbName($conName);
        }

        if (!isset($this->collections[$conName][$dbName][$collectName])) {
            $this->collections[$conName][$dbName][$collectName] = $this->getDb($dbName, $conName)->selectCollection($collectName);
        }
        return $this->collections[$conName][$dbName][$collectName];
    }
}
