<?php

namespace Kern;

class Config
{
    public static $cfg = null;
    public static $cfg_path = './application/config';

    public $data;
    
    private function __construct()
    {
        /* singleton */        
        $this->data = [];
    }
    
    public static function instance()
    {
        if (self::$cfg == null) {
            self::$cfg = new Config();
        }
        
        return self::$cfg;
    }
    
    public static function setCfgPath($cfg_path)
    {
        self::set_cfg_path($cfg_path);
    }
  
    public static function set_cfg_path($cfg_path)
    {
        self::$cfg_path = $cfg_path;
    }
        
    private static function get_cfg_file_path($file, $ext)
    {
        /* first check for an environment specific config file */
        $file_path = self::$cfg_path . '/' . kern_env() . '/' . $file . '.' . $ext;
        
        if (file_exists($file_path)) {
            return $file_path;
        }
        
        /* now, check for environment-agnostic config */
        $file_path = self::$cfg_path . '/' . $file . '.' . $ext;
        
        if (!file_exists($file_path)) {
            throw new Exception("No config file for $file found...");
        }
        
        return $file_path;
    }
    
    public static function get($cfg_file = 'cfg', $ext = 'ini')
    {
        $cfg = self::instance();
        
        if (isset($cfg->data[$cfg_file]))
            return $cfg->data[$cfg_file];
        
        $file_path = self::get_cfg_file_path($cfg_file, $ext);
        
        switch ($ext)
        {
            case 'ini':
                $cfg->data[$cfg_file] = parse_ini_file($file_path, true);
                break;
            case 'php':
                $cfg->data[$cfg_file] = require($file_path);
        }
        
        
        return $cfg->data[$cfg_file];
    }
}
