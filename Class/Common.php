<?php
/**
 * Основной класс работы с БД
 * User: IdnarpS
 * Date: 20.01.13
 */
class Class_Common {

  protected $_tblName; // Название таблицы, с которым работает класс

  protected $_tblAlias; // Алиас таблицы, с которым работает класс

  protected $_objName; // Название объекта

  protected $_idColumn; // Название колонки с основным идентификационным элементом

  protected $_db; // Подключение к БД

  protected $_sql; //SQL-запрос, созданный классом
  
  private $_stm;

  public function __construct($tblName){
    $this->_tblName = $tblName;
    $this->_db = Class_Config::DB();
    $this->_idColumn = 'id';
    $this->_initDefaults();
  }

  /**
   * Метод для вставки одной строки в таблицу
   * @param $data Массив с данными для вставки в таблицу.
   *              Вид: array('название поля в таблице' => 'значение',
   *                         'название поля в таблице' => 'значение',
   *                         ...)
   * @return integer|bool id, вставленного поля или false в случае ошибки
   */
  public function insert($data) {
    if (!is_array($data)) {
      return false;
    }
    try{
      $this->_sql = 'INSERT INTO ' . $this->_tblName . ' AS ' . $this->_tblAlias . ' SET ' . $this->_dataForInsertUpdate($data);
      $this->_stm = $this->_db->prepare($this->_sql);
      $this->_bindArrayValue($this->_stm, $data);
      $this->_stm->execute();
      $id = $this->_db->lastInsertId();
      if ($id) {
        return $id;
      }
    }
    catch (PDOException $e) {
      echo $e->getMessage();
      return false;
    }
  }

  /**
   * @param array $fields Ассоциативный массив с полями для выборки
   *                      Вид: array([] => 'алиас.поле', [tblAlias] => 'алиас.поле'), для таблицы класса и без join алиас можно не указывать
   * @param array $where Ассоциативный массив или массив ассоциативных массивов с данными для выборки
   *                      Вид: array('name' => значение,
   *                                 'value' => '',
   *                                 'sign' => 'знак сравнения'(необязательное поле),
   *                                 'alias' => 'алиас для таблицы поля'(необязательное поле))
   * @param array|bool $leftJoin Массив со строками left join
   *                             Вид: array([] => 'ref_nomenclature AS rn ON rn.id=z.ref_nomenclature_id')
   * @param array $params Массив с дополнительными параметрами запроса
   *                      Вид: array([distinct] => 1|0,
   *                                 [order] => 'название поля|полей с алисами таблицы',
   *                                 [limit] => 'количество запрашиваемых записей')
   * @return array|bool Результат запроса к БД
   */
  public function select($fields, $where, $leftJoin = false, $params = array()) {
    if (!is_array($fields)) {
      return false;
    }
    $this->_sql = 'SELECT ' . ($params['distinct'] ? 'DISTINCT' : '') . $this->_fromFields($fields) .
                  ' FROM ' . $this->_tblName . ' AS ' . $this->_tblAlias .
                  $this->_addLeftJoin($leftJoin) . ' WHERE ' .$this->_dataForWhere($where) .
                  ($params['order'] ? ' ORDER BY ' . $params['order'] : '') .
                  ($params['limit'] ? ' LIMIT ' . $params['limit'] : '');
    // Преобразуем входные данные для where для привязки значений к PDOStatment
    try{
      $this->_stm = $this->_db->prepare($this->_sql);
      $this->_bindArrayValue($this->_stm, $this->_prepareForBind($where));
      $this->_stm->execute();
      return $this->_stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
      echo $e->getMessage();
      return false;
    }
  }

  /**
  * @param array $fields Ассоциативный массив с полями для выборки
  *                      Вид: array([] => 'алиас.поле', [tblAlias] => 'алиас.поле'), для таблицы класса и без join алиас можно не указывать
  * @param array $where Ассоциативный массив или массив ассоциативных массивов с данными для выборки
  *                      Вид: array('name' => значение,
  *                                 'value' => '',
  *                                 'sign' => 'знак сравнения'(необязательное поле),
  *                                 'alias' => 'алиас для таблицы поля'(необязательное поле))
  * @param array|bool $leftJoin Массив со строками left join
  *                             Вид: array([] => 'ref_nomenclature AS rn ON rn.id=z.ref_nomenclature_id')
  * @param array $params Массив с дополнительными параметрами запроса
  *                      Вид: array([distinct] => 1|0,
  *                                 [order] => 'название поля|полей с алисами таблицы',
  *                                 [limit] => 'будет заменено на "0,1"')
  * @return array|bool Результат запроса к БД
  */
  public function selectOne ($fields, $where, $leftJoin = false, $params = array()) {
    $params['limit'] = '0,1';
    $result = $this->select($fields, $where, $leftJoin, $params);
    return $result[0];
  }

  /**
   * Метод для поиска данных из таблицы по уникальному ключу
   * @param integer $id Id записи
   * @param array $fields Поля, которые необходимо вывести
   * @return array|bool Массив запрашиваемых данных или ошибка при некорректных данных
   */
  public function selectById ($id, $fields = array()) {
    $this->_sql = 'SELECT ' . $this->_fromFields($fields) .
                  ' FROM ' . $this->_tblName . ' AS ' . $this->_tblAlias .
                  'WHERE ' . $this->_idColumn . '=:id';
    $stm = $this->_db->prepare($this->_sql);
    $this->_bindArrayValue($stm, array('id' => $id));
    $stm->execute();
    return $stm->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * @param $data Массив с данными для вставки в таблицу.
   *              Вид: array('название поля в таблице' => 'значение',
   *                         'название поля в таблице' => 'значение',
   *                         ...)
   * @param $where Ассоциативный массив или массив ассоциативных массивов с данными для выборки
   *                      Вид: array('name' => значение,
   *                                 'value' => '',
   *                                 'sign' => 'знак сравнения'(необязательное поле),
   *                                 'alias' => 'алиас для таблицы поля'(необязательное поле))
   * @return mixed Количество обновлённых строк в БД|false в случае ошибки
   */
  public function update($data, $where) {
    if (!is_array($data)) {
      return false;
    }
    try{
      $this->_sql = 'UPDATE ' . $this->_tblName . ' AS ' . $this->_tblAlias .
                    ' SET ' . $this->_dataForInsertUpdate($data) .
                    ' WHERE ' . $this->_dataForWhere($where);
      $this->_stm = $this->_db->prepare($this->_sql);
      $this->_bindArrayValue($this->_stm, array_merge($data, $this->_prepareForBind($where)));
      $this->_stm->execute();
    }
    catch (PDOException $e) {
      echo $e->getMessage();
      return false;
    }
    return $this->getCountRows();
  }

  /**
   * Метод для отображения последнего запроса к БД
   * @return string последнее SQL-выражение, сформированное классом
   */
  public function getSQL() {
    return $this->_sql;
  }

  /**
   * Метод для создания алиаса таблицы, с которым работает класс
   */
  public function createAlias() {
    $fieldArr = explode('_', $this->_tblName);
    if (count($fieldArr) > 1) {
      $this->_tblAlias = mb_substr($fieldArr[0], 0, 1) . mb_substr($fieldArr[1], 0, 3) .
                         ($fieldArr[2] ? mb_substr($fieldArr[2], 0, 1) : '');

    } else {
      $this->_tblAlias = mb_substr($fieldArr[0], 0, 3);
    }
  }


  /**
   * Возвращает текущий PDOStatment объект
   * @return object текущий PDOStatment объект
   */
  public function getStatement() {
    return $this->_stm;
  }

  public function getCountRows() {
    return $this->_stm->columnCount();
  }

  /**
  * Метод для формирования тела вставки для директивы INSERT
  * @param $data Массив с данными для вставки в таблицу.
  *              Вид: array('название поля в таблице' => 'значение',
  *                         'название поля в таблице' => 'значение',
  *                         ...)
  * @return string Строку с параметрами для INSERT
  */
  private function _dataForInsertUpdate($data) {
    $result = '';
    foreach ($data as $key => $val) {
      $result[] = ' ' . $this->_tblAlias . '.' . $key . ' = :' . $key;
    }
    return implode(',', $result);
  }

  /**
   * Метод для привязки значений к параметрам
   * @param object $req объект PDOStatement с SQL-выражением для запроса к БД
   * @param array $array ассоциативный массив c данными для привязки
   * @param array $typeArray ассоциативный массив для связки типа переменной с объявлением типа параметра для PDO выражения
  */
  private function _bindArrayValue($req, $array, $typeArray = false) {
    if (is_object($req) && ($req instanceof PDOStatement)) {
      foreach ($array as $key => $value) {
        if ($typeArray) {
          $req->bindValue(":$key", $value, $typeArray[$key]);
        } else {
          if (is_int($value)) {
            $param = PDO::PARAM_INT;
          } elseif (is_string($value)) {
            $param = PDO::PARAM_STR;
          } elseif (is_bool($value)) {
            $param = PDO::PARAM_BOOL;
          } elseif (is_null($value)) {
            $param = PDO::PARAM_NULL;
          } else {
            $param = false;
          }
          if ($param) {
            $req->bindValue(":$key", $value, $param);
          }
        }
      }
    }
  }

  /**
   * Метод для склеивания запрашиваемых полей из таблиц
   * @param array $data массив полей
   * @return string строка запрашиваемых полей
   */
  private function _fromFields($data = array()) {
    if (!$data) {
      return ' * ';
    }
    // Проверяем есть ли алиас у каждого запрашиваемого значения.
    // Если его не находим, то ставим алиас таблицы по умолчанию
    foreach ($data as $key => $val) {
      if (!mb_strpos($val, '.')) {
        $data[$key] = $this->_tblAlias . '.' . $val;
      }
      if (!is_int($key)) {
        $data[$key] = $data['key'] . ' AS ' . $key;
      }
    }
    return ' ' . implode(' , ', $data);
  }

  /**
   * Метод для построения строки с joinами
   * @param array $data массив со строками присоединений
   * @return string строку cо сформированными присоединениями
   */
  private function _addLeftJoin($data) {
    if (!$data) {
      return '';
    }
    $return = '';
    foreach ($data as $val) {
      $return .= ' LEFT JOIN ' . $val;
    }
    return $return;
  }

  /**
   * Метод для формирования условия отбора для SQL-выражения
   * @param $data Ассоциативный массив или массив ассоциативных массивов с данными для выборки
   *                      Вид записи: array('name' => название поля,
   *                                        'value' => '',
   *                                        'sign' => 'знак сравнения'(необязательное поле),
   *                                        'alias' => 'алиас для таблицы поля'(необязательное поле))
   * @return string строка cо сформированными условиями отбора
   */
  private function _dataForWhere($data){
    if (!$data) {
      return '1 = 1';
    }
    $return = '';
    if (is_array($data) && isset($data[0])) {
      foreach ($data as $val) {
        $return[] = $this->_addWhereItem($val);
      }
      $return = implode(' AND ', $return);
    } else if (is_array($data)) {
      $return .= ' ' . $this->_addWhereItem($data);
    }
    return $return;
  }

  /**
   * Метод для добавления отдельного выражения-условия отбора where
   * @param $data массив с данными для условия
   * @return string часть запроса where
   */
  private function _addWhereItem($data) {
    $sign = $data['sign'] ? $data['sign'] : '=';
    $name = ($data['alias'] ? $data['alias'] : $this->_tblAlias) . '.' . $data['name'];
    return $name . ' ' . $sign . ' :' . $data['name'];
  }

  /**
   * Метод для перобразования м
   * @param $array массив который приходит для выражения where
   */
  private function _prepareForBind($array) {
    $return = array();
    if ($array[0]) {
      foreach ($array as $val) {
        $return[$val['name']] = $val['value'];
      }
    } else {
      $return[$array['name']] = $array['value'];
    }
    return $return;
  }

  /**
   * Метод для инициализации значений по умолчанию и подготовки класса
   */
  private function _initDefaults() {
    if (!$this->_tblAlias) {
      $this->createAlias();
    }
  }

}