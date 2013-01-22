<?php
/**
 * Конфигурационный файл
 * User: IdnarpS
 * Date: 20.01.13
 */
class Class_Config{

 static public function DB() {
   return new PDO('mysql:host=localhost;dbname=shop', 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8"'));
 }

}
