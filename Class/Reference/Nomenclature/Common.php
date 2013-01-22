<?php
/**
 * Класс для работы с товарами
 * User: IdnarpS
 * Date: 21.01.13
 */
class Class_Reference_Nomenclature_Common extends Class_Common {

  public function __construct(){
    parent::__construct();
    $this->_tblName = 'ref_nomenclature';
    $this->_objName = 'Class_Nomenclature_Common';
  }

}
