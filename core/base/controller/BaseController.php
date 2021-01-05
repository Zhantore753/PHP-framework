<?php


namespace core\base\controller;


use core\base\exceptions\RouteException;

abstract class BaseController
{
    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    public function route(){
        $controller = str_replace('/', '\\', $this->controller);

        try{
            $object = new \ReflectionMethod($controller, 'request');

            $args = [
                'parameters' => $this->parameters,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod
            ];

            $object->invoke(new $controller, $args);
        }catch(\ReflectionException $e){
            throw new RouteException($e);
        }

    }

    public function request($args){

    }
}