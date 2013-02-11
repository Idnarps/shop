<?php
/**
 * Файл для авторизации пользователя в панели контроля
 * User: IdnarpS
 * Date: 26.01.13
 */
class Class_Authorization {

  private $_twig;

  private $_db;

  // Свойство сообщающее, что логин пользователя прошёл успешно
  private $isLogin;

  // Конструктор класса
  public function __construct() {
    $this->_db = Class_Config::DB();
  }

  /**
   * Метод, определяющий залогинен ли пользователь
   * @return bool
   */
  public function getIsLogin() {
    return $this->isLogin;
  }

  /**
   * Метод для отображения страницы логина и определения авторизованного пользователя
   */
  public function login() {
    $result['error'] = $this->_isUser();
    if (!(false === $result['error'])) {
      // Если возникла, какая либо ошибка или страницу логина выводим впервые, то отображаем HTML-страницу
      $result['loginPage'] = Class_Config::templateRender('login.html', array('error' => $result['error']));
    }
    return $result;
  }

  private function _isUser() {
    $userCommon = new Class_Reference_User_Common();
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $pwd = isset($_POST['pwd']) ? trim($_POST['pwd']) : '';
    $error = '';
    if ($login && $pwd) {
      $user = $userCommon->selectOne(array('id', 'name', 'surname', 'patronymic'), array(array('name' => 'login', 'value' => $login),
                                                                                         array('name' => 'password', 'value' => $pwd),
                                                                                         array('name' => 'type', 'value' => 'admin')));
      //echo $userCommon->getSQL();
      $error = 'Пользователь с таким логином/паролем не найден!';
      if ($user) {
        // Если нашли такого пользователя
        $this->isLogin = true;
        $_SESSION['cp_user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['surname'] . ' ' . $user['name'] . ' ' . $user['patronymic'];
        $error = false;
      }
    }
    return $error;
  }

}
