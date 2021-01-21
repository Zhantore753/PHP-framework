<?php

namespace core\admin\controller;


use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{

    protected  function inputData(){

        $db = Model::instance();

        $table = 'teachers';

        $files = '';

        $_POST['id'] = 8;
        $_POST['name'] = '';
        $_POST['content'] = "<p>New' Book1<p>";

        $res = $db->edit($table, ['files' => $files]);

        exit('id = ' . $res['id'] . ' Name = ' . $res['name']);
    }

}