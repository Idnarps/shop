<?php
/**
 * ����� ��� ������ � ���������������
 * User: ScvortsovAV
 * Date: 25.01.13
 */

class Class_Reference_Manufacturer_Common extends Class_Common {

  public function __construct(){
    parent::__construct();
    $this->_tblName = 'ref_manufacturer';
    $this->_objName = 'Class_Manufacturer_Common';
  }

}