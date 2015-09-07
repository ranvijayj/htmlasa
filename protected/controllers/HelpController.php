<?php

class HelpController extends Controller
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

    public function accessRules()
    {
        return array(

         array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('GetVideoByUrl','Video','GetVideoBySearchQuery'),
                'users'=>array('admin', 'user', 'approver', 'data_entry_clerk', 'db_admin', 'processor', 'client_admin'),
         ),

            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }


    public function actionIndex()
	{
		$this->render('index');
	}


    public function actionGetVideoByUrl()
    {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['url']) ) {
            ini_set('open_basedir', NULL);

            $video_url = $_POST['url'];

           $parts = explode('/',$video_url);
           $video_number = $parts[count($parts)-1];
            if($video_number) {
                $video = Videos::model()->findByAttributes(array(
                    'Video_URL'=>$video_url
                ));
                $result['pass'] = $video->Video_Password;
            }

            $result['html']= $this->renderPartial('video_player', array(
                'video'=>$video,
                'video_number'=>$video_number
            ),true);



            echo CJSON::encode($result);


        }


    }

    public function actionVideo()
    {

        $visibility_array = Videos::getRightsForVideo();
        $videos = Videos::model()->findAllByAttributes(array(
            'Visibility'=>$visibility_array
        ));




        $this->render('video', array(
            'videos' => $videos,
        ));
    }




    public function actionGetVideoBySearchQuery() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['query'])) {

            $queryString = trim($_POST['query']);
            $options = array(

                'search_option_title' => intval($_POST['search_option_title']),
                'search_option_log_line' => intval($_POST['search_option_log_line']),
                'search_option_description' => intval($_POST['search_option_description']),
                'search_option_link_name' => intval($_POST['search_option_link_name']),
            );

            $video_list = Videos::getVideoListByQueryString($queryString, $options);

            $result['html'] = $this->renderPartial('_video', array(
                'video_list' => $video_list,
            ),true);

            echo CJSON::encode($result);


        }

    }

}