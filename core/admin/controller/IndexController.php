<?php

namespace core\admin\controller;


use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{

    protected  function inputData(){

        $db = Model::instance();

        $table = 'teachers';

        $res = $db->edit($table);

        exit('id = ' . $res['id'] . ' Name = ' . $res['name']);
    }

}