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
        $where = $this->createWhere($table, $set);

        $join_arr = $this->createJoin($table, $set);

        $fields .= $join_arr['fields'];
        $join = $join_arr['join'];
        $where .= $join_arr['where'];

        $fields = rtrim($fields, ',');

        $order = $this->createOrder($table, $set);

        $limit = $set['limit'] ? $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);

    }

}