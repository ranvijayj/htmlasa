<?php if (count($doclist) > 0) {

    foreach($doclist as $doc)
    {
        $type = explode('/', $doc['Mime_Type']); $type = $type[1];
        echo "<tr>
                               <td class='width30'>
                                    <input type='checkbox' class='delete_checkbox' name='Documents[".$doc['Document_ID']."][Document_ID]]' value='".$doc['Document_ID']."' />
                                    <input type='hidden' class='hidden_doctype' name='Documents[".$doc['Document_ID']."][Document_Type]' value='".$doc['Document_Type']."' />
                               </td>
                               <td class='width20'>".$doc['Document_Type']."</td>
                               <td data-id='" . $doc['Document_ID'] ."' class='pointer_file' >
                                        <img src='" . Yii::app()->request->baseUrl . "/images/file_types/" . $type . ".png' alt='" . strtoupper($type) . "' class='img_type' />"
                                        . substr($doc['File_Name'],0,20) ."...".
                                "</td>
                                <td class='width130'>".Helper::convertDate($doc['Created'])."</td>
                                <td >".$doc['First_Name']." ".$doc['Last_Name']."</td>
                                <td ><a class='show_canvas' href='#' data-id='" . $doc['Document_ID'] ."'>Change file</a></td>
                                <td >Name date time of modification</td>
                          </tr>";
    }
} else {
    ?>
    <tr id="no_images">
        <td>Documents were not found.</td>
    </tr>
<?php } ?>