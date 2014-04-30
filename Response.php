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
    
    
    public function set_type($type)
    {
        $this->type = $type;
    }
    
    public function to_json()
    {
        return json_encode(
            [
                'resp_status'    => $this->status,
            ] + $this->data;
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
                throw new Exception('Request has an unkown type.');
        }
    }
}
