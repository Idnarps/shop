<?php
/**
 * Класс для работы с контрагентами
 * User: ScvortsovAV
 * Date: 25.01.13
 */

class Class_Reference_Contractor_Common extends Class_Common {

  public function __construct(){
    parent::__construct();
    $this->_tblName = 'ref_contractor';
    $this->_objName = 'Class_Contractor_Common';
  }

}