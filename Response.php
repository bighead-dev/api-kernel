<?php

namespace Kern;

class Response
{
    const TYPE_JSON = 1;

    public $status = [
        'success' => true,
    ];
    public $data   = [];
    public $type   = self::TYPE_JSON;
    
    /*
     * Construct a new response in error or success
     * Succesful responses are populated with nothing or
     * an array of data.
     * Error responses are populated with an error string
     */
    public function __construct($data = null, $is_error = false)
    {
        if ($is_error == 'err') {
            return $this->error($data);
        }
    
        if (is_null($data)) {
            return;
        }
        
        $this->data = $data;
    }
    
    public function set_type($type)
    {
        $this->type = $type;
    }
    
    public function to_json()
    {
        return json_encode(
            [
                'resp_status'    => $this->status,
            ] + $this->data,
            JSON_PRETTY_PRINT
        );
    }
    
    public function is_success()
    {
        return $this->status['success'];
    }
    
    public function error($error = '')
    {
        $this->status['success'] = false;
        $this->status['error']   = $error;
    }
    
    public function __toString()
    {
        switch ($this->type)
        {
            case self::TYPE_JSON:
                header('Content-type: application/json');
                return $this->to_json();
            default:
                throw new Exception('Response has an unkown type.');
        }
    }
}
