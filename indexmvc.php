<?php
spl_autoload_register(function ($class_name) {
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name).'.php';
    include $class_name;
});

$_SESSION['userMode'] = 0;

$model = new xvmvc\model\ModelMenu();
$controller = new xvmvc\controller\ControllerMenu($model);
$view = new xvmvc\view\ViewMenu($controller, $model);

echo $view->output();
