<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Vendors List</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquerymin.js"></script>
    <style>
        table {
            border-collapse: collapse;
            border: 1px solid #000;
        }

        td, th {
            border: 1px solid #000;
            font-size: 10px;
        }
    </style>
</head>
<body>
<div style="width: 640px">
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th style="width: 55px;">Fed ID</th>
            <th>Company Name</th>
            <th>Address</th>
            <th>City</th>
            <th>State</th>
            <th>ZIP</th>
            <th>Shortcut</th>
            <th>Checkprint</th>
            <th>1099</th>
            <th>Default GL</th>
        </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($vendorsList as $vendor) {
                echo '<tr>
                          <td>' . $i . '</td>
                          <td>' . $vendor->client->company->Company_Fed_ID . '</td>
                          <td>' . $vendor->client->company->Company_Name . '</td>
                          <td>' . $vendor->client->company->adreses[0]->Address1 . '</td>
                          <td>' . $vendor->client->company->adreses[0]->City . '</td>
                          <td>' . $vendor->client->company->adreses[0]->State . '</td>
                          <td>' . $vendor->client->company->adreses[0]->ZIP . '</td>
                          <td>' . $vendor->Vendor_ID_Shortcut . '</td>
                          <td>' . $vendor->Vendor_Name_Checkprint . '</td>
                          <td>' . ($vendor->Vendor_1099 ? 'yes' : 'no') . '</td>
                          <td>' . $vendor->Vendor_Default_GL . '</td>
                      </tr>';
                $i++;
            }
            ?>
        </tbody>
    </table>
    <script type="text/javascript">
        $(document).ready(function() {
            setTimeout(function() {
                window.print();
                window.close();
            }, 200)
        });
    </script>
</div>
</body>
</html>