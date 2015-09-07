<?php
/**
 * This file is only agent for launching PDF viewer Widget
 */

        $url = '/documents/FileContent?file_id='.$file_id;

        $this->widget('ext.pdfJs.QPdfJs',array(
            'url'=>$url,
            'options'=>array(
                'buttons'=>array(
                    'print' => true,
                    'download'=>true,
                ),
                'height'=>600,
                'file_id'=>$file_id,
                'approved'=>$approved,
            )
        ));

        ?>


