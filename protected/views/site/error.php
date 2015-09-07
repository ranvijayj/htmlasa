<?php
/* @var $this SiteController */
/* @var $error array */

$this->pageTitle=Yii::app()->name . ' - Error';
$this->breadcrumbs=array(
	'??',
);
?>
<h1>Action Not Available</h1>
<div class="wrapper" style="border-right: 0; width: 929px;">
    <div class="error_page">
        <div class="twelve columns">

            <?if ($code != '403') {?><h1 class="error-code"><?php echo $code; ?></h1><?}?>

            <?php
                if ($code == '404') {
                     echo '<h1>Sorry, the page you tried cannot be found.</h1>';
                } else if ($code == '403') {?>
                    <h1>
                    <svg id="svgelem" height="100" style="width: 100px;" xmlns="http://www.w3.org/2000/svg">
                            <circle id="redcircle" cx="50" cy="50" r="50" fill="grey" />
                            <circle id="redcircle" cx="50" cy="50" r="40" fill="white" />
                            <text x="27" y="85" fill="grey" style="font-size: 90px;font-weight: bold;">?</text>

                    </svg>
                    </h1>
                    <h1>Sorry,this action is not currently available to the user</h1>
                <?} else {
                    echo '<h1>Site is closed for maintenance</h1>';
                }
            ?>
        </div>
        <?php
            if ($code == '404') {
                echo '<p>You may have typed the address incorrectly or you may have used an outdated link.</p>';
            } else if ($code == '403') {
                echo "<p>Possible reasons:</p>
                      <p>1) Your Usertype doesn't have access to this page</p>
                      <p>2) Client has not selected Service Level for this page. The client administrator can follow the link to upgrade <a href='/myaccount?tab=service'>Service Level</a> Settings</p>";
            }
        ?>
        <p>
            If the problem persists, please <a href="mailto:<?php echo Yii::app()->config->get('SUPPORT_EMAIL'); ?>">email us</a>.
        </p>
    </div>
</div>
