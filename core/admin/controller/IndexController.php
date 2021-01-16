<?php

namespace core\admin\controller;


use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{

    protected  function inputData(){

        $db = Model::instance();

        $table = 'teachers';

        $res = $db->get($table, [
            'fields' => ['id', 'name',],
            'where' => ['name' => 'masha, olya, sveta', 'name' => 'Masha', 'surname' => 'Sergeevna'],
            'operand' => ['IN', '<>'],
            'condition' => ['AND'],
            'order' => ['fio', 'name'],
            'order_direction' => ['DESC'],
            'limit' => '1'
        ]);

        exit('I am admin panel');
    }

}