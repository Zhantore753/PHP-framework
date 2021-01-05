<?php


namespace core\base\controller;


use core\base\exceptions\RouteException;

abstract class BaseController
{
    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    public function route(){
        $controller = str_replace('/', '\\', $this->controller);

        try{
            $object = new \ReflectionMethod($controller, 'request'); // Проверка существует ли метод request в классе $controller
            // возвращается объект

            $args = [
                'parameters' => $this->parameters,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod
            ];

            // в случае если $controller имеет метод request
            $object->invoke(new $controller, $args); // в метод request передается $args
        }catch(\ReflectionException $e){
            throw new RouteException($e->getMessage()); // выкидывает ошибку
        }

    }

    public function request($args){
        $this->parameters = $args['parameters']; //$this->parameters в данном случае достпуен другим наследуемым им контроллерам

        $inputData = $args['inputMethod']; // сохраняем имя входного метода
        $outputData = $args['outputMethod']; // сохраняем имя выходного метода

        $this->$inputData(); // вызов для формирования

        $this->page = $this->$outputData(); // Собираем шаблон по данным которые вернул выходной метод

        if($this->errors){ //пероверка на нличие ошибок
            $this->writeLog(); // логировние ошибок
        }

        $this->getPage(); // Даем браузеру страницу
    }

    protected function render($path = '', $parameters = []){

        extract($parameters); //создание таблицы параметров// создание переменных на основе массива в виде $key = $value

        if(!$path){ // если path не был передан
            $path = TEMPLATE . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0]; // возвращает имя класса в нижнем регистре, который в свою очередь разбивается на массив с помощью explode. И мы берем нулевый элемент этого массива
        }

        ob_start(); // открытие буфера обмена

        if(!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - '.$path); // Проверка на наличие шаблона в дириктории

        return ob_get_clean(); // возвращает то что собрал и закрывется(буфер обмена)
    }

    protected function getPage(){
        exit($this->page);
    }
}