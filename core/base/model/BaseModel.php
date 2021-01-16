<?php

namespace core\base\model;


use core\base\controller\Singleton;
use core\base\exceptions\DbException;

class BaseModel
{

    use Singleton; // используем Singleton

    protected $db; // Свойство $db где будет храниться подключенная база данных

    private function __construct() // переопределяем конструктор
    {
        $this->db = @new \mysqli(HOST, USER, PASS, DB_NAME); // устанаваливаем соединение с базой данных на основе констант

        if($this->db->connect_error){ // Проверяем на подлючение
            throw new DbException('Ошибка подключения к базе данных: ' . $this->db->connect_errno . ' ' . $this->db->connect_error); // выбрасываем ошибку если не удалось подключится
        }

        $this->db->query("SET NAMES UTF8");
    }

    final public function query($query, $crud = 'r', $return_id = false){ // функция запросов к базе данных

        $result = $this->db->query($query); // объект запроса

        if($this->db->affected_rows === -1){ // проверка на наличие запроса
            throw new DbException('Ошибка в SQL запросе: ' . $query . ' - ' . $this->db->errno . ' ' . $this->db->error); // исключение
        }

        switch($crud){ // проверка на метод
            case 'r': // если read
                if($result->num_rows){
                    $res = []; // создаем массив
                    for($i = 0; $i < $result->num_rows; $i++){ // проходимя по объекту содержащим ответ запроса
                        $res[] = $result->fetch_assoc();
                    }
                    return $res; // возвращем результат
                }
                return false;
                break;

            case 'c': // если create
                if($return_id) return $this->db->insert_id; // если return_id не false то возращаем insert_id
                return true;
                break;

            default:
                return true;
                break;
        }

    }

    /**
     * @param $table - Таблицы базы данных
     * @param array $set
     * 'fields' => ['id', 'name',],
     * 'where' => ['fio' => 'Smirnova', 'name' => 'Masha', 'surname' => 'Sergeevna'],
     * 'operand' => ['=', '<>'],
     * 'condition' => ['AND'],
     * 'order' => ['fio', 'name'],
     * 'order_direction' => ['ASC', "DESC"],
     * 'limit' => '1'
     */

    final public function get($table, $set = []){

        $fields = $this->createFields($table, $set);
        $order = $this->createOrder($table, $set);
        $where = $this->createWhere($table, $set);
        $join_arr = $this->createJoin($table, $set);

        $fields .= $join_arr['fields'];
        $join = $join_arr['join'];
        $where .= $join_arr['where'];

        $fields = rtrim($fields, ',');

        $limit = $set['limit'] ? $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);

    }

    protected function createFields($table = false, $set){

        $set['fields'] = (is_array($set['fields']) && !empty($set['fields'])) // проверка на то является ли ячейка fields массивом и на ее наличие впринципе
                            ? $set['fields'] : ['*']; // если проверка прошла то дальше работаем с этой ячейкой иначе берем все то есть передаем '*'

        $table = $table ? $table . '.' : ''; // Проверка на то есть ли вообще $table

        $fields = ''; // создаем переменную $fields

        foreach ($set['fields'] as $field){ // перебираем ячейку fields как $field
            $fields .= $table . $field . ','; // конкатенируем к переменной $table, $field и запятую
        }

        return $fields; // ворзвращаем переменную $fields

    }

    protected function createOrder($table = false, $set){

        $table = $table ? $table . '.' : ''; // Проверка на то есть ли вообще $table

        $order_by = ''; // создаем переменную $order_by

        if(is_array($set['order']) && !empty($set['order'])){ // проверка на то является ли ячейка order массивом и на ее наличие впринципе

            $set['order_direction'] = (is_array($set['order_direction']) && !empty($set['order_direction'])) // проверка на то является ли ячейка order_direction массивом и на ее наличие впринципе
                ? $set['order_direction'] : ['ASC']; // если проверка прошла то дальше работаем с этой ячейкой иначе делаем ASC

            $order_by = 'ORDER BY ';

            $direct_count = 0; // counter

            foreach ($set['order'] as $order){ // перебираем ячейку order как $order

                if($set['order_direction'][$direct_count]){ // если $set['order_direction'][$direct_count] не false или не пуста
                    $order_direction = strtoupper($set['order_direction'][$direct_count]); // поднимаем все в вверхний регистр
                    $direct_count++; // counter++
                }else{ // иначе
                    $order_direction = strtoupper($set['order_direction'][$direct_count - 1]); // поднимаем все в вверхний регистр предыдущую ячейку
                }

                $order_by .= $table . $order . ' ' . $order_direction . ','; // конкатенируем к переменной $table, $order, ' ', $order_direction и запятую
            }

            $order_by = rtrim($order_by, ','); // избавляемся от запятой в конце строки
        }

        return $order_by; // возвращаем $order_by

    }

    protected function createWhere($table = false, $set, $instruction = 'WHERE'){

        $table = $table ? $table . '.' : ''; // Проверка на то есть ли вообще $table

        $where = ''; // создаем переменную $where

        if(is_array($set['where']) && !empty($set['where'])){ // проверка на то является ли ячейка where массивом и на ее наличие впринципе

            $set['operand'] = (is_array($set['operand']) && !empty($set['operand'])) ? $set['operand'] : ['=']; // Проверка на то задан ли операнд если нет то по умолчанию =
            $set['condition'] = (is_array($set['condition']) && !empty($set['condition'])) ? $set['condition'] : ['AND']; // Проверка на то задан ли condition если нет то по умолчанию AND

            $where = $instruction; // записываем в where дополнительный параметр функции

            $o_count = 0; // counter operand
            $c_count = 0; // counter condition

            foreach ($set['where'] as $key => $item){ // перебор ячейки where в виде ключ => значение

                $where .= ' '; // добавляем пробел

                if($set['operand'][$o_count]){ // проверка на не пустотность
                    $operand = $set['operand'][$o_count]; // записываем операнд
                    $o_count++; // увеличиваем counter
                }else{ // иначе
                    $operand = $set['operand'][$o_count - 1]; // записываем операнд с предыдущей ячейки
                }

                if($set['condition'][$c_count]){ // проверка на не пустотность
                    $condition = $set['condition'][$c_count]; // записываем condition
                    $c_count++; // увеличиваем counter
                }else{ // иначе
                    $condition = $set['condition'][$c_count - 1]; // записываем condition с предыдущей ячейки
                }

                if($operand === 'IN' || $operand === 'NOT IN'){ // Если хоть что то из этого есть то идем дальше

                    if(is_string($item) && strpos($item, 'SELECT')){ // Проверка на строку и наличие в ней сточки SELECT
                        $in_str = $item;
                    }else{ // иначе
                        if(is_array($item)) $temp_item = $item; // Проверяем на то массив это или нет есл да то просто кладем в переменную $temp_item
                        else $temp_item = explode(',', $item); // Если же не массив разбивем строку на массив, с помощью указанного разделителя

                        $in_str = ''; // создание переменной

                        foreach ($temp_item as $v){ // перебор массива с значениями $v
                            $in_str .= "'" . trim($v) . "',"; // конкатенируем строку в кавычках и избавляемся от пробелов
                        }
                    }

                    $where .= $table . $key . ' ' . $operand . ' (' . trim($in_str, ',') . ') ' . $condition; // формровние $where

                }elseif((strpos($operand, 'LIKE')) !== false){ // Проверка на на наличие LIKE  в операнде

                    $like_template = explode('%', $operand); // разбиваем с помощью разделителя процента переменную $operand

                    foreach ($like_template as $lt_key => $lt){ // перебор массива в виде ключ => значение
                        if(!$lt){ // если lt false
                            if(!$lt_key){ // если lt_key false
                                $item = '%' . $item;
                            }else{
                                $item .= '%';
                            }
                        }
                    }

                    $where .= $table . $key . ' LIKE ' . "'" . $item . "' $condition"; // формровние $where

                }else{

                    if((strpos($item, 'SELECT')) === 0){
                        $where .= $table . $key . $operand . '(' . $item . ") $condition"; // формровние $where
                    }else{
                        $where .= $table . $key . $operand . "'" . $item . "' $condition"; // формровние $where
                    }

                }

            }

            $where = substr($where, 0, strrpos($where, $condition)); // формровние $where, удаление полседнего condition

        }

        return $where; // возвращаем итоговую $where

    }

}