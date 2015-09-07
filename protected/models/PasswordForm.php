<?php

class PasswordForm extends CFormModel
{
    public  $oldPass;
    public  $newPass;
    public  $newPass2;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array (
            array('oldPass, newPass, newPass2', 'required'),
            array('oldPass, newPass, newPass2', 'length', 'min'=>3),
            array('newPass2', 'compare', 'compareAttribute'=>'newPass'),
            array('oldPass', 'checkPassword'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'oldPass' => 'Old Password',
            'newPass' => 'Change Password',
            'newPass2' => 'Confirm Change',
        );
    }

    /**
     * Check password rule
     */
    public function checkPassword() {
            $user = Users::model()->findByPk(Yii::app()->user->userID);
            if($user->User_Pwd != md5($this->oldPass)) {
                $this->addError('oldPass','Incorrect old password');
            }
    }
}