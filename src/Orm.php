<?php

namespace Kern;

/* interface for allowing an object to be saved to a database */
interface OrmSerializable {
    public function orm_data_to_serialize(); /* returns the data to be serialized */
}

interface OrmDriver {
    public function query($model);
    public function get(Orm\Query $query);
    public function save($o);
    public function delete(&$o);
    public function register($param);
}

class Orm
{
    public $driver;    
    public static $instance;
    
    public static function instance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        
        self::$instance = new Orm();
        
        return self::$instance;
    }
    
    public static function driver(OrmDriver $driver)
    {
        self::instance()->driver = $driver;
    }
    
    public static function query($model)
    {
        return self::instance()->driver->query($model);
    }
    
    public static function get(Orm\Query $query)
    {
        return self::instance()->driver->get($query);
    }
    
    public static function save($data)
    {
        self::instance()->driver->save($data);
    }
    
    public static function delete(&$data)
    {
        self::instance()->driver->delete($data);
    }
    
    public static function register($param)
    {
        self::instance()->driver->register($param);
    }
}
