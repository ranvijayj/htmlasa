<?php
    if ($userClientRow->hasClientAdminPrivileges()) {
        $style = 'style="cursor: pointer;"';
    } else {
        $style = '';
    }
?>
<h2>Project Detail:</h2>
<ul class="sidebar_active_list">
    <li><span class="acct_det_first_col">Project Name:</span><span class="details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($project->Project_Name); ?></span></li>
    <li><span class="acct_det_first_col">Description:</span><span class="details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($project->Project_Description); ?></span></li>
    <li><span class="acct_det_first_col">Project #:</span><span class="details_page_value" <?php echo $style; ?>><?php echo  CHtml::encode($project->Project_Prod_Number); ?></span></li>
    <li><span class="acct_det_first_col">PO St. #:</span><span class="details_page_value" <?php echo $style; ?>><?php echo  CHtml::encode($project->PO_Starting_Number); ?></span></li>
    <li><span class="acct_det_first_col">CKRQ St. #:</span><span class="details_page_value" <?php echo $style; ?>><?php echo  CHtml::encode($project->Ck_Req_Starting_Numb); ?></span></li>
</ul>

<h2>Project PO Formatting:</h2>
<ul class="sidebar_active_list">
    <li><span class="acct_det_first_col">Client Name:</span><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($poFormatting->PO_Format_Client_Name); ?></span></li>
    <li><span class="acct_det_first_col">Project Name:</span><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($poFormatting->PO_Format_Project_Name); ?></span></li>
    <li><span class="acct_det_first_col">Job Name:</span><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($poFormatting->PO_Format_Job_Name); ?></span></li>
    <li><span class="acct_det_first_col">Starting #:</span><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo  CHtml::encode($poFormatting->PO_Format_Starting_Num); ?></span></li>
    <li><span class="acct_det_first_col">Address:</span><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($poFormatting->PO_Format_Address); ?></span></li>
    <li><span class="acct_det_first_col">City/St/Zip:</span><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($poFormatting->PO_Format_City_St_ZIP); ?></span></li>
    <li><span class="acct_det_first_col">Phone:</span><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo CHtml::encode($poFormatting->PO_Format_Phone); ?></span></li>
    <li><span class="acct_det_first_col">Show Add. Lan.:</span><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo ($poFormatting->PO_Format_Sig_Req == 1) ? 'Yes' : 'No'; ?></span></li>
    <li><span class="acct_det_first_col">Add. Language:</span><br/><span class="formatting_details_page_value" <?php echo $style; ?>><?php echo  CHtml::encode($poFormatting->PO_Format_Addl_Language); ?></span></li>
</ul>
