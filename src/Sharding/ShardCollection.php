<?php

namespace Maghead\Sharding;

use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;
use Maghead\Sharding\Hasher\FlexihashHasher;
use Maghead\Sharding\Hasher\Hasher;
use Ramsey\Uuid\Uuid;

class ShardCollection implements ArrayAccess, IteratorAggregate
{
    protected $shards;

    protected $mapping;

    public function __construct(array $shards, ShardMapping $mapping = null)
    {
        $this->shards = $shards;
        $this->mapping = $mapping;
    }


    /**
     * A simple UUID generator base on Ramsey's implementation.
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

    public function getMapping()
    {
        return $this->mapping;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->shards);
    }
    
    public function offsetSet($name,$value)
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

    public function createDispatcher(Hasher $hasher = null)
    {
        if (!$hasher) {
            $hasher = new FlexihashHasher($this->mapping);
        }
        return new ShardDispatcher($this->mapping, $hasher, $this);
    }
}
