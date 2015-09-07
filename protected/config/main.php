<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'D-APC',

	// preloading 'log' component
	'preload'=>array('log','config'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
        'admin',
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'123',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
        */
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),

        'config'=>array(
            'class' => 'DConfig',
        ),

		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
            'showScriptName' => false,
			'rules'=>array(
                                '<controller:register>/<auth_url:[\w\-]+>'=>'register/index',
                                '<controller:[^myaccount]\w+>.<extension:(txt|xml)>'=>'directfiles/index',
                                '<controller:\w+>.xml'=>'<controller>/index',
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),

		'db'=>array(
			'connectionString' => 'mysql:host=asa-prod.cimiyfizt4vn.us-west-2.rds.amazonaws.com;dbname=asaap',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'LACMA2486',
			'charset' => 'utf8',
		),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		
		'session' => array (
		
		                'class' => 'application.components.AsaCDbHttpSession',
                                'connectionID' => 'db',
                                'sessionTableName' => 'sessions',
		),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
			    /*
				array(
					'class'=>'CFileLogRoute',
                    'levels'=>'error, warning, notice, strict, info, trace',
				),
				*/
                array (
                    'class'=>'CDbLogRoute',
                    'connectionID'=>'db',
                    'levels'=>'error, warning, notice, strict, info',
                ),
                array(
                    'class'=>'CEmailLogRoute',
                    'levels'=>'error, warning',
                    'except'=>'system.CModule.*, exception.CHttpException.404, exception.CHttpException.403',
                    'emails'=>'alitvinov@acceptic.com',
                ),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),

        'Paypal' => array(
        'class'=>'application.components.Paypal',
        'apiLive' => false,
        'returnUrl' => 'paypal/confirmpayment/', //regardless of url management component
        'cancelUrl' => 'paypal/cancelpayment/', //regardless of url management component      
  // Default currency to use, if not set USD is the default
        'currency' => 'USD',
        // Default description to use, defaults to an empty string
        //'defaultDescription' => '',
        // Default Quantity to use, defaults to 1
        //'defaultQuantity' => '1',
        //The version of the paypal api to use, defaults to '3.0' (review PayPal documentation to include a valid API version)
        //'version' => '3.0',
        ),
      ),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(		
        'dbhost' => 'asa-prod.cimiyfizt4vn.us-west-2.rds.amazonaws.com',
        'dbuser' => 'root',
        'dbpassword' => 'LACMA2486',
        'dbname' => 'asaap',
	),
);
