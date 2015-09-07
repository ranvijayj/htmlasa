<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;
    public $timezoneOffset;
    public $resolution;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('username, password, timezoneOffset', 'required'),
			// rememberMe needs to be a boolean
            array('timezoneOffset', 'numerical', 'integerOnly'=>true),
            array('resolution', 'length', 'max'=>14),
			array('rememberMe', 'boolean'),
			// password needs to be authenticated
			array('password', 'authenticate'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'rememberMe'=>'Remember me next time',
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_identity = new UserIdentity($this->username, $this->password);
			if(!$this->_identity->authenticate($this->timezoneOffset,$this->resolution)) {
                $this->addError('password','Incorrect username or password.');
            }
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if($this->_identity === null)
		{
                      $this->_identity = new UserIdentity($this->username,$this->password);
			          $this->_identity->authenticate($this->timezoneOffset);
		}

            if($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
                $duration = $this->rememberMe ? 3600*24*30 : 0; // 30 days
                Yii::app()->user->login($this->_identity, $duration);//here Yii::app()->user->userID become available
                return true;
            } else {
                return false;
            }
	}

    /**
     * Relogin user.
     * @return boolean whether login is successful
     */
    public function relogin()
    {
        $user_login = Yii::app()->user->userLogin;
        $timezoneOffset = Yii::app()->user->userTimezoneOffset;
        //Yii::app()->user->logout();
        if($this->_identity === null)
        {
            $this->_identity = new UserIdentity($user_login, 'temp100');
            $this->_identity->reauthenticate($timezoneOffset);
        }

        if($this->_identity->errorCode === UserIdentity::ERROR_NONE)
        {
            $duration = $this->rememberMe ? 3600*24*30 : 0; // 30 days
            Yii::app()->user->login($this->_identity, $duration);
            return true;
        }
        else
            return false;
    }

    /**
     * Relogin user.
     * Not used yet.
     * @return boolean whether login is successful
     */
    public function reloginByUid($user_id)
    {

        $user = Users::model()->findByPk($user_id);

        $user_devices = UsersDevices::model()->findByAttributes(array('User_ID'=>$user_id));

        $user_login = $user->User_Login;
        $timezoneOffset = $user_devices->TymeZone;
        //$timezoneOffset = Yii::app()->user->userTimezoneOffset;
        //Yii::app()->user->logout();
        if($this->_identity === null)
        {
            $this->_identity = new UserIdentity($user_login, 'temp100');
            $this->_identity->reauthenticate($timezoneOffset);
        }

        if($this->_identity->errorCode === UserIdentity::ERROR_NONE)
        {
            $duration = $this->rememberMe ? 3600*24*30 : 0; // 30 days
            Yii::app()->user->login($this->_identity, $duration);
            return true;
        }
        else
            return false;
    }
}