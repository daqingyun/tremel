<?php
/*******************************************************************************
 * re-order all the gene list based on selected point in the figure
 * 
 * Daqing Yun @ CS
 * Email: dyun@memphis.edu
 * 
 * Last updated: Aug 26, 2014
 * 
 *******************************************************************************/
include("util.php");

//
// Calculate the number of common genes and tfs between
// two modules $x and $y.
function calc_common_gene_tf($gene1, $gene2, $tf1, $tf2)
{
	$num_commongene = 0;
	$num_commontf = 0;
	for($i = 0; $i < count($gene1); $i ++)
		if(in_array($gene1[$i], $gene2))
			$num_commongene = $num_commongene + 1;
	
	for($j = 0; $j < count($tf1); $j ++)
		if (in_array($tf1[$j], $tf2))
			$num_commontf = $num_commontf + 1;
		
	return ($num_commongene + $num_commontf);
}

//
// Calculate union genes and tfs number of two clusters
// all elements in $x and $y should be in lower cases
//function CalcUnionGeneTF($gene1, $gene2, $tf1, $tf2)
function calc_union_gene_tf($gene1, $gene2, $tf1, $tf2)
{
	$num_uniongene = count($gene1);
	
	for($i = 0; $i < count($gene2); $i ++)
		if(!in_array($gene2[$i], $gene1))
			$num_uniongene = $num_uniongene + 1;

	$num_uniontf = count($tf1);
	for ($j = 0; $j < count($tf2); $j ++)
		if (!in_array($tf2[$j], $tf1))
			$num_uniontf = $num_uniontf + 1;
		
	return ($num_uniongene + $num_uniontf);
}

//
// Calculate the similarity of two clusters
// The similarity is defined as follows:
// Sim(C1, C2) = [# intersection(C1,C2) / # union(C1,C2)]
//function calc_simUtil($gene1, $gene2, $tf1, $tf2)
function calc_sim_util($gene1, $gene2, $tf1, $tf2)
{
	$common = calc_common_gene_tf($gene1, $gene2, $tf1, $tf2);
	$union  = calc_union_gene_tf($gene1, $gene2, $tf1, $tf2);
	
	// echo $common . '/' . $union;
	
	return round($common / $union, 3);
}

// reorder all the records
//function calc_sim($array, $baseid)
function calc_sim($array, $baseid)
{
	$num_records = count($array);
	
	// note we have an extra ',' at the end of each record in the database
	$base_gene_items_array = array_map('strtolower', explode(',', $array[$baseid]['gene']));
	$counter = count($base_gene_items_array);
	if($base_gene_items_array[$counter-1]=="")
		$base_gene_items_array = array_slice($base_gene_items_array, 0, $counter-1);
	
	$base_tf_items_array = array_map('strtolower', explode(',', $array[$baseid]['TF']));
	$counter = count($base_tf_items_array);
	if($base_tf_items_array[$counter-1]=="")
		$base_tf_items_array = array_slice($base_tf_items_array, 0, $counter-1);
	
	$array[$baseid]['sim'] = 1.0;
	
	// calculate similarities of all clusters
	// there is an extra "," in the database
	// makes our calculation inaccurate, to dismiss we minus 1
	for($i = 0; $i < $num_records; $i ++)
	{
		$comp_gene_items_array = array_map('strtolower', explode(',', $array[$i]['gene']));
		$counter = count($comp_gene_items_array);
		if($comp_gene_items_array[$counter-1] == "")
			$comp_gene_items_array = array_slice($comp_gene_items_array, 0, $counter-1);
		
		$comp_tf_items_array = array_map('strtolower', explode(',', $array[$i]['TF']));
		$counter = count($comp_tf_items_array);
		if($comp_tf_items_array[$counter-1] == "")
			$comp_tf_items_array = array_slice($comp_tf_items_array, 0, $counter-1);	
		
		$array[$i]['sim'] = calc_sim_util($base_gene_items_array, $comp_gene_items_array,
				$base_tf_items_array, $comp_tf_items_array);
	}	
	return $array;
}

/* not used anymore
function SortByKC($array)
{
	// sort the records
	foreach ($array as $key => $row){
		$kvalue[$key] = $row['k'];
		$rank[$key] = $row['rank'];
		$cluster_no[$key] = $row['module'];
	}
	//array_multisort($rank, SORT_ASC, $array);
	array_multisort($kvalue, SORT_ASC, $rank, SORT_ASC, $array);
	return $array;
}
*/
?>