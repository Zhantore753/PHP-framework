<?php

namespace core\admin\controller;


use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{

    protected  function inputData(){

        $db = Model::instance();

        $query = "SELECT teachers.id, teachers.name, students.id as s_id, students.name as s_name 
                    FROM teachers
                    LEFT JOIN stud_teach ON teachers.id = stud_teach.teachers
                    LEFT JOIN students ON stud_teach.students = students.id
                    ";

        $res = $db->query($query);

        exit('I am admin panel');
    }

}