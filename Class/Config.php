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
  static private $_twigParams = array();

  // Инициализация основных параметров приложения
  static public function init() {
    self::$workPath = $_SERVER['DOCUMENT_ROOT'] . '/Class/' . Class_Config::$types[$_GET['p']] . '/' . $_GET['cl'] . '/';
    self::$className = 'Class_' . Class_Config::$types[$_GET['p']] . '_' . $_GET['cl'] . '_' . $_GET['ty'];
    self::$classFile = self::$workPath . $_GET['ty'] . '.php';
    session_start();
    // Инициализируем загрузчик
    // Добавим пути, где могут лежать шаблоны
    $tplPath[] = $_SERVER['DOCUMENT_ROOT'] . '/cp/tpl/';
    $tplClass = self::$workPath . 'tpl/';
    if (file_exists($tplClass)) {
      $tplPath[] = $tplClass;
    }
    $loader = new Twig_Loader_Filesystem($tplPath);
    //Настроим Twig
    self::$twig = new Twig_Environment($loader);
    $escaper = new Twig_Extension_Escaper(false); //Загружаем расширение с настройкой, чтоб оно не экранировало всё попало
    self::$twig->addExtension($escaper);
    self::ParseDopParams();
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

  /**
   * Проверяем на существование дополнительных GET-переменных (они могут не попасть в $_GET после htaccess'а)
   * @return void
   */
  static public function ParseDopParams() {
    $dop_params = urldecode($_SERVER['REQUEST_URI']);
    $dop_params = explode("?", $dop_params);
    if($dop_params[1])
    {
      $dop_params = explode("&", $dop_params[1]);
      foreach($dop_params as $value)
      {
        $value = explode("=",$value);
        if (isset($value[1])) {
          $_GET[$value[0]] = $value[1];
        }
      }
    }
  }

}
