<?php


namespace core\admin\controller;


use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class ShowController extends BaseAdmin
{

    protected function inputData(){

        if(!$this->userId) $this->execBase();

        $this->createTableData();

        $this->createData(['fields' => 'content']);

        return $this->expansion(get_defined_vars());

    }

    protected function createData($arr = []){

        $fields = [];
        $order = [];
        $order_direction = [];

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
            if(is_array($arr['fields'])){
                $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
            }else{
                $fields[] = $arr['fields'];
            }
        }

        if($this->columns['parent_id']){
            if(!in_array('parent_id' ,$fields)) $fields[] = 'parent_id';
            $order[] = 'parent_id';
        }

        if($this->columns['menu_position']) $order[] = 'menu_position';
        elseif ($this->columns['date']){
            if($order) $order_direction = ['ASC', 'DESC'];
            else $order_direction[] = 'DESC';

            $order[] = 'date';
        }

        if($arr['order']){
            if(is_array($arr['order'])){
                $order = Settings::instance()->arrayMergeRecursive($order, $arr['order']);
            }else{
                $order[] = $arr['order'];
            }
        }
        if($arr['order_direction']){
            if(is_array($arr['order_direction'])){
                $order_direction = Settings::instance()->arrayMergeRecursive($order_direction, $arr['order_direction']);
            }else{
                $order_direction[] = $arr['order_direction'];
            }
        }

        $this->data = $this->model->get($this->table, [
            'fields' => $fields,
            'order' => $order,
            'order_direction' => $order_direction
        ]);

    }

}