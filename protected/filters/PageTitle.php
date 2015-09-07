<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 4/22/15
 * Time: 10:40 AM
 */
class PageTitle extends CFilter {
    public $prevTitle;
    public $controller;

    public function preFilter($filterChain) {
        //$title = $this->getPageTitle();
        $this->controller->setPageTitle ('bla bala');
        return true;
    }

    public function postFilter($filterChain) {

        $this->controller->setPageTitle ('bla bala');
        return true;

    }


}