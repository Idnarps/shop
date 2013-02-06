<?php
/**
 * Created by JetBrains PhpStorm.
 * User: IdnarpS
 * Date: 06.02.13
 */
class Class_Reference_Nomenclature_Form {

  private $_twig;
  private $_db;
  private $_struct;

  public function __construct()
  {
    $this->_db = Class_Config::DB();
  }

  private function _list()
  {
    $nomenclCommon = new Class_Reference_Nomenclature_Common();
    $res = $nomenclCommon->select(array('id', 'name', 'full_name'), array('name'=>'id', 'value' => 1, 'sign' => '>='));
    $this->_initStruct();
    $cols = $this->_getStruct("Nomenclature");

    //Создаем экземпляр класса списка
    $list = new Class_List_Main($cols, null, 'Jira');
    $list->mysqlAddRows($res);
    //Добавляем строки к списку
    $findContent=$list->Show();

    return Class_Config::templateRender('list.html', array('name' => $findContent));
  }

  public function run($act) {
    switch($act) {
      case 'list':
        $result = $this->_list();
        break;
    }
    return $result;
  }

  /**
 	 * Установка структуры
 	 */
  private function _initStruct()
  {
  	$this->_struct["Nomenclature"] = Array(
 			Array("id", "", "", null, "checkbox"),
 			Array("name", "Наименование", "", null, "text"),
 			Array("full_name", "Полное наименование", "", null, "text")
 		);
 	}

 	/**
 	 * Определение структуры для заданного журнала
 	 * @param $curJournal - журнал для которого необходимо вернуть структуру
 	 * @return mixed - массив со структурой
 	 */
 	private function _getStruct($curJournal)
 	{
 		$return = Array();
 		foreach($this->_struct[$curJournal] as $arr)
 		{
 			$return[]=$arr;
 		}
 		return $return;
 	}


}
