<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Cron',
    // preloading 'log' component
    //
    'preload'=>array(
        'log',
    ),

    // autoloading model and component classes
    'import'=>array(
        'application.models.*',
        'application.components.*',
        'application.controllers.*',

    ),


    // application components
    'components'=>array(

        'user'=>array(
            // enable cookie-based authentication
            //'allowAutoLogin'=>false,
            'class' => 'UserIdentity',
        ),

        'config'=>array(
            'class' => 'DConfig',
        ),


        'db'=>array(
            'class'=>'CDbConnection',
            'connectionString' => 'mysql:host=asa-prod.cimiyfizt4vn.us-west-2.rds.amazonaws.com;dbname=asaap',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'LACMA2486',
            'charset' => 'utf8',
        ),


        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'logFile'=>'cron.log',

                ),
                array(
                    'class'=>'CEmailLogRoute',
                    'levels'=>'error, warning',
                    'except'=>'system.CModule.*, exception.CHttpException.404, exception.CHttpException.403',
                    'emails'=>'alitvinov@acceptic.com',
                ),

            ),
        ),
    ),

);
