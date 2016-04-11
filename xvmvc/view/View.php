<?php
/**
 * Created by PhpStorm.
 * User: bmaurer
 * Date: 4/8/2016
 * Time: 11:52 AM
 */

namespace xvmvc\view;

class View
{
    private $model;
    private $controller;
    public function __construct($controller, $model)
    {
        $this->controller = $controller;
        $this->model = $model;
    }
    public function output()
    {
    }
}
