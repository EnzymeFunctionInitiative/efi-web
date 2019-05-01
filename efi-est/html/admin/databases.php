<?php

include_once '../includes/stats_main.inc.php';
include_once '../includes/stats_admin_header.inc.php';


if (isset($_POST['add_database'])) {
	
	$result = functions::add_database($db,$_POST['db_date'],$_POST['db_interpro'],$_POST['db_unipro'],$_POST['db_default']);

	$message = "<div class='alert alert-success' role='alert'>" . $result['MESSAGE'] . "</div>";

}
elseif (isset($_POST['clear_form'])) { 

	unset($_POST);

}

$databases = functions::get_databases($db);
$databases_html = "";
if (count($databases)) {

	foreach ($databases as $database) {
		$databases_html .= "<tr>";
		$databases_html .= "<td>" . $database['db_version_date'] . "</td>";
		$databases_html .= "<td>" . $database['db_version_interpro'] . "</td>";
		$databases_html .= "<td>" . $database['db_version_unipro'] . "</td>";
		$databases_html .= "<td>" . $database['db_version_created'] . "</td>";
		$databases_html .= "<td>efidb/" . $database['db_version_date'] . "</td>";
		if ($database['db_version_default']) {
			$databases_html .= "<td><span class='glyphicon glyphicon-ok'></span></td>";
		}
		else {
			$databases_html .= "<td>&nbsp</td>";
		}
		$databases_html .= "</tr>";


	}
}


?>


<h3>Available Databases</h3>
<table class='table table-condensed table-bordered span8'>
<tr>
	<th>EFI-EST Date</th>
	<th>InterPro Version</th>
	<th>Uniprot Version</th>
	<th>Date Added</th>
	<th>Module Name</th>
	<th>Default</th>
</tr>

<?php echo $databases_html; ?>
</table>

<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<div class='form-group'>
	<label for='db_date'>EFI-EST Date</label>
	<input type='text' name='db_date' class='form-control' id='db_date'>
</div>
<div class='form-group'>
	<label for='db_interpro'>InterPro Version</label>
	<input type='text' name='db_interpro' class='form-control' id='db_interpro'>
</div>
<div class='form-group'>
	<label for='db_unipro'>UniProt Version</label>
	<input type='text' name='db_unipro' class='form-control' id='db_unipro'>
</div>
<div class='form-group'>
	<label>Make Default: <input type='checkbox' name='db_default' id='db_default'></label>
</div>

<input type='submit' name='add_database' class='btn btn-primary' value='Add Database'>
<input type='submit' name='clear_form' class='btn btn-warning' value='Cancel'>


</form>
<p>
<?php if (isset($message)) { echo $message; } ?>

<?php include_once '../includes/stats_footer.inc.php' ?>
