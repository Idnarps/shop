<?php
/**
 * Конфигурационный файл
 * User: IdnarpS
 * Date: 20.01.13
 */
class Class_Config{

  // Типы запросов к разделам контрольной панели
  static public $types = array('ref' => 'Reference', 'doc' => 'Document');

  // Текущая рабочая директория
  static public $workPath = '';

  // Текущий класс
  static public $className = '';

  // Текущий первоначально вызванный файл
  static public $classFile = '';

  // Подключение Twig
  static public $twig;

  // Служебные параметры для Twig
  static private $_twigParams;

  // Инициализация основных параметров приложения
  static public function init() {
    self::$workPath = $_SERVER['DOCUMENT_ROOT'] . 'Class/' . Class_Config::$types[$_GET['p']] . '/' . $_GET['cl'] . '/';
    self::$className = 'Class_' . Class_Config::$types[$_GET['p']] . '_' . $_GET['cl'] . '_' . $_GET['ty'];
    self::$classFile = self::$workPath . $_GET['ty'] . '.php';
    session_start();
    // Инициализируем загрузчик
    $loader = new Twig_Loader_Filesystem(array($_SERVER['DOCUMENT_ROOT'] . 'cp/tpl/', self::$workPath . 'tpl/'));
    //Настроим Twig
    self::$twig = new Twig_Environment($loader);
    $escaper = new Twig_Extension_Escaper(false); //Загружаем расширение с настройкой, чтоб оно не экранировало всё попало
    self::$twig->addExtension($escaper);
    // Теперь определимся с пользователем
  }

  static public function DB() {
    try{
      return new PDO('mysql:host=localhost;dbname=shop', 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8"'));
    } catch (PDOException $e) {
      echo $e->getMessage();
      return false;
    }
  }

  private function  _getTwigParams() {
    return self::$_twigParams;
  }

  static function templateRender($templateName, $params){
   return self::$twig->render($templateName, array_merge($params, self::$_twigParams));
  }

}
