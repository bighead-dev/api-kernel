<?php

namespace Kern;

class Request
{
    public $class;
    public $method;
    public $uri;
    
    const DELETE = 1;
	const PUT    = 2;
	const POST   = 4;
	const GET    = 8;
    
    public $req_type;
    public $data;
    
    private $is_json_req;
    
    const DEFAULT_METHOD = 'index';
    
    public function __construct($uri = '', $req_method = '', $data = null)
    {
        $this->uri = $uri;
        $this->class = str_replace(
            ['-', ' '],
            ['_', '\\'],
            ucwords(str_replace('/', ' ', $uri))
        );
		
		if (($pos = strpos($this->class, '.')))
		{
			$this->method = str_replace('-', '_', substr($this->class, $pos + 1));
			$this->class = substr($this->class, 0, $pos);
		}
		else {
			$this->method = self::DEFAULT_METHOD; /* default */
		}
		
		$this->set_uri($uri);
		$this->set_request_type($req_method);
		$this->set_req_data($data);
    }
    
    public function is_valid()
    {
        $valid = false;
        $erp = error_reporting(E_ALL & (~E_NOTICE & ~E_WARNING));

		if (
		    strlen($this->class) &&
		    strlen($this->method) &&
		    class_exists($this->class) &&
		    method_exists($this->class, $this->method)
		   ) {
			$valid = true;
		}
		
		error_reporting($erp);
		return $valid;
    }
    
    public function set_request_type($type)
    {
        $type = strtoupper(($type) ?: $this->get_request_method());
        
        switch ($type)
        {
            case 'GET':
                $this->req_type = self::GET;
                break;
            case 'POST':
                $this->req_type = self::POST;
                break;
            case 'PUT':
                $this->req_type = self::PUT;
                break;
            case 'DELETE':
                $this->req_type = self::DELETE;
                break;
            default:
                throw new Exception('invalid request method');
        }        
    }
    
    public function set_uri($uri)
    {
        $this->uri = ($uri) ?: $_SERVER['REQUEST_URI'];
    }
    
    protected function get_request_method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    protected function set_req_data($data)
    {
        if ($data)
        {
            $this->data = $data;
            return;
        }
        
        switch ($this->req_type)
        {
            case self::GET:
                $this->data = $_GET;
                return;
            case self::POST:
                if (!$this->is_json_req())
                {
                    $this->data = $_POST;
                    return;
                }
            case self::DELETE:
            case self::PUT:
                if (!$this->is_json_req())
                {
                    $this->data = null;
                    return;
                }
                
                /* todo - properly validate */
                $this->data = json_decode(file_get_contents("php://input"), true);
        }
    }
    
    public function is_post() {
        return $this->req_type === self::POST;
    }
    public function is_get() {
        return $this->req_type === self::GET;
    }
    public function is_put() {
        return $this->req_type === self::PUT;
    }
    public function is_delete() {
        return $this->req_type === self::DELETE;
    }
    
    public function set_route($class, $method)
    {
        $this->class    = $class;
        $this->method   = $method;
    }
    
    public function is_json_req()
    {
        if ($this->is_json_req !== null) {
            return $this->is_json_req;
        }
        
        $ct = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        $this->is_json_req = strpos($ct, 'json') !== false;
        return $this->is_json_req;
    }
}
