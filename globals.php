<?php

use Kern;

function kern_make_request($endpoint, $req_method, $data = [])
{
    $req = Router::create_request($endpoint, $req_method, $data);
    
    if (!$req) {
        return false;
    }
    
    return Dispatcher::dispatch($req);
}

/*
 * The main entry point into the api. Every request from the browser
 * runs through the following code
 */
function kern_api_entry_point()
{
    if (!Router::is_api_url($_SERVER['REQUEST_URI'])) {
        return;
    }

    $resp = kern_make_request($_SERVER['REQUEST_URI'], '', null);
    
    if (!$resp) {
        $resp = new Response();
        $resp->error("Invalid endpoint: '" . $uri . "'");
    }
    
    if ($resp instanceof Response == false) {
        throw new Exception('An invalid response was returned from the API');
    }
    
    echo $resp;
    exit();
}
