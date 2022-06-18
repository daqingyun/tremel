<?php 
/*****************************************************************************************
 * 
 * Utilities for the "computation"
 * Daqing Yun @ CS <dyun@memphis.edu>
 * 
 * Created on: Apr 25, 2013
 * Last Updated: Aug 03, 2017
 * 
 * Highlight search term, tf, and genes
 * Add counts of entities into the title bars in the expandable panel
 * 
 * Copyright (c) 2013-2014 All Rights Reserved
 * Bioinformatics Program, The University of Memphis
 * 
 *****************************************************************************************/

include_once("constants.php");

//
// Functionality:
//		Construct "go to page" string in format: "First Prev Curr Next Last"
// Parameters:
//		$q: tag to indicate if the page string is in a new page or in a reloaded page
//		$total_page: total number of pages
//		$curr_page: current page number
// Return value:
//		The page displaying string
function get_go2_pgstr($q, $total_page, $curr_page)
{
	$pagestr = '';
	
	// it is not a refreshed page based on selected point in the chart
	if($q <= 0)
	{
		if($total_page == 1){
			$pagestr =
			'<<&nbsp;&nbsp;Prev&nbsp;&nbsp;[1]&nbsp;&nbsp;Next&nbsp;&nbsp;>>';
		}
		else if($curr_page == 1){
			$pagestr =
			'<<&nbsp;&nbsp;Prev&nbsp;&nbsp
			['.$curr_page.']&nbsp;&nbsp;
			<a href=?page='.($curr_page+1).'>Next</a>&nbsp;&nbsp;
			<a href=?page='.($total_page).'> >> </a>';
		}
		else if($curr_page == $total_page){
			$pagestr =
			'<a href=?page=1> << </a>&nbsp;&nbsp;
			<a href=?page='.($curr_page-1).'>Prev</a>&nbsp;&nbsp;
			['.$curr_page.']&nbsp;&nbsp;Next&nbsp;&nbsp;>>';
		}
		else{
			$pagestr =
			'<a href=?page=1> << </a>&nbsp;&nbsp;
			<a href=?page='.($curr_page-1).'>Prev</a>&nbsp;&nbsp;
			['.$curr_page.']&nbsp;&nbsp;
			<a href=?page='.($curr_page+1).'> Next </a>&nbsp;&nbsp;
			<a href=?page='.($total_page).'> >> </a>';
		}
	}
	// it is a refreshed page based on selected point in the chart
	else{
		if($total_page == 1){
			$pagestr =
			'<<&nbsp;&nbsp;Prev&nbsp;&nbsp;[1]&nbsp;&nbsp;Next&nbsp;&nbsp;>>';
		}
		else if($curr_page == 1){
			$pagestr =
			'<<&nbsp;&nbsp;Prev&nbsp;&nbsp
			['.$curr_page.']&nbsp;&nbsp;
			<a href=?page='.($curr_page+1).'&q='. $q .'>Next</a>&nbsp;&nbsp;
			<a href=?page='.($total_page).'&q='. $q .'> >> </a>';
		}
		else if($curr_page == $total_page){
			$pagestr =
			'<a href=?page=1&q='. $q .'> << </a>&nbsp;&nbsp;
			<a href=?page='.($curr_page-1).'&q='. $q .'>Prev</a>&nbsp;&nbsp;
			['.$curr_page.']&nbsp;&nbsp;
			Next&nbsp;&nbsp;>>';
		}
		else{
			$pagestr =
			'<a href=?page=1&q='. $q .'> << </a>&nbsp;&nbsp;
			<a href=?page='.($curr_page-1).'&q='. $q .'>Prev</a>&nbsp;&nbsp;
			['.$curr_page.']&nbsp;&nbsp;
			<a href=?page='.($curr_page+1).'&q='. $q .'> Next </a>&nbsp;&nbsp;
			<a href=?page='.($total_page).'&q='. $q .'> >> </a>';
		}
	}
	return $pagestr;
}

//
// Functionality:
//		Construct content string, the content of the genes, tfs and terms
//		together with their corresponding k values, ranks, scores, and module #
//		We first poll the gene id from the table 'geneid' in database tremel
//		the id (number) will be used to compise the link to http://www.ncbi.nlm.nih.gov/gene
// Parameters:
//		$page:
//		$page_size:
//		$total_records:
//		$array:
//		$q:
// Return value:
//		The constructed content string.
function get_main_content_result_str($page, $page_size, $total_records, $array, $q, $key1, $type1, $key2, $type2, $key3, $type3)
{
	// load the gene ID numbers into $table which will be used to construct
	// the link of each gene at http://www.ncbi.nlm.nih.gov/gene/xxxxxx
	$table = array();

	// read gene_id table from databases	 
	//$db_handle = mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die(mysql_error());
	//$db_found = mysql_select_db(DB_NAME, $db_handle) or die(mysql_error());
	$link = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	if (!$link) {
		echo "Error: Unable to connect to MySQL." . PHP_EOL;
		echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
		echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
		mysqli_close($link);
		exit;
	} else {
		$SQL = "SELECT * FROM geneid";
		if ($result = mysqli_query($link, $SQL)) {
				while (($temp = mysqli_fetch_assoc($result))==TRUE) {
					$tmp_table[]=$temp;
				}
		}
		
		for($idx=0; $idx<count($tmp_table); $idx+=1)
			$table[$tmp_table[$idx]['gene_name']]=$tmp_table[$idx]['gene_id'];
		
		mysqli_close($link);
	}
	
	// sort the records
	foreach ($array as $key => $row)
	{
		$sims[$key] = $row['sim'];
		$kvalues[$key] = $row['k'];
		$modnum[$key] = $row['module'];
	}
	
	// save selected
	$array_selected = $array[$q];
	
	// sort by similarity, k value, cluster number in which similarity is the critical.
	array_multisort($sims, SORT_DESC, SORT_NUMERIC,
	$kvalues, SORT_ASC, SORT_NUMERIC,
	$modnum, SORT_ASC, SORT_NUMERIC, $array) or 
	die("Some internal errors happened with 'array_multisort' in 'util.php'");
	
	
	// insert the selected at the beginning
	array_unshift($array, $array_selected);
	
	// remove duplicated
	for($i=1; $i<count($array); $i++)
		if($array[$i] == $array_selected)
			array_splice($array, $i, 1);	
	
	// construct content string
	$contentstr = '';
	for($i = ($page-1) * $page_size; ($i < ($page-1) * $page_size + $page_size) && ($i < $total_records); $i++)
	{
		// format the output data
        $k_value = $array[$i]['k'];
		$mod_num = $array[$i]['module'];
		$gene_array = explode(',', $array[$i]['gene']);
		$gene_score_array = explode(',', $array[$i]['gene_score']);
		$go_array = explode('$', $array[$i]['GO']);
		$go_score_array = explode('$', $array[$i]['GO_score']);
		$kegg_array = explode('$', $array[$i]['KEGG']);
		$kegg_score_array = explode('$', $array[$i]['KEGG_score']);
		$term_array = str_replace(",", ", ", substr($array[$i]['term'],0,strlen($array[$i]['term'])-1));
		$tf_array = explode(',', $array[$i]['TF']);
		$tf_score_array = explode(',', $array[$i]['TF_score']);
		$genestr = "";
		$gostr = "";
		$keggstr = "";
		$tfstr = "";
		$gene_count = count($gene_array);
		$go_count = count($go_array);
		$kegg_count = count($kegg_array);
		$tf_count = count($tf_array);
		
		// construct gene content string
		for ($ii = 0; $ii < ($gene_count-1); $ii = $ii+1) {
		    $jj = $ii + 1;
			if ($key1 != "" and strcmp($type1, "gene") == 0 and strcmp(strtolower($gene_array[$ii]), strtolower($key1)) == 0) {
				$genestr = $genestr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
					<td style='width:10em;'>" . $jj . "</td>
					<td style='width:10em;'>" . $gene_score_array[$ii] . "</td>
					<td style='width:auto;'><a data-toggle='tooltip' data-placement='right' title='Click symbol to reach Entrez Gene' style='color:blue;' href='http://www.ncbi.nlm.nih.gov/gene/"
						. $table[strtolower($gene_array[$ii])] . "' target='_blank'><span style='background-color: orange;'>" . $gene_array[$ii] . "</span></a></td>
				</tr>";
			} else if ($key2 != "" and strcmp($type2, "gene") == 0 and strcmp(strtolower($gene_array[$ii]), strtolower($key2)) == 0) {
				$genestr = $genestr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
					<td style='width:10em;'>" . $jj . "</td>
					<td style='width:10em;'>" . $gene_score_array[$ii] . "</td>
					<td style='width:auto;'><a data-toggle='tooltip' data-placement='right' title='Click symbol to reach Entrez Gene' style='color:blue;' href='http://www.ncbi.nlm.nih.gov/gene/"
						. $table[strtolower($gene_array[$ii])] . "' target='_blank'><span style='background-color: orange;'>" . $gene_array[$ii] . "</span></a></td>
				</tr>";
			} else if ($key3 != "" and strcmp($type3, "gene") == 0 and strcmp(strtolower($gene_array[$ii]), strtolower($key3)) == 0) {
				$genestr = $genestr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
					<td style='width:10em;'>" . $jj . "</td>
					<td style='width:10em;'>" . $gene_score_array[$ii] . "</td>
					<td style='width:auto;'><a data-toggle='tooltip' data-placement='right' title='Click symbol to reach Entrez Gene' style='color:blue;' href='http://www.ncbi.nlm.nih.gov/gene/"
						. $table[strtolower($gene_array[$ii])] . "' target='_blank'><span style='background-color: orange;'>" . $gene_array[$ii] . "</span></a></td>
				</tr>";
			} else {
				$genestr = $genestr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
						<td style='width:10em;'>" . $jj . "</td>
						<td style='width:10em;'>" . $gene_score_array[$ii] . "</td>
						<td style='width:auto;'><a data-toggle='tooltip' data-placement='right' title='Click symbol to reach Entrez Gene' style='color:blue;' href='http://www.ncbi.nlm.nih.gov/gene/"
							. $table[strtolower($gene_array[$ii])] . "' target='_blank'>" . $gene_array[$ii] . "</a></td>
				</tr>";
			}
		}
		
		// because in our database we have an extra "," which is caused by the format issue
		// so if there is one found that means no records are found
		if($go_count >= 2) {
			for ($ii = 0; $ii < ($go_count-1); $ii = $ii+1) {
				$jj = $ii + 1;
				$gostr = $gostr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
						<td style='width:6em;'>" . $jj . "</td>
						<td style='width:6em;'>" . $go_score_array[$ii] . "</td>
						<td style='width:auto;'>" . $go_array[$ii] . "</td>
				 </tr>";
			}
		} else{
			$gostr = 
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
						<td>No enrichments found.</td>
						<td></td><td></td>
				</tr>";
		}
		
		if($kegg_count >= 2) {
			for ($ii = 0; $ii < ($kegg_count-1); $ii = $ii+1) {
				$jj = $ii + 1;
				$keggstr = $keggstr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
						<td style='width:6em;'>" . $jj . "</td>
						<td style='width:6em;'>" . $kegg_score_array[$ii] . "</td>
						<td style='width:auto;'>" . $kegg_array[$ii] . "</td>
				 </tr>";
			}
		} else {
			$keggstr =
			"<tr style='font-family: Arial, Courier New; font-size:9pt;'>
					<td>No enrichments found.</td>
					<td></td>
					<td></td>
			 </tr>";
		}
		
		// construct tf content string
		for ($ii = 0; $ii < ($tf_count-1); $ii = $ii+1) {
		    $jj = $ii + 1;
			if ($key1 != "" and strcmp($type1, "TF") == 0 and strcmp(strtolower($tf_array[$ii]), strtolower($key1)) == 0) {
				$tfstr = $tfstr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
						<td style='width:10em;'>" . $jj . "</td>
						<td style='width:10em;'>" . $tf_score_array[$ii] . "</td>
						<td style='width:auto;'><a data-toggle='tooltip' data-placement='right' title='Click symbol to reach Entrez Gene' style='color:blue;' href='http://www.ncbi.nlm.nih.gov/gene/"
							. $table[strtolower($tf_array[$ii])] . "' target='_blank'><span style='background-color: yellow;'>" . $tf_array[$ii] . "</span></a></td>
				</tr>";
			} else if ($key2 != "" and strcmp($type2, "TF") == 0 and strcmp(strtolower($tf_array[$ii]), strtolower($key2)) == 0) {
				$tfstr = $tfstr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
						<td style='width:10em;'>" . $jj . "</td>
						<td style='width:10em;'>" . $tf_score_array[$ii] . "</td>
						<td style='width:auto;'><a data-toggle='tooltip' data-placement='right' title='Click symbol to reach Entrez Gene' style='color:blue;' href='http://www.ncbi.nlm.nih.gov/gene/"
							. $table[strtolower($tf_array[$ii])] . "' target='_blank'><span style='background-color: yellow;'>" . $tf_array[$ii] . "</span></a></td>
				</tr>";
			} else if ($key3 != "" and strcmp($type3, "TF") == 0 and strcmp(strtolower($tf_array[$ii]), strtolower($key3)) == 0) {
				$tfstr = $tfstr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
						<td style='width:10em;'>" . $jj . "</td>
						<td style='width:10em;'>" . $tf_score_array[$ii] . "</td>
						<td style='width:auto;'><a data-toggle='tooltip' data-placement='right' title='Click symbol to reach Entrez Gene' style='color:blue;' href='http://www.ncbi.nlm.nih.gov/gene/"
							. $table[strtolower($tf_array[$ii])] . "' target='_blank'><span style='background-color: yellow;'>" . $tf_array[$ii] . "</span></a></td>
				</tr>";
			} else {
				$tfstr = $tfstr .
				"<tr style='font-family: Arial, Courier New; font-size:10pt;'>
						<td style='width:10em;'>" . $jj . "</td>
						<td style='width:10em;'>" . $tf_score_array[$ii] . "</td>
						<td style='width:auto;'><a data-toggle='tooltip' data-placement='right' title='Click symbol to reach Entrez Gene' style='color:blue;' href='http://www.ncbi.nlm.nih.gov/gene/"
							. $table[strtolower($tf_array[$ii])] . "' target='_blank'>" . $tf_array[$ii] . "</a></td>
				</tr>";
			}
		}
		
		// construct term content string
		//echo $term_array;
		//echo $type1 . " "; echo $type2. " "; echo $type3. " "; exit;
		$term_array_with_highlight = "";
		$term_array = explode(', ', $term_array);
		//echo count($term_array);
		//echo $term_array[1];
		for ($ii = 0; $ii < count($term_array)-1; $ii = $ii+1) {
			if ($key1 != "" and strcmp($type1, "term") == 0 and strcmp(strtolower($term_array[$ii]), strtolower($key1)) == 0) {
				$term_array_with_highlight = $term_array_with_highlight . "<span style='background-color: red;'>" . $term_array[$ii] . "</span>, ";
			} else if ($key2 != "" and strcmp($type2, "term") == 0 and strcmp(strtolower($term_array[$ii]), strtolower($key2)) == 0) {
				$term_array_with_highlight = $term_array_with_highlight . "<span style='background-color: red;'>" . $term_array[$ii] . "</span>, ";
			} else if ($key3 != "" and strcmp($type3, "term") == 0 and strcmp(strtolower($term_array[$ii]), strtolower($key3)) == 0) {
				$term_array_with_highlight = $term_array_with_highlight . "<span style='background-color: red;'>" . $term_array[$ii] . "</span>, ";
			} else {
				$term_array_with_highlight = $term_array_with_highlight . $term_array[$ii] . ", ";
			}
		}
		// handle the last one with appending the comma
		if ($key1 != "" and strcmp($type1, "term") == 0 and strcmp(strtolower($term_array[$ii]), strtolower($key1)) == 0) {
			$term_array_with_highlight = $term_array_with_highlight . "<span style='background-color: red;'>" . $term_array[count($term_array)-1];
		} else if ($key2 != "" and strcmp($type2, "term") == 0 and strcmp(strtolower($term_array[$ii]), strtolower($key2)) == 0) {
			$term_array_with_highlight = $term_array_with_highlight . "<span style='background-color: red;'>" . $term_array[count($term_array)-1];
		} else if ($key3 != "" and strcmp($type3, "term") == 0 and strcmp(strtolower($term_array[$ii]), strtolower($key3)) == 0) {
			$term_array_with_highlight = $term_array_with_highlight . "<span style='background-color: red;'>" . $term_array[count($term_array)-1];
		} else {
			$term_array_with_highlight = $term_array_with_highlight . $term_array[count($term_array)-1];
		}
		
		$contentstr = $contentstr .
		"
		<h3 data-toggle='tooltip' data-placement='right' title='Click title bar to open/close descriptions' style='font-family: Arial, Times; font-size:10pt; width: auto; text-align:left;'>
				<i>k</i> : <b>" . $k_value . "</b>&nbsp;&nbsp;&nbsp;
				Module# : <b>" . $mod_num . "</b>&nbsp;&nbsp;&nbsp;
				Search Entity Rank in Module :&nbsp<b>" . $array[$i]['rank'] . "</b>
		</h3>" .		
		"<div>			
			<table class='main_table'>
					<thead>
			              <tr style='font-family: Arial, Times; font-size:10pt; background-color:rgb(197,217,241); text-align:center'>
								<td colspan='3'><b>Genes (" . ($gene_count-1) . ")</b></td>
						  </tr>
					      <tr style='font-family: Arial, Times; font-size:10pt;'>
								<td style='width:10em;'><u>Rank</u></td>
								<td style='width:10em;'><u>Score</u></td>
								<td style='width:auto;'><u>Symbol</u></td>
						  </tr>
					</thead>
			</table>			
			<div class='content'>
				<table class='main_table'>
					" . $genestr . "
				</table>
			</div>			
			<table style='width: 100%;'>					
					<tr style='font-family: Arial, Times; font-size:10pt; background-color:rgb(197,217,241); text-align:center'>
							<td colspan='3'><b>Transcription Factors (" . ($tf_count-1) . ")</b></td>
					</tr>
					<tr style='font-family: Arial, Times; font-size:10pt;'>
							<td style='width:10em;'><u>Rank</u></td>
							<td style='width:10em;'><u>Score</u></td>
							<td style='width:auto;'><u>Symbol</u></td>
					</tr>
					" . $tfstr . "
			</table>			
			<table style='width: 100%; table-layout: fixed;'>					
					<tr style='font-family: Arial, Times; font-size:10pt; background-color:rgb(197,217,241); text-align:center;'>
							<td rowspan='1' colspan='3'><b>Terms (" . (count($term_array)) . ")</b></td>
					</tr>
					<tr style='font-family: Arial, Times; font-size:10pt; width: 100%; text-wrap: normal; white-space:normal;'>
							<td rowspan='1'; colspan='3'>" . $term_array_with_highlight . "</td>
					</tr>
			</table>			
			<table class='main_table'>
					<thead>
			              <tr style='font-family: Arial, Times; font-size:10pt; background-color:rgb(255,204,153); text-align:center'>
									<td colspan='3'><b>Enriched GO Categories (" . ($go_count-1) .")</b></td>
						  </tr>";
						  if($go_count >= 2)
						  	$contentstr = $contentstr . 
						  	"<tr style='font-family: Arial, Times; font-size:10pt;'>
						  			<td style='width:6em;'><u>Rank</u></td>
						  			<td style='width:6em;'><u>p-value</u></td>
						  			<td style='width:auto'><u>Category</u></td>
						  	</tr>";
						  
						  $contentstr = $contentstr . "
					</thead>
			</table>			
			<div class='content'>
				<table class='main_table'>
					" . $gostr . "
				</table>
			</div>
			
			<table class='main_table'>
					<thead>
			              <tr style='font-family: Arial, Times; font-size:10pt; background-color:rgb(255,204,153); text-align:center'>
							<td colspan='3'><b>Enriched KEGG Pathways (". ($kegg_count-1) .")</b></td>
						  </tr>";
					      if($kegg_count >= 2)
							$contentstr = $contentstr . 
					      	"<tr style='font-family: Arial, Times; font-size:10pt;'>
					      			<td style='width:6em;'><u>Rank</u></td>
					      			<td style='width:6em;'><u>p-value</u></td>
					      			<td style='width:auto;'><u>Pathway</u></td>
					      	 </tr>";
							 
						  $contentstr = $contentstr ."
					</thead>
			</table>			
			<div class='content'>
				<table class='main_table'>
					" . $keggstr . "
				</table>
			</div>
		</div>";
	}
	return $contentstr;
}

/*
 * Calculate rank of all the clusters
 */
/*
function Calc_rank($array, $search_type, $keyword)
{
	for($i=0; $i<count($array); $i++){
		$items = array_map('strtolower', explode(',', $array[$i][$search_type]));
		for($j = 0; $j < count($items);$j++){
			if(strcasecmp($keyword, $items[$j]) == 0){
				//$rank = $j+1;
				$array[$i]['rank'] = $j+1;
			}
		}
	}
}
*/


//
// Determine the selected base index in array, we select
// the one with the smallest k value as the base
function select_baseid($array)
{
	$base = 0;
	for($i = 0; $i < count($array); $i++)
	{
		if($array[$i]['rank'] == 1)
			if($array[$i]['k'] < $array[$base]['k'])
				$base = $i;
	}
	return $base;
}
?>