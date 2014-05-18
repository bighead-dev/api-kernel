<?php

/* simple function for making requests */
function kern_make_request($endpoint, $req_method, $data = [])
{
    $req = Kern\Router::create_request($endpoint, $req_method, $data);
    
    if (!$req) {
        return null;
    }
    
    return Kern\Dispatcher::dispatch($req);
}
