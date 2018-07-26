<?php

function time_mark($marked_words,$status){
	$time_now = date("Y-m-d H:i:s");
	$print_words = "***************".$marked_words."**********";
	$print_time = "***************".$time_now."**********";
	echo PHP_EOL;
	echo colorize($print_words,$status).PHP_EOL;
	echo colorize("***************",$status).PHP_EOL;
	echo colorize($print_time,$status).PHP_EOL;
	echo PHP_EOL;
	return $time_now;
}

function mark($marked_words,$status){
	$print_words = "|=|-----".$marked_words;
	echo colorize($print_words,$status).PHP_EOL;
}

function note_mark($marked_key,$marked_value){
	$len_str=strlen($marked_key);
	$len_empty= 20-$len_str;
	$i=0;
	
	echo colorize("|+|".$marked_key,"NOTE");
	while($i < $len_empty){
		$i+=1;
		echo " ";
	}
	echo colorize(":".$marked_value,"NOTE").PHP_EOL;
}




function empty_mark($num){
	$i=0;
	while($i<$num){
		echo PHP_EOL;
		$i+=1;
	}
}

function colorize($text,$status){
	$out="";
	switch($status){
	case"SUCCESS":
		$out="[32m";//Greenbackground
		break;
	case"FAILURE":
		$out="[31m";//Redbackground
		break;
	case"WARNING":
		$out="[33m";//Yellowbackground
		break;
	case"NOTE":
		$out="[34m";//Bluebackground
		break;
	default:
		throw new Exception("Invalidstatus:".$status);
	}
	return chr(27)."$out"."$text".chr(27)."[0m";
}

?>
