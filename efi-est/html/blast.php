<?php

if (isset($_GET['blast'])) {
	$search = array("\r\n","\r","\t"," ");
	$replace = "";
	$formatted_blast = str_ireplace($search,$replace,$_GET['blast']);
	$width = 80;
        $break = "<br>";
        $cut = true;
	echo wordwrap($formatted_blast,$width,$break,$cut);

}
