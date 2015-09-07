<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 6/25/15
 * Time: 2:16 PM
 */

//jsData = json_encode($documents);
$jsData = CJavaScript::jsonEncode($documents);
//$jsData = CJSON::jsonEncode($documents);





?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
          var what_he_need = [
              ['Task', 'Hours per Day'],
              ['Work',     11],
              ['Eat',      2],
              ['Commute',  2],
              ['Watch TV', 2],
              ['Sleep',    7]
          ];

          console.log ("What he need",what_he_need);

          var array_to_data = <?=$jsData;?>;

          console.log ("Row array",array_to_data);




          var array_to_data = [<?=$documents;?>];
          //var data = google.visualization.arrayToDataTable( what_he_need );
          console.log("First of my",array_to_data);


          var data = google.visualization.arrayToDataTable( array_to_data );



          var options = {
              title: 'My Daily Activities'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));

        chart.draw(data, options);
      }
    </script>


    <div id="piechart" style="width: 900px; height: 500px;"></div>

