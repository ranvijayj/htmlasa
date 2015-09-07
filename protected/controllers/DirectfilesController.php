<?php

class DirectfilesController extends Controller
{
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
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index'),
                'users'=>array('*'),
            ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Index action
	 */
	public function actionIndex($controller, $extension)
	{
        //display robots.txt
        if ($controller == 'robots' && $extension == 'txt') {
            $this->generateRobotsTxt();
            die;
        }

        // generate sitemap.xml
        if ($controller == 'sitemap' && $extension == 'xml') {
            $this->generateSitemap();
            die;
        }

        throw new CHttpException('404','Unable to resolve the request "' . $controller . '.' . $extension . '". ');
	}

    /**
     * Display robots.txt
     */
    private function generateRobotsTxt() {
        $path = Yii::getPathOfAlias('webroot') . '/robots.txt';
        $content = file_get_contents($path);
        header("Content-type: text/plain");
        echo $content;
        exit();
    }

    /**
     * Generate sitemap.xml
     */
    private function generateSitemap() {
        $urls = array();

        $urls[0]['url'] = Yii::app()->config->get('SITE_URL');
        $urls[0]['priority'] = '1.0';

        $urls[1]['url'] = Yii::app()->config->get('SITE_URL') . '/site/register';
        $urls[1]['priority'] = '0.8';

        $urls[2]['url'] = Yii::app()->config->get('SITE_URL') . '/site/forgotpassword';
        $urls[2]['priority'] = '0.8';

        header("Content-type: text/xml");
        echo Helper::buildXmlSitemap($urls, 'weekly');
        exit();
    }
}
