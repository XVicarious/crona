<?php

namespace xvmvc\view;

/**
 * Class View
 * @package xvmvc\view
 */
class View
{
    protected $model;
    protected $controller;

    /**
     * View constructor.
     * @param $controller
     * @param $model
     */
    public function __construct($controller, $model)
    {
        $this->controller = $controller;
        $this->model = $model;
    }

    public function output()
    {
    }
}
