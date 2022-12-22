<?php
require_once(__DIR__."/../../init.php");

use \efi\sanitize;


$seq = sanitize::get_sanitize_seq("blast");

if ($seq) {
	$search = array("\r\n","\r","\t"," ");
	$replace = "";
	$formatted_blast = str_ireplace($search, $replace, $seq);
	$width = 80;
    $break = "<br>";
    $cut = true;
	echo wordwrap($formatted_blast, $width, $break, $cut);

}


