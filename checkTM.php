<?php
/*
Creation Date: 27 March 2012
Author: Ian O'Keeffe

Modification History:
--------------------------
21-03-2012
This code checks a TM file for inconsistencies
*/

//*************************************************************************************************************
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
		$TM_array[$count][5] = 0; //location of first iteration of source
		$TM_array[$count][6] = 0; //location of first iteration of target
		$TM_array[$count][7] = 0; //location of original pair (if duplicate), else 0
		$TM_array[$count][8] = 0;
		$TM_array[$count][9] = 0;
	}
}
//IOK Testing: print out file for testing
//for($i = 1; isset($TM_array[$i][0]); $i++) {
//	echo '<br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  '.$TM_array[$i][2].'  '.$TM_array[$i][3].'  '.$TM_array[$i][4].'  '.$TM_array[$i][5].'  '.$TM_array[$i][6].'  '.$TM_array[$i][7].'  '.$TM_array[$i][8].'  '.$TM_array[$i][9];
//}

//Step 1: Isolate duplicate sources - 
//	once complete, if $TM_array[$i][0] == $TM_array[$i][5] not a duplicate source
//  if $TM_array[$i][0] != $TM_array[$i][5] then $TM_array[$i][5] holds seq. of the first occurance, and $i is a duplicate source
for($i = 1; isset($TM_array[$i][0]); $i++) {
$totalCount = $i;
	if($TM_array[$i][5] == 0) { //not yet inspected	
		$TM_array[$i][5] = $i; //first occurance of this source, set to value of seq no.
		}

	if($TM_array[$i][6] == 0) { //not yet inspected	
		$TM_array[$i][6] = $i; //first occurance of this target, set to value of seq no.
		}

	for($j = $i+1; isset($TM_array[$j][0]); $j++) {

		if($TM_array[$j][5] == 0) { //not yet inspected	
			if($TM_array[$i][2] == $TM_array[$j][2]) { //duplicate source

				$TM_array[$j][5] = $i; //duplicate occurance of this source, set to value of original seq no.

			}
		}

		if($TM_array[$j][6] == 0) { //not yet inspected	
			if($TM_array[$i][4] == $TM_array[$j][4]) { //duplicate target

				$TM_array[$j][6] = $i; //duplicate occurance of this target, set to value of original seq no.

			}
		}
	}
}


//Step 2: Look for duplicate entries
$duplicateCount = 0;
echo '<br><br><br><br> Duplicate entries -------------------------------------------------------------><br><br>';

for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] == $TM_array[$i][6]) { //matching source and target
		if($TM_array[$i][0] != $TM_array[$i][5]) { //seq. != source and target, so must be a duplicate	
			$TM_array[$i][7] = $TM_array[$i][5]; //duplicate. place original seq. no of pair in [7]
			$duplicateCount++;

			$orig = $TM_array[$i][5]; // seq no of original entry
			echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  <b>'.$TM_array[$i][2].'</b>  '.$TM_array[$i][3].'  <b>'.$TM_array[$i][4].'</b> <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  <b>'.$TM_array[$orig][2].'</b>  '.$TM_array[$orig][3].'  <b>'.$TM_array[$orig][4].'</b>';

		}
	}
}


//Step 3: Look for Cat 2 entries (many to 1)
$cat2Count = 0;
echo '<br><br><br><br> CATEGORY 2 Inconsistencies (Many to 1)-----------------------------------------><br><br>';
for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] > $TM_array[$i][6]) { //source > target
		$TM_array[$i][8] = 1; //duplicate. place original seq. no of pair in [8]
		$cat2Count++;
		
		$orig = $TM_array[$i][6]; // seq no of original entry
		echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  <b>'.$TM_array[$i][2].'</b>  '.$TM_array[$i][3].'  '.$TM_array[$i][4].' <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  <b>'.$TM_array[$orig][2].'</b>  '.$TM_array[$orig][3].'  '.$TM_array[$orig][4];

	}
}


//Step 4: Look for Cat 3 entries (1 to many)
$cat3Count = 0;
echo '<br><br><br><br> CATEGORY 3 Inconsistencies (1 to many)-----------------------------------------><br><br>';
for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] < $TM_array[$i][6]) { //source < target
		$TM_array[$i][9] = 1; //duplicate. place original seq. no of pair in [9]
		$cat3Count++;

		$orig = $TM_array[$i][5]; // seq no of original entry
		echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  '.$TM_array[$i][2].'  '.$TM_array[$i][3].'  <b>'.$TM_array[$i][4].'</b> <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  '.$TM_array[$orig][2].'  '.$TM_array[$orig][3].'  <b>'.$TM_array[$orig][4].'</b>';
	}
}


//IOK Testing: print out file for testing
//echo '<br><br>Status so far... <br>';
//for($i = 1; isset($TM_array[$i][0]); $i++) {
//	echo '<br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  '.$TM_array[$i][2].'  '.$TM_array[$i][3].'  '.$TM_array[$i][4].'  '.$TM_array[$i][5].'  '.$TM_array[$i][6].'  '.$TM_array[$i][7].'  '.$TM_array[$i][8].'  '.$TM_array[$i][9];
//}



$results = 'File type: TMX <br> Number of entries: '.$totalCount.' <br> Duplicate entries: '.$duplicateCount.' <br> Cat 2 inconsistency total: '.$cat2Count.' <br> Cat 3 inconsistency total: '.$cat3Count;





//Save results to file
return $results;
}


//*************************************************************************************************************
//This function checks XLIFF-format files
function checkTM_XLIFF($file){

//Open XLIFF file using DOMDocument to access the tag information
$xlfDom = new DOMDocument(); 		//To handle XLIFF format files 
$xlfDom->loadXML($file);		//Open $file as a DOMDocument XML file object


//Will need to store the language pair segments in an array for processing...



//Find the right tagged fields, and fire them into an array
$count = 0;

//Get the source language
$filedata = $xlfDom->getElementsByTagName('file');
foreach ($filedata as $file) {
	$srclang = $file->getAttribute('source-language');
	$tgtlang = $file->getAttribute('target-language');
}

//Put the language pair data into an array
$transunits = $xlfDom->getElementsByTagName('trans-unit');
foreach ($transunits as $transunit) {
	$count++;
	$TM_array[$count][0] = $count;

	$srcseg = $transunit->getElementsByTagName('source');
	$srcsegment = $srcseg->item(0)->nodeValue;
	$TM_array[$count][1] = $srclang;
	$TM_array[$count][2] = $srcsegment;
		
	$tgtseg = $transunit->getElementsByTagName('target');
	$tgtsegment = $tgtseg->item(0)->nodeValue;
	$TM_array[$count][3] = $tgtlang;
	$TM_array[$count][4] = $tgtsegment;		

	$TM_array[$count][5] = 0; //location of first iteration of source
	$TM_array[$count][6] = 0; //location of first iteration of target
	$TM_array[$count][7] = 0; //location of original pair (if duplicate), else 0
	$TM_array[$count][8] = 0;
	$TM_array[$count][9] = 0;

}
//IOK Testing: print out file for testing
//for($i = 1; isset($TM_array[$i][0]); $i++) {
//	echo '<br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  '.$TM_array[$i][2].'  '.$TM_array[$i][3].'  '.$TM_array[$i][4].'  '.$TM_array[$i][5].'  '.$TM_array[$i][6].'  '.$TM_array[$i][7].'  '.$TM_array[$i][8].'  '.$TM_array[$i][9];
//}

//Step 1: Isolate duplicate sources - 
//	once complete, if $TM_array[$i][0] == $TM_array[$i][5] not a duplicate source
//  if $TM_array[$i][0] != $TM_array[$i][5] then $TM_array[$i][5] holds seq. of the first occurance, and $i is a duplicate source
for($i = 1; isset($TM_array[$i][0]); $i++) {
$totalCount = $i;
	if($TM_array[$i][5] == 0) { //not yet inspected	
		$TM_array[$i][5] = $i; //first occurance of this source, set to value of seq no.
		}

	if($TM_array[$i][6] == 0) { //not yet inspected	
		$TM_array[$i][6] = $i; //first occurance of this target, set to value of seq no.
		}

	for($j = $i+1; isset($TM_array[$j][0]); $j++) {

		if($TM_array[$j][5] == 0) { //not yet inspected	
			if($TM_array[$i][2] == $TM_array[$j][2]) { //duplicate source

				$TM_array[$j][5] = $i; //duplicate occurance of this source, set to value of original seq no.

			}
		}

		if($TM_array[$j][6] == 0) { //not yet inspected	
			if($TM_array[$i][4] == $TM_array[$j][4]) { //duplicate target

				$TM_array[$j][6] = $i; //duplicate occurance of this target, set to value of original seq no.

			}
		}
	}
}


//Step 2: Look for duplicate entries
$duplicateCount = 0;
echo '<br><br><br><br> Duplicate entries -------------------------------------------------------------><br><br>';

for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] == $TM_array[$i][6]) { //matching source and target
		if($TM_array[$i][0] != $TM_array[$i][5]) { //seq. != source and target, so must be a duplicate	
			$TM_array[$i][7] = $TM_array[$i][5]; //duplicate. place original seq. no of pair in [7]
			$duplicateCount++;

			$orig = $TM_array[$i][5]; // seq no of original entry
			echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  <b>'.$TM_array[$i][2].'</b>  '.$TM_array[$i][3].'  <b>'.$TM_array[$i][4].'</b> <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  <b>'.$TM_array[$orig][2].'</b>  '.$TM_array[$orig][3].'  <b>'.$TM_array[$orig][4].'</b>';

		}
	}
}


//Step 3: Look for Cat 2 entries (many to 1)
$cat2Count = 0;
echo '<br><br><br><br> CATEGORY 2 Inconsistencies (Many to 1)-----------------------------------------><br><br>';
for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] > $TM_array[$i][6]) { //source > target
		$TM_array[$i][8] = 1; //duplicate. place original seq. no of pair in [8]
		$cat2Count++;
		
		$orig = $TM_array[$i][6]; // seq no of original entry
		echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  <b>'.$TM_array[$i][2].'</b>  '.$TM_array[$i][3].'  '.$TM_array[$i][4].' <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  <b>'.$TM_array[$orig][2].'</b>  '.$TM_array[$orig][3].'  '.$TM_array[$orig][4];

	}
}


//Step 4: Look for Cat 3 entries (1 to many)
$cat3Count = 0;
echo '<br><br><br><br> CATEGORY 3 Inconsistencies (1 to many)-----------------------------------------><br><br>';
for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] < $TM_array[$i][6]) { //source < target
		$TM_array[$i][9] = 1; //duplicate. place original seq. no of pair in [9]
		$cat3Count++;

		$orig = $TM_array[$i][5]; // seq no of original entry
		echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  '.$TM_array[$i][2].'  '.$TM_array[$i][3].'  <b>'.$TM_array[$i][4].'</b> <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  '.$TM_array[$orig][2].'  '.$TM_array[$orig][3].'  <b>'.$TM_array[$orig][4].'</b>';
	}
}


//IOK Testing: print out file for testing
//echo '<br><br>Status so far... <br>';
//for($i = 1; isset($TM_array[$i][0]); $i++) {
//	echo '<br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  '.$TM_array[$i][2].'  '.$TM_array[$i][3].'  '.$TM_array[$i][4].'  '.$TM_array[$i][5].'  '.$TM_array[$i][6].'  '.$TM_array[$i][7].'  '.$TM_array[$i][8].'  '.$TM_array[$i][9];
//}



$results = 'File type: XLIFF <br> Number of entries: '.$totalCount.' <br> Duplicate entries: '.$duplicateCount.' <br> Cat 2 inconsistency total: '.$cat2Count.' <br> Cat 3 inconsistency total: '.$cat3Count;





//Save results to file
return $results;
}


//***********************************************************************************************************
//This function will be for handling tab-delimited text files...
function checkTM_TXT($file){
$a = $file;

$a = str_replace( array( "\r\n" , "\t" ) , array( "[NEW*LINE]" , "[tAbul*Ator]" ) , $a );

$count = 0;
	foreach( explode( "[NEW*LINE]" , $a ) AS $lines ) {
		$count++;
		$TM_array[$count][0] = $count;
		$TM_array[$count][1] = "?";
		$TM_array[$count][3] = "?";
		$TM_array[$count][5] = 0; //location of first iteration of source
		$TM_array[$count][6] = 0; //location of first iteration of target
		$TM_array[$count][7] = 0; //location of original pair (if duplicate), else 0
		$TM_array[$count][8] = 0;
		$TM_array[$count][9] = 0;

		$tabCount = 0;
		foreach( explode( "[tAbul*Ator]" , $lines ) AS $li ) {
			$tabCount++;
			if($tabCount == 1) {
				$TM_array[$count][2] = $li;
			}
			if($tabCount == 2) {
				$TM_array[$count][4] = $li;
			}
		}
	}
	
//Step 1: Isolate duplicate sources - 
//	once complete, if $TM_array[$i][0] == $TM_array[$i][5] not a duplicate source
//  if $TM_array[$i][0] != $TM_array[$i][5] then $TM_array[$i][5] holds seq. of the first occurance, and $i is a duplicate source
for($i = 1; isset($TM_array[$i][0]); $i++) {
$totalCount = $i;
	if($TM_array[$i][5] == 0) { //not yet inspected	
		$TM_array[$i][5] = $i; //first occurance of this source, set to value of seq no.
		}

	if($TM_array[$i][6] == 0) { //not yet inspected	
		$TM_array[$i][6] = $i; //first occurance of this target, set to value of seq no.
		}

	for($j = $i+1; isset($TM_array[$j][0]); $j++) {

		if($TM_array[$j][5] == 0) { //not yet inspected	
			if($TM_array[$i][2] == $TM_array[$j][2]) { //duplicate source

				$TM_array[$j][5] = $i; //duplicate occurance of this source, set to value of original seq no.

			}
		}

		if($TM_array[$j][6] == 0) { //not yet inspected	
			if($TM_array[$i][4] == $TM_array[$j][4]) { //duplicate target

				$TM_array[$j][6] = $i; //duplicate occurance of this target, set to value of original seq no.

			}
		}
	}
}


//Step 2: Look for duplicate entries
$duplicateCount = 0;
echo '<br><br><br><br> Duplicate entries -------------------------------------------------------------><br><br>';

for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] == $TM_array[$i][6]) { //matching source and target
		if($TM_array[$i][0] != $TM_array[$i][5]) { //seq. != source and target, so must be a duplicate	
			$TM_array[$i][7] = $TM_array[$i][5]; //duplicate. place original seq. no of pair in [7]
			$duplicateCount++;

			$orig = $TM_array[$i][5]; // seq no of original entry
			echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  <b>'.$TM_array[$i][2].'</b>  '.$TM_array[$i][3].'  <b>'.$TM_array[$i][4].'</b> <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  <b>'.$TM_array[$orig][2].'</b>  '.$TM_array[$orig][3].'  <b>'.$TM_array[$orig][4].'</b>';

		}
	}
}


//Step 3: Look for Cat 2 entries (many to 1)
$cat2Count = 0;
echo '<br><br><br><br> CATEGORY 2 Inconsistencies (Many to 1)-----------------------------------------><br><br>';
for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] > $TM_array[$i][6]) { //source > target
		$TM_array[$i][8] = 1; //duplicate. place original seq. no of pair in [8]
		$cat2Count++;
		
		$orig = $TM_array[$i][6]; // seq no of original entry
		echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  <b>'.$TM_array[$i][2].'</b>  '.$TM_array[$i][3].'  '.$TM_array[$i][4].' <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  <b>'.$TM_array[$orig][2].'</b>  '.$TM_array[$orig][3].'  '.$TM_array[$orig][4];

	}
}


//Step 4: Look for Cat 3 entries (1 to many)
$cat3Count = 0;
echo '<br><br><br><br> CATEGORY 3 Inconsistencies (1 to many)-----------------------------------------><br><br>';
for($i = 1; isset($TM_array[$i][0]); $i++) {
	if($TM_array[$i][5] < $TM_array[$i][6]) { //source < target
		$TM_array[$i][9] = 1; //duplicate. place original seq. no of pair in [9]
		$cat3Count++;

		$orig = $TM_array[$i][5]; // seq no of original entry
		echo '<br><br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  '.$TM_array[$i][2].'  '.$TM_array[$i][3].'  <b>'.$TM_array[$i][4].'</b> <br> ==> Original entry:<br>'.$TM_array[$orig][0].'  '.$TM_array[$orig][1].'  '.$TM_array[$orig][2].'  '.$TM_array[$orig][3].'  <b>'.$TM_array[$orig][4].'</b>';
	}
}


//IOK Testing: print out file for testing
//echo '<br><br>Status so far... <br>';
//for($i = 1; isset($TM_array[$i][0]); $i++) {
//	echo '<br>'.$TM_array[$i][0].'  '.$TM_array[$i][1].'  '.$TM_array[$i][2].'  '.$TM_array[$i][3].'  '.$TM_array[$i][4].'  '.$TM_array[$i][5].'  '.$TM_array[$i][6].'  '.$TM_array[$i][7].'  '.$TM_array[$i][8].'  '.$TM_array[$i][9];
//}



$results = 'File type: TXT <br> Number of entries: '.$totalCount.' <br> Duplicate entries: '.$duplicateCount.' <br> Cat 2 inconsistency total: '.$cat2Count.' <br> Cat 3 inconsistency total: '.$cat3Count;





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