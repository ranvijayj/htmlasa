<?php

class PaypalController extends Controller
{
    public $layout='//layouts/column2';

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
            array('allow',
                'actions'=>array('DirectPayment', 'buy', 'cancelpayment', 'ConfirmPayment'),
                'users'=>array('admin', 'db_admin', 'client_admin'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Payment by ExpressCheckout
     */
    public function actionBuy()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['amount'])) {
            $amount = floatval($_POST['amount']);
            $answer = array(
                'success' => 0,
                'message' => '',
            );

            // get credit card and user info
            $cc = Ccs::getUserCreditCard(Yii::app()->user->userID);
            $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);
            $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);

            if ($cc && $user && $client) {
                // set
                $paymentInfo['Order']['theTotal'] = $amount;
                $paymentInfo['Order']['description'] = "Payment for using digital AP Clerk";
                $paymentInfo['Order']['quantity'] = '1';

                // call paypal
                $result = Yii::app()->Paypal->SetExpressCheckout($paymentInfo);

                //Detect Errors
                if (!Yii::app()->Paypal->isCallSucceeded($result)) {
                    $answer['message'] = $result['L_LONGMESSAGE0'];
                    /*
                    if (Yii::app()->Paypal->apiLive === true) {
                        //Live mode basic error message
                        $answer['message'] = 'We were unable to process your request. Please try again later.';
                    } else {
                        //Sandbox output the actual error message to dive in.
                        $answer['message'] = $result['L_LONGMESSAGE0'];
                    }
                    */
                } else {
                    // send user to paypal
                    $token = urldecode($result["TOKEN"]);
                    $payPalURL = Yii::app()->Paypal->paypalUrl.$token;

                    $payPalToken = PaypalTokens::getPayPalToken(Yii::app()->user->clientID);
                    $payPalToken->Amount = $amount;
                    $payPalToken->Token = $token;
                    if ($payPalToken->validate()) {
                        $payPalToken->save();
                        $answer = array(
                            'success' => 1,
                            'message' => $payPalURL,
                        );
                    }
                }
            } else {
                // if user don't have credit card info
                $answer['message'] = 'Please fill in and save Credit Card Information on Credit Card tab.';
            }
            echo CJSON::encode($answer);
            Yii::app()->end();
        }
    }

    public function actionConfirmPayment()
    {
        $token = trim($_GET['token']);
        $payerId = trim($_GET['PayerID']);

        $result = Yii::app()->Paypal->GetExpressCheckoutDetails($token);

        $payPalToken = PaypalTokens::getPayPalToken(Yii::app()->user->clientID);

        if ($payPalToken) {
            if ($payPalToken->Token == $token) {
                $result['PAYERID'] = $payerId;
                $result['TOKEN'] = $token;
                $result['ORDERTOTAL'] = $payPalToken->Amount;

                //Detect errors
                if(!Yii::app()->Paypal->isCallSucceeded($result)) {
                    /*
                    if (Yii::app()->Paypal->apiLive === true) {
                        //Live mode basic error message
                        $error = 'We were unable to process your request. Please try again later';
                    } else {
                        //Sandbox output the actual error message to dive in.
                        $error = $result['L_LONGMESSAGE0'];
                    }
                    */
                    $error = $result['L_LONGMESSAGE0'];
                    Yii::app()->user->setFlash('success', $error);
                } else {
                    $paymentResult = Yii::app()->Paypal->DoExpressCheckoutPayment($result);
                    //Detect errors
                    if (!Yii::app()->Paypal->isCallSucceeded($paymentResult)) {
                        /*
                        if (Yii::app()->Paypal->apiLive === true) {
                            //Live mode basic error message
                            $error = 'We were unable to process your request. Please try again later';
                        } else {
                            //Sandbox output the actual error message to dive in.
                            $error = $paymentResult['L_LONGMESSAGE0'];
                        }
                        */
                        $error = $paymentResult['L_LONGMESSAGE0'];
                        Yii::app()->user->setFlash('success', $error);
                    } else {
                        //payment was completed successfully
                        ServicePayments::addClientPayment(Yii::app()->user->clientID, $payPalToken->Amount, date('Y-m-d'),true);
                        Yii::app()->user->setFlash('success', "Payment has been successfully made!");
                    }
                }
            } else {
                $payPalToken->delete();
            }
        }
        $this->redirect('/myaccount?tab=service');
    }

    /**
     * If payment was rejected
     */
    public function actionCancelPayment()
    {
        //The token of the cancelled payment typically used to cancel the payment within your application
        $token = $_GET['token'];
        $payPalToken = PaypalTokens::getPayPalToken(Yii::app()->user->clientID);
        $payPalToken->delete();
        Yii::app()->user->setFlash('success', "Payment has been rejected!");
        $this->redirect('/myaccount?tab=service');
    }

    /**
     * Do direct payment for using system
     */
    public function actionDirectPayment() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['cvv2']) && isset($_POST['amount'])) {
            $cvv2 = intval($_POST['cvv2']);
            $amount = floatval($_POST['amount']);
            $answer = array(
                'success' => 0,
                'message' => '',
            );

            // get credit card and user info
            $cc = Ccs::getUserCreditCard(Yii::app()->user->userID);
            $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);
            $client = Clients::model()->with('company')->findByPk(Yii::app()->user->clientID);

            if ($cc && $user && $client) {
                $paymentInfo = array(
                    'Member' => array(
                        'first_name' => $user->person->First_Name,
                        'last_name' => $user->person->Last_Name,
                        'billing_address' => $user->person->adresses[0]->Address1,
                        'billing_address2' => $user->person->adresses[0]->Address2,
                        'billing_country' => $user->person->adresses[0]->Country,
                        'billing_city' => $user->person->adresses[0]->City,
                        'billing_state' => $user->person->adresses[0]->State,
                        'billing_zip' => $user->person->adresses[0]->ZIP,
                    ),
                    'CreditCard' => array(
                        'card_number' => preg_replace('/\-/', '', $cc->CC_Number),
                        'expiration_month' => $cc->Exp_Month,
                        'expiration_year' => $cc->Exp_Year,
                        'cv_code' => $cvv2,
                        'credit_type' => $cc->type->CC_Type,
                    ),
                    'Order' => array(
                        'theTotal'=> $amount,
                    )
                );

                /*
                 * On Success, $result contains [AMT] [CURRENCYCODE] [AVSCODE] [CVV2MATCH]
                 * [TRANSACTIONID] [TIMESTAMP] [CORRELATIONID] [ACK] [VERSION] [BUILD]
                 *
                 * On Fail, $ result contains [AMT] [CURRENCYCODE] [TIMESTAMP] [CORRELATIONID]
                 * [ACK] [VERSION] [BUILD] [L_ERRORCODE0] [L_SHORTMESSAGE0] [L_LONGMESSAGE0]
                 * [L_SEVERITYCODE0]
                 */

                $result = Yii::app()->Paypal->DoDirectPayment($paymentInfo);

                //Detect Errors
                if (!Yii::app()->Paypal->isCallSucceeded($result)) {
                    $answer['message'] = $result['L_LONGMESSAGE0'];
                    /*
                    if (Yii::app()->Paypal->apiLive === true) {
                        //Live mode basic error message
                        $answer['message'] = 'We were unable to process your request. Please try again later.';
                    } else {
                        //Sandbox output the actual error message to dive in.
                        $answer['message'] = $result['L_LONGMESSAGE0'];
                    }
                    */
                } else {
                    //Payment was completed successfully, do the rest of your stuff
                    $answer = array(
                        'success' => 1,
                        'message' => '',
                    );
                    ServicePayments::addClientPayment(Yii::app()->user->clientID, $amount, date('Y-m-d'),true);
                    Yii::app()->user->setFlash('success', "Payment has been successfully made!");
                }
            } else {
                // if user don't have credit card info
                $answer['message'] = 'Please fill in and save Credit Card Information on Credit Card tab.';
            }
            echo CJSON::encode($answer);
            Yii::app()->end();
        }
	} 
}