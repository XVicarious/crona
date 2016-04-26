<?php
spl_autoload_register(function ($class_name) {
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name).'.php';
    include $class_name;
});

$component = $_POST['component'];
$data = $_POST['data'];

$baseName = 'xvmvc';
$modelName = "$baseName\\model\\Model$component";
$controllerName = "$baseName\\controller\\Controller$component";
$viewName = "$baseName\\view\\View$component";

$model = new $modelName;
$controller = new $controllerName($model);
$view = new $viewName($controller, $model);

if (isset($_POST['action']) && !empty($_POST['action'])) {
    $controller->{$_POST['action']};
}
