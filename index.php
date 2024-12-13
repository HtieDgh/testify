<?php
require_once './vendor/autoload.php';

$f3=Base::instance();

$f3->config('app/config.ini');
$f3->config('app/routes.ini');

$db=new DB\SQL
(
	$f3->get('db_type').':host='.$f3->get('db_host').';port='.$f3->get('db_port').';dbname='.$f3->get('db_name'),
	$f3->get('db_login'),
	$f3->get('db_password')
);

$f3->set('DEBUG',4);

$f3->run();