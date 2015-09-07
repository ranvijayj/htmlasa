    <div id="video_list" style="min-height: 500px;">
        <?
        foreach ($video_list as $video) {
            $str = '<a class="video_link" data-id="'.$video->Video_ID.'" href="'.$video->Video_URL.'">'. $video->Video_Log_Line.'</a><br />';

            if ($video->Visibility !=5) {
                echo $str;
            } else if ($video->Visibility ==4) {
                if(Yii::app()->user->clientID = $video->Clients_Client_ID) {
                    echo $str;
                }
            } else if ($video->Visibility ==5) {
                if(Yii::app()->user->clientID = $video->Clients_Client_ID && Yii::app()->user->projectID == $video->Project_ID) {
                    echo $str;
                }
            }
        }
        ?>

    </div>
