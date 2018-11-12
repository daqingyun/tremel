<?php 

/*
 * Utilities for the "computation"
 * Daqing Yun @ Bioinformatics
 * Email: dyun@memphis.edu
 * Created on: Apr 25, 2013
 * Last Updated: June 01, 2013
 */

/* 
 * Calculate common genes number of two clusters
 * all elements in $x and $y should be in lower case
 */
function common_genes($x, $y)
{
	$NumOfCommonGenes = 0;
	for($i=0; $i<count($x); $i++){
		if(in_array($x[$i],$y)){
			$NumOfCommonGenes = $NumOfCommonGenes+1;
		}
	}
	return $NumOfCommonGenes;
}

/*
 * Calculate union genes number of two clusters
 * all elements in $x and $y should be in lower cases
 */
function union_genes($x, $y)
{
	$num_of_union_genes = count($x);
	for($i=0; $i<count($x); $i++){
		if(!in_array($x[$i],$y)){
			$num_of_union_genes = $num_of_union_genes+1;
		}
	}
	return $num_of_union_genes;
}

/*
 * Calculate the similarity of two clusters
 * The similarity is defined as follows:
 * Sim(C1, C2) = [# intersection(C1,C2) / # union(C1,C2)]
 */
function CalcSim($x, $y)
{
	return common_genes($x, $y)/union_genes($x, $y);
}

/*
 * Construct "GoToPage" string
 * "Previous Page", "Next Page", "First Page" and "Last Page"
 */
function ConstructPageStr($q, $NumOfPages, $CurrentPage)
{
	$page_str = '';
	// it is not a refreshed page based on selected point in the chart
	if($q <= 0){
		if($CurrentPage == 1){
			$page_str = '<< Previous <a href=?page='.($CurrentPage).'> ['.$CurrentPage.'] </a> <a href=?page='.($CurrentPage+1).'>Next</a> <a href=?page='.($NumOfPages).'> >> </a>';
		}
		elseif($CurrentPage == $NumOfPages){
			$page_str = '<a href=?page=1> << </a> <a href=?page='.($CurrentPage-1).'>Previous</a> <a href=?page='.($CurrentPage).'> ['.$CurrentPage.'] </a> Next >> </a>';
		}
		else{
			$page_str = '<a href=?page=1> << </a> <a href=?page='.($CurrentPage-1).'>Previous</a> <a href=?page='.($CurrentPage).'> ['.$CurrentPage.'] </a> <a href=?page='.($CurrentPage+1).'>Next</a> <a href=?page='.($NumOfPages).'> >> </a>';
		}
	}
	// it is a refreshed page based on selected point in the chart
	else{
		if($CurrentPage == 1){
			$page_str = '<< Previous <a href=?page='.($CurrentPage).'&q='. $q .'> ['.$CurrentPage.'] </a> <a href=?page='.($CurrentPage+1).'&q='. $q .'>Next</a> <a href=?page='.($NumOfPages).'&q='. $q .'> >> </a>';
		}
		elseif($CurrentPage == $NumOfPages){
			$page_str = '<a href=?page=1&q='. $q .'> << </a> <a href=?page='.($CurrentPage-1).'&q='. $q .'>Previous</a> <a href=?page='.($CurrentPage).'&q='. $q .'> ['.$CurrentPage.'] </a> Next >> </a>';
		}
		else{
			$page_str = '<a href=?page=1&q='. $q .'> << </a> <a href=?page='.($CurrentPage-1).'&q='. $q .'>Previous</a> <a href=?page='.($CurrentPage).'&q='. $q .'> ['.$CurrentPage.'] </a> <a href=?page='.($CurrentPage+1).'&q='. $q .'>Next</a> <a href=?page='.($NumOfPages).'&q='. $q .'> >> </a>';
		}
	}
	return $page_str;
}

/*
 * Construct content string
 * The content of the genes, tfs and terms together with their corresponding
 * K values, Ranks and Scores.
 */
function ConstructContentStr($page, $PageSize, $NumOfRecords, $array, $q)
{
	// sort the records
	foreach ($array as $key => $row){
		$sims[$key] = $row['sim'];
		$kvalue[$key] = $row['kvalue'];
		$cluster_no[$key] = $row['cluster_no'];
	}
	
	// save selected
	$array_q = $array[$q];
	
	// sort by similarity, k value, cluster number. similarity is the critical.
	if(array_multisort($sims, SORT_DESC, SORT_NUMERIC, $kvalue, SORT_ASC, SORT_NUMERIC, $cluster_no, SORT_ASC, SORT_NUMERIC, $array)){
		// if successfully sorted, do nothing
		;
	}
	else{
		// throw out some error message
		// keep a error log maybe
		echo "Some internal errors happend with 'array_multisort'";
	}
	
	// insert the selected at the beginning
	array_unshift($array, $array_q);
	
	// remove duplicated
	for($i=1; $i<count($array); $i++){
		if($array[$i] == $array_q){
			array_splice($array,$i,1);
		}
	}	
	
	// construct content string
	$ContentStr = '';
	for($i=($page-1)*$PageSize; ($i<($page-1)*$PageSize+10)&&($i<$NumOfRecords); $i++){
		// format the output data
        $KValue = $array[$i]['kvalue'];
		$ClusterNo = $array[$i]['cluster_no'];
		$Genes = explode(',', $array[$i]['genes']);
		$GeneScores = explode(',', $array[$i]['g_scores']);
		$Terms = $array[$i]['terms'];
		$TFs = explode(',', $array[$i]['tfs']);
		$TFScores = explode(',', $array[$i]['tf_scores']);
		$ContentStr = $ContentStr .
		"
		<h3 onclick='initialize(0,this)'; style='font-family:Courier New, Trebuchet MS;'><b>k:</b><b>" . $KValue . "</b>
		<b>cluster_no:</b><b>" . $ClusterNo . "</b> <b>Rank:</b><b>" . $array[$i]['rank'] . "</b><BR></h3>" .
		"<table style='font-family:Courier New, Trebuchet MS;text-align:center;word-break:break-all;width:auto;overflow:auto;'>
		<tr><td colspan=3 style='background-color:rgb(197,217,241)'>Genes</td>
		<td></td>
		<td colspan=3 style='background-color:rgb(197,217,241)'>Transcription Factors</td>
		</tr>
		<tr>
		<td><b><u>Rank</u></b></td><td><b><u>Score</u></b></td><td><b><u>Symbol</u></b></td><td></td>
		<td><b><u>Rank</u></b></td><td><b><u>Score</u></b></td><td><b><u>Symbol</u></b></td><td></td>
		</tr>"."
		<tr>
		<td>1</td><td>". $GeneScores[0] ."</td><td>". $Genes[0] ."</td><td></td>
		<td>1</td><td>". $TFScores[0] ."</td><td>". $TFs[0] ."</td>
		</tr>
		<tr>
		<td>2</td><td>". $GeneScores[1] ."</td><td>". $Genes[1] ."</td><td></td>
		<td>2</td><td>". $TFScores[1] ."</td><td>". $TFs[1] ."</td>
		</tr>
		<tr>
		<td>3</td><td>". $GeneScores[2] ."</td><td>". $Genes[2] ."</td><td></td>
		<td>3</td><td>". $TFScores[2] ."</td><td>". $TFs[2] ."</td>
		</tr>
		<tr>
		<td>4</td><td>". $GeneScores[3] ."</td><td>". $Genes[3] ."</td><td></td>
		<td>4</td><td>". $TFScores[3] ."</td><td>". $TFs[3] ."</td>
		</tr>
		<tr>
		<td>5</td><td>". $GeneScores[4] ."</td><td>". $Genes[4] ."</td><td></td>
		<td>5</td><td>". $TFScores[4] ."</td><td>". $TFs[4] ."</td>
		</tr>
		<tr>
		<td>6</td><td>". $GeneScores[5] ."</td><td>". $Genes[5] ."</td><td></td>
		<td>6</td><td>". $TFScores[5] ."</td><td>". $TFs[5] ."</td>
		</tr>
		<tr>
		<td>7</td><td>". $GeneScores[6] ."</td><td>". $Genes[6] ."</td><td></td>
		<td>7</td><td>". $TFScores[6] ."</td><td>". $TFs[6] ."</td>
		</tr>
		<tr>
		<td>8</td><td>". $GeneScores[7] ."</td><td>". $Genes[7] ."</td><td></td>
		<td>8</td><td>". $TFScores[7] ."</td><td>". $TFs[7] ."</td>
		</tr>
		<tr>
		<td>9</td><td>". $GeneScores[8] ."</td><td>". $Genes[8] ."</td><td></td>
		<td>9</td><td>". $TFScores[8] ."</td><td>". $TFs[8] ."</td>
		</tr>
		<tr>
		<td>10</td><td>". $GeneScores[9] ."</td><td>". $Genes[9] ."</td><td></td>
		<td>10</td><td>". $TFScores[9] ."</td><td>". $TFs[9] ."</td>
		</tr>
		<tr>
		<td colspan=7 style='background-color:rgb(197,217,241)'>Terms</td>
		</tr>
		<tr>
		<td colspan=7 style='text-align:left;padding: 0em 1em 0em 1em;'>". $Terms ."
		</td>
		</tr>"."
		</table>
		";
	}
	return $ContentStr;
}

/*
 * Calculate rank of all the clusters
 */
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


/*
 * Determine the selected base index in array,
 * We select the one with smallest k value as the base.
 */
function SelectBaseIndex($array)
{
	$base = 0;
	for($i=0; $i<count($array); $i++){
		if($array[$i]['rank'] == 1){
			if($array[$i]['kvalue'] < $array[$base]['kvalue']){
				$base = $i;
			}
		}
	}
	return $base;
}
?>