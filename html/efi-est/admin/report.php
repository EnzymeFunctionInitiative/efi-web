<?php
require_once(__DIR__."/inc/stats_main.inc.php");
require_once(__EST_DIR__."/libs/efi_statistics.class.inc.php");
require_once(__EST_DIR__."/libs/report.class.inc.php");

if (isset($_POST['create_user_report'])) {

	$type = $_POST['report_type'];
	$data = efi_statistics::get_unique_users($db);
	$filename = "unique_users." . $type; 
}

if (isset($_POST['create_job_report'])) {

	$type = $_POST['report_type'];
	$month = $_POST['month'];
	$year = $_POST['year'];
	$data =	efi_statistics::get_jobs($db,$month,$year);
	$filename = "job_report_" . $month . "-" . $year . "." . $type;	
}


switch ($type) {
	case 'csv':
		report::create_csv_report($data,$filename);
		break;
	case 'xls':
		report::create_excel_2003_report($data,$filename);
		break;
	case 'xlsx':
		report::create_excel_2007_report($data,$filename);
		break;
}

?>
