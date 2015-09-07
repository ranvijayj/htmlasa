<?php
/*
 *
 *
 */

class ProgressBar
{
    protected static $_instance;
    protected  $status;

    private function __construct(){
        $this->status=0;
    }
    private function __clone(){
    }

    public static function init() {

        if (null === self::$_instance) {
                 self::$_instance = new self();

         $_SESSION['progress']=0;
        }
        return self::$_instance;

    }


    public function step($step){

        session_start();
        $_SESSION['progress']= $_SESSION['progress']+$step;
        session_write_close();
            //if($_SESSION['progress'] > 100) {$_SESSION['progress'] =100;}


    }

    public function done(){
        $_SESSION['progress']=100;
    }

    public function destroy(){
        $_SESSION['progress']=0;
    }

    public static function toZero(){
        $_SESSION['progress']=0;
    }

    public static function returnStatus(){
        return $_SESSION['progress'];
    }

    public static function returnState(){
        return $_SESSION['progress_state'];
    }



    public static function setStatus($status){
        session_start();
        $_SESSION['progress']=$status;
        session_write_close();
    }

    public static function appendStatus($status){
        session_start();
        $_SESSION['progress'].=$status;
        session_write_close();
    }

    public static function setState($status){
        session_start();
        $_SESSION['progress_state']=$status;
        session_write_close();
    }

}
