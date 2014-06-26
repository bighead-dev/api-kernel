<?php

namespace Kern;

/* load the loader */
require_once __DIR__ . '/Loader.php';

abstract class Bootstrap
{
    /* bootstrap entry point */
    public function main()
    {
        $loaders = array_merge(
            [
                require __DIR__.'/Autoload.php'
            ],
            $this->loaders()
        );
        
        $this->register_loaders($loaders);
        
        kern_env($this->env());
        
        $this->erp();
        $this->routes(Router::instance());
        $this->startup();
    }
    
    public function register_loaders($loaders)
    {
        foreach ($loaders as $ldr)
        {
            if ($ldr instanceof Loader == false) {
                throw new Exception('A loader defined is not an instance of Kern\Loader');
            }
            
            $ldr->register();
        }
    }
    
    abstract public function loaders();
    abstract public function routes(Router $rtr);
    abstract public function startup();
    abstract public function env();
    abstract public function erp();
}
