# View

Views are classes that represent a view file to be rendered.

**Example**

*View Class*
````php
<?php
// view file

namespace View\Home;

class Index()
{
    public $title;
    public $headline;
    public $view_file = 'home/index';
    
    public function to_uppercase($str)
    {
        return strtoupper($str);
    }
}
````

````php
<?php

// controller file

namespace Controller;

use Kern;
use View;

class Home extends Base
{
    public function index()
    {
        $v = new View\Home\Index();
        
        $v->title = 'title';
        $v->headline = 'headline';
        $v->set_data([
            'extra-param'   => 1
        ]);
        
        return new Kern\Response\HtmlResponse($v->render());
    }
}
````

````php
<?php
// actual view file to be rendered
?>
<html>
    <head>
        <title><?=$this->to_uppercase($this->title)?></title>
    </head>
    <body>
        <h1><?=$this->headline?></h1>
    </body>
</html>
````

As you can see from the example, the literal view file is included in context of the View class/object. So in your view file, you can use any methods/properties available to view object which can greatly simplify view development.

Some views however, are very simple, and don't need a full class model to be defined for a view. So you can just use `View::renderFile($file, $data)` to create an anonymous view for the file and data which just returns a string of the rendered view file.
