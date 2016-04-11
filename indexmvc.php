<?php
/**
 * Created by PhpStorm.
 * User: bmaurer
 * Date: 4/8/2016
 * Time: 11:59 AM
 */

$model = new \xvmvc\model\ModelLogin();
$controller = new \xvmvc\controller\ControllerLogin($model);
$view = new \xvmvc\view\ViewLogin($controller, $model);

echo $view->output();
