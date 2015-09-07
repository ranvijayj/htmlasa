<?php
class ShowPdfWidget extends CWidget {
    public $params = array();

    public function run() {
        //convert to pdf
        if( $this->params['mime_type'] && strtolower( $this->params['mime_type'])!='application/pdf' &&  intval($this->params['doc_id'])>0 ) {
            $return_array=FileModification::prepareFile($this->params['doc_id']);
            $return_array = FileModification::ImageToPdf($return_array['path_to_dir'],$return_array['filename'],$return_array['ext']);
            $return_array = FileModification::writeToBase($return_array['path_to_dir'],$return_array['filename'],'application/pdf',$this->params['doc_id']);
            $this->params['mime_type'] = 'application/pdf';
        } else if (!$this->params['mime_type']) {
            $return_array=FileModification::prepareFile($this->params['doc_id']);
            $return_array = FileModification::writeToBase($return_array['path_to_dir'],$return_array['filename'],'application/pdf',$this->params['doc_id']);
        }

        //old style display
        if ($this->params['mode']==1) {
            if ($this->params['show_rotate']) {
                $show_rotate_buttons_block =  $this->render("application.views.filemodification.buttons",array(
                        'buttons' => array('rotate_cw','rotate_ccw'),
                        'docId'  => $this->params['doc_id'],
                        'file_name'=>'',
                        'imgId'=>''
                    ), true);

            }

            $result = $this->render('application.views.filemodification.iframe',array(
                    'mime_type'=>$this->params['mime_type'],
                    'doc_id'=>$this->params['doc_id'],
                    'show_rotate_buttons_block'=> $show_rotate_buttons_block,
                ),true
            );

           echo $result;
        }


        //PDF.js iframe
        //full version PDF.js viewer only with CSS modified and some toolbars and buttons hided
        if ($this->params['mode']==3) {
            /*$result = $this->render('application.views.filemodification.pdfjs',array(
                    'doc_id'=>$this->params['doc_id'],
                    'mime_type'=>$this->params['mime_type'],
                    'approved'=>$this->params['approved'],
                    'height'=>$this->params['height'],
                ),true
            );
            echo $result;*/

            /*$url = '/documents/getdocumentfile?doc_id='.intval($this->params['doc_id']);
            $this->widget('ext.pdfJs.QPdfJs',array(
                'url'=>$url,
                'options'=>array(
                    'buttons'=>array(
                        'print' => $this->params['approved'],
                        'download'=>$this->params['approved'],
                    ),
                    'height'=>800,
                    'approved'=>$this->params['approved'],

                )
            ));*/

            //we need to add files in cache in order not to show real path to whole internet.
            $file_id = FileCache::addToFileCache($this->params['doc_id']);
            $height = $this->params['height']? $this->params['height']: 800 ;
            $content = '<iframe src="/documents/PreviewFile?file_id='.$file_id.'&approved='.$this->params['approved'].'" style="width: 99.9%;height:'.$height.'px;" > </iframe>';
            echo $content;

        }


        //PDF.js own viewer
        //custom viewer based on PDF.JS sample customised as a built-in Chrome viewer. Has text selection functionallity
        if ($this->params['mode']==4) {
            $result = $this->render('application.views.filemodification.ownviewer',array(
                    'doc_id'=>$this->params['doc_id'],
                    'mime_type'=>$this->params['mime_type'],
                    'approved'=>$this->params['approved']
                ),true
            );
            echo $result;
        }

        //PDF.js own viewer
        //custom viewer based on PDF.JS sample customised as a built-in Chrome viewer. WITHOUT text selection functionallity
        if ($this->params['mode']==5) {
            $result = $this->render('application.views.filemodification.ownviewer_light',array(
                    'doc_id'=>$this->params['doc_id'],
                    'mime_type'=>$this->params['mime_type'],
                    'approved'=>$this->params['approved']
                ),true
            );
            echo $result;
        }





        //$this->render('breadCrumb');
    }

}