<?php
namespace LazyRecord\DSN;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

/**
 * Data object for DSN information
 *
 * getHost(), getPort(), getDBName() methods are used by MySQL and PostgreSQL
 *
 */
class DSN implements ArrayAccess, IteratorAggregate
{
    protected $driver;

    protected $attributes;

    protected $arguments;

    /**
     * The original DSN string
     */
    protected $originalDSN;

    public function __construct($driver, array $attributes = array(), array $arguments = array(), $originalDSN = null)
    {
        $this->driver = $driver;
        $this->attributes = $attributes;
        $this->arguments = $arguments;
        $this->originalDSN = $originalDSN;
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    public function __get($key)
    {
        return $this->attributes[$key];
    }

    public function __toString()
    {
        if ($this->dsn) {
            return $this->dsn;
        }
        return $this->driver . ':' . $this->getAttributeString();
    }

    public function getAttributeString()
    {
        $attrstrs = [];
        foreach ($this->attributes as $key => $val) {
            $attrstrs[] = $key . '=' . $val;
        }
        return join(';',$attrstrs);
    }
    
    public function offsetSet($key,$value)
    {
        $this->attributes[ $key ] = $value;
    }
    
    public function offsetExists($key)
    {
        return isset($this->attributes[ $key ]);
    }
    
    public function offsetGet($key)
    {
        return $this->attributes[ $key ];
    }
    
    public function offsetUnset($key)
    {
        unset($this->attributes[$key]);
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->attributes);
    }


    public function getArguments()
    {
        return $this->arguments;
    }

    public function getHost()
    {
        if (isset($this->attributes['host'])) {
            return $this->attributes['host'];
        }
    }

    public function getPort()
    {
        if (isset($this->attributes['port'])) {
            return $this->attributes['port'];
        }
    }

    public function getDBName()
    {
        if (isset($this->attributes['dbname'])) {
            return $this->attributes['dbname'];
        }
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getOriginalDSN()
    {
        return $this->originalDSN;
    }

}



