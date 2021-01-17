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
     * 'limit' => '1',
     *  'join' => [
            [
                'table' => 'join_table1',
                'fields' => ['id as j_id', 'name as j_name'],
                'type' => 'left',
                'where' => ['name' => 'sasha'],
                'operand' => ['='],
                'condition' => ['OR'],
                'on' => ['id', 'parent_id'],
     *          'group_condition' => 'AND',
            ],
            'join_table1' => [
                'table' => 'join_table2',
                'fields' => ['id as j2_id', 'name as j2_name'],
                'type' => 'left',
                'where' => ['name' => 'sasha'],
                'operand' => ['<>'],
                'condition' => ['AND'],
                'on' => [
                        'table' => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ],
            ],
        ],
     */

    final public function get($table, $set = []){

        $fields = $this->createFields($table, $set);
        $order = $this->createOrder($table, $set);
        $where = $this->createWhere($table, $set);

        if(!$where) $new_where = true;
        else $new_where = false;
        $join_arr = $this->createJoin($table, $set, $new_where);

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

                if(is_int($order)) $order_by .= $order . ' ' . $order_direction . ','; // конкатенация без таблицы если order это число
                else $order_by .= $table . $order . ' ' . $order_direction . ','; // конкатенируем к переменной $table, $order, ' ', $order_direction и запятую
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

    protected function createJoin($table, $set, $new_where = false){

        $fields = '';
        $join = '';
        $where = '';

        if($set['join']){

            $join_table = $table; // определяем таблицу

            foreach ($set['join'] as $key => $item){ // перебираем таблицу как ключ => значение

                if(is_int($key)){ // Проверяем является ли числом
                    if(!$item['table']) continue; // Проверяем на true и продолжаем
                    else $key = $item['table']; // иначе просто записываем в значение ключа
                }

                if($join) $join .= ' '; //  конкатенируем пробел если есть $ join

                if($item['on']){
                    $join_fields = []; // массив с fields

                    switch (2){ // Проверяем на количество элементов массива (сравниваем с двойкой)
                        case count($item['on']['fields']): // Проверяем с ячейкой fields в on
                            $join_fields = $item['on']['fields'];
                            break;

                        case count($item['on']): // Проверяем элементы массива on
                            $join_fields = $item['on'];
                            break;

                        default: // Если ничего из выше перечисленного не подошло
                            continue 2; // Выходим на два уровня цикла вверх
                            break;
                    }

                    if(!$item['type']) $join .= 'LEFT JOIN'; // Проверка на наличие типа присоединения если нет то по умолчанию LEFT JOIN
                    else $join .= trim(strtoupper($item['type'])) . ' JOIN '; // Иначе на всякий случай избавляюсь от про пробелов и ставлю все в верхний регистр а также конкатенирую ' JOIN '

                    $join .= $key . ' ON '; // добавляем ON

                    if($item['on']['table']) $join .= $item['on']['table']; // Проверяем на наличие таблицы
                    else $join .= $join_table; // иначе стыкуем таблицу по умолчанию

                    $join .= '.' . $join_fields[0] . '=' . $key . '.' . $join_fields[0];

                    $join_table =  $key; // записываем текущую таблицу для следующей итерации цикла

                    if($new_where){ // Проверка на наличие new_where

                        if($item['where']){
                            $new_where = false;
                        }

                        $group_condition = 'WHERE';

                    }else{
                        $group_condition = $item['group_condition'] ? strtoupper($item['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($key, $item);
                    $where .= $this->createWhere($key, $item, $group_condition);

                }

            }

        }

        return compact('fields', 'join', 'where');

    }

}