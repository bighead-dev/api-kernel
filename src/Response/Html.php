<?php

namespace Kern\Response;

use Kern\iResponse;

class Html implements iResponse
{
    public $data;
    
    /*
     * Construct a new response in error or success
     * Succesful responses are populated with nothing or
     * an array of data.
     * Error responses are populated with an error string
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function __toString()
    {
        return $this->data;
    }
}
