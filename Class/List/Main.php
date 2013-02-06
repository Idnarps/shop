<?php
/**
 * Файл с описанием главного класса списков
 *
 * @package WTIS Window
 * @author Maverick
 */

/**
 * Класс Class_List_Main главный класс для управления списками
 *
 * @property string $dopGetParams - дополнительные GET-параметры для перехода по страницам
 * @property array $Totals - массив полей, по кторым необходимо расчитать итоги
 * @property bool $Paginal - флаг включения пагинации
 * @property int $Page - номер текущей страницы
 * @property int $RecCnt - общее количество записей
 * @property int $RPP - количество записей, отображаемых на одной странице
 *
 * @author Maverick
 */
class Class_List_Main {
  // Переменные ----------------------------------------------------------------
  private $_tplDir;
  private $_cssDir;
  private $_jsDir;
  private $_imgDir;
  private $_columns;
  private $_rows;
  private $_width; //Ширина таблицы
  private $_rowEvents; //Массив с событиями строк и их обработчиками
  private $_rowEventsString; //Строковое отображение событий строки
  private $_skin; // имя папки скина
  private $_sortField;
  private $_sortAsc;
  /**Дополнительные параметры передаваемые при сортировке*/
  private $_dopGetParams;
  private $_colorField;//Поле по которому определяется цвет строки
  /**Массив содержащий соответствие значений поля $_colorField и цветов строк*/
  private $_colors;
  /**Постраничный вывод*/
  private $_paginal;
  /**Текущая страница*/
  private $_curPage;
  /**Количество записей на странице*/
  private $_rpp;
  /**Общее количество записей*/
  private $_recCnt;
  /**массив правил расставления аттрибута DISABLED для ячейки (в случае если правило выполняется блокировка)*/
  private $_disabledRules;
  /**массив правил расставления аттрибута DISABLED для ячейки (в случае если правило не выполняется блокировка)*/
  private $_enabledRules;
  /**массив с колонками по которым нужно считать итоги*/
  private $_totals;
  /**массив с итогами*/
  private $_totalsResults;
  /**неактивный заголовок (отключена сортировка)*/
  private $_inactiveHeader;
  // Методы --------------------------------------------------------------------

  /**
   * Отображение шапки таблицы
   * @return string содержимое страницы
   */
  private function _tableHeader()
  {
  	$return = "";
	ob_start();

	include($this->_tplDir. 'tableHeader.tpl');

	echo "<tr>";
	foreach($this->_columns as $object)
	{
		$thCssClass= ($this->_sortField ==$object[0])?"class='selected'":"";
		$arrow = ($this->_sortField ==$object[0])?"<img style='float:left' src='{$this->_imgDir}sort_{$this->_sortAsc}.png'>":"";
		$asc = ($this->_sortField == $object[0])?(($this->_sortAsc=="asc")?"desc":"asc"):"asc";
		if($object[4]=="checkbox")
		{
			$object[1] .= ($object[1] ? "<br>" : "")."<input type='checkbox' name='check_all_{$object[0]}' onClick=\"check_all(this,'{$object[0]}');\">";
		}
		else
		{
		  if ($this->_inactiveHeader) {
        $onclick = '';
		  } else {
			  $onclick = "onclick=\"document.location.href='?sort={$object[0]}&asc={$asc}{$this->_dopGetParams}'\"";
		  }
		  if ($this->_inactiveHeader) {
  			$object[1] = "<a href='javascript:void(0)' {$cssClass}><div style='float:left;margin-right:3px;'>{$object[1]}</div>{$arrow}</a>";
		  } else {
  			$object[1] = "<a href='?sort={$object[0]}&asc={$asc}{$this->_dopGetParams}' {$cssClass}><div style='float:left;margin-right:3px;'>{$object[1]}</div>{$arrow}</a>";
		  }
		}
		if($object[4]!='hidden')include($this->_tplDir. 'tablePrompt.tpl');
	}
	echo "</tr>";

	$return = ob_get_clean();
	return $return;
  }

  /**
   * Отображение подвала таблицы
   * @return string содержимое страницы
   */
  private function _tableFooter()
  {
  	$return = "";
	ob_start();
	$pages = $this->_showPages();
	include($this->_tplDir. 'tableFooter.tpl');

	$return = ob_get_clean();
	return $return;
  }

  private function _showTotals()
  {
  	if(is_array($this->_totalsResults))
  	{
		$result = "<tr>";
  		foreach($this->_columns as $object)
  		{
  			if($object[4]!='hidden')
  			{
	  			$innerHTML = ($this->_totalsResults[$object[0]])?number_format($this->_totalsResults[$object[0]],2):"";
	  			ob_start();
	  			include($this->_tplDir. 'tableCellTotal.tpl');
	  			$result .= ob_get_clean();
  			}
  		}
  		$result .= "</tr>";
  	}
  	return $result;
  }

  /**
   * Отображение страничной навигации
   *
   * @return string содержимое страницы
   */
  private function _showPages()
  {
  	$pages = "";
	if($this->_paginal)
	{
		$pageCnt = floor($this->_recCnt/$this->_rpp)+(($this->_recCnt%$this->_rpp)==0?0:1);

		/* КрыловИА (начало вставки) */
		/*
		if($this->_curPage>0) $pages .= "<a href='?page=".($this->_curPage-1)."' class='pages'><< Назад</a>&nbsp;";

		for($i=0;$i<$pageCnt;$i++)
		{
			$pages .= "<a href='?page={$i}' class='".(($i==$this->_curPage)?"curPage":"pages")."'>".($i+1)."</a>&nbsp;";
		}
		*/
		if ($this->_curPage > 0){
			$pages .= "<a href='?page=0{$this->_dopGetParams}' class='pages'><< В начало</a>&nbsp;";
			$pages .= "<a href='?page=".($this->_curPage-1)."{$this->_dopGetParams}' class='pages'>Назад</a>&nbsp;";
		}

		$max_pages = ($pageCnt > 20 ? 20 : $pageCnt);
		if ($this->_curPage < $max_pages / 2)
		{
			$start_i = 0;
			$end_i = $max_pages;
		}
		elseif ($this->_curPage > $pageCnt - $max_pages / 2)
		{
			$start_i = $pageCnt - $max_pages;
			$end_i = $pageCnt;
		}
		else
		{
			$start_i = $this->_curPage - $max_pages / 2;
			$end_i = $this->_curPage + $max_pages / 2;
		}
		for($i = $start_i; $i < $end_i ; $i++)
		{
			$pages .= "<a href='?page={$i}{$this->_dopGetParams}' class='".(($i==$this->_curPage)?"curPage":"pages")."'>".($i + 1)."</a>&nbsp;";
		}

		/*
		if($this->_curPage<$pageCnt && $pageCnt>1) $pages .= "<a href='?page=".($this->_curPage+1)."' class='pages'>Далее >></a>&nbsp;";
		*/
		if (($this->_curPage < $pageCnt - 1) && $pageCnt > 1){
			$pages .= "<a href='?page=".($this->_curPage + 1)."{$this->_dopGetParams}' class='pages'>Далее</a>&nbsp;";
			$pages .= "<a href='?page=".($pageCnt - 1)."{$this->_dopGetParams}' class='pages'>В конец >></a>&nbsp;";
		}

		/* КрыловИА (окончание вставки) */
	}
	ob_start();
	include($this->_tplDir. 'pageNav.tpl');
	$pages = ob_get_clean();
	return $pages;
  }

  /**
   * Метод для отображения ячейки таблицы
   * @param int $rowId - номер текущей строки данных
   * @param int $cellId - номер текущего столбца
   * @param mixed $object - массив
   * @return string - содержимое страницы
   */
  private function _showCell($rowId,$cellId,$object)
  {
  	$return = "";

  	switch($object[4])
  	{
  		case ("select") :
  			$return = $this->_showCellSelect($rowId,$cellId,$object);
  			break;
  		case ("checkbox") :
  			$return = $this->_showCellCheckBox($rowId,$cellId,$object);
  			break;
  		case ("textbox") :
  			$return = $this->_showCellTextBox($rowId,$cellId,$object);
  			break;
  		case ("textarea") :
  			$return = $this->_showCellTextArea($rowId,$cellId,$object);
  			break;
  		case ("hidden") :
  		  $return = $this->_showCellHidden($rowId, $cellId, $object);
  		  break;
  		default :
  			ob_start();
  			$value = $this->_rows[$rowId][$object[0]];
  			$name = $object[0];
  			//Определяем есть ли прикрепленный справочник
  			$innerHTML = (is_array($object[3]) && $object[3][$value])?$object[3][$value]:$value;
  			$innerHTML = ($innerHTML)?$innerHTML:"&nbsp;";
  			include($this->_tplDir. "tableCell".ucfirst($object[4]).".tpl");
  			$return = ob_get_clean();
  			break;
  	}
	//Подсчет итогов
	if(@in_array($object[0],$this->_totals))$this->_totalsResults[$object[0]]+=$this->_rows[$rowId][$object[0]];

	return $return;
  }

    /**
   * Метод для отображения ячейки таблицы, тип ячейки - чекбокс
   * @param int $rowId - номер текущей строки данных
   * @param int $cellId - номер текущего столбца
   * @param mixed $object - массив
   * @return string - содержимое страницы
   */
  private function _showCellCheckBox($rowId,$cellId,$object)
  {
  	$rowIdValue = $this->_rows[$rowId][$object[5]["rowIdField"]];
  	$value = $this->_rows[$rowId][$object[0]];
	$name = $object[0];

	//блокировка для всего элемента
  	$disabled = $this->_isEnabled($rowId,$object[0]);

  	//Определяем включен или нет
    if(is_array($object[5]["checkedValues"]))
  		$checked = (in_array($value,$object[5]["checkedValues"]))?"CHECKED":"";

  	//Формируем строку с событиями
  	$checkboxEvents=$this->_makeEventsString($object[5]["events"]);

  	include($this->_tplDir. "tableCell".ucfirst($object[4]).".tpl");
  }

  /**
   * Метод для отображения ячейки таблицы, тип ячейки - чекбокс
   * @param int $rowId - номер текущей строки данных
   * @param int $cellId - номер текущего столбца
   * @param mixed $object - массив
   * @return string - содержимое страницы
   */
  private function _showCellTextBox($rowId,$cellId,$object)
  {
  	$rowIdValue = $this->_rows[$rowId][$object[5]["rowIdField"]];
  	$value = $this->_rows[$rowId][$object[0]];
	$name = $object[0];
	$width = $object[2];
	//блокировка для всего элемента
  	$disabled = $this->_isEnabled($rowId,$object[0]);

  	//Формируем строку с событиями
  	$textboxEvents=$this->_makeEventsString($object[5]["events"]);

  	include($this->_tplDir. "tableCell".ucfirst($object[4]).".tpl");
  }

	/**
   * Метод для отображения ячейки таблицы, тип ячейки - hidden
   * @param int $rowId - номер текущей строки данных
   * @param int $cellId - номер текущего столбца
   * @param mixed $object - массив
   * @return string - содержимое страницы
   */
  private function _showCellHidden($rowId,$cellId,$object)
  {
    $value = $this->_rows[$rowId][$object[0]];
		$name = $object[0];
		//Определяем есть ли прикрепленный справочник
		$innerHTML = (is_array($object[3]) && $object[3][$value])?$object[3][$value]:$value;
		include($this->_tplDir. "tableCell".ucfirst($object[4]).".tpl");
  }

  /**
   * Метод для отображения ячейки таблицы, тип ячейки - текстовое поле (многострочное)
   * @param int $rowId - номер текущей строки данных
   * @param int $cellId - номер текущего столбца
   * @param mixed $object - массив
   * @return string - содержимое страницы
   */
  private function _showCellTextArea($rowId,$cellId,$object)
  {
  	$rowIdValue = $this->_rows[$rowId][$object[5]["rowIdField"]];
  	$value = $this->_rows[$rowId][$object[0]];
	$name = $object[0];
	$width = $object[2];
	//блокировка для всего элемента
  	$disabled = $this->_isEnabled($rowId,$object[0]);

  	// Количество строк в текстовом поле (высота контрола)
  	$rows = $object[5]["rows"];

  	//Формируем строку с событиями
  	$textareaEvents=$this->_makeEventsString($object[5]["events"]);

  	include($this->_tplDir. "tableCell".ucfirst($object[4]).".tpl");
  }

  /**
   * Метод для отображения ячейки таблицы, тип ячейки - выпадающий список
   * @param int $rowId - номер текущей строки данных
   * @param int $cellId - номер текущего столбца
   * @param mixed $object - массив
   * @return string - содержимое страницы
   */
  private function _showCellSelect($rowId,$cellId,$object)
  {
  	$return = "";
  	ob_start();
  	$rowIdValue = $this->_rows[$rowId][$object[5]["rowIdField"]];
  	$value = $this->_rows[$rowId][$object[0]];
  	//проверяем если есть массив с псевдонимами то заменяем значение на псевдоним
  	$value = (is_array($object[3]) && $object[3][$value])?$object[3][$value]:$value;
  	$name = $object[0];
  	$width = ($object[2])?"style='width:{$object[2]};'":"";
  	//блокировка для всего селекта
  	$disabled = $this->_isEnabled($rowId,$object[0]);

  	//Формируем строку с событиями
  	$selectEvents="";
  	foreach($object[5]["events"] as $e=>$h)
  	{
  		$selectEvents .= " {$e}=\"{$h}\"";
  	}

  	//Определяем правила блокировки для option
  	$default_disabled = (is_array($object[5]['enabled_on_value'])?1:0); //Блокировка по умолчанию 1- блокировать 0 не блокировать
  	if(is_array($object[5]['enabled_on_value']))
  	{
  		$disable_rules = $object[5]['enabled_on_value'][$this->_rows[$rowId][$object[0]]];//Определяем правила для текущего значения селекта
  	}
  	elseif(is_array($object[5]['disabled_on_value']))
  	{
  		$disable_rules = $object[5]['disabled_on_value'][$this->_rows[$rowId][$object[0]]];//Определяем правила для текущего значения селекта
  	}


  	//Проходим по массиву с option
  	foreach($object[5]["options"] as $key=>$val)
  	{
  		if(is_array($disable_rules))
  		{
  			$d = (in_array($key,$disable_rules))?(1-$default_disabled):$default_disabled;
  		}

  		$options .= "<option value='{$key}' ".(($value == $val || $value == $key)?"SELECTED":"")." ".(($d)?"DISABLED":"")." >{$val}</option>";
  	}

  	include($this->_tplDir. "tableCellSelect.tpl");
  	$return = ob_get_clean();
	return $return;
  }

  /**
   * Определение аттрибута DISABLED для ячейки
   * @param int $rowId - номер строки
   * @param string $cellName - имя ячейки
   * @return string - DISABLED  случае если элемент заблокирован
   */
  private function _isEnabled($rowId,$cellName)
  {
  	//Проверяем правила блокировки если хотябы обно условие выполняется то блокируем
  	$rules = $this->_disabledRules[$cellName];
  	if(is_array($rules))
  	{
  		foreach($rules as $key=>$disValues)
  		{
  			$curValue = $this->_rows[$rowId][$key];
  			if(in_array($curValue, $disValues))return "DISABLED";
  		}
  	}

  	//Проверяем правила включения, если хотябы одно условие выполняется включаем
    $return = "";
  	$rules = $this->_enabledRules[$cellName];
  	if(is_array($rules))
  	{
  		$return = "DISABLED";
  		foreach($rules as $key=>$enValues)
  		{
  			$curValue = $this->_rows[$rowId][$key];
  			if(in_array($curValue, $enValues))$return="";
  		}
  	}
  	return $return;
  }

  /**
   * Формирует текстовое представление событий элемента, для подстановки в шаблон
   * @param mixed $eventsArr - массив формата событие=>обработчик
   * @return unknown_type
   */
  private function _makeEventsString($eventsArr)
  {
    $result = "";
  	if(is_array($eventsArr))
  	{
	    foreach($eventsArr as $e=>$h)
	  	{
	  		$result .= " {$e}=\"{$h}\"";
	  	}
  	}
  	return $result;
  }

  /**
   * Метод для получения значений свойств класса
   *
   * @param string $field название свойства
   * @return mixed
   */
  public function __get($field) {
    switch ($field) {
      case "RPP" :
      	return $this->_rpp;
      	break;
      case "Page" :
      	return $this->_curPage;
      	break;
      case "Paginal" :
      	return $this->_paginal;
      	break;
      default:
        die("Class_List_Main: свойство '{$field}' не существует");
        return NULL;
        break;
    }
  }

  /**
   * Метод для установки значений свойств класса
   *
   * @param string $field название свойства
   * @param mixed $value значение свойства
   */
  public function __set($field, $value){
    switch ($field) {
      case "Paginal" :
      	$this->_paginal = $value;
      	break;
      case "Page" :
      	$this->_curPage = $value;
      	break;
      case "RecCnt" :
      	$this->_recCnt = $value;
      	break;
      case "RPP" :
      	$this->_rpp = $value;
      	break;
      case "dopGetParams" :
      	$this->_dopGetParams = $value;
      	break;
      case "Totals" :
      	$this->_totals = $value;
      	break;
      default:
        die("Class_List_Main: свойство \"{$field}\" не существует");
        break;
    }
  }

  /**
   * Конструктор класса
   *
   * $Columns[0] - Ключ, $Columns[1] - Значение, $Columns[2] - Ширина, $Columns[3] - Массив для преобразования значение,$Columns[4] - тип,$Columns[5] - массив с возможными элементами(для select)
   * @param bool $inactiveHeader неактивный заголовок (отключена сортировка)
   * @return Class_List_Main
   */
  public function __construct($Columns, $Width = "100%", $Skin="Jira", $inactiveHeader = false) {

	$this->_columns = $Columns;
	$this->_width = ($Width)?$Width:"100%";
	$this->_rows = Array();
	$this->_skin = $Skin;

	$this->_tplDir = $_SERVER['DOCUMENT_ROOT']."/class/List/skins/{$this->_skin}/tpl/";
	$this->_cssDir = "/Class/List/skins/{$this->_skin}/css/";
	$this->_jsDir = "/Class/List/js/";
	$this->_imgDir = "/Class/List/skins/{$this->_skin}/images/";

	$this->_colorField = "";
	$this->_colors = null;

	$this->_paginal=0;
	$this->_curPage = 0;
	$this->_rpp = 20;
	$this->_recCnt=0;

	$this->_inactiveHeader = $inactiveHeader;

  }


  /**
   * Добавление обработчкиа события к строкам, и пересчет переменной $_rowEventsString
   *
   * mixed $Row - массив с данными
   *
   * @return void
   */
  public function addRowEvent($event,$handler)
  {
  	$this->_rowEvents[$event] .= $handler;

  	$str="";
  	foreach($this->_rowEvents as $e=>$h)
  	{
  		$str .= " {$e}=\"{$h}\"";
  	}
  	$this->_rowEventsString = $str." ";
  }

  /**
   * Установка параметров сортировки
   * @param string $field - поле по которому сортируем
   * @param string $asc - порядок сортировки (asc/desc)
   * @return void
   */
  public function setSort($field,$asc)
  {
  	$this->_sortField=$field;
  	$this->_sortAsc=$asc;
  }

  /**
   * Установка
   * @param string $Field - поле по которому будет определятся цвет
   * @param string $Colors - массив значение=>цвет
   * @return void
   */
  public function setColorRule($Field,$Colors)
  {
  	$this->_colorField = $Field;
  	$this->_colors = $Colors;
  }

  /**
   * Метод устанавливает правила установки аттрибута  DISABLED на содержимое ячейки (блокировка в случае если правило выполняется )
   * @param string $field - отображаемое поле
   * @param string $fieldDefiner - поле по которому определяется правило
   * @param mixed $fieldDefinerValues - массив с блокирующими значениями определяющего поля
   * @return void
   */
  public function setDisableRule($field,$fieldDefiner,$fieldDefinerValues)
  {
    $this->_disabledRules[$field][$fieldDefiner]=$fieldDefinerValues;
  }

/**
   * Метод устанавливает правила установки аттрибута  DISABLED на содержимое ячейки (блокировка в случае если правило не выполняется )
   * @param string $field - отображаемое поле
   * @param string $fieldDefiner - поле по которому определяется правило
   * @param mixed $fieldDefinerValues - массив с блокирующими значениями определяющего поля
   * @return void
   */
  public function setEnableRule($field,$fieldDefiner,$fieldDefinerValues)
  {
    $this->_enabledRules[$field][$fieldDefiner]=$fieldDefinerValues;
  }


  /**
   * Добавление строки
   * mixed $Row - массив с данными
   * @return void
   */
  public function addRow($Row)
  {
  	$this->_rows[]=$Row;
  }

  /**
   * Добавление строк из результата выборки mysql
   * mixed $Res - результат mysql выборки
   * @return void
   */
  public function mysqlAddRows($Res)
  {
    foreach ($Res as $row)
    {
      $this->addRow($row);
    }
  }

  /**
   * Отображение таблицы
   * @return string содержимое страницы
   */
  public function Show()
  {
  	$return = "";

	$return .= $this->_tableHeader();

	ob_start();

	for($i=0;$i<count($this->_rows);$i++)
	{
		ob_start();

		$rowId=$i;
		$j=0;
		foreach($this->_columns as $object)
		{
			echo $this->_showCell($i,$j,$object);
			$j++;
		}
		//$rowStyle = ($this->_colorField)?"style='background-color:#".$this->_colors[$this->_rows[$i][$this->_colorField]]."'":"class='list".(1+$rowId%2)."'";
		$rowStyle = "style='background-color:#".$this->_colors[$this->_rows[$i][$this->_colorField]]."' class='list".(1+$rowId%2)."'";
		$content = ob_get_clean();

		include($this->_tplDir. 'tableRow.tpl');
	}
	$return .= ob_get_clean();
	$return .= $this->_showTotals();
	$return .= $this->_tableFooter();
	return $return;
  }

  	/* КрыловИА (начало вставки) */

	// Формирование Excel-файла и выдача его клиенту
	public function SaveToExcel($fileName = "file"){
		require_once($_SERVER['DOCUMENT_ROOT'].'/class/exportFromExel/exportFromExel.php');
		$export = new exportFromExel();
    foreach ($this->_columns as &$column){
			if (!$column[1]){
				$column[4] = "hidden";
			}
			if ($column[4] != "hidden"){
				$w = $column[2] && $column[2] != '1px' ? ''.(int)$column[2]*2 : '200';
				$w_exel = ''.$w*10;
				$export->fields[$column[0]] = array('nameCol' => $column[1], 'w' => $w, 'w_exel' => $w_exel);
			}
		}
		unset($column);
		foreach ($this->_rows as &$row){
			foreach ($this->_columns as $column){
	  		$value = $row[$column[0]];
        /*if (is_numeric($value)) {
          $value = number_format($value, 2, ',', '');
        }*/
				$row[$column[0]] = (is_array($column[3]) && $column[3][$value] ? $column[3][$value] : $value);
				$row[$column[0]] = str_replace("<br>", " ", $row[$column[0]]);
				$row[$column[0]] = str_replace("&nbsp;", " ", $row[$column[0]]);
				$row[$column[0]] = strip_tags($row[$column[0]]);
			}
		}
		$export->arrReport = $this->_rows;
		$export->getExelFile($fileName.".xls");
	}

	/* КрыловИА (конец вставки) */

}