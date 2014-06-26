<?php

/* simple function for making requests */
function kern_make_request($endpoint, $req_method, $data = [], $headers = null)
{
    $req = Kern\Router::create_request($endpoint, $req_method, $data, $headers);
    
    if (!$req) {
        return null;
    }
    
    return Kern\Dispatcher::dispatch($req);
}

function kern_db($new_instance = null)
{
    static $db = null;
    
    if ($new_instance || !$db)
    {
        $cfg = Kern\Config::get('db');
        $tmp_db = new PDO($cfg['dsn'], $cfg['user'], $cfg['pass']);
        
        if ($new_instance) {
            return $tmp_db;
        }
    
        if (!$db) {
            $db = $tmp_db;
        }
    }
    
    return $db;
}

function kern_env($env = null)
{
    static $kern_env;
    
    if (!$env && !$kern_env) {
        throw new Kern\Exception("Kern Environment hasn't been set");
    }
    
    if ($env) {
        $kern_env = $env;
    }
    else
    {
        return $kern_env;
    }
}
