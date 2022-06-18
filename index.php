<?php 
include_once("constants.php");

//
// Clear up all cookies
setcookie("cookie[keyword1]",    "", time()-3600);
setcookie("cookie[searchType1]", "", time()-3600);
setcookie("cookie[keyword2]",    "", time()-3600);
setcookie("cookie[searchType2]", "", time()-3600);
setcookie("cookie[keyword3]",    "", time()-3600);
setcookie("cookie[searchType3]", "", time()-3600);
?>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link href="./style.css" rel="stylesheet" type="text/css" media="screen"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="./lib/jquery-1.9.1.js"></script>
	<script src="./lib/jquery-ui.js"></script>
	<link href="./img/paw.gif" rel="SHORTCUT ICON"/>
	<title>TREMEL: Transcription REgulatory Modules Extracted from Literature</title>
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
	End Piwik code for localhost/tremel -->
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
<body>
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
	
	<?php $question_hover_str = "<p style='font-family: Arial; font-weight: bold;'>Search:</p>
								 <ol style='font-family: Georgia; font-size: 10pt;'>
								 <li>Select entity type from pulldown menu: Gene, TF, or Term.</li>
								 <li>Enter gene/TF  symbol or any keyword.</li>
								 <li>Click 'Tremel Search' to submit.</li>
								 <li>Click [+/-] to add additional search fields.</li>
								 </ol>
								 <p style='font-family: Arial; font-weight: bold;'>Visualization:</p>
								 <ol style='font-family: Georgia; font-size: 10pt;'>
								 <li>Click on <span style='color:blue; font-weight: bold;'>blue</span> point in the plot to display the terms, genes and TFs associated with that module.</li>
								 <li>The selected dot is displayed in <span style='color:red; font-weight: bold;'>red</span>.Other dots are colored based on the similarity to the selected module.</li>
								 <li>Rotate the plot by left-click and dragging the mouse.</li>
								 <li>Customize the plot using right-click menu options.</li>
								 </ol>"; ?>
	
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
	
	<div id="content" class="firstpage">
	</div>
	
	<div id="div_line" style="height: 1;"></div>
	<div class="footer"
		style="font-family: Arial, Times;
		font-size: 9pt;
		text-align: center;">
			All Rights Reserved &copy; 2014-2017 &middot;
			<a href="http://www.memphis.edu/binf/">Bioinformatics</a> &middot;
			<a href="http://www.memphis.edu/">University of Memphis</a> &middot;
			<a href="mailto:rhomayon@memphis.edu;sujoyroy@memphis.edu;daqingyun@gmail.com;">Contact</a>
	</div>
</body>
</html>
