
<?php

include 'SpellCorrector.php';

header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$spellCheckDisable = isset($_GET['scd']) ? $_GET['scd'] : 0;
$rankingAlgo = isset($_REQUEST['rankingalgo']) ? $_REQUEST['rankingalgo'] : 0;
$results = false;
$typed = $query;

if ($query)
	{
	
	$query = preg_replace('!\s+!', ' ', $query);

	if($spellCheckDisable == 0){

				$pieces = explode(" ",$query);
				$mistake = 0;
				$finalQuery="";

				foreach ($pieces as $key => $value) {
					$correctValue = SpellCorrector::correct($value);
		
			    		if (strcmp($value, $correctValue) !== 0) {
						$mistake = 1;
			
					}
					$finalQuery=$finalQuery.$correctValue." ";
				}

	
				if($mistake == 1){
					
					$query = $finalQuery;
				}

	} 
 
	require_once ('solr-php-client-master/Apache/Solr/Service.php');

	// create a new solr service instance - host, port, and corename
	// path (all defaults in this example)

	$solr = new Apache_Solr_Service('localhost', 8983, 'solr/newssitefinal');


	// if magic quotes is enabled then stripslashes will be needed

	if (get_magic_quotes_gpc() == 1)
		{
		$query = stripslashes($query);
		}

	try
		{

			if($_REQUEST['rankingalgo']==0){
				$results = $solr->search($query, 0, $limit);
			} else{
				$additionalParameters = array(
 									'sort' => 'pageRankFile desc'

							);
				$results = $solr->search($query, 0, $limit, $additionalParameters);
			}
		
		}

	catch(Exception $e)
		{

		die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString() }</pre></body></html>");
		}
	}

?>
<html>   <head> 



<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>


li.autocompleteItemSelect{
	background-color:#efefef;
}


div#suggestions li:hover{

background-color:#efefef;

}


</style>

<script type="text/javascript">

	

	$(function(){



		$('#q').on("keyup", function(e){
				var contentType = this.name;
				var inputId = this.id;
				var IdTalk = this.lang;
		   	if(e.keyCode == 13){
		   		var selected = $('div#suggestions li.autocompleteItemSelect');
			   	if(selected.length > 0) {
						$('div#suggestions').html('').hide();
						$('#q').val(selected.text().trim()).focus();
		   		}
					return false;
				} else if(e.keyCode == 27) {
					$('div#suggestions').html('').hide();
					return false;
				} else if(e.keyCode == 8 && $(this).val()=='') {
					$('div#suggestions').html('').hide();
					return false;
				} else if(e.keyCode == 40){

					if($('div#suggestions .autocompleteItemSelect').next().length == 0) {
						console.log('here you');
						$('div#suggestions li:last').removeClass('autocompleteItemSelect');
		   			$('div#suggestions li:first').addClass('autocompleteItemSelect');
		   		} else {
		   			$('div#suggestions li.autocompleteItemSelect').removeClass('autocompleteItemSelect').next().addClass('autocompleteItemSelect');
		   		}
					//this.value = $('#productAutoComplete a.autocompleteItemSelect').text();
				} else if(e.keyCode == 38) {
					if($('div#suggestions .autocompleteItemSelect').prev().length == 0) {
		   			$('div#suggestions li:last').addClass('autocompleteItemSelect');
		   			$('div#suggestions li:first').removeClass('autocompleteItemSelect');
		   		} else {
		   			$('div#suggestions li.autocompleteItemSelect').removeClass('autocompleteItemSelect').prev().addClass('autocompleteItemSelect');
		   		}
					//this.value = $('#productAutoComplete a.autocompleteItemSelect').text();;
				} else {
		       				if(this.value.length >= 1) {
		           	
		           				console.log('suggesting');
		           				suggest();
		           	
						
						} else {
							$('div#storeUsersSuggestion').html('').hide();
						}
				}
				//var pos = $(this).offset();
		   	//$('div#suggestions').css({'top': pos.top + 15, 'left' : pos.left - 0.5});
			}).focus();


		function suggest(){


		var typed = $('#q').val();
		typed = $.trim(typed);
		var terms= typed.split(" ");
		var lastSpace = typed.lastIndexOf(" ");
		var previousTerms='';
		if(lastSpace!=-1){
			previousTerms += typed.substr(0,lastSpace);
		}
		var lastterm = terms[terms.length -1];

		var urlAjax = 'suggest.php?q='+lastterm;

		var suggestionsList = '<ul  style="list-style-type: none;/*! width: 100%; */margin: 0;padding: 0;">';
	

		console.log(typed);
		if(typed!=''){
				
			$.ajax({
				url : urlAjax,
				dataType : 'json',
				method : 'GET',
				success : function(data){

					var map = data.suggest.suggest;
					for(var key in map){
						var array = map[key].suggestions;
						var cnt=0;
						for(var index in array){
							if(cnt==5){
								break;
							}
							var suggested = array[index].term;
							if(suggested.indexOf('.') < 0 &&  suggested.indexOf(':') <0 && suggested.indexOf('_') <0){ 
							suggestionsList += '<li style="line-height: 22px;">'+previousTerms+' '+array[index].term+'</li>';
cnt++;
}
						}

					}

					suggestionsList +='</ul>';	

					$('#suggestions').html(suggestionsList).show();
					$('div#suggestions li').click(function(){

								$('div#suggestions').html('').hide();
								$('#q').val($(this).text().trim()).focus();
								
							});
					console.log($('#suggestions').html());

					
				}, error : function(data){

					console.log('error somewhere');
				}

			});
		}

	}

	});
	

</script>
  <title>PHP Solr Client Example</title>   </head>   <body>



 <form accept-charset="utf-8" method="get" name="queryform" onkeypress="return event.keyCode != 13;">
 
<div style="position:relative;float:left; width:53%"  >
<label for="q">Search:</label>
 <input autocomplete="off" id="q" name="q" type="text"   style="width:85%;" value="<?php
echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>

	<div id="suggestions" style="display:none;position:absolute;align-content: left;left: 65px;border: solid 1px #efefef;width: 85%;z-index:2;background-color:white;">
		
	</div>

</div>
<input name="rankingalgo" value="0" type="radio" 

<?php if( (!isset($_GET['rankingalgo'])) || (isset($_GET['rankingalgo']) && $_GET['rankingalgo']=="0")){echo 'checked="checked"';}?>                        
/> Solr Internal Ranking
<input name="rankingalgo" value="1" type="radio" 
<?php if(isset($_GET['rankingalgo']) && $_GET['rankingalgo']=="1"){echo 'checked="checked"';} ?>
 />
<input name="scd" value=<?php echo $spellCheckDisable; ?>  type="hidden"/> 


Page Rank 

 <input type="submit" value="search"/> 
 </form>
<?php

// display results

if ($results)
	{


	$total = (int)$results->response->numFound;
	$start = min(1, $total);
	$end = min($limit, $total); ?>
 <div>Results <?php
	echo $start; ?> - <?php
	echo $end; ?> of <?php
	echo $total; ?>:</div>
 <ol> 

<?php



if($spellCheckDisable == 0 && $mistake == 1){ 

?>
<p><span class="spell">
Showing results for</span> <?php echo $query; ?><br><span class="spell_orig">Search instead for</span> <a class="spell_orig" href="<?php echo 'index.php?q='.$typed.'&scd=1'.'&rankingalgo='.$rankingAlgo; ?>" ><?php echo $typed; ?></a><br></p>
<?php

}  

if($spellCheckDisable == 1){
				$terms = explode(" ", $query);	
				$finalCorrectQuery="";

				foreach ($terms as $key => $value) {
					$correctValue = SpellCorrector::correct($value);
					$finalCorrectQuery=$finalCorrectQuery.$correctValue." ";
				}

?>
<p><span class="spell">Did you mean:</span> <a class="spell" href="<?php echo 'index.php?q='.$finalCorrectQuery.'&scd=0'.'&rankingalgo='.$rankingAlgo; ?>"><b><i><?php echo $finalCorrectQuery; ?></i></b></a> </p>


<?php

}



	foreach($results->response->docs as $doc)
		{

			$meta = $doc->og_desciption;
			$metahasall = 1;
			$metahasone =0;

			$title = $doc->title;
			$titlehasall = 1;
			$titlehasone =0;

			$tms = explode(" ",$query);


			foreach ($tms as $key => $value) {

						    		if (stripos($meta, $value) === false) {
									$metahasall = 0;
						
								} else{
									$metahasone = 1;
								}


								if (stripos($title, $value) === false) {
									$titlehasall = 0;
						
								} else{
									$titlehasone = 1;
								}
					
					
				}

				$addr = $doc->id;
				$fileaddr = str_replace("NBCNewsDownloadData", "textFilesNewsWebsite", $addr);
				$filestring =  file_get_contents($fileaddr);

				$snippet = "";
				
				$lines = explode("\n",$filestring);

				$foundall = 0;
				$foundone = 0;
				$stringhavingone = "";
				$stringhavingall="";

	foreach($lines as $line){

		//echo $line;

		$hasall=1;
		$hasone = 0;

		foreach ($tms as $key => $value) {


				    		if (stripos($line, $value) === false) {
							
							$hasall = 0;
						
						} else{
							//echo '############## found one in line'.$line;
							$foundone = 1;
							$stringhavingone = $line; 
						}
					
		}

		if($hasall==1){
			//echo 'found all in line'.$line;
			$foundall = 1;
			$stringhavingall = $line;
			break;
		}
	
	}

	if($foundall==1){
		$snippet = $stringhavingall;
	} else if ($metahasall==1){
		$snippet = $meta;
	}else if ($titlehasall==1){
		$snippet = $meta;
	}else if($foundone == 1){
		$snippet = $stringhavingone;
	} else if($metahasone==1){
		$snippet = $meta;
	}else if($titlehasone==1){
		$snippet = $meta;
	} else {
		$snippet = $meta;
	}


//$snippet = $doc->og_description;

 ?>   <li>
 <table style="text-align: left; margin-bottom:26px">

 <tr style="font-size:20px;">
 
 <td> <a href="<?php echo htmlspecialchars($doc->og_url, ENT_NOQUOTES, 'utf-8'); ?>" target="_blank" ><?php
			echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8'); ?>
</a></td>
 </tr>

<tr>
 
 <td><a style="color: #006621;" href="<?php echo htmlspecialchars($doc->og_url, ENT_NOQUOTES, 'utf-8'); ?>" target="_blank" ><?php
			echo htmlspecialchars($doc->og_url, ENT_NOQUOTES, 'utf-8'); ?></a></td>
 </tr>

<tr>
 
 <td><?php
			echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td>
 </tr>

<tr>
 
 <td id="snippetText" style="color: #545454;/*! font-size: 16px; */"><?php
			echo htmlspecialchars($snippet, ENT_NOQUOTES, 'utf-8'); ?></td>
 </tr>
 </table>   </li>
<?php
		} ?>
 </ol>
<?php
	} ?> </body> 


 </html>
