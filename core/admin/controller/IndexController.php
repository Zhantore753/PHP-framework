<?php

namespace core\admin\controller;


use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{

    protected  function inputData(){

        $db = Model::instance();

        $table = 'teachers';

        $color = ['red', 'blue', 'black'];

        $res = $db->get($table, [
            'fields' => ['id', 'name',],
            'where' => ['name' => "O'Raily"],
            'limit' => '1'
        ])[0];

        exit('id = ' . $res['id'] . ' Name = ' . $res['name']);
    }

}