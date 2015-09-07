<?php

class StripeController extends Controller
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
                'actions'=>array('ExecutePayment','ExecuteRpPayment','ExecuteRpAnalogPayment'),
                'users'=>array('admin', 'db_admin', 'client_admin'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Payment by Stripe
     */
    public function actionExecutePayment()
    {

        if (isset($_POST['use_last_cc']) && isset($_POST['amount_to_pay'])) {
            $monthly_or_not = intval($_POST['monthly_payment']);
            $useLastCC = intval($_POST['use_last_cc']);
            $amountToPay = floatval($_POST['amount_to_pay']);

            $stripeCustomer = StripeCustomers::model()->findByPk(Yii::app()->user->userID);
            $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

            Yii::import("ext.stripe.lib.Stripe", true);
            Stripe::setApiKey(Yii::app()->config->get('STRIPE_SECRET_KEY'));
            Stripe::setApiVersion("2014-06-17");
            if ($useLastCC == 1 && $stripeCustomer) {
                // Charge the Customer
                try {
                    Stripe_Charge::create(array(
                            "amount" => intval(100*$amountToPay), // amount in cents
                            "currency" => "usd",
                            "customer" => $stripeCustomer->Customer_ID)
                    );
                    ServicePayments::addClientPayment(Yii::app()->user->clientID, $amountToPay, date('Y-m-d'),$monthly_or_not,'auto');

                    Yii::app()->user->setFlash('success', "Payment has been successfully made!");
                } catch(Stripe_CardError $e) {
                    Yii::app()->user->setFlash('success', $e->getMessage());
                }
            } else if (isset($_POST['stripeToken'])) {

                $userSettings = UsersSettings::model()->findByAttributes(array('User_ID'=>Yii::app()->user->userID));

                if (isset($_POST['Automatic_CC_Charge']) && $_POST['Automatic_CC_Charge']=='on') {
                    $userSettings->Automatic_CC_Charge = 1;
                } else {
                    $userSettings->Automatic_CC_Charge = 0;
                }
                $userSettings->save();

                if ($stripeCustomer) {
                    //delete old user info
                    $cu = Stripe_Customer::retrieve($stripeCustomer->Customer_ID);
                    $cu->delete();
                } else {
                    $stripeCustomer = new StripeCustomers();
                    $stripeCustomer->User_ID = Yii::app()->user->userID;
                }

                // Get the credit card details submitted by the form
                $token = $_POST['stripeToken'];

                // Create a Customer
                $customer = Stripe_Customer::create(array(
                    "card" => $token,
                    "description" => $user->person->Email,
                ));

                // Charge the Customer
                try {
                    Stripe_Charge::create(array(
                        "amount" => intval(100*$amountToPay), // amount in cents
                        "currency" => "usd",
                        "customer" => $customer->id)
                    );

                    // Save the customer ID in our database so we can use it later
                    $stripeCustomer->Customer_ID = $customer->id;
                    $stripeCustomer->save();

                    ServicePayments::addClientPayment(Yii::app()->user->clientID, $amountToPay, date('Y-m-d'),$monthly_or_not,'auto');

                    Yii::app()->user->setFlash('success', "Payment has been successfully made!");
                } catch(Stripe_CardError $e) {
                    Yii::app()->user->setFlash('success', $e->getMessage());
                }
            }
        }
        $this->redirect(Yii::app()->createUrl('/myaccount?tab=service'));
    }

    /**
     * Payment by Stripe for remote processing
     */
    public function actionExecuteRpPayment()
    {
    //if (Yii::app()->request->isAjaxRequest)  {

        if (isset($_POST['use_last_cc']) && isset($_POST['amount_to_pay'])) {



            $useLastCC = intval($_POST['use_last_cc']);
            $amountToPay = floatval($_POST['amount_to_pay']);
            $rp_id =  intval($_POST['rp_id']);

            $stripeCustomer = StripeCustomers::model()->findByPk(Yii::app()->user->userID);
            $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

            Yii::import("ext.stripe.lib.Stripe", true);
            Stripe::setApiKey(Yii::app()->config->get('STRIPE_SECRET_KEY'));
            Stripe::setApiVersion("2014-06-17");
            if ($useLastCC == 1 && $stripeCustomer) {
                // Charge the Customer
                try {
                    Stripe_Charge::create(array(
                            "amount" => intval(100*$amountToPay), // amount in cents
                            "currency" => "usd",
                            "customer" => $stripeCustomer->Customer_ID)
                    );

                    $rp = RemoteProcessing::model()->findByPk($rp_id);
                    $rp->Payment = $amountToPay;
                    $rp->PaymentDate = time();

                    $rp->save();

                    RemoteProcessing::digitalBookPayed($rp);

                    Yii::app()->user->setFlash('success', "Payment has been successfully made!");
                } catch(Stripe_CardError $e) {
                    Yii::app()->user->setFlash('success', $e->getMessage());
                    $result['error'] = $e->getMessage();
                }

               // echo CJSON::encode($result);
                $this->redirect(Yii::app()->createUrl('/remoteprocessing'));

            } else if (isset($_POST['stripeToken'])) {

                $userSettings = UsersSettings::model()->findByAttributes(array('User_ID'=>Yii::app()->user->userID));


                if ($stripeCustomer) {
                    //delete old user info
                    try {
                        $cu = Stripe_Customer::retrieve($stripeCustomer->Customer_ID);
                        $cu->delete();
                    } catch (Stripe_InvalidRequestError $e) {
                        $stripeCustomer->delete();
                    }

                } else {
                    $stripeCustomer = new StripeCustomers();
                    $stripeCustomer->User_ID = Yii::app()->user->userID;
                }

                // Get the credit card details submitted by the form
                $token = $_POST['stripeToken'];

                // Create a Customer
                $customer = Stripe_Customer::create(array(
                    "card" => $token,
                    "description" => $user->person->Email,
                ));

                // Charge the Customer
                try {
                    Stripe_Charge::create(array(
                            "amount" => intval(100*$amountToPay), // amount in cents
                            "currency" => "usd",
                            "customer" => $customer->id)
                    );

                    // Save the customer ID in our database so we can use it later
                    $stripeCustomer->Customer_ID = $customer->id;
                    $stripeCustomer->save();


                    $rp = RemoteProcessing::model()->findByPk($rp_id);
                    $rp->Payment = $amountToPay;
                    $rp->PaymentDate = time();
                    $rp->save();

                    RemoteProcessing::digitalBookPayed($rp);

                    // ServicePayments::addClientPayment(Yii::app()->user->clientID, $amountToPay, date('Y-m-d'));

                    Yii::app()->user->setFlash('success', "Payment has been successfully made!");
                } catch(Stripe_CardError $e) {
                    Yii::app()->user->setFlash('success', $e->getMessage());
                }
            }
        }
        $this->redirect(Yii::app()->createUrl('/remoteprocessing'));
    }
    //}


    /**
     * Payment by Stripe for remote processing
     */
    public function actionExecuteRpAnalogPayment()
    {

        //if (Yii::app()->request->isAjaxRequest)  {


        if (isset($_POST['use_last_cc']) && isset($_POST['amount_to_pay'])) {

            $useLastCC = intval($_POST['use_last_cc']);
            $amountToPay = floatval($_POST['amount_to_pay']);
            $rp_id =  intval($_POST['rp_id']);
            $copies_count = intval($_POST['copies_count']);
            $pages_on_sheet = intval($_POST['pages_on_sheet']);
            $quality = strval($_POST['quality']);


            $stripeCustomer = StripeCustomers::model()->findByPk(Yii::app()->user->userID);
            $user = Users::model()->with('person')->findByPk(Yii::app()->user->userID);

            Yii::import("ext.stripe.lib.Stripe", true);
            Stripe::setApiKey(Yii::app()->config->get('STRIPE_SECRET_KEY'));
            Stripe::setApiVersion("2014-06-17");
            if ($useLastCC == 1 && $stripeCustomer) {
                // Charge the Customer
                try {
                    Stripe_Charge::create(array(
                            "amount" => intval(100*$amountToPay), // amount in cents
                            "currency" => "usd",
                            "customer" => $stripeCustomer->Customer_ID)
                    );


                    //ServicePayments::addClientPayment(Yii::app()->user->clientID, $amountToPay, date('Y-m-d'));
                    //Yii::app()->user->setFlash('success', "Payment has been successfully made!");
                    $rp = RemoteProcessing::model()->findByPk($rp_id);
                    $rp->AnalogPayment = $amountToPay;
                    $rp->AnalogPaymentDate = time();
                    $rp->save();

                    RemoteProcessing::analogBookPayed($rp,$quality,$pages_on_sheet,$copies_count);

                    Yii::app()->user->setFlash('success', "Payment for paper book has been successfully made! ");
                } catch(Stripe_CardError $e) {
                    Yii::app()->user->setFlash('success', $e->getMessage());
                    $result['error'] = $e->getMessage();
                }

                // echo CJSON::encode($result);
                $this->redirect(Yii::app()->createUrl('/remoteprocessing'));

            } else if (isset($_POST['stripeToken'])) {

                $userSettings = UsersSettings::model()->findByAttributes(array('User_ID'=>Yii::app()->user->userID));

                /*if (isset($_POST['Automatic_CC_Charge']) && $_POST['Automatic_CC_Charge']=='on') {
                    $userSettings->Automatic_CC_Charge = 1;
                } else {
                    $userSettings->Automatic_CC_Charge = 0;
                }
                $userSettings->save();*/

                if ($stripeCustomer) {
                    //delete old user info
                    $cu = Stripe_Customer::retrieve($stripeCustomer->Customer_ID);
                    $cu->delete();
                } else {
                    $stripeCustomer = new StripeCustomers();
                    $stripeCustomer->User_ID = Yii::app()->user->userID;
                }

                // Get the credit card details submitted by the form
                $token = $_POST['stripeToken'];

                // Create a Customer
                $customer = Stripe_Customer::create(array(
                    "card" => $token,
                    "description" => $user->person->Email,
                ));

                // Charge the Customer
                try {
                    Stripe_Charge::create(array(
                            "amount" => intval(100*$amountToPay), // amount in cents
                            "currency" => "usd",
                            "customer" => $customer->id)
                    );

                    // Save the customer ID in our database so we can use it later
                    $stripeCustomer->Customer_ID = $customer->id;
                    $stripeCustomer->save();


                    $rp = RemoteProcessing::model()->findByPk($rp_id);
                    $rp->AnalogPayment = $amountToPay;
                    $rp->AnalogPaymentDate = time();
                    $rp->save();

                    RemoteProcessing::analogBookPayed($rp,$quality,$pages_on_sheet,$copies_count);

                    Yii::app()->user->setFlash('success', "Payment for paper book has been successfully made! ");
                } catch(Stripe_CardError $e) {
                    Yii::app()->user->setFlash('success', $e->getMessage());
                }
            }
        }
        $this->redirect(Yii::app()->createUrl('/remoteprocessing'));
    }
    //}



}