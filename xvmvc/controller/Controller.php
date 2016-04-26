<?php

namespace xvmvc\controller;

/**
 * Class Controller
 * @package xvmvc\controller
 */
class Controller
{
    private $model;

    /**
     * Controller constructor.
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }
}
