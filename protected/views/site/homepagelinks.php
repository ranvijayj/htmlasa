<div class="home-page">
    <table>
        <tr>
            <td>
                <a href="<?=Yii::app()->createUrl('/uploads');?>">
                    <div class="home-item">
                        <i class="icon-upload"></i> <span>Upload Docs</span>
                    </div>
                </a>
            </td>
            <td>
                <a href="<?=Yii::app()->createUrl('/ap')?>">
                    <div class="home-item">
                        <i class="icon-docs"></i> <span>View AP</span>
                    </div>
                </a>
            </td>
            <td>
                <a href="<?=Yii::app()->createUrl('/site/page',array('view'=>'history'));?>">
                    <div class="home-item">
                        <i class="icon-book-open"></i> <span>History</span>
                    </div>
                </a>
            </td>
            <td>
                <a href="<?=Yii::app()->createUrl('/coa');?>">
                    <div class="home-item">
                        <i class="icon-chart-bar"></i> <span>Chart Of Accounts</span>
                    </div>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                <a href="<?=Yii::app()->createUrl('/vendor');?>">
                    <div class="home-item">
                        <i class="icon-user-male"></i> <span>Vendors</span>
                    </div>
                </a>
            </td>
            <td>
                <a href="<?=Yii::app()->createUrl('/site/page',array('view'=>'remote_processing'));?>">
                    <div class="home-item">
                        <i class="icon-paper-plane"></i> <span>Remote Processing</span>
                    </div>
                </a>
            </td>
            <td>
                <a href="<?=Yii::app()->createUrl('/myaccount?tab=man_users');?>">
                    <div class="home-item">
                        <i class="icon-users-1"></i> <span>Manage Users</span>
                    </div>
                </a>
            </td>
            <td>
                <a href="<?=Yii::app()->createUrl('/site/page',array('view'=>'help'));?>">
                    <div class="home-item">
                        <i class="icon-help-circled-alt"></i> <span>Help</span>
                    </div>
                </a>
            </td>
        </tr>
    </table>
</div>