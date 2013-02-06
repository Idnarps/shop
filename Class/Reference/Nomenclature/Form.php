<?php
/**
 * Created by JetBrains PhpStorm.
 * User: IdnarpS
 * Date: 06.02.13
 */
class Class_Reference_Nomenclature_Form {

  private $_twig;

  private $_db;

  public function __construct() {
    $this->_db = Class_Config::DB();
  }

  private function _show(){
    return Class_Config::templateRender('list.html', array('name' => 'Вася'));
  }

  public function run($act) {
    switch($act) {
      case 'show':
        $result = $this->_show();
        break;
    }
    return $result;
  }

}
