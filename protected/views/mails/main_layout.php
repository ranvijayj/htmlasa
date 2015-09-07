<div style="width: 100%; height: 100%; font-family: 'Arial Narrow', Arial;">
    <div style="height: auto; padding-bottom: 65px; margin: 0 auto; width: 900px;">
        <div style="height: 50px; padding: 0; width: 100%;">
            <div style="background-color: #015EC5; border: 1px solid #666; color: #FFFFFF; float: left; font-size: 16px; font-weight: bold; margin: 2px 10px; padding: 10px 20px;">ASA AP</div>
            <div style="color: #000; float: right; font-size: 24px; font-weight: bold; margin-top: 15px;">ASA AP</div>
        </div>
        <div style="border: 1px solid #8E8E8E; margin-bottom: 10px;">
            <div style=" background-color: #0078C1; font-family: 'Arial Narrow',Arial; font-size: 18px; font-weight: bold; height: 55px; line-height: 55px; margin-bottom: 0; padding-left: 10px;">
                <?php echo $subject; ?>
            </div>
            <div style="padding: 10px; font-size: 15px;">
                <?php echo $body; ?>
            </div>
        </div>
        <div style="color:#000; font-size:13px; padding:5px 5px; font-style: italic;">
            ASA AP support team.<br>
            <p>
                Support: <?php echo Yii::app()->config->get('SUPPORT_NUMBER'); ?>
                <br>
                Website:
                <a target="_blank" style="color: #0066CC" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>">http://<?php echo $_SERVER['HTTP_HOST']; ?></a>
                <br>
                Email:
                <a target="_blank" style="color: #0066CC" href="mailto:<?php echo Yii::app()->config->get('ADMIN_EMAIL'); ?>"><?php echo Yii::app()->config->get('ADMIN_EMAIL'); ?></a>
            </p>
        </div>
    </div>
</div>