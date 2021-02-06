<?php


namespace core\admin\controller;


class ShowController extends BaseAdmin
{

    protected function inputData(){
        $this->execBase();

        $this->createTableData();

        $this->createData(['fields' => ['content']]);

    }

    protected function outputData(){

    }

}