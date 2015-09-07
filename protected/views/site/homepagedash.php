


        <div class="home-page small" >
            <div class="homepage_section_left section" style="width: 310px;float: left;">

                <span class="big_numbers"> Company / Project </span>

                <span><img class="reload_icon_primary" src="<?=Yii::app()->baseUrl?>/images/_reload.png"></span>

                <?php  foreach ($projects_list as $item) { ?>

                    <li style="padding-top: <?= $item['Project_Name'] == 'all' ? '10px' : '0px'; ?>;">
                        <a href="#" class="client_project" data-client-id="<?=$item['Client_ID']?>" data-project-id="<?=$item['Project_ID']?>">

                            <span title="<?=$item['DocCount'].' total items';?>"
                                  style="font-weight: <?=$item['DocCount']==0 ? 'normal' : 'bold'; ?>;"><?=$item['Company_Name']?> / <?=$item['Project_Name']?>
                            </span>
                        </a>
                    </li>
                <?} ?>

            </div>

            <div class="homepage_section_center section" style="width: 475px;float: left;">
                <div id="center_up" style="margin-bottom: 30px;text-align: left;">

                    <span class="big_numbers">Document Status </span>
                    <span class="small_numbers navigator_indicator"> All companies/ All projects </span>


                    <table style="margin: 20px 0px;width: 470px;">

                        <tr style="margin-bottom: 10px;">
                            <td id="in_data_entry" class="big_numbers"><?=$total_metrics['totals_array']['total_to_entry']?></td>
                            <td id="in_approval" class="big_numbers"><?=$total_metrics['totals_array']['total_in_approval']?></td>
                            <td id="to_approval" class="big_numbers"><?=$total_metrics['totals_array']['total_to_approve']?></td>
                            <td id="to_batch" class="big_numbers"><?=$total_metrics['totals_array']['total_to_batch']?></td>
                            <td id="to_file" class="big_numbers"><?=$total_metrics['totals_array']['total_to_file']?></td>
                        </tr>

                        <tr>
                            <td class="small_numbers">In Data Entry</td>
                            <td class="small_numbers">In Approval</td>
                            <td class="small_numbers">To Approve</td>
                            <td class="small_numbers">To Batch</td>
                            <td class="small_numbers">To File</td>
                        </tr>
                    </table>
                </div>

                <!-- chart section -->
                <div id="center_down">
                    <div class="small_numbers navigator_indicator" style="float: left;">
                        All companies/ All projects
                    </div>

                    <div style="margin-left: 80%;">
                        <img class="reload_icon" src="<?=Yii::app()->baseUrl?>/images/_reload.png"  data-project-id="all" data-client-id="all">
                    </div>

                    <div style="border-bottom: 1px solid #d3d3d3;width: 400px;margin:10px 0px 10px 15px;">
                        <!-- just underline -->
                    </div>


                    <!--<div id="piechart" >

                    </div>-->


                    <div id="chart_div">

                    </div>

                    <div id="chart_popup" style="display: none;color: #ff0000;" class="small_numbers">Please select specific Company or Project</div>
                </div>

            </div>

            <div class="homepage_section_right section" style="width: 240px;float: right;">
            <table>
                <tr>
                    <td>
                        <a href="<?=Yii::app()->createUrl('/uploads');?>">
                            <div class="home-item small">
                                <i class="icon-upload"></i> <span>Upload Docs</span>
                            </div>
                        </a>
                    </td>

                    <td>
                        <a href="<?=Yii::app()->createUrl('/vendor');?>">
                            <div class="home-item small">
                                <i class="icon-user-male"></i> <span>Vendors</span>
                            </div>
                        </a>
                    </td>
                </tr>

                <tr>
                    <td>
                        <a href="<?=Yii::app()->createUrl('/ap')?>">
                            <div class="home-item small">
                                <i class="icon-docs"></i> <span>View AP</span>
                            </div>
                        </a>
                    </td>

                    <td>
                        <a href="<?=Yii::app()->createUrl('/site/page',array('view'=>'remote_processing'));?>">
                            <div class="home-item small">
                                <i class="icon-paper-plane"></i> <span>Remote Processing</span>
                            </div>
                        </a>
                    </td>

                </tr>

                <tr>
                    <td>
                        <a href="<?=Yii::app()->createUrl('/site/page',array('view'=>'history'));?>">
                            <div class="home-item small">
                                <i class="icon-book-open"></i> <span>History</span>
                            </div>
                        </a>
                    </td>

                    <td>
                        <a href="<?=Yii::app()->createUrl('/myaccount?tab=man_users');?>">
                            <div class="home-item small">
                                <i class="icon-users-1"></i> <span>Manage Users</span>
                            </div>
                        </a>
                    </td>

                </tr>

                <tr>
                    <td>
                        <a href="<?=Yii::app()->createUrl('/coa');?>">
                            <div class="home-item small">
                                <i class="icon-chart-bar"></i> <span>Chart Of Accounts</span>
                            </div>
                        </a>
                    </td>


                    <td>
                        <a href="<?=Yii::app()->createUrl('/site/page',array('view'=>'help'));?>">
                            <div class="home-item small">
                                <i class="icon-help-circled-alt"></i> <span>Help</span>
                            </div>
                        </a>
                    </td>

                </tr>
            </table>
        </div>
    </div>

        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/googlecharts.js"></script>

        <script type="text/javascript">

            var chart_data = '<?=CJavaScript::jsonEncode(array($total_metrics['data_for_chart'],$total_metrics['totals_array']));?>';
            var google_charts = new GoogleCharts(<?=Yii::app()->user->clientID;?>,'<?=Yii::app()->user->projectID;?>',chart_data);

            $(document).ready(function() {

            });


        </script>

