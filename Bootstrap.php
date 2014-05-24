<?php

namespace Kern;

/* load the loader */
require_once __DIR__ . '/Loader.php';

class Bootstrap
{
    private $callbacks = [];
    
    public function __construct($callbacks = [])
    {
        $this->callbacks = $callbacks;
    }
    
    /* set error reporting */
    private function set_erp()
    {
        switch (Config::$env)
        {
            case Config::ENV_DEV:
            case Config::ENV_STG:
                ini_set('display_errors', 1);
                error_reporting(E_ALL);
                break;
            case Config::ENV_PRD:
                error_reporting(0);
                ini_set('display_errors', 0);
                break;
            default:
                throw new Exception('Environment not set correctly');
        }
    }
    
    private function run_callback($key)
    {
        switch ($key)
        {
            case 'env':
                return array_key_exists('env', $this->callbacks) ? $this->callbacks['env']() : 'dev';
            case 'loaders':
                return array_key_exists('loaders', $this->callbacks) ? $this->callbacks['loaders']() : [];
            case 'routes':
                return array_key_exists('routes', $this->callbacks) ? $this->callbacks['routes'](Router::instance()) : null;
            case 'startup':
                return array_key_exists('startup', $this->callbacks) ? $this->callbacks['startup']() : null;
        }
    }
    
    /* bootstrap entry point */
    public function main()
    {
        $loaders = array_merge(
            [
                require __DIR__.'/Autoload.php'
            ],
            $this->run_callback('loaders')
        );
        
        foreach ($loaders as $ldr)
        {
            if ($ldr instanceof Loader == false) {
                throw new Exception('A loader defined is not an instance of Kern\Loader');
            }
            
            $ldr->register();
        }
        
        Config::set_env($this->run_callback('env'));
        $this->set_erp();
        
        $this->run_callback('routes');
        
        $this->run_callback('startup');
    }
}
