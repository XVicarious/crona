<?php
/**
 * Created by PhpStorm.
 * User: bmaurer
 * Date: 4/8/2016
 * Time: 11:54 AM
 */

namespace xvmvc\controller;

class Controller
{
    private $model;
    public function __construct($model)
    {
        $this->model = $model;
    }
}
