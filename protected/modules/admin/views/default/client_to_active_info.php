<ul class="sidebar_list">
    <li><h2 class="sidebar_block_list_header"><?php  echo  CHtml::encode($company->Company_Name); ?></h2></li>
    <li>Company ID: <?php  echo  $company->Company_ID; ?></li>
    <li>Fed ID: <?php  echo  $company->Company_Fed_ID; ?></li>
    <li>Address: <?php  echo  CHtml::encode($address->Address1); ?></li>
    <li>City: <?php  echo  CHtml::encode($address->City); ?></li>
    <li>State: <?php  echo  CHtml::encode($address->State); ?></li>
    <li>Zip: <?php  echo  CHtml::encode($address->ZIP); ?></li>
</ul>
<div class="grid-view" style="padding-top: 11px;" >
    <table class="items mbot0">
        <thead>
        <tr>
            <th><span>Company Projects</span></th>
        </tr>
        </thead>
    </table>
    <div style="height: 280px; overflow: auto">
        <table class="items" id="client_to_active_projects">
            <tbody>
            <?php
            if (count($projects)) {
                foreach ($projects as $project) {
                    echo '<tr>';
                    echo '<td>' . CHtml::encode($project->Project_Name) .  '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr>';
                echo '<td>Projects were not found</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
