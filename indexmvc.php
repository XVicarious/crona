<?php
spl_autoload_register(function ($class_name) {
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name).'.php';
    include $class_name;
});

$_SESSION['userMode'] = 0;

$model = new xvmvc\model\ModelLogin();
$controller = new xvmvc\controller\ControllerLogin($model);
$view = new xvmvc\view\ViewLogin($controller, $model);

echo $view->output();
