<?php


namespace core\base\controller;

//был создан исключительно для того чтобы не дублировать код
trait Singleton
{
    static private $_instance;

    private function __construct(){}

    private function __clone(){}

    static public function instance(){
        if(self::$_instance instanceof self){
            return self::$_instance;
        }
        return  self::$_instance = new self;
    }
}