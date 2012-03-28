<?php
/*
Creation Date: 27 March 2012
Author: Ian O'Keeffe

Modification History:
--------------------------
21-03-2012
This code checks a TM file for inconsistencies
*/

//This function checks TMX-format files
function checkTM_TMX($file){

//Open TMX file using DOMDocument to access the tag information
$tmxDom = new DOMDocument(); 		//To handle XML format files 
$tmxDom->loadXML($file);		//Open $file as a DOMDocument XML file object


//Will need to store the language pair segments in an array for processing...



//Find the right tagged fields, and fire them into an array
$count = 0;

//Get the source language
$headerdata = $tmxDom->getElementsByTagName('header');
foreach ($headerdata as $header) {
	$srclang = $header->getAttribute('srclang');
}

//Put the language pair data into an array
$tus = $tmxDom->getElementsByTagName('tu');
foreach ($tus as $tu) {
	$count++;
	$TM_array[$count][0] = $count;
	$tuvs = $tu->getElementsByTagName('tuv');
	foreach ($tuvs as $tuv) {
		$lang = $tuv->getAttribute('xml:lang');		//seems to be non-standard!
		$lang = $tuv->getAttribute('lang');
		$seg = $tuv->getElementsByTagName('seg');
		$segment = $seg->item(0)->nodeValue;
		if($srclang == $lang) {
			$TM_array[$count][1] = $lang;
			$TM_array[$count][2] = $segment;
		} else {
			$TM_array[$count][3] = $lang;
			$TM_array[$count][4] = $segment;		
		}
	}
}

for($i = 1; isset($TM_array[$i][0]); $i++) {
	echo '<br>'.$TM_array[$i][0].' '.$TM_array[$i][1].' '.$TM_array[$i][2].' '.$TM_array[$i][3].' '.$TM_array[$i][4];
}

$results = 'Nothing processed';





//Save results to file
return $results;
}


/*
Example XLIFF file

<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xliff PUBLIC "-//XLIFF//DTD XLIFF//EN"
"http://www.oasis-open.org/committees/xliff/documents/xliff.dtd" >
<xliff version="1.0" xml:lang="en">
<file datatype="plaintext" original="MultiLingualContent.fla" source-language="en-IE" target-language="fr-FR">
		<header></header>
		<body>
			<trans-unit id="001" resname="IDS_GREETINGS">
				<source xml:lang="en-US">Welcome to our web site!</source>
				<target xml:lang="fr-FR">Bienvenue sur notre site web!</target>
			</trans-unit>
			<trans-unit id="002" resname="IDS_TEST">
				<source></source>
			</trans-unit>
		</body>
	</file>
</xliff>
*/

?>