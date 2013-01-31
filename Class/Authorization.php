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
   * Метод для отображения
   */
  public function login() {
    $userCommon = new Class_Reference_User_Common();
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $pwd = isset($_POST['pwd']) ? trim($_POST['pwd']) : '';
    if ($login && $pwd) {
      $user = $userCommon->selectOne(array('id', 'name', 'surname', 'patronymic'), array('login' => $login,
                                                                                         'password' => $pwd,
                                                                                         'type' => 'admin'));
      $error = 'Пользователь с таким логином/паролем не найден!';
      if ($user) {
        $this->isLogin = true;
        $_SESSION['cp_user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['surname'] . ' ' . $user['name'] . ' ' . $user['patronymic'];
      }
    } else {

    }
  }

}
