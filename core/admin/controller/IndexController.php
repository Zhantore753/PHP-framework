<?php

namespace core\admin\controller;


use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{

    protected  function inputData(){

        $db = Model::instance();

        $table = 'teachers';

        $files['gallery_img'] = ['red.jpg', 'blue.jpg', 'black.jpg'];
        $files['img'] = 'main_img.jpg';

        $res = $db->add($table, [
            'fields' => ['name' => 'Katya', 'content' => 'Hello'],
            'except' => ['name'],
            'files' => $files,
        ]);

        exit('id = ' . $res['id'] . ' Name = ' . $res['name']);
    }

}