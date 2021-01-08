<?php


namespace core\base\controller;


use core\base\exceptions\RouteException;
use core\base\settings\Settings;

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

        $data = $this->$inputData(); // вызов для формирования и помещение результата в переменную

        if(method_exists($this, $outputData)){
            $page = $this->$outputData($data);
            if($page) $this->page = $page;
        } // Проверяем на наличие выходного метода если есть то:
        // Собираем шаблон по данным которые вернул выходной метод(передаю то что вернул входной метод)
        elseif ($data){
            $this->page = $data;
        }  // Проверяем на наличие $data если есть то передаем странице

        if($this->errors){ //пероверка на нличие ошибок
            $this->writeLog(); // логировние ошибок
        }

        $this->getPage(); // Даем браузеру страницу
    }

    protected function render($path = '', $parameters = []){

        extract($parameters); //создание таблицы параметров// создание переменных на основе массива в виде $key = $value

        if(!$path){ // если path не был передан

            $class = new \ReflectionClass($this); // берем информацию о классе

            $space = str_replace('\\', '/',$class->getNamespaceName() . '\\'); // запрашиваем пространство имен класса и превращаем в путь
            $routes = Settings::get('routes'); // берем routes для дальнейшей проверки

            if($space === $routes['user']['path']) $template = TEMPLATE; // проверка на user, в переменную $template передаю дефолтный TEMPLATE
            else $template = ADMIN_TEMPLATE; // в переменную $template передаю админский TEMPLATE

            $path = $template . explode('controller', strtolower($class->getShortName()))[0]; // возвращает имя класса в нижнем регистре, который в свою очередь разбивается на массив с помощью explode. И мы берем нулевый элемент этого массива
        }

        ob_start(); // открытие буфера обмена

        if(!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - '.$path); // Проверка на наличие шаблона в дириктории

        return ob_get_clean(); // возвращает то что собрал и закрывется(буфер обмена)
    }

    protected function getPage(){

        if(is_array($this->page)){ // Проверка на является ли $this->page массивом
            foreach ($this->page as $block) echo $block; // перебор массива формировние страницы
        } else{
            echo $this->page;  // формировние страницы
        }
        exit();

    }
}