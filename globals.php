<?php

use Kern;

function kern_make_request($endpoint, $req_method, $data = [])
{
    $req = Router::create_request($endpoint, $req_method, $data);
    return Dispatcher::dispatch($req);
}
