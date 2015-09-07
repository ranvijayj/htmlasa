<?php

/**
 * Contain mail methods
 */
class Mail
{

    /**
     * Registration email with company profile creation
     * @param $email
     * @param $login
     * @param $password
     * @param $firstName
     * @param $lastName
     * @param $company
     * @return bool
     */
    public static function sendRegistrationMail($email, $login, $password, $firstName, $lastName, $company)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(2052);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{login}}' => $login,
                '{{password}}' => $password,
                '{{list}}' => $company ? '<p>&nbsp;&nbsp;1) ' . CHtml::encode($company) . '<p>' : '',
                '{{my_profile_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount',
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail(0,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Notified the User who uploaded the W9 that there is a W9 detail that needs to be entered
     * @param $email
     * @param $firstName
     * @param $lastName
     * @return bool
     */
    public static function sendNewW9ForDataEntry($email, $firstName, $lastName, $delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(4016);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{w9_data_link}}' => Yii::app()->config->get('SITE_URL') . '/dataentry/w9',
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Registration email without company profile creation
     * @param $email
     * @param $login
     * @param $password
     * @param $firstName
     * @param $lastName
     * @param $company
     * @return bool
     */
    public static function sendUserRegistrationMail($email, $login, $password, $firstName, $lastName, $company)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(2051);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{login}}' => $login,
                '{{password}}' => $password,
                '{{list}}' => '<p>&nbsp;&nbsp;1) ' . CHtml::encode($company) . '<p>',
                '{{my_profile_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount',
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail(0,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Send expiration date notification
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $company
     * @param $tierName
     * @param $date
     * @return bool
     */
    public static function sendExpirationDateNotification($email, $firstName, $lastName, $company, $tierName, $date, $term)
    {
      if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(3017);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;
            echo "term is ".$term."\n";
            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{date}}' => $date,
                '{{term}}' => $term,
                '{{tier_name}}' => $tierName,
                '{{company}}' => CHtml::encode($company),
                '{{service_level_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount?tab=service',
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail(0,$email, $templateTitle, $templateBody);

      } else {
        return true;
      }
    }

    /**
     * Function send email about payment that going to be maid
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $company
     * @param $tierName
     * @param $date
     * @param $amount
     * @return bool
     */
    public static function sendAutomaticPaymentNotification($email, $firstName, $lastName, $company, $tierName, $date,$amount,$term)
    {
      if (Yii::app()->config->get('SEND_MAIL')) {
           echo "inside sendAutomaticPaymentNotification\n";
            $template = MailTemplates::model()->findByPk(3019);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;


            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{date}}' => $date,
                '{{term}}' => $term,
                '{{tier_name}}' => $tierName,
                '{{company}}' => CHtml::encode($company),
                '{{service_level_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount?tab=service',
                '{{pay_sum}}'=>$amount
            );

            echo "before replaced";
            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

        echo "inside sendAutomaticPaymentNotification - after replaceValues\n";
        //var_dump($templateBody);die;
            return self::sendHtmlMail(0,$email, $templateTitle, "\n\r".$templateBody);
      } else {
          return true;
      }
    }
    /**
     * Function sends email about payment that has been made
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $company
     * @param $tierName
     * @param $date
     * @param $amount
     * @return bool
     */
    public static function sendExecutedAutomaticPaymentNotification($email, $firstName, $lastName, $company, $tierName, $date,$amount)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(3018);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{date}}' => $date,
                '{{tier_name}}' => $tierName,
                '{{company}}' => CHtml::encode($company),
                '{{service_level_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount?tab=service',
                '{{pay_sum}}'=>$amount
            );


            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);


            return self::sendHtmlMail(0,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Sent user registration email to client (Когда сисадмин подтверждает рагестрацию пользователя, желающего добавиться к существ. компании)
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $pendingFirstName
     * @param $pendingLastName
     * @param $company
     * @return bool
     */
    public static function sendClientOfUserRegistrationMail($email,$firstName, $lastName, $pendingFirstName, $pendingLastName, $company,$delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(2055);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{pending_first_name}}' => $pendingFirstName,
                '{{pending_last_name}}' => $pendingLastName,
                '{{list}}' => '<p>&nbsp;&nbsp;1) ' . CHtml::encode($company) . '<p>',
                '{{my_profile_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount',
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Sent pending approval Documents notification
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $companies
     * @param $docType
     * @return bool
     */
    public static function sendPendingApprovalDocumentsNotification($delayed,$user, $companies, $docType = "AP",$client='',$project='')
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            //$user->person->Email,$user->person->First_Name, $user->person->Last_Name
            $template = MailTemplates::model()->findByPk(4015);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $clientsList = '';

            foreach ($companies as $key => $company) {
                $clientsList .= "<p>&nbsp;&nbsp;" . ($key+1) .") " . CHtml::encode($company) . "<p>";
            }

            $company_id = $client ? $client->company->Company_ID : 0;
            $project_id = $project ? $project->Project_ID : 0;
            $userId = $user ? $user->User_ID : 0;

            $link = Yii::app()->config->get('SITE_URL') . '/' . strtolower($docType);
            $short_link = $link;

            $link .= '?cid='.$company_id.'&pid='.$project_id.'&uid='.$userId;

            $replacedValues = array(
                '{{first_name}}' => $user->person->First_Name,
                '{{last_name}}' => $user->person->Last_Name,
                '{{list}}' => $clientsList,
                '{{doc_type}}' => $docType,
                '{{ap_link}}' => $link,
                '{{short_link}}' => $short_link,
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$user->person->Email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Send client admin assignment mail (При назначении новых или снятии старых администраторов клиента сисадмином)
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $company
     * @param $add
     * @param bool $currentAdminEmail
     * @return bool
     */
    public static function sendClientAssignAdminMail($email, $firstName, $lastName, $company, $add, $currentAdminEmail = false,$delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            if ($add) {
                $template = MailTemplates::model()->findByPk(3015);
                $templateBody = $template->Message_Body;
                $templateTitle = $template->Title;

                $replacedValues = array(
                    '{{first_name}}' => $firstName,
                    '{{last_name}}' => $lastName,
                    '{{list}}' => '<p>&nbsp;&nbsp;1) ' . CHtml::encode($company) . '<p>',
                    '{{site_url}}' => Yii::app()->config->get('SITE_URL'),
                );

                $templateBody = Helper::replaceMatches($templateBody, $replacedValues);
            } else {
                $template = MailTemplates::model()->findByPk(3016);
                $templateBody = $template->Message_Body;
                $templateTitle = $template->Title;

                if ($currentAdminEmail !== false) {
                    $currentAdminEmail = '<a target="_blank" style="color: #0066CC" href="mailto:' . $currentAdminEmail . '">' . $currentAdminEmail . '</a>';
                } else {
                    $currentAdminEmail = 'No client admins';
                }

                $replacedValues = array(
                    '{{first_name}}' => $firstName,
                    '{{last_name}}' => $lastName,
                    '{{list}}' => '<p>&nbsp;&nbsp;1) ' . CHtml::encode($company) . '<p>',
                    '{{client_admin_mail}}' => $currentAdminEmail,
                );

                $templateBody = Helper::replaceMatches($templateBody, $replacedValues);
            }
            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Send notification about changing client's status
     * @param $email
     * @param $companyName
     * @param $firstName
     * @param $lastName
     * @param $activated
     * @return bool
     */
    public static function sendClientStatusMail($email, $companyName, $firstName, $lastName, $activated,$delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $messID = 1073;
            $url = Yii::app()->config->get('SITE_URL') . '/site/login';
            if (!$activated) {
                $messID = 1074;
                $url = Yii::app()->config->get('SUPPORT_EMAIL');
            }

            $template = MailTemplates::model()->findByPk($messID);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $clientsList = "<p>&nbsp;&nbsp;1) " . CHtml::encode($companyName) . "<p>";

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{list}}' => $clientsList,
                '{{url}}' => $url,
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Add user to client mail (При добавлении пользователя в список пользователей клиента)
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $company
     * @return bool
     */
    public static function sendAddUserToClientMail($email, $firstName, $lastName, $company,$delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(1071);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{list}}' => '<p>&nbsp;&nbsp;1) ' . CHtml::encode($company) . '<p>',
                '{{site_url}}' => Yii::app()->config->get('SITE_URL'),
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);
            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Reject users mail (При отклонении регистрирующегося пользователя администратором клиента)
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $company
     * @return bool
     */
    public static function sendRejectMail($email, $firstName, $lastName, $company, $delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(2054);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{list}}' => '<p>&nbsp;&nbsp;1) ' . CHtml::encode($company) . '<p>',
                '{{site_url}}' => Yii::app()->config->get('SITE_URL'),
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Reject users by system admin mail (При отклонении регистрирующегося пользователя администратором)
     * @param $email
     * @param $firstName
     * @param $lastName
     * @return bool
     */
    public static function sendRejectUserByAdminMail($email, $firstName, $lastName, $delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(2053);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Remove users from client's list mail (При удалении пользователя из списка пользователей клиента)
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $company
     * @return bool
     */
    public static function sendRemoveUserFromClientMail($email, $firstName, $lastName, $company, $delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(1072);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{list}}' => '<p>&nbsp;&nbsp;1) ' . CHtml::encode($company) . '<p>',
                '{{my_profile_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount',
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);
            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Send an email to the User when a failed login attempt is executed on their account
     * @param $email
     * @param $login
     * @param $password
     * @param $firstName
     * @param $lastName
     * @return bool
     */
    public static function sendFailedLoginAttempt($email, $login, $password, $firstName, $lastName, $delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(1254);
            $templateBody = $template->Message_Body;
            //$templateTitle = $template->Title;
            $templateTitle = "Failed Login Notice";

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{login}}' => $login,
                '{{password}}' => $password,
            );

            //$templateBody = Helper::replaceMatches($templateBody, $replacedValues);
            $templateBody = 'A failed login was attempted in your D-APC account. If you are unaware of an attempted login please review your settings.';
            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }


    /**
     * New password mail (Отправка нового пароля, когда юзер забыл старый)
     * @param $email
     * @param $login
     * @param $password
     * @param $firstName
     * @param $lastName
     * @return bool
     */
    public static function sendNewPassword($email, $login, $password, $firstName, $lastName, $delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(1252);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{login}}' => $login,
                '{{password}}' => $password,
                '{{my_profile_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount',
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * Email Change Confirmation
     * @param $email
     * @param $firstName
     * @param $lastName
     * @return bool
     */
    public static function sendEmailChangeConfirmation($email, $firstName, $lastName, $delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(1253);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{my_profile_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount',
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);
        } else {
            return true;
        }
    }

    /**
     * User Registration Request - Admin
     * @return bool
     */
    public static function sendUserRegistrationRequest($uid='')
    {
        $detailsString ='';
        if ($uid) {
            $user = Users::model()->findByPk($uid);
            $newUserName = $user->person->First_Name .' '.$user->person->Last_Name;
            $newUserLogin = $user->User_Login;

            if ($user->Invited_by_UID) {
                $user = Users::model()->findByPk($user->Invited_by_UID);
                $existingUserName = $user->person->First_Name .' '.$user->person->Last_Name;
                $existingUserLogin = $user->User_Login;

                $detailsString = '<br/><span>Username : '.$newUserName.'('.$newUserLogin.') </span> <br/> <span class="red"> Invited by : '.$existingUserName.'('.$existingUserLogin.') </span>';

            } else {
                $detailsString = '<br/><span>Username : '.$newUserName.'('.$newUserLogin.') </span> <br/>';
            }




        }

        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(2050);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $condition = new CDbCriteria();
            $condition->addInCondition('t.User_Type', array(Users::ADMIN, Users::DB_ADMIN));
            $admins = Users::model()->with('person')->findAll($condition);

            foreach ($admins as $admin) {
                $replacedValues = array(
                    '{{first_name}}' => $admin->person->First_Name,
                    '{{last_name}}' => $admin->person->Last_Name,
                    '{{admin_link}}' => Yii::app()->config->get('SITE_URL') . '/admin',
                    '{{details_string}}' => $detailsString
                );

                $body = Helper::replaceMatches($templateBody, $replacedValues);

                self::sendHtmlMail(0,$admin->person->Email, $templateTitle, $body);
            }
            return true;
        } else {
            return true;
        }
    }

    /**
     * Send mail with html content-type
     * @param $email
     * @param $subject
     * @param $body
     * @return bool
     */
    public static function sendHtmlMail($delayed,$email, $subject, $body)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {

            $mainLayout = MailTemplates::model()->findByPk(1000);
            $mainLayoutBody = $mainLayout->Message_Body;

            $replacedValues = array(
                '{{message_title}}' => $subject,
                '{{message_body}}' => $body,
                '{{support_number}}' => Yii::app()->config->get('SUPPORT_NUMBER'),
                '{{http_host}}' => Yii::app()->config->get('SITE_URL'),
                '{{support_email}}' => Yii::app()->config->get('SUPPORT_EMAIL'),
            );

            $message = Helper::replaceMatches($mainLayoutBody, $replacedValues);

            // set html type

                $headers= "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=utf-8\r\n";

            // additional headers
            $headers .= "From: " . Yii::app()->config->get('EMAIL_FROM') ."\r\n"."X-Mailer: PHP/" . phpversion();


            //return mail($email, $subject, $message, $headers); //Send mail
            return self::smartMailHandler($delayed,$email, $subject, $message, $headers);

       } else {
          return true;
       }
    }

    /**
     * Sends mail and file attached
     * @param $email
     * @param $subject
     * @param $body
     * @param $filename
     * @param $filepath
     * @return bool
     */
    public static function sendComplexMail($email,$subject,$body,$filename,$filepath)
    {

        if (Yii::app()->config->get('SEND_MAIL')) {
            $to = $email;
            $from = Yii::app()->config->get('EMAIL_FROM');



            $mainLayout = MailTemplates::model()->findByPk(1000);
            $mainLayoutBody = $mainLayout->Message_Body;
            $replacedValues = array(
                '{{message_title}}' => $subject,
                '{{message_body}}' => $body,
                '{{support_number}}' => Yii::app()->config->get('SUPPORT_NUMBER'),
                '{{http_host}}' => Yii::app()->config->get('SITE_URL'),
                '{{support_email}}' => Yii::app()->config->get('SUPPORT_EMAIL'),
            );
            $message = Helper::replaceMatches($mainLayoutBody, $replacedValues);

            //Mail::sendFile($from, $to, $subject, $message, $filename, $filepath);
            $boundary = "---";
            /* Headers */
            $headers = "From: $from\nReply-To: $from\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";
            $body = "--$boundary\n";
            /* Add text body */
            $body .= "Content-type: text/html; charset='utf-8'\n";
            $body .= "Content-Transfer-Encoding: quoted-printablenn";
            $body .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode($filename)."?=\n\n";
            $body .= $message."\n";
            $body .= "--$boundary\n";
            $file = fopen($filepath, "r"); //Open file
            $text = fread($file, filesize($filepath)); //Read file
            fclose($file); //Close file
            /* Create body */
            $body .= "Content-Type: application/octet-stream; name==?utf-8?B?".base64_encode($filename)."?=\n";
            $body .= "Content-Transfer-Encoding: base64\n";
            $body .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode($filename)."?=\n\n";
            $body .= chunk_split(base64_encode($text))."\n";
            $body .= "--".$boundary ."--\n";
            return mail($to, $subject, $body, $headers); //Send mail



        } else {
            return true;
        }
    }

    /**
     * Send mail with html content-type
     * @param $email
     * @param $subject
     * @param $body
     * @return bool
     */
    public static function sendTestHtmlMail($email, $subject, $body)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {

            $mainLayout = MailTemplates::model()->findByPk(1000);
            $mainLayoutBody = $mainLayout->Message_Body;

            $replacedValues = array(
                '{{message_title}}' => $subject,
                '{{message_body}}' => $body,
                '{{support_number}}' => Yii::app()->config->get('SUPPORT_NUMBER'),
                '{{http_host}}' => Yii::app()->config->get('SITE_URL'),
                '{{support_email}}' => Yii::app()->config->get('SUPPORT_EMAIL'),
            );

            $message = Helper::replaceMatches($mainLayoutBody, $replacedValues);

            // set html type
            $headers= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n";

            // additional headers
            $headers .= "From: " . Yii::app()->config->get('EMAIL_FROM') ."\r\n"."X-Mailer: PHP/" . phpversion();

            // send mail
            //die($email);
            echo "Inside sendTestHtmlMAil notification\n";
            mail($email, $subject, $message, $headers);

        } else {
            return true;
        }
    }


    /**
     * Send document by mail
     * @param $email
     * @param $filename
     * @param $filepath
     * @param $comName
     * @return bool
     */
    public static function sendDocument($email, $filename, $filepath, $comName,$user_model)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $to = $email;
            $from = Yii::app()->config->get('EMAIL_FROM');
            $subject = 'D-APC - document from ' . $comName;

            $mainLayout = MailTemplates::model()->findByPk(1000);
            $mainLayoutBody = $mainLayout->Message_Body;
            $replacedValues = array(
                '{{message_title}}' => $subject,
                '{{message_body}}' => $user_model->person->Person_ID.'{'. $user_model->person->First_Name.' '.$user_model->person->Last_Name. '} has sent you a document from the D-APC system.<br/>
                 The document is from ' . $comName.' and is attached to this email as '.$filename,
                '{{support_number}}' => Yii::app()->config->get('SUPPORT_NUMBER'),
                '{{http_host}}' => Yii::app()->config->get('SITE_URL'),
                '{{support_email}}' => Yii::app()->config->get('SUPPORT_EMAIL'),
            );
            $message = Helper::replaceMatches($mainLayoutBody, $replacedValues);

            Mail::sendFile($from, $to, $subject, $message, $filename, $filepath);
        } else {
            return true;
        }
    }

    /**
     * Send Service Level Payment
     * @param $filename
     * @param $filepath
     * @param $comName
     * @param $fedID
     * @return bool
     */
    public static function sendServicePayment($filename, $filepath, $comName, $fedID)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $to = Yii::app()->config->get('SUPPORT_NUMBER');
            $from = Yii::app()->config->get('EMAIL_FROM');
            $subject = 'Service Level Payment Doc from ' . $comName;

            $mainLayout = MailTemplates::model()->findByPk(1000);
            $mainLayoutBody = $mainLayout->Message_Body;
            $replacedValues = array(
                '{{message_title}}' => $subject,
                '{{message_body}}' => 'Service Level Payment Doc from ' . $comName . '.<br/>Company Fed-ID: '.  $fedID,
                '{{support_number}}' => Yii::app()->config->get('SUPPORT_NUMBER'),
                '{{http_host}}' => Yii::app()->config->get('SITE_URL'),
                '{{support_email}}' => Yii::app()->config->get('SUPPORT_EMAIL'),
            );
            $message = Helper::replaceMatches($mainLayoutBody, $replacedValues);

            Mail::sendFile($from, $to, $subject, $message, $filename, $filepath);
        } else {
            return true;
        }
    }

    /**
     * Send file by email
     */
    public static function sendFile($from, $to, $subject, $message, $filename, $filepath)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $boundary = "---";
            /* Headers */
            $headers = "From: $from\nReply-To: $from\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";
            $body = "--$boundary\n";
            /* Add text body */
            $body .= "Content-type: text/html; charset='utf-8'\n";
            $body .= "Content-Transfer-Encoding: quoted-printablenn";
            $body .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode($filename)."?=\n\n";
            $body .= $message."\n";
            $body .= "--$boundary\n";
            $file = fopen($filepath, "r"); //Open file
            $text = fread($file, filesize($filepath)); //Read file
            fclose($file); //Close file
            /* Create body */
            $body .= "Content-Type: application/octet-stream; name==?utf-8?B?".base64_encode($filename)."?=\n";
            $body .= "Content-Transfer-Encoding: base64\n";
            $body .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode($filename)."?=\n\n";
            $body .= chunk_split(base64_encode($text))."\n";
            $body .= "--".$boundary ."--\n";
            return mail($to, $subject, $body, $headers); //Send mail
        } else {
            return true;
        }
    }

    /**
     * Send file by email
     */
    public static function sendFileNew($from, $to, $subject, $message, $filename, $filepath)
    {
        require 'PHPMailer/PHPMailerAutoload.php';

        if (Yii::app()->config->get('SEND_MAIL')) {

            $email = new PHPMailer();

            $email->From      = $from;
            $email->FromName  = $from;
            $email->Subject   = $subject;
            $email->Body      = $message;
            $email->IsHTML(true);
            $email->AddAddress( $to );
            $email->AddAttachment( $filepath , $filename );

            return $email->Send();

        } else {
            return true;
        }
    }





    //{{login_account_link}}
    public static function sendApprovalDataEntryRequiredNotification($email, $firstName, $lastName, $company, $delayed=0)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(1254);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{company}}' => CHtml::encode($company),
                '{{login_account_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount',
            );

            $templateBody = "\r\n".Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);

        } else {
            return true;
        }
    }

    /**
     * Sends notifications about documents that needs approving from Approval Cue mode
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $company
     * @param $items
     * @return bool
     */
    public static function sendApprovalCueNotification($email, $firstName, $lastName, $company,$items, $delayed=0)
    {

        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(1254);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $firstName,
                '{{last_name}}' => '',
                '{{company}}' => CHtml::encode($company),
                '{{items}}' => '',//$items//items for future features
                '{{login_account_link}}' => Yii::app()->config->get('SITE_URL') . '/myaccount',
            );

            $templateBody ="\r\n". Helper::replaceMatches($templateBody, $replacedValues);



            return self::sendHtmlMail($delayed,$email, $templateTitle, $templateBody);

        } else {
            return true;
        }
    }

 public static function  notifyAdminAboutStripePaymentExecuted($email,$company_name,$pendingSettings,$settingsToPay,$tierNameBefore,$tierNameAfter,$expDateBefore,$expDateAfter,$amount, $delayed=0)
 {
     if (Yii::app()->config->get('SEND_MAIL')) {


         $template = MailTemplates::model()->findByPk(301900);
         $templateBody = $template->Message_Body;
         $templateTitle = $template->Title;

         $replacedValues = array(
             '{{user_email}}' => $email,
             '{{company_name}}' => $company_name,
             '{{amount}}' => $amount,
             '{{pendingSettings}}' => $pendingSettings,
             '{{settingsToPay}}' => $settingsToPay,
             '{{tierNameBefore}}' => $tierNameBefore,
             '{{tierNameAfter}}' => $tierNameAfter,
             '{{expDateBefore}}' => $expDateBefore,
             '{{expDateAfter}}' => $expDateAfter,

         );

         $templateBody ="\r\n". Helper::replaceMatches($templateBody, $replacedValues);



         return self::sendHtmlMail($delayed,Yii::app()->config->get('ADMIN_EMAIL'), $templateTitle, $templateBody);

     } else {
         return true;
     }
 }

    /**
     * used for notification about payment for digital copy of the remote processing book
     * @param $book_id
     * @param $amount
     * @param $filename
     * @param $filesize
     * @param $pages
     * @param $client_name
     * @return bool
     */
    public static function  notifyAdminAboutBookPayment($book_id,$amount,$filename,$filesize,$pages,$client_name, $delayed=0)
    {
     if (Yii::app()->config->get('SEND_MAIL')) {
         $template = MailTemplates::model()->findByPk(301901);
         $templateBody = $template->Message_Body;
         $subject = $template->Title;

         $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

         $replacedValues = array(
             '{{user_names}}' => $user->person->First_Name.' '.$user->person->First_Name,
             '{{company_name}}' => $client_name,
             '{{amount}}' => $amount,
             '{{book_number}}' => $book_id,
             '{{book_name}}' => $filename,
             '{{book_size}}' => $filesize,
             '{{pages}}'=>$pages
         );

         $message ="\r\n". Helper::replaceMatches($templateBody, $replacedValues);


         //send letter with html markup and file attached
         return self::sendHtmlMail($delayed,Yii::app()->config->get('ADMIN_EMAIL'), $subject, $message);

     } else {
         return true;
     }
}

    /**
     * Used for notification about payment for paper (analog) book
     * @param $book_id
     * @param $amount
     * @param $filename
     * @param $filesize
     * @param $pages
     * @param $quality
     * @param $pages_per_sheets
     * @param $copies
     * @param $client_name
     * @return bool
     */
    public static function  notifyAdminAboutAnalogBookPayment($book_id,$amount,$filename,$filesize,$pages,$quality,$pages_per_sheets,$copies,$client_name)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(301902);
            $templateBody = $template->Message_Body;
            $subject = $template->Title;

            $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

            $replacedValues = array(
                '{{user_names}}' => $user->person->First_Name.' '.$user->person->First_Name,
                '{{company_name}}' => $client_name,
                '{{amount}}' => $amount,
                '{{book_number}}' => $book_id,
                '{{book_name}}' => $filename,
                '{{book_size}}' => $filesize,
                '{{pages}}'=>$pages,
                '{{quality}}'=>$quality,
                '{{pages_per_sheets}}'=>$pages_per_sheets,
                '{{copies}}'=>$copies

            );

            $message ="\r\n". Helper::replaceMatches($templateBody, $replacedValues);
            //send letter with html markup
            return self::sendHtmlMail(0,Yii::app()->config->get('ADMIN_EMAIL'), $subject, $message);

        } else {
            return true;
        }
    }



    public static function  notifyAdminAboutRemoteProcessing($to,$filename,$filepath,$parameters)
    {
        if (Yii::app()->config->get('SEND_MAIL')) {

            $client = Clients::model()->with('company')->findByPk($parameters['client_id']);
            $from = Yii::app()->config->get('EMAIL_FROM');

            $template = MailTemplates::model()->findByPk(3020);
            $templateBody = $template->Message_Body;
            $subject = $template->Title;

            $replacedValues = array(
                '{{cli_name}}' => $client->company->Company_Name,
                '{{time_spent}}' => $parameters['timeSpent'],
                '{{archive_size}}' => $parameters['booksize'],
                '{{total_files_size}}' => $parameters['filesSize'],
                '{{filepath}}'=>$filepath
            );

            $message ="\r\n". Helper::replaceMatches($templateBody, $replacedValues);


           //send letter wioth html markup and file attached
            Mail::sendComplexMail($to, $subject, $message, $filename, $filepath);

        } else {
            return true;
        }

    }

public  static  function smartMailHandler($delayed,$email, $subject, $message, $headers){

    if ($delayed) {
        //put message into queue
        MailsToDelivery::add($email, $subject, $message, $headers);
    } else {
        return mail($email, $subject, $message, $headers); //Send mail
    }
}


    public  static  function notifyClAdminAboutInvitedUserLogin($user,$delayed=0){

        $clientAdmin = Users::model()->findByPk($user->Invited_by_UID);

        if (Yii::app()->config->get('SEND_MAIL')) {
            $template = MailTemplates::model()->findByPk(2056);
            $templateBody = $template->Message_Body;
            $templateTitle = $template->Title;

            $replacedValues = array(
                '{{first_name}}' => $clientAdmin->person->First_Name,
                '{{last_name}}' => $clientAdmin->person->Last_Name,
                '{{new_user_name}}' => $user->person->First_Name.' '.$user->person->Last_Name,
                '{{user_login}}' => $user->User_Login,
                '{{company}}' => CHtml::encode($company),
                '{{login_time}}' => $user->Last_Login,
                '{{login_ip}}' => $user->Last_IP,
            );

            $templateBody = Helper::replaceMatches($templateBody, $replacedValues);

            return self::sendHtmlMail($delayed,$clientAdmin->person->Email, $templateTitle, $templateBody);
        } else {
            return true;
        }

    }



}