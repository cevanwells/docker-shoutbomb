#!/usr/bin/env php
<?php
//this is the file from Shoutbomb, LLC
$csvFileUsers = $_ENV['APP_DIR'].'/config/registered_users.csv';
$registered_users = array();
//read the file into an array
$file_handle = fopen($csvFileUsers, 'r');
while (!feof($file_handle) ) {
	$tmparr = fgetcsv($file_handle, 1024);
	if(is_array($tmparr)){
		$registered_users = array_merge($registered_users,$tmparr);
	}
}
fclose($file_handle);

// open the $inFile for reading
$inFile = fopen($argv[1], 'r');

// determine type of file we are processing
// based on the filename
$type = get_file_type($argv[1]);

// open the $outFile for writing
$outFile = fopen(generateFileName($type, $_ENV['APP_DIR']."/outbox/"),'a');
if(!isset($outFile)){
	exit(0); //stop program and fix the file directory...
}
$notice = array();
if($inFile != FALSE){
	while (!feof($inFile) ) {
		$notice = fgetcsv($inFile, 0, "|");
		//need to clean up the ID to reflect Shoutbomb format... first make sure only numbers and then on the first 7 digits
		if(is_array($notice)){
			$patronShortID = normalize_patron_id($type, $notice);
			//now see if the patron is in the array of Shoutbomb registered patrons... if so write to new file, otherwise move on
			if(in_array($patronShortID, $registered_users)){
				write_notice_line($type, $notice, $outFile);
			}
		}
	}
}

// close both $outFile and $inFile
if(isset($inFile)){
	fclose($inFile);
}
if(isset($outFile)){
	fclose($outFile);
}

function write_notice_line($type, $line, $file) {
	$out = '';
	switch ($type) {
		case 'holds':
			$out = $line[0]."|".$line[1]."|".$line[2]."|".$line[3]."|".$line[4]."|".$line[5]."|".$line[6]."|".$line[7]."\r\n";;
			break;
		case 'renew':
		case 'overdue':
			$out = $line[0]."|".$line[1]."|".$line[2]."|".$line[3]."|".$line[4]."|".$line[5]."|".
				   $line[6]."|".$line[7]."|".$line[8]."|".$line[9]."|".$line[10]."|".$line[11]."|".$line[12]."\r\n";
			break;
	}

	fwrite($file,$out);
}

function generateFileName($info,$type) {
	$localvar = array($info,get_unique_id(5,10),date('Y-m-d_H_i_s'));
	$thisIsTheName = implode("_",$localvar);
	$thisIsTheName = $type.$thisIsTheName.".txt";
	return $thisIsTheName;
}
function get_unique_id($lstartwhere = 0, $lhowmuch = 32) {
	return substr ( md5 ( uniqid ( mt_rand ( 15, 15 ), true ) ), $lstartwhere, $lhowmuch );
}

function normalize_patron_id($type, $line) {
	$patronId = '';
	switch ($type) {
		case 'holds':
			$patronId = $line[3];
			break;
		case 'renew':
		case 'overdue':
			$patronId = $line[0];
			break;
	}
	$normalizedPatronId = trim(preg_replace('/[^0-9]/', '', $patronId)); // pls_short_id
	return substr($normalizedPatronId, 0, 7);
}

function get_file_type($fileName) {
	$fileName = basename($fileName, '.txt');
	return trim(preg_replace('/\d*/', '', $fileName));
}
?>
