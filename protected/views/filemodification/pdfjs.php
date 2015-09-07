<div class="w9_detail_block" id="w9_detail_block1" style="overflow: hidden;height: inherit;">

    <?if (strpos($mime_type, 'pdf')) {?>
        <?  $file_id = FileCache::addToFileCache($doc_id);?>
        <iframe src='/documents/PreviewFile?file_id=<?=$file_id;?>&approved=<?=$approved?>' style="width: 99.9%;height: <?=$height? $height:800; ?>px; ">      </iframe>

    <? } else {

            if ( intval($doc_id)!=0 ) {
                echo '<img src="/documents/getdocumentfile?doc_id='.$doc_id .' alt="" id="document_file" title="" class="documet_file width100pn">';
            } else if (is_string( $doc_id )) {
                echo '<img src="/documents/getdocumentfilebypath?doc_id='.urlencode($doc_id) .'" alt="" id="document_file" title="" class="documet_file width100pn">';
            }

    }?>

</div>

