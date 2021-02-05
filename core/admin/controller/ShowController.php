<?php


namespace core\admin\controller;


class ShowController extends BaseAdmin
{

    protected function inputData(){
        $this->excetBase();

        $this->createTableData();

        exit();
    }

    protected function outputData(){

    }

}