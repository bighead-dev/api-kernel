<?php

namespace Kern;

class Response
{
    public $status = [
        'success' => true,
    ];
    public $data   = [];
    
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
}
