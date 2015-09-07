<?php

class RegisterForm extends CFormModel
{
    /**
     * Form params
     */
    public  $User_Login;
    public  $First_Name;
    public  $Last_Name;
    public  $Email;
    public  $Email_Confirmation;

    public  $Client_ID;
    public  $Fed_ID;
    public  $Company_Name;
    public  $Address1;
    public  $City;
    public  $State;
    public  $ZIP;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array (
            array('Company_Name, Address1, City, State, Fed_ID, ZIP', 'safe'),
            array('User_Login, First_Name, Last_Name, Email, Email_Confirmation', 'required'),
            array('Email_Confirmation', 'required','on'=>'newClientScenario'),
            array('User_Login', 'check_unique'),
            array('Fed_ID', 'check_fed_id', 'on' => 'newClientScenario'),
            array('Client_ID', 'numeric_type'),
            array('Email,Email_Confirmation', 'email'),
            //array('Email', 'unique', 'className' => 'Persons', 'caseSensitive'=>false,'allowEmpty'=>false),
            array('Email_Confirmation', 'email'),
            array('Email_Confirmation', 'compare','compareAttribute'=>'Email','message'=>'Emails do not match - please re-enter'),
            //array('Company_Name, Address1, City, State, Fed_ID, ZIP', 'required', 'on' => 'newClientScenario'),
            array('State', 'length', 'max' => '4', 'on' => 'newClientScenario'),
            array('Address1, City', 'length', 'max' => '45', 'on' => 'newClientScenario'),
            array('ZIP', 'length', 'max' => '15', 'on' => 'newClientScenario'),
            array('ZIP', 'numerical', 'on' => 'newClientScenario'),
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
            'Company_Name' => 'Company name',
            'Address1' => 'Address',
            'City' => 'City',
            'Fed_ID' => 'Fed ID',
            'State' => 'State',
            'ZIP' => 'ZIP Code',
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
     * Check Fed ID rule
     */
    public function check_fed_id() {

        $company = Companies::model()->find('Company_Fed_ID=:Fed_ID',
            array(':Fed_ID'=>$this->Fed_ID));
        if($company != null) {
            $this->addError('Fed_ID','Company with this Fed ID already exists');
        } else if (!preg_match('/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/', $this->Fed_ID)) {
            $this->addError('Fed_ID','Invalid Fed ID, correct formatting: xx-xxxxxxx');
        }
    }

    /**
     * Check numeric type rule
     */
    public function numeric_type() {
         if(!is_numeric($this->Client_ID)) {
              $this->addError('Client_ID','You must choose or create a new company');
         }
    }
}