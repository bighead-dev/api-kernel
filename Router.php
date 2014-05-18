<?php

namespace Kern;

class Router
{
    public static $rtr  = null;
    const DEFAULT_METHOD = 'index';
    
    private function __construct()
    {
       
    }
    
    public static function instance()
    {
        if (self::$rtr == null)
            self::$rtr = new Router();
        
        return self::$rtr;
    }
        
    public function parse_uri_from_request(Request $req, &$class, &$method)
    {
        $class  = '';
        $method = '';
        $path   = parse_url($req->uri)['path'];

        /* get the path after the actual script name if there is one */
        $idx = strrpos($path, '.php');
        $idx = $idx === false ? 0 : $idx + 4; /* plus 4 because it's after the .php */
        
        $path = substr($path, $idx);
        
        if (strlen($path) == 0) {
            return false;
        }
        
        /* find the method if there is one */
        $period_idx = strrpos($path, '.');
        
        if ($period_idx !== false)
        {
            $method = substr($path, $period_idx + 1);
            $path   = substr($path, 0, $period_idx);
        }
        
        /* replace dashes with underscores, and turn a/url/like/this to A\Class\Name\Like\This */
        $class = str_replace(
            ['-', ' '],
            ['_', '\\'],
            ucwords(str_replace('/', ' ', $path))
        );
        
        /* make sure the class starts with a \ */
        if ($class[0] != '\\') {
            $class = '\\' . $class;
        }
        
        /* replace dashes with underscores */
        if ($method) {
            $method = str_replace('-', '_', $method);
        }
        else {
            $method = self::DEFAULT_METHOD;
        }
        
        return true;
    }
    
    public function is_route_valid($class, $method)
    {
        $valid = false;
        $erp = error_reporting(E_ALL & (~E_NOTICE & ~E_WARNING));
        
		if (
		    strlen($class) &&
		    strlen($method) &&
		    class_exists($class) &&
		    method_exists($class, $method)
		   ) {
			$valid = true;
		}
		
		error_reporting($erp);
		
		return $valid;
    }
    
    public static function is_api_url($uri)
    {
        $path = parse_url($uri)['path'];
        return strpos($path, '/apiv2') === 0;
    }
    
    private function _create_request($uri = '', $req_method = '', $data = null)
    {
        $req = new Request($uri, $req_method, $data);
		
		$class; $method;
		$res = $this->parse_uri_from_request($req, $class, $method);
        
        if (!$res) {
            return false;
        }
        
        if (!$this->is_route_valid($class, $method)) {
            return false;
        }
        
        $req->set_route($class, $method);
        		
		return $req;
    }
    
    public static function create_request($uri = '', $req_method = '', $data = null)
    {    
		return self::instance()->_create_request($uri, $req_method, $data);
    }
}

