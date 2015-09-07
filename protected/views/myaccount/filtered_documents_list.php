<?php
if (count($userDocuments) > 0) {
    $clientsList = '';
    foreach ($userInfo->clients as $client) {
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
    echo '<td clospan="3">Documents were not found</td>';
    echo '</tr>';
}
?>