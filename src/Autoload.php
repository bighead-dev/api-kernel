<?php

require_once 'globals.php';

return new Kern\Loader('Kern', [
    'Kern\Bootstrap'                => __DIR__.'/Bootstrap.php',
    'Kern\Client'                   => __DIR__.'/Client.php',
    'Kern\Config'                   => __DIR__.'/Config.php',
    'Kern\Dispatcher'               => __DIR__.'/Dispatcher.php',
    'Kern\Exception'                => __DIR__.'/Exception.php',
    'Kern\iResponse'                => __DIR__.'/iResponse.php',
    'Kern\OrmDriver'                => __DIR__.'/Orm.php',
    'Kern\OrmHydratable'            => __DIR__.'/Orm.php',
    'Kern\OrmSerializable'          => __DIR__.'/Orm.php',
    'Kern\Orm'                      => __DIR__.'/Orm.php',
    'Kern\Orm\Query'                => __DIR__.'/Orm/Query.php',
    'Kern\Orm\Db\Param'             => __DIR__.'/Orm/Db/Param.php',
    'Kern\Orm\Db\Query'             => __DIR__.'/Orm/Db/Query.php',
    'Kern\Orm\DbHydratable'         => __DIR__.'/Orm/Db.php',
    'Kern\Orm\Db'                   => __DIR__.'/Orm/Db.php',
    'Kern\Request'                  => __DIR__.'/Request.php',
    'Kern\Response\HtmlResponse'    => __DIR__.'/Response/Html.php',
    'Kern\Response\JsonResponse'    => __DIR__.'/Response/Json.php',
    'Kern\Response\XmlResponse'     => __DIR__.'/Response/Xml.php',
    'Kern\Routable'                 => __DIR__.'/Router.php',
    'Kern\Route'                    => __DIR__.'/Router.php',
    'Kern\Router'                   => __DIR__.'/Router.php',
    'Kern\View'                     => __DIR__.'/View.php',
]);
