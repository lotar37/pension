<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

Yii::setPathOfAlias('bootstrap', dirname(__FILE__).'/../extensions/bootstrap');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Пенсионное обеспечение лиц, проходивших военную службу и членов их семей',
	'language'=>'ru',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
// 		'application.modules.userAdmin.components.DBConnection',
// 		'application.modules.userAdmin.components.PgSchema',
// 		'application.modules.userAdmin.components.DBAuthManager',
// 		'application.modules.userAdmin.components.UserIdentity',
		'application.modules.userAdmin.components.*',
		'application.modules.userAdmin.models.Users',
		
		'application.models.*',
		'application.components.*',
// 			'ext.Numbers.Words.Locale.ru',
// 			'ext.Numbers.words',
		'ext.giix-components.*',

	),
		
// 	'theme'=>'bootstrap',

	'modules'=>array(
		// uncomment the following to enable the Gii tool
	
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'generatorPaths'=>array(
					'ext.giix-core',
					'bootstrap.gii',
					),
			'password'=>'123',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','192.168.238.1'),
		),
		'userAdmin'=>array(
			'dbConnection'=>'dba',
		),
// 		'auth'=>array(
// 			'class'=>'system.gii.GiiModule',
// 			'password'=>'123',
// 			'dbConnection'=>'dba',
// 		),
		'bootstrap',
		
	),

	// application components
	'components'=>array(

		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),

		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
				'/admin' => '/userAdmin',
			), 
		),
			

		// database settings are configured in database.php
		'db'=>require(dirname(__FILE__).'/database.php'),
		'dba'=>require(dirname(__FILE__).'/database_a.php'),

		'authManager'=>array(
				'class'=>'DBAuthManager',
				'connectionID'=>'dba',
				'installPass'=>'1234',
				
		),
		
		
		'bootstrap'=>array(
				'class'=>'bootstrap.components.Bootstrap',
		),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		
	'_log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				array(
					'class'=>'CWebLogRoute',
				),
			),
		),
		
 	'zzlog'=>array(
        'class'=>'CLogRouter',
        'routes'=>array(
            array(
                'class'=>'ext.yii-debug-toolbar.YiiDebugToolbarRoute',
               'ipFilters'=>array('127.0.0.1','192.168.238.1'),
            ),
          ),
        ),

	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	    'MFD_ATTR_SIZE_CONVERT_CM_TO_MM_MULTIPLIER' => 10,
	    'MFD_ATTR_SIZE_CONVERT_CM_TO_PICA_MULTIPLIER' => 72 / 2.54 / 12,
	    'MFD_ATTR_SIZE_CONVERT_CM_TO_POINT_MULTIPLIER' => 72 / 2.54,
	    'MFD_ATTR_SIZE_CONVERT_CM_TO_PIXEL_MULTIPLIER' => 96 / 2.54,
	),
);
