<?php

define('VG_ACCESS', true);

header('Content-Type:text/html;charset-utf-8');
session_start();

require_once 'config.php'; //
require_once 'core/base/settings/internal_settings.php';

use core\base\exceptions\RouteException;
use core\base\controller\RouteController;

use core\base\settings\Settings;

$s = Settings::get('routes');
$s1 = Settings::get('templateArr');

try{
    RouteController::instance()->route();
}catch(RouteException $e){
    exit($e->getMessage());
}