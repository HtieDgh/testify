<?php
require_once './vendor/autoload.php';

$f3=Base::instance();

$f3->config('app/config.ini');
$f3->config('app/routes.ini');
$f3->config('venv/venv.ini'); // Переменные окружения для приложения (их нет в VCS)

$f3->set("SITE_DOMAIN",$f3["HTTP_TYPE"].'://'.$f3["DOMAIN"].$f3["BASE"]);


$f3->run();