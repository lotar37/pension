<?php

// This is the database connection configuration.
return array(
	//'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
	// uncomment the following lines to use a MySQL database
	/*
	'connectionString' => 'mysql:host=localhost;dbname=testdrive',
	'emulatePrepare' => true,
	'username' => 'root',
	'password' => '',
	'charset' => 'utf8',
	*/
	'connectionString' => 'pgsql:host=localhost;port=5432;dbname=pens',
    'username' => 'postgres',
    'password' => '1234567', // обязателен, пустой может не сработать
    'charset' => 'utf8',
    'autoConnect' => false, // не устанавливать соединение при старте приложения - для оптимизации
    'enableProfiling' => true,
	
  'emulatePrepare' => true,
  //'charset' => 'utf8',
  'enableParamLogging' => 0,
 // 'enableProfiling' => true,
  //'schemaCachingDuration' => 0,
  //'tablePrefix' => '',
	
	
);