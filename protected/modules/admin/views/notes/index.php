<?php
Yii::app()->clientScript->registerScriptFile('/js/jquery.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/grid.locale-en.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/jquery.jqGrid.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/jquery-ui.custom.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile('/css/ui.jqgrid.css');
$this->breadcrumbs=array(
    'Admin'=>array('/admin'),
    'DB Admin Panel',
);

?>

<?php
    $this->renderPartial('//widgets/db_panel_menu');
?>

<?php echo $out; ?>