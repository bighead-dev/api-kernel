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
        $model = new $class($req);

        if ($model instanceof Model == false) {
            return false;
        }
        
        $res = $model->{$req->route->method}($resp);
        
        if ($res) {
            return $res;
        }
        
        return null;
    }
}
