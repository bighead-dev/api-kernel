<?php

namespace Kern;

class Model
{
    public $req;

    public function __construct(Request $req)
    {
        $this->req = $req;
    }
}
