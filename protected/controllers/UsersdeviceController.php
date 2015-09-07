<?php

class UsersdeviceController extends Controller
{
    /**
     * Layout color
     * @var string
     */
    public $layoutColor = "#0078C1";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('DeleteDevice'),
                'users'=>array('@'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('CheckIfSuperdevice'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('AddSuperDevice'),
                'users'=>array('admin','db_admin'),
            ),

            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionDeleteDevice() {

        if (Yii::app()->request->isAjaxRequest && isset($_POST['device_id'])) {

            $hash = strval($_POST['device_id']);

            //$device = UsersDevices::model()->findByPk($id);
            $devices = UsersDevices::model()->findAllByAttributes(array(
                      'MOB_Hash'=>$hash,
                       'User_ID'=>Yii::app()->user->userID
            ));
            $result =1;

            foreach ($devices as $device) {
                if ($device) {
                    $device->delete();
                    $result= $result *  1;
                } else {
                    $result= $result *  0;
                }
            }
            echo $result;

        }

    }

    /**
     * adds device from witch user can login without login and password
     */
    public function actionAddSuperDevice() {

        if (Yii::app()->request->isAjaxRequest && isset($_POST['hash']) && isset($_POST['user_id'])) {

            $hash = strval($_POST['hash']);

            $device = UsersDevices::model()->findByAttributes(array(
                      'MOB_Hash'=>$hash,
                       'User_ID'=>intval($_POST['user_id'])
            ));

            $result =1;

            if ($device) {
                $device->Super_Login = 1;
                $device->Trusted = 1;

                $device->save();
            }

            echo $result;

        }

    }

    public function actionCheckIfSuperdevice() {

        if (Yii::app()->request->isAjaxRequest) {

            /*$hash = strval($_POST['hash']);


            $device = UsersDevices::model()->findByAttributes(array(
                      'MOB_Hash'=>$hash,
                       'User_ID'=>intval($_POST['user_id'])
            ));

            $result =1;

            if ($device) {
                $device->Super_Login = 1;
                $device->save();
            }*/

            echo 0;

        }

    }
}