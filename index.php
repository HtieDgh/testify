<?php
require_once './vendor/autoload.php';

$f3=Base::instance();

$f3->config('app/config.ini');
$f3->config('app/routes.ini');
// перемменые окружения для приложения
$f3->config('venv/venv.ini'); 

$f3->set("SITE_DOMAIN",$f3->get("HTTP_TYPE").'://'.$f3->get("DOMAIN").'/'.$f3->get("ROOT_DIR").'/');

$db=new DB\SQL
(
	$f3->get('db_type').':host='.$f3->get('db_host').';port='.$f3->get('db_port').';dbname='.$f3->get('db_name'),
	$f3->get('db_login'),
	$f3->get('db_password')
);

$f3->run();