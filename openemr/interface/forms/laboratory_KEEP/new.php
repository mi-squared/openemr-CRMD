<?php
/** **************************************************************************
 *	LABORATORY/NEW.PHP
 *
 *	Copyright (c)2014 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is free software: you can redistribute it and/or modify it 
 *	under the terms of the GNU General Public License as published by the Free 
 *	Software Foundation, either version 3 of the License, or (at your option) 
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 *	FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 *	more details.
 *
 *	You should have received a copy of the GNU General Public License along with 
 *	this program.  If not, see <http://www.gnu.org/licenses/>.	This program is 
 *	free software; you can redistribute it and/or modify it under the terms of 
 *	the GNU Library General Public License as published by the Free Software 
 *	Foundation; either version 2 of the License, or (at your option) any 
 *	later version.
 *
 *  @package mdts
 *  @subpackage laboratory
 *  @version 2.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *  @uses laborabory/common.php
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/acl.inc");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

/* INITIALIZE FORM DEFAULTS */
$form_name = "laboratory";
$form_title = 'Laboratory Order';
$save_url = $rootdir.'/forms/'.$form_name.'/create.php';
$cancel_url = $rootdir.'/patient_file/encounter/encounter_top.php';

// retrieve providers
$providers = array();
$query = "SELECT * FROM procedure_providers WHERE type = 'laboratory' ORDER BY name ";
$result = sqlStatement($query);
while ($record = sqlFetchArray($result)) {
	if ($record['ppid']) $labs[] = $record;
}
if (count($labs) < 1) die ("FATAL ERROR: laboratory providers have not been created.");

$pop = (!empty($_REQUEST['pop']))? true : false;

if (count($labs) == 1) { // skip selection if only one laboratory
	$lab_id = $labs[0]['ppid'];
	include("create.php");
	exit;
}

?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php html_header_show();?>
		<title><?php echo $form_title; ?></title>

		<link rel="stylesheet" type="text/css" href="<?php echo $css_header;?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmt.default.css" />
				
		<script>
			function saveClicked() {
				var f = document.forms[0];
				if ( f.lab_id.value == '_blank' ) {
					alert('You must select a procedure provider before continuing.');
					return;
				}

				<?php if (!$pop) { ?>top.restoreSession(); <?php } ?>
				f.submit();
 			}
		</script>
	</head>

	<body class="body_top">

		<form method='post' action="<?php echo $save_url ?>" name='<?php echo $form_name; ?>'> 
			<div class="wmtTitle">
				<input type='hidden' name='mode' value='new' />
				<input type='hidden' name='pop' value='<?php if ($pop) echo '1' ?>' />
				<span class='title'>New <?php echo $form_title; ?></span>
			</div>

<!-- BEGIN FORM -->

			<!--  Start of Orders -->
			<div class="wmtMainContainer wmtColorMain">
				<div class="wmtCollapseBar wmtColorBar" id="PreCollapseBar">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">	
						<tr>
							<td>
								&nbsp;
							</td>
							<td class="wmtChapter" style="text-align: center">
								<?php echo $form_title ?>
							</td>
							<td style="text-align: right">
								&nbsp;
							</td>
						</tr>
					</table>
				</div>
			
				<div class="wmtCollapseBox" id="PreBox" style="padding: 40px 20px">
					<table border="0" cellspacing="2" cellpadding="0">
						<tr>
							<td class="wmtLabel" style="width:120px;white-space:nowrap">
								Select Laboratory Provider:
							</td>
						</tr><tr>
							<td class="wmtLabel">
								<select name="lab_id">
									<option value="_blank">-- select --</option>
<?php 
foreach ($labs AS $lab) {
	echo "<option value = '".$lab['ppid']."'>".$lab['name'];
//REMOVED	if ($lab['notes']) echo " (".$lab['notes'].")";
	echo "</option>\n";
}
?>
								</select>
							</td>
							<td style="padding-left:40px">
								<a class="css_button" tabindex="-1" href="javascript:saveClicked()"><span>Continue</span></a>
								<a class="css_button" tabindex="-1" onClick="top.restoreSession()" href="<?php echo $cancel_url ?>"><span>Cancel</span></a>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
		</form>
	</body>

</html>
