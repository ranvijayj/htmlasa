<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class ForgotPasswordForm extends CFormModel
{
	public $username;
	public $email;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			//array('email,username', 'required'),
			array('email', 'checkEmail'),
            array('username', 'check_username'),
			array('username', 'checkEmptyValue'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
            'username'=>'Enter your account username',
			'email'=>'Enter your email address',
		);
	}

    /**
     * Check username rule
     */
    public function check_username() {
        if ($this->username != '') {
            $user = Users::model()->find('User_Login=:login',
                array(':login'=>$this->username));
            if($user == null) {
                $this->addError('username','Login does not exists');
            }
        }
    }

    /**
     * Check email rule
     */
    public function checkEmail() {
        if ($this->email != '') {
            $pattern = '/^([0-9a-zA-Z]([\-\.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][\-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/';
            if(!preg_match($pattern, $this->email)) {
                $this->addError('email','Email is not valid');
            } else {
                $person = Persons::model()->find('Email=:email',
                    array(':email'=>$this->email));
                if (!$person) {
                    $this->addError('email','Email does not exists');
                }
            }
        }
    }

    /**
     * Check empty value rule
     * @param $attribute
     * @param $params
     */
    public function checkEmptyValue($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			if(trim($this->username) == '' && $this->email == '') {
                $this->addError('email','You must enter username or email.');
            }
		}
	}
}
