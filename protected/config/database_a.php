<?php

return array(
		
	'class'=>'DBConnection',
	'connectionString' => 'pgsql:host=localhost;port=5432;dbname=pens',

    'schema' => 'auth',
		
    'username' => 'postgres',
    'password' => '1234567', // обязателен, пустой может не сработать
	'charset' => 'utf8',
    'autoConnect' => false, // не устанавливать соединение при старте приложения - для оптимизации
    'enableProfiling' => true,
	
  'emulatePrepare' => true,
  'enableParamLogging' => 0,

	
);
?>