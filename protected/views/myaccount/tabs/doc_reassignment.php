<h2>Document Reassignment</h2>

<?php $restricted_users_array=array('user','data_entry_clerk');
$alowed_users_array=array('client_admin','db_admin','admin','approver','processor');
if(in_array($user_role,$alowed_users_array)){
echo '<div id="user_allowed" data-id="allowed"></div>';
?>

<div class="user_documents_search">
    <label class="free_label" for="date_to_filter_docs">
        Doc. Created After:
    </label>
    <input id="date_to_filter_docs" type="text" maxlength="45" name="date_to_filter_docs">
</div>
<div id="user_documents" class="grid-view">
    <table class="items" style="margin-bottom: 0px;">
        <thead>
        <tr>
            <th class="width70"><span style="color: #fff;">Date</span></th><th class="width110"><span>File Name</span></th><th class="width110"><span>Client</span></th><th><span>Project</span></th></tr>
        </thead>
    </table>
    <div style="height: 400px; overflow: auto">
        <table class="items" id="docs_users_grid">
            <tbody>
            <?php

            if (count($userDocuments) > 0) {
                $clientsList = '';
                foreach ($user_clients as $client) {
                    $clientsList .= "<li style='min-height: 22px; min-width: 120px; text-align: left; padding-left: 2px;'><span class='user_client_id'>" . $client->Client_ID . "</span> / <span class='user_client_name'>" . CHtml::encode($client->company->Company_Name) . "</span></li>";
                }

                $userProjectsHtml = array();
                foreach ($userProjects as $clientId => $projects) {
                    $projectsList = '';
                    foreach ($projects as $projectID => $projectName) {
                        $projectsList .= "<li style='min-height: 22px; min-width: 120px; text-align: left; padding-left: 2px;'><span class='user_project_id'>" . $projectID . "</span> / <span class='user_project_name'>" . CHtml::encode($projectName) . "</span></li>";
                    }
                    $userProjectsHtml[$clientId] = $projectsList;
                };

                foreach ($userDocuments as $document) {
                    echo '<tr id="doc' . $document->Document_ID . '">';
                    if (Documents::checkReassigmentPossibility($document)) {
                        echo '<td class="width70">' . Helper::convertDate($document->Created) .  '</td>
                              <td class="width110">' . Helper::cutText(15, 100, 14,  $document->image->File_Name) .  '</td>

                                  <td class="dropdown_cell width110" id="doc_client" data="' . $document->Client_ID . '">
                                      <div class="dropdown_cell_ul">
                                          <span class="dropdown_cell_value">' . $document->Client_ID . ' / ' . ($document->client ? CHtml::encode($document->client->company->Company_Name) : '') . '</span>
                                          <ul>' . $clientsList . '</ul>
                                      </div>
                                  </td>
                                  <td class="dropdown_cell" id="doc_project">
                                      <div class="dropdown_cell_ul">
                                          <span class="dropdown_cell_value">' . $document->Project_ID . ' / ' . (isset($userProjects[$document->Client_ID][$document->Project_ID]) ? CHtml::encode($userProjects[$document->Client_ID][$document->Project_ID]) : 'No Project') . '</span>
                                          <ul>' . (isset($userProjectsHtml[$document->Client_ID]) ? $userProjectsHtml[$document->Client_ID] : '') . '</ul>
                                      </div>
                                  </td>';
                                    } else {
                                        echo '<td class="width70">' . Helper::convertDate($document->Created) .  '</td>
                              <td class="width110">' . Helper::cutText(15, 100, 14,  $document->image->File_Name) .  '</td>

                                  <td class="dropdown_cell width110" id="doc_client" data="' . $document->Client_ID . '">
                                      <div class="dropdown_cell_ul">
                                          <span class="dropdown_cell_value">' . $document->Client_ID . ' / ' . ($document->client ? CHtml::encode($document->client->company->Company_Name) : '') . '</span>

                                      </div>
                                  </td>
                                  <td class="dropdown_cell" id="doc_project">
                                      <div class="dropdown_cell_ul">
                                          <span class="dropdown_cell_value">' . $document->Project_ID . ' / ' . (isset($userProjects[$document->Client_ID][$document->Project_ID]) ? CHtml::encode($userProjects[$document->Client_ID][$document->Project_ID]) : 'No Project') . '</span>
                                      </div>
                                  </td>';
                    }
                    echo '</tr>';
                }
            } else {
                echo '<tr id="doc0">';
                echo '<td clospan="3">Select date to populate this grid</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>
