<?php

namespace Kern;

use Closure;
use Util;

class Route
{
    const SIMPLE = 0x1;
    const REGEX  = 0x2;
    const DEFAULT_METHOD = 'index';

    public $type;
    public $key;
    public $val;
    
    public $class;
    public $method;
        
    public function __construct($key, $val, $type = self::SIMPLE)
    {
        $this->key  = $key;
        $this->val  = $val;
        $this->type = $type;
    }
    
    public is_simple()
    {
        return $this->type & self::SIMPLE;
    }
    
    public function set_class_and_method_from_request(Request $req)
    {
        $this->class  = '';
        $this->method = self::DEFAULT_METHOD;
        
        if ($this->val instanceof Closure) {
            $this->val($req, $this->class, $this->method);
        }
        else if (is_string($this->val)) {
            $this->get_class_and_method_from_string($this->class, $this->method);
        }
        else if (is_array($this->val)) {
            $this->get_class_and_method_from_array($this->class, $this->method);
        }
    }
    
    private function get_class_and_method_from_string(&$class, &$method)
    {
        if (strlen($this->val) == 0) {
            return;
        }
            
        /* find the method if there is one */
        $period_idx = strrpos($this->val, '.');
    
        if ($period_idx !== false)
        {
            $method = substr($this->val, $period_idx + 1);
            $path   = substr($this->val, 0, $period_idx);
        }
    
        $class = Util\String::convert_slahes_to_ns_class($path);
                
        /* replace dashes with underscores */
        if ($method) {
            $method = str_replace('-', '_', $method);
        }
        else {
            $method = self::DEFAULT_METHOD;
        }
    }
    
    private function get_class_and_method_from_array(&$class, &$method)
    {
        if (array_key_exists('class', $this->val)) {
            $class = $this->val['class'];
        }
        if (array_key_exists('method', $this->val)) {
            $method = $this->val['method'];
        }
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
}

class Router
{
    public static $rtr  = null;
    const DEFAULT_METHOD = 'index';
    
    protected $simple_routes = [];
    protected $regex_routes  = [];
    
    public $route_not_found_handler = function(Request $req)
    {
        throw new Exception('Route not found for this request: ' . print_r($req, true));
    };
    
    private function __construct()
    {
       
    }
    
    public static function instance()
    {
        if (self::$rtr == null)
            self::$rtr = new Router();
        
        return self::$rtr;
    }
        
    public function get_route_from_request(Request $req)
    {        
        $path   = parse_url($req->uri)['path'];

        /* get the path after the actual script name if there is one */
        $idx = strrpos($path, '.php');
        $idx = $idx === false ? 0 : $idx + 4; /* plus 4 because it's after the .php */
        
        $path = substr($path, $idx);
        
        /* check the simple routes first */
        if (array_key_exists($path, $this->simple_routes)) {
            return $this->simple_routes[$path];
        }
        
        /* check the regex routes */
        foreach ($this->regex_routes as $route)
        {
            if (preg_match('@' . preg_quote($route->key, '@') . '@', $path)) {
                return $route;
            }
        }
        
        return $this->route_not_found_handler();
    }
    
    public function is_endpoint_valid($class, $method)
    {
        
    }
    
    public function add_route(Route $route)
    {
        if ($route->is_simple()) {
            $this->simple_routes[$route->key] = $route;
        }
        else {
            $this->regex_routes[] = $route;
        }
    }
    
    private function _create_request($uri = '', $req_method = '', $data = null)
    {
        $req = new Request($uri, $req_method, $data);
		
		$class; $method;
		$route = $this->get_route_from_request($req, $class, $method);
        
        if (!$res || $route instanceof Route == false) {
            throw new Exception("Invalid route supplied");
        }
        
        $route->set_class_and_method_from_request($req);
        
        if (!$route->is_valid()) {
            return false;
        }
        
        $req->set_route($route);
        		
		return $req;
    }
    
    public static function create_request($uri = '', $req_method = '', $data = null)
    {    
		return self::instance()->_create_request($uri, $req_method, $data);
    }
}

