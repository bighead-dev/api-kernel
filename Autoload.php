<?php

if (!defined('KERN_PATH')) {
    throw new Exception("KERN_PATH is not defined");
}

require_once 'globals.php';

return new Lib\Loader('Kern', KERN_PATH.'/');
