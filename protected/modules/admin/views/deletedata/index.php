
<?php
Yii::app()->clientScript->registerScriptFile('/js/jquery.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/grid.locale-en.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/jquery.jqGrid.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/jquery-ui.custom.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile('/css/ui.jqgrid.css');

$this->breadcrumbs=array(
    'Admin'=>array('/admin'),
    'Delete Data',
);

?>

<?php
    $this->renderPartial('//widgets/db_panel_menu');
?>

<!--<h1>Remote Processing </h1>-->

<div id="wrapper" style="margin: 10px auto;width: 1000px;border: 1px solid;min-height:600px;padding: 30px; ">

    IT WORKS

</div>





