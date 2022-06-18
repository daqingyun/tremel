<?php
/***********************************************************************************************
 * Database.php
 * 
 * The Database class is meant to simplify the task of accessing
 * information from the website's database.
 *
 * sql utilities
 * search in the database utilities
 *
 * <daqing.yun@gmail.com>
 *
 * July 29, 2017
 *    update mysql functions use newer version for php 7, i.e., mysqli_xxx
 *
 * Last Updated: July 27, 2017 
 ***********************************************************************************************/

include_once("constants.php");

/**
 * search in the database according to the keyword
 * and its type (terms, genes, tfs)
 */
function search_in_db($key1, $type1, $key2, $type2, $key3, $type3)
{
	/* these functions are deprecated */
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
			// construct the sql statement
			if($key1!="")
				$SQL = "SELECT * FROM unstem_new WHERE find_in_set('$key1', "."$type1".")";
			if($key2!="")
				$SQL = $SQL . "and find_in_set('$key2', "."$type2".")";
			if($key3!="")
				$SQL = $SQL . "and find_in_set('$key3', "."$type3".")";

			$SQL = $SQL . " ORDER BY find_in_set('$key1', "."$type1".")";

		    // deprecated - $result = mysql_query($SQL) or die("Invalid query: " . mysql_error());
			if ($result = mysqli_query($link, $SQL)) {
				while (($row = mysqli_fetch_assoc($result))==TRUE) {
					$array[]=$row;
				}
			} 	
		 	mysqli_close($link);
		 }
	
	// 
	if (count($array) <= 0) {
		return ;
	}
	return $array;
}
?>
