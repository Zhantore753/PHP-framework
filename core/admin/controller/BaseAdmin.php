<?php


namespace core\admin\controller;

use core\admin\model\Model;
use core\base\controller\BaseController;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseAdmin extends BaseController
{

    protected $model;

    protected $table;
    protected $columns;
    protected $data;

    protected $menu;
    protected $title;

    protected function inputData(){

        $this->init(true);

        $this->title = 'VG engine';

        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');

        $this->sendNoCacheHeaders();

    }

    protected function outputData(){

    }

    protected function sendNoCacheHeaders(){
        header("Last-Modified: " . gmdate("D, d m Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: max-age=0");
        header("Cache-Control: post-check=0,pre-check=0");
    }

    protected function execBase(){
        self::inputData();
    }

    protected function createTableData(){

        if(!$this->table){
            if($this->parameters) $this->table = array_keys($this->parameters)[0];
            else $this->table = Settings::get('defaultTable');
        }

        $this->columns = $this->model->showColumns($this->table);

        if(!$this->columns) new RouteException('Не найдены поля в таблице - ' . $this->table, 2);

    }

    protected function createData($arr = [], $add = true){

        $fields = [];
        $order = [];
        $order_direction = [];

        if($add){

            if(!$this->columns['id_row']) return $this->data = [];

            $fields[] = $this->columns['id_row'] . ' as id';
            if($this->columns['name']) $fields['name'] = 'name';
            if($this->columns['img']) $fields['img'] = 'img';

            if(count($fields) < 3){
                foreach ($this->columns as $key => $item){
                    if(!$fields['name'] && (strpos($key, 'name')) !== false){
                        $fields['name'] = $key . ' as name';
                    }
                    if(!$fields['img'] && (strpos($key, 'img')) === 0){
                        $fields['img'] = $key . ' as img';
                    }
                }
            }

            if($arr['fields']){
                $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
            }

            if($this->columns['parent_id']){
                if(!in_array('parent_id' ,$fields)) $fields[] = 'parent_id';
                $order[] = 'parent_id';
            }

            if($this->columns['menu_position']) $order[] = 'menu_position';
            elseif ($this->columns['date']){
               if($order) $order_direction = ['ASC', 'DESC'];
               else $order_direction[] = ['DESC'];

               $order[] = 'date';
            }

            if($arr['order']){
                $order = Settings::instance()->arrayMergeRecursive($order, $arr['order']);
            }
            if($arr['order_direction']){
                $order_direction = Settings::instance()->arrayMergeRecursive($order_direction, $arr['order_direction']);
            }

        }else{

            if(!$arr) return $this->data = [];

            $fields = $arr['fields'];
            $order = $arr['order'];
            $order_direction = ['order_direction'];

        }

        $this->data = $this->model->get($this->table, [
            'fields' => $fields,
            'order' => $order,
            'order_direction' => $order_direction
        ]);

        exit;

    }

}