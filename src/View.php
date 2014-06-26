<?php

namespace Kern;

class View
{
    public static $cfg;
    
    public $view_file       = '';
    public $view_data       = [];
    public $view_ouput      = '';
    public $view_children   = [];
    
    public $parent = null;
    
    public function __construct(View $parent = null)
    {
        if (!self::$cfg) {
            throw new Exception('Config for Kern\View has not been set');
        }
    
        $this->parent = $parent;
    }
    
    public static function setCfg($cfg)
    {
        self::set_cfg($cfg);
    }
    public static function set_cfg($cfg)
    {
        self::$cfg = $cfg;
    }
    
    public function setParent(View $parent)
    {
        return $this->set_parent($parent);
    }
    public function set_parent(View $parent)
    {
        $this->parent = $parent;
        return $this;
    }
    public function add_child(View $child)
    {
        $this->view_children[] = $child;
        $child->set_parent($this);
    }
    
    public function setFile($file)
    {
        return $this->set_file($file);
    }
    public function set_file($file)
    {
        $this->view_file = $file;
        return $this;
    }
    
    public function setData($data)
    {
        return $this->set_data($data);
    }
    public function set_data($data)
    {
        $this->view_data = $data;
        return $this;
    }
    
    public function __get($name)
    {
        if (array_key_exists($name, $this->view_data)) {
            return $this->view_data[$name];
        }
        
        return null;
    }
    
    public function __set($name, $val)
    {
        $this->view_data[$name] = $val;
    }
    
    public function build($file = '', $data = null)
    {
        if ($file == '') {
            $file = $this->view_file;
        }
        if ($data) {
            $this->setData($data);
        }
    
        $file = self::$cfg['view_path'] . $file . '.php';
        
        if (!file_exists($file)) {
            throw new Exception(sprintf("View file '%s' does not exist", $file));
        }
        
        ob_start();
        
        include $file;
                
        $this->view_output = ob_get_clean();
        return $this;
    }
    
    public function output()
    {
        echo $this->view_output;
        return $this;
    }
    
    public function clear()
    {
        unset($this->view_output);
        $this->view_output = '';
        return $this;
    }
    
    public function __toString()
    {
        return $this->view_output;
    }
    
    public function render($file = '', $data = null)
    {
        $this->build($file, $data);
        return $this->__toString();
    }
    
    public static function renderFile($file, $data = [])
    {
        return self::render_file($file, $data);
    }
    public static function render_file($file, $data = [])
    {
        $cls = get_called_class();
        $v = new $cls();
        
        return $v->set_file($file)->set_data($data)->render();
    }
    
    
    public static function renderResponse($file, $data = [])
    {
        return self::render_response($file, $data);
    }
    public static function render_response($file, $data = [])
    {
        return new Kern\Response\HtmlResponse(static::renderFile($file, $data));
    }
}
