<?php
/**
 * Конфигурационный файл
 * User: IdnarpS
 * Date: 20.01.13
 */
class Class_Config{

  const DB_HOST = "localhost";
  const DB_USER = "root";
  const DB_PASS = "";
  const DB_NAME = "shop";

  static public function DB() {
    return new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8"'));
  }

}
