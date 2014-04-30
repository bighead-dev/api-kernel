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
        $resp = new Response();
            
        $class = $req->class;
        $model = new $class($req);

        if ($model instanceof Model == false) {
            return false;
        }
        
        $res = $model->{$req->method}($resp);
        
        if ($res) {
            $resp = $res;
        }
        
        return $resp;
    }
}
