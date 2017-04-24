<?php

namespace Maghead\Sharding;

use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;
use Maghead\Sharding\Hasher\FastHasher;
use Maghead\Sharding\Hasher\Hasher;
use Ramsey\Uuid\Uuid;
use SQLBuilder\Universal\Query\UUIDQuery;

class ShardCollection implements ArrayAccess, IteratorAggregate
{
    protected $shards;

    protected $mapping;

    protected $repoClass;

    public function __construct(array $shards, ShardMapping $mapping = null, $repoClass = null)
    {
        $this->shards = $shards;
        $this->mapping = $mapping;
        $this->repoClass = $repoClass;
    }


    /**
     * A simple UUID generator base on Ramsey's implementation.
     *
     * The reason that we define this method here is that:
     *
     * SQLBuilder\Universal\UUIDQuery needs the db connection to get the UUID
     * generated by database.
     */
    public function generateUUID()
    {
        // See https://github.com/ramsey/uuid/wiki/Ramsey%5CUuid-Cookbook
        if ($keyGenerator = $this->mapping->getKeyGenerator()) {
            switch ($keyGenerator) {
                case "uuid-v1":
                    return Uuid::uuid1();
                case "uuid-v4":
                    return Uuid::uuid4();
                    /*
                case "uuid-v3":
                    return Uuid::uuid3(Uuid::NAMESPACE_DNS, 'php.net');
                case "uuid-v5":
                    return Uuid::uuid5(Uuid::NAMESPACE_DNS, 'php.net');
                     */
            }
        }
        return Uuid::uuid4();
    }


    protected function queryUUID()
    {
        $shardId = array_rand($this->shards);
        $shard = $this->shards[$shardId];
        return $shard->queryUUID();
    }



    public function getMapping()
    {
        return $this->mapping;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->shards);
    }
    
    public function offsetSet($name, $value)
    {
        $this->shards[ $name ] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->shards[ $name ]);
    }
    
    public function offsetGet($name)
    {
        return $this->shards[ $name ];
    }
    
    public function offsetUnset($name)
    {
        unset($this->shards[$name]);
    }

    public function dispatch($key, Hasher $hasher = null)
    {
        $dispatcher = $this->createDispatcher($hasher);
        return $dispatcher->dispatch($key);
    }

    public function createDispatcher(Hasher $hasher = null)
    {
        return new ShardDispatcher($this->mapping, $this, $hasher);
    }

    public function __call($method, $args)
    {
        $results = [];
        foreach ($this->shards as $shardId => $shard) {
            $repo = $shard->createRepo($this->repoClass);
            $results[$shardId] = call_user_func_array([$repo, $method], $args);
        }
        return $results;
    }

    public function first($callback)
    {
        foreach ($this->shards as $shardId => $shard) {
            $repo = $shard->createRepo($this->repoClass);
            if ($ret = $callback($repo, $shard)) {
                return $ret;
            }
        }
        return null;
    }

    /**
     * locateBy method locates the shard by the given callback.
     *
     * the shard will be returned if the callback return true
     *
     * @return Maghead\Sharding\Shard
     */
    public function locateBy($callback)
    {
        foreach ($this->shards as $shardId => $shard) {
            $repo = $shard->createRepo($this->repoClass);
            if ($callback($repo, $shard)) {
                return $shard;
            }
        }
        return null;
    }


    /**
     * Map an operation over the repository on each shard.
     *
     * This method runs the operation in sync mode.
     *
     * shardsMap returns the result of each shard. the returned value can be
     * anything.
     *
     * @return array mapResults
     */
    public function map($callback)
    {
        $mapResults = [];
        foreach ($this->shards as $shardId => $shard) {
            $repo = $shard->createRepo($this->repoClass);
            $mapResults[$shardId] = $callback($repo, $shard);
        }
        return $mapResults;
    }

    /**
     * Route a function call to a shard by using the given shard key.
     *
     * Locate a shard by the sharding key, and execute the callback.
     *
     * @return mixed result.
     */
    public function locateAndExecute($shardKey, $callback)
    {
        $dispatcher = $this->createDispatcher();
        $shard = $dispatcher->dispatch($shardKey);
        $repo = $shard->createRepo($this->repoClass);
        return $callback($repo, $shard);
    }
}
