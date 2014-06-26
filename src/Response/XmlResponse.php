<?php

namespace Kern\Response;

use SimpleXMLElement;
use Kern\iResponse;

class XmlResponse implements iResponse
{
    public $status = [
        'success' => true,
    ];
    public $data   = [];
    
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
    
    public function to_xml()
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><response></response>");
        $arr = [
            'resp_status'   => $this->status,
        ] + $this->data;
        
        self::array_to_xml($arr, $xml);
                
        return $xml->asXML();
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
        header('Content-type: application/xml');
        return $this->to_xml();
    }
    
    public static function array_to_xml($arr, &$xml)
    {
        foreach ($arr as $key => $value)
        {
            if (is_array($value) || is_object($value))
            {
                if (!is_numeric($key))
                {
                    $subnode = $xml->addChild("$key");
                    self::array_to_xml($value, $subnode);
                }
                else
                {
                    $subnode = $xml->addChild("item$key");
                    self::array_to_xml($value, $subnode);
                }
            }
            else {
                $xml->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}

