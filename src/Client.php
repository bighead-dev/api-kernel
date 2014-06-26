<?php

namespace Kern;

/* simple client for making internal requests */

class Client
{
    public $headers = [];
    
    public function get($endpoint, $data = [])
    {
        return kern_make_request($endpoint, 'GET', $data, $this->headers);
    }
    public function post($endpoint, $data = [])
    {
        return kern_make_request($endpoint, 'POST', $data, $this->headers);
    }
    public function put($endpoint, $data = [])
    {
        return kern_make_request($endpoint, 'PUT', $data, $this->headers);
    }
    public function delete($endpoint, $data = [])
    {
        return kern_make_request($endpoint, 'DELETE', $data, $this->headers);
    }

    public function headers($headers)
    {
        $this->headers = $headers + $this->headers;
        return $this;
    }
    
    public function clear()
    {
        $this->headers = [];
        return $this;
    }
}
