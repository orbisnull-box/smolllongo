<?php
namespace Smolllongo;

use \MongoClient;

class Connection
{

    protected $options = [];

    /**
     * @var \MongoClient
     */
    protected $mongo;

    /**
     * @var \MongoDB
     */
    protected $db;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
        $this->mongo = new MongoClient($this->getConnectUri(), $this->getConnectParams());
    }

    protected function setOptions(array $options)
    {
        $inputOptions = $options;
        $options['server'] = ['host'=>MongoClient::DEFAULT_HOST, 'port'=>MongoClient::DEFAULT_PORT];
        if (!empty($inputOptions['server'])) {
            $options['server'] = array_merge($options['server'], $inputOptions['server']);
        }
        $options['params'] = ['connect' => false];
        if (!empty($inputOptions['params'])) {
            $options['params'] = array_merge($options['server'], $inputOptions['params']);
        }
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->mongo->connect();
    }
    /**
     * @return \Mongo
     */
    public function getMongoClient()
    {
        return $this->mongo;
    }

    public function getConnectUri()
    {
        $auth = '';
        $server = $this->getOptions()['server'];

        if (!is_null($server['username']) and !is_null($server['password'])) {
            $auth = $server['username'] . ':' . $server['password'] . '@';
        }
        $uri = 'mongodb://' . $auth . $server['host'] . ':' .$server['port'];
        return $uri;
    }

    public function getConnectParams()
    {
        return (array) $this->getOptions()['params'];
    }
}