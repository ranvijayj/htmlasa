
    <?php $form=$this->beginWidget('CActiveForm', array ('id'=>'coa_adding_form'));?>


    <div class="row popup_row">
        <table>
            <tr>
                <td><?php echo $form->DropDownList(CoaClass::model(),'COA_Class_ID',CHtml::listData($coa_classes,'COA_Class_ID','Class_Shortcut'));?></td>

                <td><?php echo $form->TextField($coa,'COA_Name');?></td>

                <td><?php echo $form->TextField($coa,'COA_Acct_Number');?></td>

                <td><?php echo $form->TextField($coa,'COA_Budget');?></td>
            </tr>
            <tr>
                <td></td>

                <td></td>

                <td></td>

                <td style="text-align: right;padding-right: 30px;">
                    <a href="#" id="confirm_coa_adding">Save</a><br>
                    <a href="#" id="cancel_coa_adding">Cancel</a>
                </td>
            </tr>
        </table>
    </div>


    <?php $this->endWidget(); ?>


