<?php

namespace Kern;

class Config
{
    const ENV_DEV   = 'dev';
    const ENV_STG   = 'stg';
    const ENV_PRD   = 'prd';

    public static $cfg = null;
    public static $cfg_path = './application/config';
    public static $env = '';

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
    
    public static function setEnv($env)
    {
        self::set_env($env);
    }
    
    public static function set_env($env)
    {
        self::$env = $env;
    }
    
    public static function get($cfg_file = 'cfg', $ext = 'ini')
    {
        $cfg = self::instance();
        
        if (isset($cfg->data[$cfg_file]))
            return $cfg->data[$cfg_file];
        
        $env = (self::$env) ? self::$env . '/' : '';
        
        switch ($ext)
        {
            case 'ini':
                $cfg->data[$cfg_file] = parse_ini_file(self::$cfg_path .'/'. $env . $cfg_file . '.ini', true);
                break;
            case 'php':
                $cfg->data[$cfg_file] = require(self::$cfg_path . '/' . $env . $cfg_file . '.php');
        }
        
        
        return $cfg->data[$cfg_file];
    }
}
