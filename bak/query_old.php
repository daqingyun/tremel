<?php
/*************************************************************************
 * query.php - query results showing page
 * 
 * Functionality:
 * 		1. Get the keywords from the form,
 * 		2. Perform the search,
 * 		3. Show the results.
 * 
 * Author:
 * 		Daqing Yun <dyun@memphis.edu> @ CS
 *      Sujoy Roy <sujoyroy@memphis.edu> @ Bioinformatics
 * 		Ramin Homayouni <rhomayon@memphis.edu> @ Bioinformatics
 * 		Created: Apr 25, 2013
 * 		Last updated: Aug 18, 2014
 * 
 * Change log:
 * 		Dec 28, 2013:
 * 			1. Re-import the cluster data for both stemmed/unstemmed.
 * 			2. Add another search option stemmed/unstemmed.
 * 			3. Update the UI, looks great now?
 * 
 * 		Jan 01, 2014:
 * 			1. Fix the bug that result records show not smoothly and
 * 			   fill in the content holder, currently the size of each
 * 			   recored adjusts the size of content in it.
 * 
 *      Apr 01, 2014 (for the paper submission):
 *          1. Change the word ‘cluster’ to ‘module’ everywhere
 *          2. Start numbering from 1 instead of 0
 *          3. More details, refer to TREMEL issues under the ./doc/
 *
 *      May 09, 2014 (after finals, for the paper submission):
 *          1. Please refer to TREMEL issues 2 ./doc/ directory
 *
 *		Aug 18, 2014 (before submission):
 *			1. Use Scatter 3D chart to present the results,
 *			2. 
 *
 *      Detailed change history can be found at ./doc directory.
 *
 * Copyright (c) 2013-2014 All Rights Reserved
 * Bioinformatics Program, The University of Memphis
 * 
 *************************************************************************/

include_once("sql.php");
include_once("stem.php");
include_once("reorder.php");
include_once("constants.php");
include_once("util.php");

/*************************************************************************
 * Local variables definitions for references
 * 
 * $key1, $type1;
 * $key2, $type2;
 * $key3, $type3;
 * $queryNum;
 * $rawResArray: raw format data of the results polled from database;
 * $cpyResArray: duplicate of rawResArray;
 * $numOfRecords: number of records in the result array;
 * $numOfPages: ;
 * $maxRank;
 * $baseID;
 * $pageID;
 * $pageSize;
 * $selectID: the selected point in the chart;
 * $kValue: ;
 * $rank: ;
 * $moduleNum: ;
 * $coreData: actual numerical data of the scatter 3D chart;
 * $chartData: chart data which includes coreData;
 *************************************************************************/

//
// If there is new info input, replace that of corresponding in cookies,
// and then perform the search based on the updated information.
//
$queryNum = 1; // by default

if($_POST['Keyword1'])
{
	// Get the info
	$key1 = $_POST['Keyword1'];
	$type1 = $_POST['SearchType1'];

	// Set cookies
	setcookie("cookie[Keyword1]", $key1);
	setcookie("cookie[SearchType1]", $type1);
	
	// Clear others
	$key2 = "";
	$type2 = "";
	$key3 = "";
	$type3 = "";
}


if ($_POST['Keyword2'])
{
	$queryNum = 2;
	
    // Get the info
	$key2 = PorterStemmer::Stem($_POST['Keyword2']);
	$type2 = $_POST['SearchType2'];
	
	// Set cookies
	setcookie("cookie[Keyword2]", $key2);
	setcookie("cookie[SearchType2]", $type2);
	
	// Clear others
	$key3 = "";
	$type3 = "";
}

if ($_POST['Keyword3'])
{
	$queryNum = 3;
	
    // Get the info
	$key3 = PorterStemmer::Stem($_POST['Keyword3']);
	$type3 = $_POST['SearchType3'];
	
	// Set cookies
	setcookie("cookie[Keyword3]", $key3);
	setcookie("cookie[SearchType3]", $type3);
}

//
// If there is no new info (keywords) input, that means the page is
// re-loaded that is caused by user clicking refresh button i.e. point in
// figure or web browser
//
if(!$_POST['Keyword1'] && !$_POST['Keyword2'] && !$_POST['Keyword3'])
{
	// Get cookies
	if(isset($_COOKIE['cookie'])){		
		$key1 = $_COOKIE['cookie']['Keyword1'];
		$type1 = $_COOKIE['cookie']['SearchType1'];
		
		$key2 = $_COOKIE['cookie']['Keyword2'];
		$type2 = $_COOKIE['cookie']['SearchType2'];
		
		$key3 = $_COOKIE['cookie']['Keyword3'];
		$type3 = $_COOKIE['cookie']['SearchType3'];
	}
	else{
		// Do nothing, the results will be empty.
		// JavaScript part will handle the notifications.
		;
	}
}

//
// Fetch raw results
// Function SearchInDB() is defined in sql.php
//
$rawResArray = SearchInDB($key1, $type1, $key2, $type2, $key3, $type3);
$numOfRecords  = count($rawResArray);

//
// Calculate rank, save the maximal rank, as "maxRank"
//
$maxRank = -1;
for($i=0; $i<$numOfRecords; $i++)
{
	$items = array_map('strtolower', explode(',', $rawResArray[$i][$type1]));
	
	for($j=0; $j<count($items); $j++){
		if(0 == strcasecmp($key1, $items[$j])){
			if($j+1 > $maxRank)
				$maxRank = $j+1;
			$rawResArray[$i]['rank'] = $j+1;
		}
	}
}

//
// Calculate the base ID that is with smallest k Value
//
$baseID = SelectBaseID($rawResArray);

//
// Get page ID
//
if(isset($_GET['page']))
	$pageID = intval($_GET['page']);
else
	$pageID = 1;	
$pageSize = PAGE_SIZE;


//
// Get ID of the selected point in the chart
//
$selectID = -1;
if(isset($_GET['q']))
{
	$selectID = intval($_GET['q']);
}
else
{
	$selectID = -1;
}

//
// Compute the similarity based on the selected point's ID ($selectID)
//
if($selectID > 0)
{
	$rawResArray = CalcSim($rawResArray, $selectID);
	$mark = $selectID;
}
else
{
	$rawResArray = CalcSim($rawResArray, $baseID);
	$mark = $baseID;
}


//
// Data format: 'data' : [[k, rank, mod#, sim], [k, rank, mod#, sim], ...]
$coreData = "'data' : [";
for($i = 0; $i < $numOfRecords; $i ++)
{
	$kValue    = intval($rawResArray[$i]['k']);
	$rank      = intval($rawResArray[$i]['rank']);	
	$moduleNum = intval($rawResArray[$i]['module']);
	$sim       = $rawResArray[$i]['sim'];	
	$coreData  = $coreData . "[" . $kValue . "," . $rank. "," .$moduleNum. "," .$sim. "]";
	if($i < $numOfRecords-1) $coreData = $coreData . ",";
}
$coreData = $coreData . "]";

//
// Calculate number of pages
// according to the total number of searched result records
//
if($numOfRecords)
{
	if($numOfRecords < $pageSize)
		$numOfPages = 1;
	elseif($numOfRecords % $pageSize)
		$numOfPages = (int)($numOfRecords/$pageSize)+1;
	else
		$numOfPages = $numOfRecords/$pageSize;
}
else
	$numOfPages = 0;

/**
 * Construct page number display string
 */
$currPage = $pageID;
$pageShowStr = '';
$pageShowStr = GetPageShowStr($selectID, $numOfPages, $currPage);
?>

<html>
  <head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <link href="style.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="./img/paw.gif" rel="ICON"/>
    <title>TREMEL</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script>$(function(){$( "#hitt" ).accordion({heightStyle: "content"});});</script>
	<script type="text/javascript" src="./js/canvasXpress.min.js"></script>
	
	<script type="text/javascript">
		var showChart = function (){
			var chart = new CanvasXpress('chartPosition', {
				'y':{
					'vars' : [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
					'smps' : ['\'k\'', 'rank', 'mod#', 'sim'],
					<?php echo $coreData; ?>
					},
				},
				{'colorBy': 'sim',
				 'graphType': 'Scatter3D',
				 'xAxis': ['Sample1'],
				 'yAxis': ['Sample2'],
				 'zAxis': ['Sample3']}
			);
		}
	</script>
    
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(initialize);
      function initialize() {
		if(arguments.length == 1){
			var pp = <?php echo $pageID; ?>;
			data = new google.visualization.arrayToDataTable(<?php echo $chart_data; ?>);
			var x = data.getNumberOfRows();
			if(x==0){
				document.getElementById('msg').innerHTML='No records found.';
				return ;
			}
			else{
				document.getElementById('msg').innerHTML='';
			}
			var options = {
				colorAxis: {colors: ['black', 'red']},
				tooltip: {textStyle: {color: 'black', fontName: 'Arial', fontSize: 13, fontStyle: 'italic'}},
				//tooltip: {trigger: 'none'},
				//hAxis:{title: 'Factorization Approximation Rank <b>k</b>', titleTextStyle: {italic: 'false'}, logScale: 'true', ticks: [1,2,4,8,16,35,80,200,500,1000]},
				hAxis:{title: 'Factorization Approximation Rank k', titleTextStyle: {italic: 'false'}, logScale: 'false', ticks: [{v:1, f:"1"},{v:2, f:"2"},{v:3, f:"3"},{v:4, f:"5"},{v:5, f:"10"},{v:6, f:"15"},{v:7, f:"20"},{v:8, f:"25"},{v:9, f:"30"},{v:10, f:"50"},{v:11, f:"100"},{v:12, f:"200"},{v:13, f:"300"},{v:14, f:"500"},{v:15, f:"700"},{v:16, f:"900"}], maxValue: 16.1},
				vAxis:{title: 'Search Entity Rank in Modules', titleTextStyle: {italic: 'false'}, direction: -1, minValue: 0, maxValue: <?php echo $maxRank+1; ?>},
				isStacked: true,
				sizeAxis:  {maxSize: 3, minSize: 3}
			};
			chart = new google.visualization.BubbleChart(document.getElementById('chart_div'));
			chart.draw(data, options);
			chart.setSelection([{row:<?php echo $selectID; ?>, column:null}]);
			google.visualization.events.addListener(chart, 'select', refresh);
			google.visualization.events.addListener(chart, 'onmouseover', bubbleMouseOver);
		}
		if(arguments.length == 2){
			//alert(arguments[1].childNodes.item(1).innerText);
			var k = arguments[1].childNodes.item(2).innerText;
			var rank = arguments[1].childNodes.item(8).innerText;
			//alert(k);
			var size = data.getNumberOfRows();
			for(var i = 0; i < size; i++){
				var x = data.getValue(i, 1);
				var y = data.getValue(i, 2);
				if(k == x && rank == y){
					//alert(i);
					chart.setSelection([{row:i, column:null}]);
				}
			}
		}
        function refresh(){	
			var row = chart.getSelection()[0].row;
			//alert(row);
			var link = "./query.php?page="+pp+"&q="+row;
			//alert(link);
			window.location.assign(link);
			//alert("You will see the updated data");
        }
      }      
    </script>
    <script>$(document).ready(function(){
		// by default, entity 2 and entity 3 are hidden
		$(".panel2").hide();
		$(".panel3").hide();
		// at the initial time only one search box
		var n=1;
        $(".flip").click(function(){
			// if only entity 1 is shown
			if(n == 1){
				// show entity 2
				$(".panel2").show();
				// now there are entity 1 and entity 2
				n = 2;
			}
			// if entity 1 and entity 2 are shown
			else if(n == 2){
				// shown entity 3
				$(".panel3").show();
				// now there are 3
				n = 3;
				// change the icon to be the hide icon
				var imgNameIndex = add.src.lastIndexOf("/") + 1;
				var imgName = add.src.substr(imgNameIndex);
				add.src="./img/hide.gif";
			}
			// if there are three, only can hide
			else if (n == 3){
				// hide entity 2 and entity 3
				$(".panel2").hide();
				$(".panel3").hide();
				// now there is only entity 1
				n = 1;
				// the show icon should be displayed
				var imgNameIndex = add.src.lastIndexOf("/") + 1;
				var imgName = add.src.substr(imgNameIndex);
				add.src="./img/show.gif";
			}
		});
	});
    </script>
  </head>
  
  <body onload="showChart();">
    <div id="display">
        <a href=<?php $base_url=BASE_URL; echo $base_url;?>><img src="./img/tremel_logo_umlogo_blue_left.gif" width="400" height="80" align="left" border="0px"></a>
        <div>
        <form action="query.php" method="post" accept-charset="UTF-8">
		<table style="float:left; padding: 0em 0em 0em 2em;">
		<tr><td><span><img src="./img/show.gif" id="add" class="flip" style="cursor:pointer;"  width="16" height="15"/></span>
	       Entity 1: <input style="font-family: Times; font-size:12pt; width:100px;" type="text" name="Keyword1" value=""/>
				<select style="font-family: Times; font-size:12pt; width:80px;"  name="SearchType1">
		        <option value="genes">Gene</option>
		        <option value="TFs">TFs</option>
				<option value="terms">Terms</option>
		        </select>
				</td><td rowspan="3">
		        <input style="font-family: Times; font-size:12pt;"  type="submit" name="op" value="TREMEL Search"/>
		</td></tr>
		<tr><td class="panel2">
	       &nbsp;&nbsp;&nbsp;&nbsp;
	       Entity 2: <input style="font-family: Times; font-size:12pt; width:100px;" type="text" name="Keyword2" value=""/>
			    <select style="font-family: Times; font-size:12pt; width:80px;"  name="SearchType2">
		        <option value="genes">Gene</option>
		        <option value="TFs">TFs</option>
				<option value="terms">Terms</option>
		        </select>
		 </td></tr>
		 <tr><td class="panel3">
	       &nbsp;&nbsp;&nbsp;&nbsp;
	       Entity 3: <input style="font-family: Times; font-size:12pt; width:100px;" type="text" name="Keyword3" value=""/>
			    <select style="font-family: Times; font-size:12pt; width:80px;"  name="SearchType3">
		        <option value="genes">Gene</option>
		        <option value="TFs">TFs</option>
				<option value="terms">Terms</option>
		        </select>
		 </td></tr>
		</table>
	    </form>
        </div>
    </div>
    <div id="navbar">Notes</div>
    <div>
    <ol>
        <li>TREMEL: <b>T</b>ranscription <b>RE</b>gulatory <b>M</b>odules <b>E</b>xtracted from <b>L</b>iterature.</li>
        <li>Click [+/-] to show/hide other search keywords.</li>
        <li>Contact:
			<ul>
				<li>Prof. Ramin Homayouni (rhomayon@memphis.edu)</li>
				<li>Dr. Sujoy Roy (sujoyroy@memphis.edu) and Daqing Yun (dyun@memphis.edu)</li>
			</ul>
		</li>
    </ol>
    </div>
    <div id="navbar">
            Total <?php echo "<b>".$numOfRecords."</b>"; ?> results.
            Search Query:  <?php echo "<b>" . $key1 ."</b> {". $type1 . "}"; ?>
			<?php
				if($queryNum == 2){
					echo " AND <b>" . $key2 . "</b> {" . $type2 ."}";
				}
				elseif($queryNum == 3){
					echo " AND <b>" . $key2 . "</b> {" . $type2 ."} AND <b>" . $key3 . "</b> {" . $type3 . "}";
				}
			?>
    </div>
	<div>
		
	</div>
    <div id="main">
		<div id="msg"></div>
		<!--<div id="chart_div" align="center" style="width: 1000px; height: 650px;"></div>-->
		<div id="hitt">
		<?php
		$cpyResArray = $rawResArray;
		$content_str = ConstructContentStr($pageID, $pageSize, $numOfRecords, $cpyResArray, $mark);
		echo $content_str;
		?>
		</div>
		<div style="font-family: Courier New;"> <?php echo $pageShowStr; ?> </div>
    </div>
  </body>
</html>
