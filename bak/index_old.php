<html>
  <head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <link href="style.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="./img/paw.gif" rel="SHORTCUT ICON"/>
    <title >
      Bioinformatics : TREMEL
    </title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>    
    <script src="/A2EB891D63C8/avg_ls_dom.js" type="text/javascript"></script>
    <script type="text/javascript">
        function divContent() {
	    var browser=navigator.appName;
	    if (browser == "Microsoft Internet Explorer") {
	      document.write('<div id="content-ie">');
	    }
	    else {
	      document.write('<div id="content">');
	    }
	  }
	  
	  function divEnd() {
	    document.write("<\/div>");
	  }
	  
	  function hint(){
		var browser=navigator.appName;
		//alert(browser);
		if (browser == "Microsoft Internet Explorer"){
			alert("Please use Google Chrome, otherwise content might not be displayed appropriately.");
		}
	  }
    </script>
  </head>
  
  <body>
    <script type="text/javascript">
          divContent();
		  hint();
    </script>
    <div id="header">
      Dept of Bioinformatics, U of Memphis
    </div>
    
    <div id="titlebar">
      <img style="float:right;
          margin-left:4em;
		  margin-right:1em;
		  margin-bottom:2.5em;"
	    src="./img/memphislogo.png"
	    width="216"
	    height="81"
	    alt="UMLogo">
      <div style="font-size: 3.0em;">TREMEL</div>
      <div><br></div>
      <div style="font-size: 1.4em">Transcriptional Regulatory Modules Extracted From Literature</div>
    </div>
    
    <div id="navbar">
      <a class="current" href="index.php">Home</a>
      <a href="about.php">About</a>
    </div>
    
    <div id="main">
     <form action="query.php?page=1" method="post" id="keyword_form" accept-charset="UTF-8">
		  <div id="welcome">
		  		<img src="./img/menu-collapsed.png">		  
		  		<input style="width:305px;height:30px;"
		  			type="text" 
		  			id="query-keyword" 
		  			name="Keyword" 
		  			value=""
		  			maxlength="128"/>
		  </div>
		  <div id="welcome">
		  		<input style="margin-top:1.5em;"
		  			type="radio"
		  			name="SearchType" 
		  			value="genes" 
		  			checked="checked"/>
		  		<label>
		  		Genes
		  		</label>
		  		<input style="margin-top:1.5em;"
		  			type="radio"
		  			name="SearchType" 
		  			value="tfs"/>
		  		<label>
		  		Transcription factors
		  		</label>
		  		<input style="margin-top:1.5em;margin-left:0.8em;"
		  			type="radio" 
		  			name="SearchType" 
		  			value="terms"/>
		  		<label>
		  		Terms
		  		</label>
		  </div>
		  <div>
		  		<input style="margin-top:1.5em;" type="submit" id="searchbutton" name="op" value="Start Search"/>
		  </div>
	 </form>
    </div>
    
    <div id="navbar">
      <a class="current" href="">Results will be shown below</a>
    </div>
        
    <div id="main">
	    <p style="margin-top:1em;
          margin-left:0em;
		  margin-right:1em;
		  margin-bottom:0;
		  font-size:15px;">
		  Cluster list (ranked):
		 </p>
    </div>
    
    <div id="footer">
      &copy; Dept. of Bioinformatics, U of Memphis, TN 38152 | Last Updated: 02/04/2013
    </div>
    <script type="text/javascript">
       divEnd();
    </script>

  </body>
</html>
