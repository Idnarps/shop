<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Twig/Autoloader.php';
Twig_Autoloader::register();
$nomenklCommon = new Class_Reference_Nomenclature_Common();
//echo $nomenklCommon->insert(array('name' => 'Вася2', 'full_name' => 'Вася Пупкин2'));
//print_r($nomenklCommon->select(array('name', 'full_name'), array('name'=>'id', 'value' => 1, 'sign' => '>=')));
//echo $nomenklCommon->update(array('name' => 'Вася23', 'full_name' => 'Вася Пупкин32'),array('name'=>'id', 'value' => 1, 'sign' => '='));
//echo $nomenklCommon->getSQL();

