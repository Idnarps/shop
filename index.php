<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Twig/Autoloader.php';
Twig_Autoloader::register();
$nomenklCommon = new Class_Reference_Nomenclature_Common();
//echo $nomenklCommon->insert(array('name' => 'Вася', 'full_name' => 'Вася Пупкин'));
$nomenklCommon->select(array('name', 'full_name'), array('name'=>'id', 'value' => 1, 'sign' => '>'));
  echo $nomenklCommon->getSQL();
echo ('mkhyuf,korukorf,lo');