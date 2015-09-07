<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 2/6/15
 * Time: 9:52 AM
 */?>

<div class="search_block">

        <input type="text" name="search" data-id="<?=$summary_sl_settings['Tiers_Str'];?>" value="<?php echo $summary_sl_settings['Tier_Name'];?>" id="search_field" maxlength="250" <?= (Yii::app()->user->id=='db_admin') ? '' : 'disabled'?>>
            <div id="search_options_txtfield" style="width: 255px;top: 17px;width: 190px;padding: 0px;font-size: 11px;text-align: left;">
                    <ul>
                        <?foreach ($items as $item) {?>
                            <?$checked = in_array($item["Tier_ID"],$level_ids) ? 'checked' : '';

                            ?>
                            <li>
                                <input type='checkbox'  name='Tiers[<?=$item['Tier_ID'];?>]' data-name="<?=$item['Tier_Name'];?>" data-id="<?=$item['Tier_ID'];?>"
                                       data-users="<?=$item['Users_Count']?>"
                                       data-projects="<?=$item['Projects_Count']?>"
                                       data-storage="<?=$item['Storage_Count']?>"
                                       data-fee="<?=$item['Base_Fee']?>"
                                       data-checksum="<?=$item['Storage_Count']+$item['Projects_Count']+$item['Users_Count'];?>"
                                       data-checked="<?=$checked;?>"
                                       class="tier_level" <?=$checked?>>

                                                <span style="font-family:Arial Narrow sans-serif;color: #7988a3;font-size: 1.2em; text-shadow: 1px 1px 0 rgba(255, 255, 255, 0.8);">
                                                    <?=$item['Tier_Name'].' '.$item['Base_Fee'].'/MO';?>
                                                </span>

                            </li>
                        <?}?>
                    </ul>
            </div>
</div>

<script type="text/javascript">

    $(document).ready(function() {
        $('#search_field').focus(function() {
            clearTimeout(self.timeoutSearch);
            $('#search_options_txtfield').fadeIn(200);
        });

        $('#search_field').blur(function() {
            self.timeoutSearch = setTimeout(function() {
                $('#search_options_txtfield').fadeOut(200);
            }, 200);
        });

        $('#search_options_txtfield').click(function() {
            $('#search_field').focus();
        });

    });

</script>