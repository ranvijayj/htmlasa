<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    /**
     * Authenticates a user.
     * Makes sure if the username and password
     * @param int $timezoneOffset
     * @return boolean whether authentication succeeds.
     */
    public function authenticate($timezoneOffset = 0)
	{
		$user = Users::model()->getAuthenticateInfo($this->username,$this->password,UsersDevices::getDevHash(''));
		if(!empty($user->User_ID)) {
            $user->Last_IP = $_SERVER['REMOTE_ADDR'];
            $user->Last_Login = date("Y-m-d H:i:s");
            $user->save();

            // Set user info
            $this->setState('userInfo', $user->person->First_Name . ' ' . $user->person->Last_Name);
            $this->setState('userID', $user->User_ID);
            $this->setState('userLogin', $user->User_Login);
            $this->setState('userTimezoneOffset', $timezoneOffset);

            // get default client and project
            $default_client = false;
            $condition = new CDbCriteria();
            $condition->condition = "users_project_list.User_ID = '" . $user->User_ID . "'";
            $condition->addCondition("users_project_list.Project_ID = '" . $user->Default_Project . "'");
            $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
            $default_project = Projects::model()->with('client.company')->find($condition);
            if ($default_project) {
                $default_client = $default_project->client;
            } else {
                $settings = $user->settings;
                $settings->scenario = 'newClientScenario';
                $settings->Default_Project = 0;
                $settings->save();

                $condition = new CDbCriteria();
                $condition->condition = "users_project_list.User_ID = '" . $user->User_ID . "'";
                $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                $default_project = Projects::model()->with('client.company')->find($condition);
                if ($default_project) {
                    $user->Default_Project = $default_project->Project_ID;
                    $user->save();
                    $default_client = $default_project->client;
                } else {
                    $user->Default_Project = 0;
                    $user->save();
                }
            }

            // get client
            $client = false;
            foreach ($user->clients as $cl) {
                if ($cl->Client_ID == $default_client->Client_ID) {
                    $client = $cl;
                    break;
                } else {
                    $client = $cl;
                }
            }

            if ($client) {
                $this->setState('clientInfo', $client->company->Company_Name);
                $this->setState('clientID', $client->Client_ID);

                $projects = UsersProjectList::model()->with('project')->findAllByAttributes(array(
                    'User_ID' =>$user->User_ID,
                    'Client_ID' =>$client->Client_ID,
                ));

                if ($default_project) {
                    $project = $default_project;
                    $this->setState('projectInfo', $project->Project_Name);
                    $this->setState('projectID', $project->Project_ID);
                    $this->errorCode=self::ERROR_NONE;
                } else if ($projects) {
                    $project = $projects[0];
                    $this->setState('projectInfo', $project->project->Project_Name);
                    $this->setState('projectID', $project->Project_ID);
                    $this->errorCode=self::ERROR_NONE;
                } else {
                    $this->setState('projectInfo', 'No project');
                    $this->setState('projectID', 0);
                    $this->errorCode=self::ERROR_UNKNOWN_IDENTITY;
                }

                //set user's tier settings

                //check for delayed settings
                DelayedClientServiceSettings::checkDate(date('Y-m-d'),$client->Client_ID);

                $user_client_settings = $client->service_settings;
                $user_tier_settings = TiersSettings::agregateTiersSettings($user_client_settings->Service_Level_ID);
                $this->setState('tier_settings', $user_tier_settings);

                $userClientRelation = UsersClientList::model()->findByAttributes(array(
                    'User_ID' =>$user->User_ID,
                    'Client_ID' =>$client->Client_ID,
                ));

                // set user type from user-client relation
                if ($userClientRelation->User_Type == UsersClientList::CLIENT_ADMIN) {
                    $this->username = 'client_admin';
                } else if ($userClientRelation->User_Type == UsersClientList::APPROVER) {
                    $this->username = 'approver';
                } else if ($userClientRelation->User_Type == UsersClientList::PROCESSOR) {
                    $this->username = 'processor';
                } else if ($userClientRelation->User_Type == UsersClientList::USER) {
                    $this->username = 'user';
                } else {
                    $this->username = 'user';
                }

                // reset user type if he id Admin, DB Admin OR DEC
                if ($user->User_Type == Users::ADMIN) {
                    $this->username = 'admin';
                } else if ($user->User_Type == Users::DB_ADMIN) {
                    $this->username = 'db_admin';
                } else if ($user->User_Type == Users::DATA_ENTRY_CLERK) {
                    $this->username = 'data_entry_clerk';
                }

                if ($user->User_Type == Users::USER) {
                    $this->setState('userType', $userClientRelation->User_Type);
                } else {
                    $this->setState('userType', $user->User_Type);
                }
            } else {
                $this->setState('clientInfo', 'No company');
                $this->setState('clientID', 0);
                $this->setState('projectInfo', 'No project');
                $this->setState('userType', 'Single User');
                $this->setState('projectID', 0);
                $this->username = 'single_user';
                $this->errorCode=self::ERROR_NONE;
            }

            UserLog::createLogRecord($user->User_ID, '', 0, 0);
        }
		return !$this->errorCode;
	}


    /**
     * ReAuthenticates a user.
     * @param int $timezoneOffset
     * @return boolean whether authentication succeeds.
     */
    public function reauthenticate($timezoneOffset = 0)
    {
        $user = Users::model()->with('person','clients')->find('User_Login=:login',
            array(':login'=>$this->username));
        if(!empty($user->User_ID)) {
            $user->Last_IP = $_SERVER['REMOTE_ADDR'];
            $user->Last_Login = date("Y-m-d H:i:s");
            $user->save();

            // Set user info
            $this->setState('userInfo', $user->person->First_Name . ' ' . $user->person->Last_Name);
            $this->setState('userID', $user->User_ID);
            $this->setState('userLogin', $user->User_Login);
            $this->setState('userTimezoneOffset', $timezoneOffset);



            // get client
            $client = false;
            foreach ($user->clients as $cl) {
                if ($cl->Client_ID == $_SESSION['last_client']) {
                    $client = $cl;
                    break;
                } else {
                    $client = $cl;
                }
            }

            //set user's tier settings

            //check for delayed settings
            DelayedClientServiceSettings::checkDate(date('Y-m-d'),$client->Client_ID);

            $user_client_settings = $client->service_settings;
            $user_tier_settings = TiersSettings::agregateTiersSettings($user_client_settings->Service_Level_ID);
            $this->setState('tier_settings', $user_tier_settings);

            if ($client && $client->company) {
                $this->setState('clientInfo', $client->company->Company_Name);
                $this->setState('clientID', $client->Client_ID);

                $userClientRow = UsersClientList::model()->findByAttributes(array(
                    'User_ID' => $user->User_ID,
                    'Client_ID' => $client->Client_ID,
                ));

                //get projects
                if ($userClientRow->hasClientAdminPrivileges()) {
                    $projects = Projects::model()->findAllByAttributes(array(
                        'Client_ID' => $client->Client_ID,
                    ));
                } else {
                    $condition = new CDbCriteria();
                    $condition->condition = "users_project_list.User_ID = '" . Yii::app()->user->userID . "'";
                    $condition->addCondition("t.Client_ID = '" . $client->Client_ID . "'");
                    $condition->join = "LEFT JOIN users_project_list ON users_project_list.Project_ID = t.Project_ID";
                    $projects = Projects::model()->findAll($condition);
                }

                if ($projects) {
                    if (isset($_SESSION['last_project']) && $_SESSION['last_project'] == 'all') {
                        $this->setState('projectInfo', 'All Projects');
                        $this->setState('projectID', 'all');
                        unset($_SESSION['last_project']);
                        $this->errorCode=self::ERROR_NONE;
                    } elseif (isset($_SESSION['last_project']) && is_numeric($_SESSION['last_project'])) {
                        foreach($projects as $project) {
                            if ($project->Project_ID == $_SESSION['last_project']) {
                                $this->setState('projectInfo', $project->Project_Name);
                                $this->setState('projectID', $project->Project_ID);
                                unset($_SESSION['last_project']);
                                $this->errorCode=self::ERROR_NONE;
                                break;
                            }
                        }
                    } else {
                        $project = $projects[0];
                        $this->setState('projectInfo', $project->Project_Name);
                        $this->setState('projectID', $project->Project_ID);
                        $this->errorCode=self::ERROR_NONE;
                    }
                } else {
                    $this->setState('projectInfo', 'No project');
                    $this->setState('projectID', 0);
                    $this->errorCode=self::ERROR_UNKNOWN_IDENTITY;
                }

                if ($userClientRow->User_Type == UsersClientList::CLIENT_ADMIN) {
                    $this->username = 'client_admin';
                } else if ($userClientRow->User_Type == UsersClientList::APPROVER) {
                    $this->username = 'approver';
                } else if ($userClientRow->User_Type == UsersClientList::PROCESSOR) {
                    $this->username = 'processor';
                } else if ($userClientRow->User_Type == UsersClientList::USER) {
                    $this->username = 'user';
                } else {
                    $this->username = 'user';
                }

                // reset user type if he id Admin, DB Admin OR DEC
                if ($user->User_Type == Users::ADMIN) {
                    $this->username = 'admin';
                } else if ($user->User_Type == Users::DB_ADMIN) {
                    $this->username = 'db_admin';
                } else if ($user->User_Type == Users::DATA_ENTRY_CLERK) {
                    $this->username = 'data_entry_clerk';
                }

                if ($user->User_Type == Users::USER) {
                    $this->setState('userType', $userClientRow->User_Type);
                } else {
                    $this->setState('userType', $user->User_Type);
                }
            } else {
                $this->setState('clientInfo', 'No company');
                $this->setState('clientID', 0);
                $this->setState('projectInfo', 'No project');
                $this->setState('projectID', 0);
                $this->setState('userType', 'Single User');
                $this->username = 'single_user';
                $this->errorCode=self::ERROR_NONE;
            }
        }
        return !$this->errorCode;
    }
}