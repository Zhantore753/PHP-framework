<?php


namespace core\admin\controller;


class ShowController extends BaseAdmin
{

    protected function inputData(){
        $this->execBase();

        $this->createTableData();

        $this->createData();

        return $this->expansion(get_defined_vars());

    }

    protected function outputData(){

    }

}