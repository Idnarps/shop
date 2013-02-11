<?php
/**
 * Погнали!!!!
 * Кароче: сюда ВСЕГДА должны приходить 4 get-параметра:
 * ['p'] = тип обращения к служебному разделу('ref','doc' и др.)
 * ['cl'] = основное название класса
 * ['ty'] = тип раздела класса ['Form', 'List' и др.]
 * ['act'] = действие, которое будет производить класс. Вызывается через метод run($act) любого класса вызываемого извне.
 * Эти параметры формируются в .htaccess из урл типа /ref/Nomenclature/Form/show/
 * User: IdnarpS
 * Date: 25.01.13
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/Twig/Autoloader.php';
Twig_Autoloader::register();
Class_Config::init();
if (!$_SESSION['cp_user_id']) {
  $auth = new Class_Authorization();
  $result = $auth->login();
  if (!(false === $result['error'])) {
    echo $result['loginPage'];
    exit();
  }
}
//echo Class_Config::$classFile;
//Выясним существует ли выбранный класс
if (file_exists(Class_Config::$classFile)) {
  $class = new Class_Config::$className();
  echo $class->run($_GET['act']);
} else {
  // Выводим стандартное окно программы

}