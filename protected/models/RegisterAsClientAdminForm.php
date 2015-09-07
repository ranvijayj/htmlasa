<?php

class RegisterAsClientAdminForm extends CFormModel
{
    /**
     * Form params
     */
    public  $User_Login;
    public  $First_Name;
    public  $Last_Name;
    public  $Email;
    public  $Client_ID;
    public  $Auth_Code;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array (
            array('User_Login, First_Name, Last_Name, Email, Auth_Code', 'required'),
            array('User_Login', 'check_unique'),
            array('Email', 'email'),
            array('Auth_Code', 'check_auth'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'User_Login' => 'User login',
            'First_Name' => 'First name',
            'Email' => 'Email',
            'Last_Name' => 'Last name',
            'Client_ID' => 'Company',
            'Auth_Code' => 'Authorization Code from the letter',
        );
    }

    /**
     * Check unique rule
     */
    public function check_unique() {
            $user = Users::model()->find('User_Login=:login',
                array(':login'=>$this->User_Login));
            if($user != null) {
                $this->addError('User_Login','Login exists');
            }
    }

    /**
     * Check Auth_Code rule
     */
    public function check_auth() {
        $client = Clients::model()->findByPk($this->Client_ID);
        if($client) {
            $company = Companies::model()->findByPk($client->Company_ID);
            if ($company->Auth_Code != $this->Auth_Code) {
                $this->addError('Auth_Code','Invalid Authorization Code');
            }
        } else {
            $this->addError('Auth_Code',"Company with this Authorization Code doesn't exists");
        }
    }
}