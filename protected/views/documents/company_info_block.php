<span class="sidebar_block_header">Details:</span>

<?php

    if ($appr->DocType=='PO') {
        $po = Pos::model()->with('document')->findByPk($appr->ID);
        $poFormatting = PoFormatting::model()->findByAttributes(array(
            'Project_ID' => $po->document->Project_ID));

        //finding page in data entry array
        $page=0;
        if ($is_dec) {

            // get po to enter data
            $pos = Pos::model()->findPOToEntry();
            //var_dump($pos);
            //var_dump($docId);
            //die;


                $i=1;
                foreach($pos as $po) {
                    if ($po->Document_ID == $docId) {
                    $page=$i;
                    }
                    $i++;
                }
            $dec_link='/dataentry/po?page='.$page;
            if($page==0) {$mess = 'Data entry for this item not available for you';
            } else {$mess = '<a href="'.$dec_link.'"> Open item for data entry</a>';}
        }

    }

    if ($appr->DocType=='AP') {
    $ap = Aps::model()->with('document')->findByPk($appr->ID);

        //finding page in data entry array
        $page=0;
        if ($is_dec) {

            // get po to enter data
            $aps = Aps::model()->findAPToEntry();
            /*var_dump($aps);
            var_dump($docId);
            die;*/
            $i=1;
            foreach($aps as $ap) {
                if ($ap->Document_ID == $docId) {
                    $page=$i;
                }
                $i++;
            }
            $dec_link='/dataentry/ap?page='.$page;
            if($page==0) {$mess = 'Data entry for this item not available for you';
            } else {$mess = '<a href="'.$dec_link.'"> Open item for data entry</a>';}
        }

    }

?>

<ul class="sidebar_list">

    <li><h2 class="sidebar_block_list_header"><?php echo isset($appr->CompanyName) ? CHtml::encode($appr->CompanyName) : 'Vendor not attached'; ?></h2></li>
    <li>Number: <span class="details_page_value"><?php


                if ($appr->DocType=='PO') {   echo $po->PO_Number ? CHtml::encode($po->PO_Number) : '<span class="not_set">Not set</span>';}
                if ($appr->DocType=='AP') {   echo (($ap->Invoice_Number != '0') ? CHtml::encode($ap->Invoice_Number) : '<span class="not_set">Not set</span>');}


                ?></span></li>
    <li>Date: <span class="details_page_value"><?php echo CHtml::encode($appr->DocCreated) ? CHtml::encode(Helper::convertDate($appr->DocCreated)) : '<span class="not_set">Not Set</span>'; ?></span></li>
    <li>Job Name: <span class="details_page_value"><?php echo (isset($poFormatting->PO_Format_Job_Name) && $poFormatting->PO_Format_Job_Name) ? $poFormatting->PO_Format_Job_Name : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Account Number: <span class="details_page_value"><?php echo $po->PO_Account_Number ? CHtml::encode($po->PO_Account_Number) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Amount: <span class="details_page_value"><?php echo CHtml::encode($appr->CompanyName) ? CHtml::encode(number_format($appr->Amount, 2,'.', ',')) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Payment Type: <span class="details_page_value"><?php echo isset($po->Payment_Type) ? $po->Payment_Type : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Due Date: <span class="details_page_value"><?php echo CHtml::encode($appr->DueDate) ? CHtml::encode(Helper::convertDate($appr->DueDate)) : '<span class="not_set">Not set</span>'; ?></span></li>
    <li>Created on  <span class="details_page_value"><?php echo $appr->DocCreated."<br/> by ".$appr->OwnerFirst_Name." ".$appr->OwnerLast_Name ?></li>
    <br/> <br/>
    <? if($is_dec) {echo $mess; } ?>
</ul>