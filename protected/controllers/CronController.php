<?php

class CronController extends Controller
{
    protected function afterAction($action)
    {
        $time = sprintf('%0.5f', Yii::getLogger()->executionTime);
        $memory = round(memory_get_peak_usage()/(1024*1024), 2) . 'MB';
        echo "Time: $time, Memory: $memory";
        parent::afterAction($action);
    }

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested
	 */
	public function actionIndex($verification_code, $action)
	{
        if ($verification_code == Yii::app()->config->get('CRON_VERIFICATION_CODE')) {

            }
         else {
            $this->redirect('/');

        }

	}


    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested
     */
   public static function CommandIndex($verification_code, $action)
    {
        if ($verification_code == Yii::app()->config->get('CRON_VERIFICATION_CODE')) {
            switch ($action) {
                case 'generateapmailslist':
                    self::generateAPMailsList();
                    break;

                case 'generatepomailslist':
                    self::generatePOMailsList();
                    break;

                case 'sendmailslist':
                    self::sendMailsList();
                    break;

                case 'expirationdatenotification':
                    self::expirationDateNotification();
                    break;

                case 'testnotification':
                    self::testnotification();
                    break;

                case 'dailymailing':
                    self::dailymailing();
                    break;
                case 'cachefilesremoving':
                    self::cachefilesremoving();
                    break;

            //    case 'sendApprovalDataEntryRequiredNotification':
              //      Mail::sendApprovalDataEntryRequiredNotification('alitvinov@acceptic.com','Andrew','Litvinov','TestCompany');
                //    break;

                case 'junkfilesremoving':
                    self::junkfilesremoving();
                    break;


                default:
                    echo "Wrong parameters were given!\n";
            }
        } else {
            echo("\nError parameters were given!\n");

        }

    }
    /**
     * This method generates emails to send to users about pending approval APs
     */
    public static function generateAPMailsList()
    {
        echo "  Setting memory limit...\n";
        ini_set('memory_limit', '512M');
        // get aps to approve
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN users_settings ON users_settings.User_ID=t.User_ID";
        $condition->condition = "users_settings.Notification='0'";
        $condition->addCondition("t.Active = '1'");

        $users = Users::model()->with('person', 'clients')->findAll($condition);

        $template = MailTemplates::model()->findByPk(4015);
        $templateBody = $template->Message_Body;
        foreach($users as $user) {
            $clientsToApprove = array();

            // check AP to approve for every Company
            if ($user->clients) {

                foreach ($user->clients as $client) {
                    echo "    Inside foreach - users as cliens founded...\n";
                    $userToClient = UsersClientList::model()->findByAttributes(array(
                        'User_ID' => $user->User_ID,
                        'Client_ID' => $client->Client_ID,
                    ));

                    //get pervios user approval value
                    $condition = new CDbCriteria();
                    $condition->select = 'User_Approval_Value';
                    $condition->condition = "t.Client_ID='" . $client->Client_ID . "'";
                    $condition->addCondition("t.User_Approval_Value < '" . $userToClient->User_Approval_Value . "'");
                    $condition->order = "t.User_Approval_Value DESC";
                    $perviosUserApproval = UsersClientList::model()->find($condition);

                    if ($perviosUserApproval) {
                        $LastApproverValue = $perviosUserApproval->User_Approval_Value;
                    } else {
                        $LastApproverValue = 1;
                    }

                    // get aps to approve
                    $condition = new CDbCriteria();
                    $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT JOIN vendors ON vendors.Vendor_ID=t.Vendor_ID
                            LEFT JOIN clients ON vendors.Vendor_Client_ID=clients.Client_ID
                            LEFT JOIN companies ON clients.Company_ID=companies.Company_ID";
                    $condition->condition = "documents.Client_ID='" . $client->Client_ID . "'";
                    $condition->addCondition("t.AP_Approval_Value < '" . $userToClient->User_Approval_Value . "'");
                    $condition->addCondition("t.AP_Approval_Value >= '" . $LastApproverValue . "'");
                    $condition->addCondition("t.AP_Approval_Value != '0'");
                    $condition->order = "companies.Company_Name ASC";
                    $aps = Aps::model()->find($condition);

                    var_dump($condition);
                    if ($aps) {
                        $clientsToApprove[] = $client->company->Company_Name;
                    }
                }
            }

            // if AP to approve exists, create mail and write it into DB
            if ($clientsToApprove) {
                echo "Documents for approve have bben finded ...\n";
                $clientsList = '';
                foreach ($clientsToApprove as $key => $clientToApprove) {
                    $clientsList .= "<p>&nbsp;&nbsp;" . ($key+1) .") " . CHtml::encode($clientToApprove) . "<p>";
                }

                $replacedValues = array(
                    '{{first_name}}' => $user->person->First_Name,
                    '{{last_name}}' => $user->person->Last_Name,
                    '{{list}}' => $clientsList,
                    '{{ap_link}}' => Yii::app()->config->get('SITE_URL') . '/ap',
                    '{{doc_type}}' => Documents::AP,
                );

                $mail = new MailsToDelivery();
                $mail->Email = $user->person->Email;
                $mail->Body = Helper::replaceMatches($templateBody, $replacedValues);
                $mail->save();
            }
        }
    }

    /**
     * This method generates emails to send to users about pending approval POs
     */
    private function generatePOMailsList()
    {
        ini_set('memory_limit', '512M');
        // get users to approve
        $condition = new CDbCriteria();
        $condition->join = "LEFT JOIN users_settings ON users_settings.User_ID=t.User_ID";
        $condition->condition = "users_settings.Notification='0'";
        $condition->addCondition("t.Active = '1'");
        $users = Users::model()->with('person', 'clients')->findAll($condition);

        $template = MailTemplates::model()->findByPk(4015);
        $templateBody = $template->Message_Body;

        foreach($users as $user) {
            $clientsToApprove = array();

            // check AP to approve for every Company
            if ($user->clients) {
                foreach ($user->clients as $client) {
                    $userToClient = UsersClientList::model()->findByAttributes(array(
                        'User_ID' => $user->User_ID,
                        'Client_ID' => $client->Client_ID,
                    ));

                    //get pervios user approval value
                    $condition = new CDbCriteria();
                    $condition->select = 'User_Approval_Value';
                    $condition->condition = "t.Client_ID='" . $client->Client_ID . "'";
                    $condition->addCondition("t.User_Approval_Value < '" . $userToClient->User_Approval_Value . "'");
                    $condition->order = "t.User_Approval_Value DESC";
                    $perviosUserApproval = UsersClientList::model()->find($condition);

                    if ($perviosUserApproval) {
                        $LastApproverValue = $perviosUserApproval->User_Approval_Value;
                    } else {
                        $LastApproverValue = 1;
                    }

                    // get aps to approve
                    $condition = new CDbCriteria();
                    $condition->join = "LEFT JOIN documents ON documents.Document_ID=t.Document_ID
                            LEFT JOIN vendors ON vendors.Vendor_ID=t.Vendor_ID
                            LEFT JOIN clients ON vendors.Vendor_Client_ID=clients.Client_ID
                            LEFT JOIN companies ON clients.Company_ID=companies.Company_ID";
                    $condition->condition = "documents.Client_ID='" . $client->Client_ID . "'";
                    $condition->addCondition("t.PO_Approval_Value < '" . $userToClient->User_Approval_Value . "'");
                    $condition->addCondition("t.PO_Approval_Value >= '" . $LastApproverValue . "'");
                    $condition->addCondition("t.PO_Approval_Value != '0'");
                    $condition->order = "companies.Company_Name ASC";
                    $pos = Pos::model()->find($condition);
                    if ($pos) {
                        $clientsToApprove[] = $client->company->Company_Name;
                    }
                }
            }

            // if AP to approve exists, create mail and write it into DB
            if ($clientsToApprove) {
                $clientsList = '';
                foreach ($clientsToApprove as $key => $clientToApprove) {
                    $clientsList .= "<p>&nbsp;&nbsp;" . ($key+1) .") " . CHtml::encode($clientToApprove) . "<p>";
                }

                $replacedValues = array(
                    '{{first_name}}' => $user->person->First_Name,
                    '{{last_name}}' => $user->person->Last_Name,
                    '{{list}}' => $clientsList,
                    '{{ap_link}}' => Yii::app()->config->get('SITE_URL') . '/po',
                    '{{doc_type}}' => Documents::PO,
                );

                $mail = new MailsToDelivery();
                $mail->Email = $user->person->Email;
                $mail->Body = Helper::replaceMatches($templateBody, $replacedValues);
                $mail->save();
            }
        }
    }


    /**
     * This method sends emails to users about pending approval APs
     */
    private function sendMailsList()
    {
        $template = MailTemplates::model()->findByPk(4015);
        $templateTitle = $template->Title;
        $delayed = null;

        // get emails to send
        $emails = MailsToDelivery::model()->findAll();

        //send emails
        foreach ($emails as $key => $email) {
            Mail::sendHtmlMail($delayed,$email->Email, $templateTitle, $email->Body);
            unset($emails[$key]);
        }

        // clear mails table
        $sqlClear = "TRUNCATE TABLE `mails_to_delivery`";
        Yii::app()->db->createCommand($sqlClear)->execute();
    }


    /**
     * This method sends emails to users about pending approval APs
     */
    public static  function testnotification()
    {
        $delayed = null;
        Yii::log("starting testnotification");
        if (Yii::app()->config->get('SEND_MAIL')) {
            Mail::sendHtmlMail($delayed,Yii::app()->config->get('SUPPORT_EMAIL'), "Sheduled cron notification", "\n\rThis is sheduled notification. Cron is working. ");
            Yii::log("testnotification finished");
            return Mail::sendHtmlMail($delayed,'alitvinov@acceptic.com', "Sheduled cron notification", "\n\rThis is sheduled notification. Cron is working. ");
        }
    }

    /**
     * This method works with mails_to_delivery table
     */
    public static  function dailymailing()
    {
        $mails_to_delivery = MailsToDelivery::model()->findAll();
        foreach ($mails_to_delivery as $mail_item) {
            mail($mail_item->Email, $mail_item->Subject, $mail_item->Body, $mail_item->Headers); //Send mail
            $mail_item->delete();
        }
    }

    /**
     * This method removes some files in specified path
     */
    public static function junkfilesremoving ()
    {
        $pathes_array = array(
            '1'=>Yii::app()->basePath.'/data/temp_for_modification/',
            '2'=>Yii::app()->basePath.'/data/temp_for_pdf/',
            '3'=>Yii::app()->basePath.'/data/current_uploads_files/'
        );

        foreach ($pathes_array as $key=>$value) {
            FileModification::Delete($value);
        }


    }


    /**
     * This method removes some files in specified path
     */
    public static function cachefilesremoving ()
    {
      $pathes_array = array(
            '1'=>Yii::app()->basePath.'/data/filecache/',
      );

       $record_deleted  = FileCache::deleteCacheOlderThenDays(5);

       echo $record_deleted. ' records deleted';
    }


    /**
     * Notify companies that must to pay for using the system
     */
    public static function expirationDateNotification()
    {
        ini_set('memory_limit', '512M');

        $dateToday = date('Y-m-d');
        $dateOb = date_create($dateToday);
        $dateOb1 = date_create($dateToday);
        date_add($dateOb1, date_interval_create_from_date_string('1 days'));
        $dateTomorrow = date_format($dateOb1, 'Y-m-d');
        date_add($dateOb, date_interval_create_from_date_string('7 days'));
        $dateWeek = date_format($dateOb, 'Y-m-d');

        // get companies to notify by the condition that : company's expiration day is today or in a week
        $condition = new CDbCriteria();
        $condition->condition = "service_settings.Active_To IN ('$dateToday', '$dateWeek','$dateTomorrow')";
        $condition->addCondition("t.Client_Status = '1'");
        $clients = Clients::model()->with('service_settings', 'company')->findAll($condition);

        //$secviceSettings = ServiceLevelSettings::getServiceLevelsOptionsList();

        if(count($clients)!=0) {
            echo "Clients and service settings loaded\n";
        } else {
            echo "No clients and no services settings loaded. Finished. \n";
        }

        foreach($clients as $client) {
            $level_name = ServiceLevelSettings::getSummaryName($client->service_settings->Service_Level_ID);
            echo '#################'.$level_name;

            echo "Loaded client : ".$client->company->Company_Name."\n";
            $amountToPay = floatval($client->service_settings->Fee);
            echo " Amount to pay loaded (".$amountToPay."$) \n";
            $expDate = 'today';
            $term='24 hours';

            //generating  date strings for mail
            if ($client->service_settings->Active_To == $dateWeek) {
                $expDate = 'on ' . Helper::convertDate($dateWeek);
                $term='7 days';
            }
            if ($client->service_settings->Active_To == $dateTomorrow) {
                $expDate = 'on ' . Helper::convertDate($dateTomorrow);
                $term='24 hours';
            }
            echo " Client's date expires  : ".$term."\n";
            // get client admins
            //echo "Loading client admins\n";
            $condition = UsersClientList::getClientAdminCondition($client->Client_ID);
            $userClientList = UsersClientList::model()->with('user.person')->findAll($condition);
            echo "  date today is ".$dateToday."\n";
            echo "  service_settings->Active_To ".$client->service_settings->Active_To."\n";
            //if expiration date is today
            if ($client->service_settings->Active_To == $dateToday) {
                echo " Expiration day is today. Starting process.\n";


                $userToPay = null;
                $stripeCustomer = null;
                // try to find user to pay
                foreach ($userClientList as $clientAdmin) {
                    if ($clientAdmin->user->settings->Automatic_CC_Charge == 1) {
                        echo "  Client has has choosen automatic paiment. Starting process.\n";
                        echo "  Client ID is ".$client->Client_ID."\n";
                        echo "  User ID is ".$clientAdmin->user->User_ID."\n";
                        $stripeCustomer = StripeCustomers::model()->findByPk($clientAdmin->user->User_ID);
                        //var_dump($stripeCustomer);
                        if ($stripeCustomer) {
                            echo " Stripe customer loaded...\n";
                            $userToPay = $clientAdmin;//получили пользователя с пом которого можно оплатить.
                            break;
                        }
                    }
                }

                if ($userToPay && $stripeCustomer) {
                    //if we have user to pay we execute payment using info of this user

                    echo "User loaded and we are going to execute payment using info of this user\n";
                    Yii::import("ext.stripe.lib.Stripe", true);
                    echo "ext.stripe.lib.Stripe loaded\n";
                    Stripe::setApiKey(Yii::app()->config->get('STRIPE_SECRET_KEY'));
                    Stripe::setApiVersion("2014-06-17");
                    // Charge the Customer

                    try {

                        Stripe_Charge::create(array(
                                "amount" => intval(100*$amountToPay), // amount in cents
                                "currency" => "usd",
                                "customer" => $stripeCustomer->Customer_ID)
                        );
                        echo "Stripe_Charge has been defined\n";

                        ServicePayments::addClientPayment($client->Client_ID, $amountToPay, date('Y-m-d'),true,'auto');

                        echo "ServicePayments client's payment has been added\n";
                        Mail::sendExecutedAutomaticPaymentNotification(
                            $userToPay->user->person->Email,
                            $userToPay->user->person->First_Name,
                            $userToPay->user->person->Last_Name,
                            $client->company->Company_Name,
                            $level_name,
                            $expDate,
                            $amountToPay
                        );
                    } catch(Stripe_CardError $e) {
                        $userToPay = null;
                    }
                }

                if (!$userToPay) {
                    //if we have not user to pay we send expiration date notifications in usual way
                    echo " we have no user to pay so we send expiration date notifications in usual way";
                    foreach ($userClientList as $clientAdmin) {
                        Mail::sendExpirationDateNotification(
                            $clientAdmin->user->person->Email,
                            $clientAdmin->user->person->First_Name,
                            $clientAdmin->user->person->Last_Name,
                            $client->company->Company_Name,
                            $level_name,
                            $expDate,
                            $term
                        );
                    }
                }
            } else {
                foreach ($userClientList as $clientAdmin) {
                    if ($clientAdmin->user->settings->Automatic_CC_Charge == 1) {

                        // if user set to pay automatically
                        echo " user set to pay automatically\n";
                        Mail::sendAutomaticPaymentNotification(
                            $clientAdmin->user->person->Email,
                            $clientAdmin->user->person->First_Name,
                            $clientAdmin->user->person->Last_Name,
                            $client->company->Company_Name,
                            $level_name,
                            $expDate,
                            $amountToPay,
                            $term
                        );
                        echo " afer sending the Mail::sendAutomaticPaymentNotification\n";
                    } else {
                        // if user did not set to pay automatically
                        Mail::sendExpirationDateNotification(
                            $clientAdmin->user->person->Email,
                            $clientAdmin->user->person->First_Name,
                            $clientAdmin->user->person->Last_Name,
                            $client->company->Company_Name,
                            $level_name,
                            $expDate,
                            $term
                        );
                    }

                }
            }
        }
    }
}