<?php

namespace Kern;

class Dispatcher
{
    public static $disp = null;
    
    private function __construct()
    {
       
    }
    
    public static function instance()
    {
        if (self::$disp == null) {
            self::$disp = new Dispatcher();
        }
        return self::$disp;
    }
        
    public static function dispatch(Request $req)
    {            
        $class = $req->route->class;
        $model = new $class();

        if ($model instanceof Routable == false) {
            throw new Exception("Request is not an instance of Routable");
        }
        
        $model->setRequest($req);
        
        $res = $model->{$req->route->method}();
        
        if ($res) {
            return $res;
        }
        
        return null;
    }
}
