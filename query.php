<?php
/*************************************************************************************
 * query.php - query results showing page
 * 
 * Functionalities:
 *      1. Get the keywords from the form,
 *      2. Perform the search,
 *      3. Render the results in a 3D chart.
 *      4. Hinting usage & help pop-up.
 * 
 * Author:
 *      <daqing.yun@gmail.com>
 *
 * Created: Apr 25, 2013
 * Last updated: Aug 03, 2017
 * 
 * Change log:
 *      Dec 28, 2013:
 *          1. Re-import the cluster data for both stemmed/unstemmed.
 *          2. Add another search option stemmed/unstemmed.
 *          3. Update the UI, looks great now?
 * 
 *      Jan 01, 2014:
 *          1. Fix the bug that result records show not smoothly and
 *             fill in the content holder, currently the size of each
 *             recored adjusts the size of content in it.
 * 
 *      Apr 01, 2014 (for the paper submission):
 *          1. Change the word 'cluster' to 'module' everywhere
 *          2. Start numbering from 1 instead of 0
 *          3. More details, refer to TREMEL issues under the ./doc/
 *
 *      May 09, 2014 (after finals, for the paper submission):
 *          1. Please refer to TREMEL issues 2 ./doc/ directory
 *
 *      Aug 18, 2014 (before submission):
 *          1. Use Scatter 3D chart to present the results.
 *      
 *      Aug 20, 2014:
 *          After a little bit research, I decided to use a JavaScript
 *          library "canvasXpress", @ http://canvasxpress.org/.
 *          An email address is required to download.
 *          This lib provide a Scatter 3D Chart to us;
 *          Unfortunately, the chart does not include the function for
 *          TREMEL to get the information of the selected data.
 *          I solved this by hacking into the source code of the library and
 *          adding the "getSelectedDataID()" function; so to deploy TREMEL,
 *          the file ./js/canvasXpress.min.js should ***NOT*** be overwritten.
 *          Please check out the latest code from the svn repository at:
 *          http://dragon.cs.memphis.edu/svn/tremel/
 *          
 *      Aug 22, 2014:
 *          By using a session, the performance (responding time) is
 *          significantly improved.
 *
 *      Aug 03, 2017
 *          Please refer to ./doc/Help_file.pptx, ./doc/TREMEL_Help_rh.docx,
 *          and ./doc/TREMEL_Help_revised.docx.
 *          These changes are made mainly for the FBIOE paper of TREMEL.
 *
 *      Detailed change history can be found at ./doc directory.
 *
 * Copyright (c) 2013-2017 All Rights Reserved
 * Bioinformatics Program, The University of Memphis
 * 
 *************************************************************************************/

include_once("sql.php");
include_once("stem.php");
include_once("reorder.php");
include_once("constants.php");
include_once("util.php");

// Starts a session
session_start();

/*************************************************************************
 * Local variables definitions for references
 * 
 * $key1, $type1: keyword and its corresponding type;
 * $key2, $type2: same as above;
 * $key3, $type3: same as above;
 * $query_num: the search is based on how many keywords/types;
 * $raw_result_array: raw format data of the results polled from database;
 * $cpy_result_array: duplicate of rawResArray;
 * $num_records: number of records in the result array;
 * $num_pages: depends on the number of result records;
 * $max_rank: maximal rank in the searched results;
 * $baseid: based on who to calculate similarity;
 * $pageid;
 * $page_size;
 * $curr_page;
 * $selectid: the selected point in the chart;
 * $k_value: ;
 * $rank: ;
 * $mark: ;
 * $mod_num: ;
 * $expandable_content_list: ;
 * $core_data: actual numerical data of the scatter 3D chart;
 * $vars_data: ;
 * $chart_data: chart data which includes coreData;
 *************************************************************************/

//
// If there is new info input, replace that of corresponding in cookies,
// and then perform the search based on the updated information.
//
$query_num = 1; // by default

if($_POST['keyword1'])
{
	// Get the info
	$key1 = $_POST['keyword1'];
	$type1 = $_POST['searchType1'];

	// Set cookies
	setcookie("cookie[keyword1]", $key1);
	setcookie("cookie[searchType1]", $type1);
	setcookie("cookie[queryNum]", $query_num);
	
	// Clear others
	$key2 = "";
	$type2 = "";
	$key3 = "";
	$type3 = "";
}


if ($_POST['keyword2'])
{
	$query_num = 2;
	
    // Get the info
	$key2 = PorterStemmer::Stem($_POST['keyword2']);
	$type2 = $_POST['searchType2'];
	
	// Set cookies
	setcookie("cookie[keyword2]", $key2);
	setcookie("cookie[searchType2]", $type2);
	setcookie("cookie[queryNum]", $query_num);
	
	// Clear others
	$key3 = "";
	$type3 = "";
}

if ($_POST['keyword3'])
{
	$query_num = 3;
	
    // Get the info
	$key3 = PorterStemmer::Stem($_POST['keyword3']);
	$type3 = $_POST['searchType3'];
	setcookie("cookie[queryNum]", $query_num);
	
	// Set cookies
	setcookie("cookie[keyword3]", $key3);
	setcookie("cookie[searchType3]", $type3);
}

//
// If there is no new info (keywords) input, that means the page is
// re-loaded that is caused by user clicking refresh button i.e. point in
// figure or web browser
// !!! In this case, do we really need to get results from database again? use session
//
if(!$_POST['keyword1'] && !$_POST['keyword2'] && !$_POST['keyword3'])
{
	// Get cookies
	if(isset($_COOKIE['cookie'])){		
		$key1 = $_COOKIE['cookie']['keyword1'];
		$type1 = $_COOKIE['cookie']['searchType1'];
		
		$key2 = $_COOKIE['cookie']['keyword2'];
		$type2 = $_COOKIE['cookie']['searchType2'];
		
		$key3 = $_COOKIE['cookie']['keyword3'];
		$type3 = $_COOKIE['cookie']['searchType3'];
		
		$query_num = $_COOKIE['cookie']['queryNum'];
	}
	else{
		// Do nothing, the results will be empty.
		// JavaScript part will handle the notifications.
		;
	}
}

//
// Get ID of the selected point in the chart
//
$selectid = -1;
if(isset($_GET['q']))
{
	$selectid = intval($_GET['q']);
}
else
{
	$selectid = -1;
}

//
// Start a session for better performance (response time)
if($selectid < 0)
{
	// if it is a new search based on newly specified keyword
	// Fetch raw results
	// Function search_in_db() is defined in sql.php
	$raw_result_array = search_in_db($key1, $type1, $key2, $type2, $key3, $type3);
	$num_records  = count($raw_result_array);
	$_SESSION['raw'] = $raw_result_array;
}
else if(isset($_SESSION['raw']))
{
	// if it is a reloaded page caused by cliking the point in the chart
	// we get data from session without accessing the database
	$raw_result_array = $_SESSION['raw'];
	$num_records = count($raw_result_array);
}
else
{
	//
	// Fetch raw results
	// Function search_in_db() is defined in sql.php
	//
	$raw_result_array = search_in_db($key1, $type1, $key2, $type2, $key3, $type3);
	$num_records  = count($raw_result_array);
}


//
// Calculate rank, save the maximal rank, as "maxRank"
//
$max_rank = -1;
for($i = 0; $i < $num_records; $i ++)
{
	$items = array_map('strtolower', explode(',', $raw_result_array[$i][$type1]));
	
	for($j = 0; $j < count($items); $j++)
	{
		if(0 == strcasecmp($key1, $items[$j]))
		{
			if($j+1 > $max_rank)
				$max_rank = $j+1;
			
			$raw_result_array[$i]['rank'] = $j + 1;
		}
	}
}

//
// Calculate the base ID that is with smallest k Value
//
$baseid = select_baseid($raw_result_array);

//
// Get page ID
//
if(isset($_GET['page']))
	$pageid = intval($_GET['page']);
else
	$pageid = 1;

$page_size = PAGE_SIZE;

//
// Compute the similarity based on the selected point's ID ($selectID)
//
if($selectid >= 0)
{
	$raw_result_array = calc_sim($raw_result_array, $selectid);
	$mark = $selectid;
}
else
{
	$raw_result_array = calc_sim($raw_result_array, $baseid);
	$mark = $baseid;
}


//
// Data format: 'data' : [[k, rank, mod#, sim], [k, rank, mod#, sim], ...]
$vars_data = "'vars' : [";
$core_data = "'data' : [";
for($i = 0; $i < $num_records; $i ++)
{
	$k_value    = intval($raw_result_array[$i]['k']);
	$rank       = intval($raw_result_array[$i]['rank']);	
	$mod_num    = intval($raw_result_array[$i]['module']);
	$sim        = $raw_result_array[$i]['sim'];	
	$core_data  = $core_data . "[" . $k_value . "," . $rank. "," .$mod_num. "," .$sim. "]";
	$vars_data  = $vars_data . "'" . $i . "'"; 
	if($i < $num_records-1)
	{
		$core_data = $core_data . ",";
		$vars_data = $vars_data . ",";
	}
}
$core_data = $core_data . "]";
$vars_data = $vars_data . "]";

//
// Calculate number of pages
// according to the total number of searched result records
//
if($num_records)
{
	if($num_records < $page_size)
		$num_pages = 1;
	else if($num_records % $page_size)
		$num_pages = (int)($num_records/$page_size)+1;
	else
		$num_pages = $num_records/$page_size;
}
else
	$num_pages = 0;

/**
 * Construct page number display string
 */
$curr_pageid = $pageid;
$go2_pgstr = '';
$go2_pgstr = get_go2_pgstr($selectid, $num_pages, $curr_pageid);
?>

<html>
  <head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link href="./style.css" rel="stylesheet" type="text/css" media="screen"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="./lib/jquery-ui.css">
	<!--<script src="./lib/jquery-1.9.1.js"></script>-->
	<script src="./lib/jquery-ui.js"></script>
	<script src="./lib/canvasXpress.hacked.min.js"></script>
	<!--<link href="./img/paw.gif" rel="ICON"/>-->
	<title>TREMEL: Transcription REgulatory Modules Extracted from Literature</title>
	<script>$(function(){$( "#expandList" ).accordion({heightStyle: "content"});});</script>
	<script>$(document).ready(function(){$("input").blur(function(){;});});</script>		
	<script>$(document).ready(function()
	{
		// by default, entity 2 and entity 3 are hidden
		$(".panel2").hide(); $(".panel3").hide();
		
		// at the initial time only one search box
		// n is the number of the search keywords
		var n = 1;
        $(".flip").click(function()
		{
			// if only entity 1 is shown
			if(n == 1)
			{
				$(".panel2").fadeIn(1000);// show entity 2
				$(".submit").fadeIn(1000);
				n = 2; // now there are entity 1 and entity 2
			}
			// if entity 1 and entity 2 are shown
			else if(n == 2)
			{
				$(".panel3").fadeIn(1000); // shown entity 3
				$(".submit").fadeIn(1000);
				n = 3; // now there are 3
				
				// change the icon to be the hide icon
				var imgNameIndex = add.src.lastIndexOf("/") + 1;
				var imgName = add.src.substr(imgNameIndex);
				add.src="./img/minus.png";
			}
			// if there are three, only can hide
			else if (n == 3)
			{
				$(".panel2").fadeOut(500);
				$(".panel3").fadeOut(500);
				$(".submit").fadeIn(1000);
				n = 1; // now there is only entity 1
				// the show icon should be displayed
				var imgNameIndex = add.src.lastIndexOf("/") + 1;
				var imgName = add.src.substr(imgNameIndex);
				add.src="./img/add.png";
			}
		});
	});
    </script>
	<script type="text/javascript">
		var showScat3DCht = function (){
			var num = <?php echo $num_records ?>;
			if(num <= 0){
				document.getElementById('errmsg').innerHTML='No records found based on the provided information.';
				return;
			}
			var pp = <?php echo $pageid; ?>;
			var cht = new CanvasXpress('chtPos', {
				'y':{
					<?php echo $vars_data; ?>,
					'smps' : ['\'k\'', 'rank', 'mod#', 'Similarity'],
					<?php echo $core_data; ?>
					},},
				{'colorBy': 'Similarity',
				 'graphType': 'Scatter3D',
				 'xAxis': ['Sample1'],
				 'yAxis': ['Sample2'],
				 'zAxis': ['Sample3'],
				 'codeType' : 'pretty',
				 'selectDataMode' : 'name'},
				{
					'click' : function(o, e, t) {
						var id = cht.getSelectedDataID();
						var link = "./query.php?page="+pp+"&q="+id;
						window.location.assign(link);
					},
				}
			);
		}
	</script>
	<!-- Piwik code for localhost/tremel
	<script type="text/javascript">
		var _paq = _paq || [];
		/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
		_paq.push(['trackPageView']);
		_paq.push(['enableLinkTracking']);
		(function() {
			var u="//localhost/piwik/piwik/";
			_paq.push(['setTrackerUrl', u+'piwik.php']);
			_paq.push(['setSiteId', '1']);
			var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
			g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
		})();
	</script>
	End Piwik Code for localhost/tremel -->
	<!-- Piwik code for tremel on binf1 -->
	<script type="text/javascript">
	var _paq = _paq || [];
	_paq.push(["setDomains", ["*.binf1.memphis.edu/tremel"]]);
	_paq.push(['trackPageView']);
	_paq.push(['enableLinkTracking']);
	(function() {
		var u="//binf1.memphis.edu/piwik/";
		_paq.push(['setTrackerUrl', u+'piwik.php']);
		_paq.push(['setSiteId', '1']);
		var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
		g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
	})();
	</script>
	<noscript><p><img src="//binf1.memphis.edu/piwik/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>
	<!-- End Piwik code for tremel on binf1 -->
</head>
  
<body onload="showScat3DCht();">
	<div style="height: 1em;"></div>
	<div id="tremelhdr">
		<a href="http://www.memphis.edu/" style="float:right;">
			<img src="./img/UMLogo280.gif" width="150" height="45" border="0px"></a>
		<a href=<?php $baseURL=BASE_URL; echo $baseURL;?>>
			<img src="./img/tremel_logo_CourierNew.gif" width="200" height="45" border="0px"></a>
			<br>
			<span id="tremelhdr_font">Transcription REgulatory Modules Extracted from Literature</span>
	</div>
	
	<!--<div id="navbar">
		<a href=<?php $homeURL=BASE_URL . "index.php"; echo $homeURL;?>>Home</a>
		<a href=<?php $aboutURL=BASE_URL . "about.php"; echo $aboutURL;?>>About</a>
		<a href=<?php $docURL=BASE_URL . "doc.php"; echo $docURL;?>>Documentation</a>
		<a href=<?php $helpURL=BASE_URL . "help.php"; echo $helpURL;?>>Help/FAQ</a>
		<a href=<?php $contactURL=BASE_URL . "contact.php"; echo $contactURL;?>>Contact</a>
	</div>-->
	
	<div id="searchbar">
        <form action="query.php" method="post" accept-charset="UTF-8">
		<table border = "0" id="searchbar_table">
			<tr><td id="td_add_hide"><img src="./img/add.png" id="add" class="flip" style="cursor:pointer;"  width="25" height="25" data-toggle="tooltip" data-placement="bottom" title="Click [+/-] button to show/hide multiple search fields"/></td>
				<td id="td_search_input">
				Entity 1:
					<input class="input_text" type="text" name="keyword1" value="" data-toggle="tooltip" data-placement="bottom" title="Enter official Gene Symbol or keyword"/>
					<select name="searchType1" data-toggle="tooltip" data-placement="bottom" title="Select entity type: Gene, TF or term">
						<option value="gene">Gene</option>
						<option value="TF">TF</option>
						<option value="term">Term</option>
					</select></td>
				<td><input class="submit" type="submit" name="op" value="TREMEL Search" data-toggle="tooltip" data-placement="bottom" title="Click to submit query"/></td>
				<td id="td_help_hover"><img src="./img/question.png" width="25" height="25" data-toggle="modal" data-target="#myModal" data-toggle="tooltip" data-placement="bottom" title="display help information"/></td></tr>
					
		<tr><td class="panel2"></td><td id="td_search_input" class="panel2">
	       Entity 2: <input class="input_text" type="text" name="keyword2" value="" data-toggle="tooltip" data-placement="bottom" title="Enter official Gene Symbol or keyword"/>
			    <select name="searchType2" data-toggle="tooltip" data-placement="bottom" title="Select entity type: Gene, TF or term">
		        <option value="gene">Gene</option>
		        <option value="TF">TF</option>
				<option value="term">Term</option>
		        </select>
		 </td><td class="panel2"></td></tr>
		 <tr><td class="panel3"></td><td id="td_search_input" class="panel3">
	       Entity 3: <input class="input_text" type="text" name="keyword3" value="" data-toggle="tooltip" data-placement="bottom" title="Enter official Gene Symbol or keyword"/>
			    <select name="searchType3" data-toggle="tooltip" data-placement="bottom" title="Select entity type: Gene, TF or term">
		        <option value="gene">Gene</option>
		        <option value="TF">TF</option>
				<option value="term">Term</option>
		        </select>
		 </td><td class="panel3"></td></tr>
		</table>
	    </form>
    </div>
	
	<!-- Modal -->
	<div class="modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title">Tremel Help</h3>
				</div>
				<div class="modal-body">
					<p style="font-family: Arial; font-weight: bold;">Search Box(es)</p>
					<p style="font-family: Georgia; font-size: 10pt; text-align: left; word-break: keep-all;">
					The user can query TREMEL with either genes, transcription factors (TFs) or terms,
					or a combination of up to 3 entities of any type. The entity type can be selected
					from the drop down list to the right of each search box. Clicking the "+" button
					to the left of the first search box opens an additional search box. A maximum of
					3 search boxes are allowed. The gene and TF queries need to be the official symbols
					designated by the National Center for Biotechnology Information (NCBI). Only one
					symbol is allowed per search box. The term query can be any single keyword. All
					queries are case insensitive. The output of the tool consists of two panels.</p>
					<!--<ol style="font-family: Georgia; font-size: 10pt;">
						<li>Select entity type from pulldown menu: Gene, TF, or Term.</li>
						<li>Enter gene/TF  symbol or any keyword.</li>
						<li>Click 'Tremel Search' to submit.</li>
						<li>Click [+/-] to add additional search fields.</li>
					</ol>-->
					<p style="font-family: Arial; font-weight: bold;">Top Panel: 3-D interactive plot</p>
					<p style="font-family: Georgia; font-size: 10pt; text-align: left; word-break: keep-all;">
					The top panel is comprised of a 3-dimensional interactive plot that shows all Annotated
					Transcriptional Modules (ATMs) containing the search box entities, as points.
					The axes of the plot correspond to the Non-negative Tensor Factorization (NTF)
					approximation rank <i>k</i>, the ATM#, and the rank of the queried entity (first search
					box only) in the ATMs. An ATM can be selected in the panel by clicking on its corresponding
					point in the 3-D plot. The color of the selected point changes to red and the colors of
					the remaining points corresponding to all other ATMs are depicted in terms of similarity
					to the selected ATM. The most similar ATMs are colored in shades of red while the least
					similar ones are colored in shades of blue. The similarity between any two ATMs is
					calculated as the Jaccard coefficient between the sets of genes and TFs in the respective
					ATMs. Upon initial search completion, one ATM is preselected and colored in red.
					This initial selection is performed in a manner such that the ATM with the lowest
					queried entity rank is picked. Ties are resolved in favor of the ATM with the lowest
					NTF approximation rank. </p>
					<!--<ol style="font-family: Georgia; font-size: 10pt;">
						<li>Click on <span style="color:blue; font-weight: bold;">blue</span> point in the plot to display the terms, genes and TFs associated with that module.</li>
						<li>The selected dot is displayed in <span style="color:red; font-weight: bold;">red</span>. Other dots are colored based on the similarity to the selected module.</li>
						<li>Rotate the plot by left-click and dragging the mouse.</li>
						<li>Customize the plot using right-click menu options.</li>
					</ol>-->
					<p style="font-family: Arial; font-weight: bold;">Bottom Panel: ATM contents</p>
					<p style="font-family: Georgia; font-size: 10pt; text-align: left; word-break: keep-all;">
					The bottom panel contains several sub-panels, each corresponding to an ATM point in the first
					panel. The top sub-panel corresponds to the selected ATM, and the remaining ATMs are ordered
					according to their similarity to the selected ATM. Each sub-panel displays the ranked genes,
					TFs and terms of the corresponding ATM, as well as the enriched GO categories and KEGG pathways.
					Clicking a sub-panel expands it to display its contents, and closes the previously open sub-panel.
					The contents of only one sub-panel are viewable at a time. </p>
					<p style='font-family: Arial; font-weight: bold;'>Search:</p>
					<ol style='font-family: Georgia; font-size: 10pt;'>
					<li>Select entity type from pulldown menu: Gene, TF, or Term.</li>
					<li>Enter gene/TF symbol or any keyword.</li>
					<li>Click 'TREMEL Search' to submit.</li>
					<li>Click [+/-] to add additional search fields.</li>
					</ol>
					<p style='font-family: Arial; font-weight: bold;'>Visualization:</p>
					<ol style='font-family: Georgia; font-size: 10pt;'>
					<li>Click on any point in the plot to display the terms, genes and TFs included in that ATM along with the enriched GO categories and KEGG pathways.</li>
					<li>The selected point is displayed in <span style="color:red;">red</span>. Other points are colored in shades of <span style="color:red;">red</span> and <span style="color:blue;">blue</span> based on the similarity to the selected module.</li>
					<li>Rotate the plot by left-click and dragging the mouse.</li>
					<li>Customize the plot using right-click menu options.</li>
					</ol>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	
	<div id="div_line" style="height: 1;"></div>
	
    <div id="hint">
            Total <?php echo "<b>".$num_records."</b>"; ?> results.
            Search Query:  <?php echo "<b>" . $key1 ."</b> {". $type1 . "}"; ?>
			<?php
				if($query_num == 2){
					echo " AND <b>" . $key2 . "</b> {" . $type2 ."}";
				}
				elseif($query_num == 3){
					echo " AND <b>" . $key2 . "</b> {" . $type2 ."} AND <b>" . $key3 . "</b> {" . $type3 . "}";
				}
			?>
    </div>
	
	<div id="errmsg"></div>
	
	<?php if($num_records <= 0) echo "<div>"; else echo "<div id='threed'>"; ?>
	<p>
		<canvas id='chtPos' width='590' height='590'>
			Your browser does not support the HTML5 canvas tag.
		</canvas>
	</p>
	</div>
	
	<?php if($num_records <= 0) echo "<div>"; else echo "<div id='result'>"; ?>
    <div>
		<!--<div id="chart_div" align="center" style="width: 1000px; height: 650px;"></div>-->
		<div id="expandList">
		<?php
		$cpy_result_array = $raw_result_array;
		$expandable_content_list = get_main_content_result_str($pageid, $page_size, $num_records, $cpy_result_array, $mark, $key1, $type1, $key2, $type2, $key3, $type3);
		echo $expandable_content_list;
		?>
		</div>
		<div style="font-family: Arial, Courier New; font-size: 9pt; font-weight: bold; text-align: center;">
		<?php if($num_records <= 0) ; else echo $go2_pgstr; ?> </div>
    </div>
	</div>
	
	<div id="div_line" style="height: 1;"></div>
	<div class="footer"
		style="font-family: Arial, Times;
		font-size: 9pt;
		text-align: center;">
			All Rights Reserved &copy; 2014-2017 &middot;
			<a href="http://www.memphis.edu/binf/">Bioinformatics</a> &middot;
			<a href="http://www.memphis.edu/">University of Memphis</a> &middot;
			<a href="mailto:rhomayon@memphis.edu;sujoyroy@memphis.edu;dyun@memphis.edu;">Contact</a>
	</div>
</body>
</html>
