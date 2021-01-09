<?php


namespace core\base\settings;

use core\base\controller\Singleton;
use core\base\settings\Settings;


class ShopSettings
{

    use Singleton;

    private $baseSettings;

    private $routes = [
        'plugins' => [
            'dir' => false,
            'routes' => [

            ],
        ]
    ];

    private $templateArr = [
        'text' => ['price', 'short'],
        'textarea' => ['goods_content'],
    ];

    static public function get($property){
        return self::getInstance()->$property;
    }

    static private function getInstance(){
        if(self::$_instance instanceof self){
            return self::$_instance;
        }


        self::instance() -> baseSettings = Settings::instance();
        $baseProperties = self::instance()->baseSettings->clueProperties(get_class());
        self::$_instance->setProperty($baseProperties);

        return  self::$_instance;
    }

    protected function setProperty($properties){
        if($properties){
            foreach($properties as $name => $property){
                $this->$name = $property;
            }
        }
    }
}