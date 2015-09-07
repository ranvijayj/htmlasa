<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 4/22/15
 * Time: 10:40 AM
 */
class AnalizeUrl extends CFilter {

    public function preFilter($filterChain) {
        $cid = intval($_GET['cid']);
        $pid = intval($_GET['pid']);


        if ($cid!=0 && $cid != Yii::app()->user->clientID) {
            //we need to change client
            if ($pid!=0 && $pid != Yii::app()->user->projectID) {
                //we also need to change project
                $action = "client and project change";

                $pid =  Yii::app()->user->projectID;
            }

            $action = "client change";

        }

        if ($pid!=0 && $pid != Yii::app()->user->projectID) {
            //we need to change project
            $action = "client change";
        }



        return true;
    }

    public function postFilter($filterChain) {

    }


}