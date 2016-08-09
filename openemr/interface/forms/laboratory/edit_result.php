<?php
/** **************************************************************************
 *	LABORATORY/EDIT_RESULT.PHP
 *
 *	Copyright (c)2014 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package mdts
 *  @subpackage laboratory
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
require_once("../../../interface/globals.php");
include_once("$srcdir/wmt/wmt.include.php");
include_once("$srcdir/wmt/wmt.class.php");

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

// form information
$form_name = 'procedure_result';
$form_title = 'Add/Edit Results';

// grab inportant stuff
$order_id = $_REQUEST['order'];
$item_id = $_REQUEST['item'];
$pid = ($_REQUEST['pid'])? $_REQUEST['pid']: $_SESSION['pid'];
$encounter = ($_REQUEST['enc'])? $_REQUEST['enc']: $_SESSION['encounter'];
$task = ($_REQUEST['task'])? $_REQUEST['task']: 'edit';

$order_data = new wmtForm('procedure_order',$order_id);
$item_data = new wmtForm('procedure_order_item',$item_id);

$results = array();
$query = "SELECT * FROM procedure_type ";
$query .= "WHERE parent = ? AND (procedure_type = 'res' || procedure_type = 'rec') ";
$query .= "ORDER BY seq";
$result = sqlStatement($query,array($item_data->test_id));
while ($record = sqlFetchArray($result)) $results[] = $record;

// If we are saving, then save and close the window.
if ($task == 'edit' && $_POST['form_save']) {
	$form_begin = fixDate($_POST['form_begin'], '');
	$form_end   = fixDate($_POST['form_end'], '');

	$form_data->date = date('Y-m-d H:i:s');
	$form_data->type = 'medical_problem';
	$form_data->title = formData('form_title');
	$form_data->begdate = $form_begin;
	$form_data->enddate = $form_end;
	$form_data->occurrence = formData('form_occurrence');
	$form_data->referredby = formData('form_referredby');
	$form_data->extrainfo = formData('form_description');
	$form_data->diagnosis = formData('form_diagnosis');
	$form_data->activity = 1;
	$form_data->comments = formData('form_comments');
	$form_data->pid = $pid;
	$form_data->user = $_SESSION['authId'];
	$form_data->outcome = formData('form_outcome');
	$form_data->destination = formData('form_destination');
	 
	if ($form_data->id) {
		$form_data->update();
	}
	else {
		$id = wmtIssue::insert($form_data);
		wmtIssue::linkEncounter($pid, $encounter, $id);
	}

	// Close this window and redisplay the updated list of issues.
	echo "<html><body><script language='JavaScript'>\n";
	echo " var myboss = opener ? opener : parent;\n";
	echo " if (myboss.refreshIssue) myboss.refreshIssue($id,'$tmp_title');\n";
	echo " else if (myboss.reloadIssues) myboss.reloadIssues();\n";
	echo " else myboss.location.reload();\n";
	echo " if (parent.$ && parent.$.fancybox) parent.$.fancybox.close();\n";
	echo " else window.close();\n";
	echo "</script></body></html>\n";
	exit();
}

// If we are unlinking.
if ($task == 'unlink') {
	if ($form_data->id) {
		wmtIssue::unlinkEncounter($pid, $encounter, $id);
	}

	// Close this window and redisplay the updated list of issues.
	echo "<html><body><script language='JavaScript'>\n";
	echo " var myboss = opener ? opener : parent;\n";
	echo " if (myboss.refreshIssue) myboss.refreshIssue($id,'$tmp_title');\n";
	echo " else if (myboss.reloadIssues) myboss.reloadIssues();\n";
	echo " else myboss.location.reload();\n";
	echo " if (parent.$ && parent.$.fancybox) parent.$.fancybox.close();\n";
	echo " else window.close();\n";
	echo "</script></body></html>\n";
	exit();
}


?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php html_header_show();?>
		<title><?php echo $form_title ?> for <?php echo $pat_data->format_name; ?> on <?php echo $form_data->date; ?></title>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<!-- link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmt.default.css" / -->

		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.4.3.min.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmtstandard.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
		
		<!-- pop up calendar -->
		<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
		<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
		
		<script>
			// This is for callback by the find-code popup.
			function set_related(codetype, code, selector, codedesc) {
				var f = document.forms[0];
				var d = '';
				var s = '';
				if (code) {
					s = codetype + ':' + code;
					d = codedesc;
				} 
				else {
					s = '';
					d = '';
				}
				f.form_diagnosis.value = s;
				f.form_description.value = d;
			}

			// This invokes the find-code popup.
			function sel_diagnosis() {
				dlgopen('<?php echo $GLOBALS['webroot'] ?>/interface/patient_file/encounter/find_code_popup.php?codetype=ICD9', '_blank', 500, 400);
			}

			// Process click on Delete link.
			function deleteme() {
				dlgopen('<?php echo $GLOBALS['webroot'] ?>/interface/patient_file/deleter.php?issue=<?php echo attr($issue) ?>', '_blank', 500, 450);
			  return false;
			}

			// Process click on Delete link.
			function unlinkme() {
				location.href='edit_issue.php?task=unlink&issue=<?php echo attr($issue); ?>&pid=<?php echo attr($pid); ?>&enc=<?php echo attr($encounter) ?>';
			  return false;
			}

			// Called by the deleteme.php window on a successful delete.
			function imdeleted() {
				closeme();
			}

			function closeme() {
			    if (parent.$) {
				    parent.reloadIssues();
				    parent.$.fancybox.close();
			    }
			    window.close();
			 }

			// Check for errors when the form is submitted.
			function validate() {
				var f = document.forms[0];
				if (!f.form_title.value || !f.form_diagnosis.value) {
					alert("<?php echo addslashes(xl('Please enter a title and diagnosis!!')); ?>");
					return false;
				}
				top.restoreSession();
				return true;
			}

			 $(document).ready(function(){

			    $('#common_diagnosis').change(function(){
					notes = $('option:selected',this).attr('notes');
					$('#form_comments').val(notes);
					codes = $('option:selected',this).val();
					$('#form_diagnosis').val(codes);
					titles = $('option:selected',this).text();
					$('#form_title').val(titles);
					short = $('option:selected',this).attr('short');
					$('#form_description').val(short);
					$('#form_begin').val('<?php echo date('Y-m-d') ?>');
			    });
			});
		</script>
	</head>

	<body class="body_top" style="margin:auto;width:635px">
		<form method='post' name='theform'  style=""
			 action='edit_issue.php?issue=<?php echo attr($issue); ?>&pid=<?php echo attr($pid); ?>&enc=<?php echo attr($encounter); ?>'
			 onsubmit='return validate()'>

			<table style="margin:10px auto;width:100%">
				<tr>
					<td class="wmtLabel" colspan="4" style="font-size:16px;">
						<u>Add/Edit Procedure Results</u>
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel" style="width:80px">Status:</td>
					<td class="wmtData" style="width:30%">
						<select class="wmtInput" name="form_occurrence">
							<?php echo ListSel($form_data->occurrence,'proc_res_status') ?>
						</select>
					</td>
					<td class="wmtLabel">Date:</td>
					<td class="wmtData" style="width:30%;white-space:nowrap">
						<input class="wmtInput" type='text' size='10' name='form_begin' id='form_begin'
							value='<?php if ($form_data->begdate) echo date('Y-m-d',strtotime($form_data->begdate)) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd begin date or onset of problem'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_begin' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
					<td class="wmtLabel" style="width:80px">Clinician:</td>
					<td class="wmtData" style="width:30%">
						<select class="wmtSelect" name='request_provider' id='request_provider' style="min-width:150px">
							<option value='_blank'>-- select --</option>
<?php 
	$rlist= sqlStatement("SELECT * FROM users WHERE active=1 AND facility_id > 0 ORDER BY lname");
	while ($rrow= sqlFetchArray($rlist)) {
    	echo "<option value='" . $rrow['id'] . "'";
		if ($form_data->request_provider == $rrow['id']) echo " selected";
		if (!$form_data->request_provider && $_SESSION['authUser'] == $rrow['username']) echo " selected";
		echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
    	echo "</option>";
  	}
?>
						</select>
					</td>
				</tr>
				<?php /*?>
				<tr>
					<td class="wmtLabel">Referred By:</td>
					<td class="wmtData" colspan="3">
						<input class="wmtFullInput" type="text" name="form_referredby" value="<?php echo $form_data->referredby ?>" />
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel">End Date:</td>
					<td class="wmtData">
						<!-- input class="wmtInput" type="text"  style="width:70px" /-->
						<input class="wmtInput" type='text' size='10' name='form_end' id='form_end'
							value='<?php if ($form_data->enddate) echo date('Y-m-d',strtotime($form_data->enddate)) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd end date or date resolved'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_end' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
					<td class="wmtLabel">Outcome:</td>
					<td class="wmtData">
						<select class="wmtFullInput" name="form_outcome">
							<?php echo ListSel($form_data->outcome,'outcome') ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Destination:</td>
					<td class="wmtData" colspan="3">
						<input class="wmtFullInput" type="text" name="form_destination" value="<?php echo $form_data->destination ?>" />
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel" colspan="4">
						Plan Of Care:<br/>
						<textarea class="wmtFullInput" rows="7" name="form_comments" id="form_comments" style="resize:none"><?php echo $form_data->comments ?></textarea>
					</td>
				</tr>
				<?php */ ?>
			</table>
			<center>
				<?php if ($issue) { ?>
				<input type='submit' name='form_save' value='<?php echo xla('Update'); ?>' />
				&nbsp;
				<input type='button' value='<?php echo xla('Unlink'); ?>' onclick='unlinkme()' />
				<?php } else { ?>
				<input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />
				<?php } ?>
				<?php if ($issue && acl_check('admin', 'super')) { ?>
					&nbsp;
					<input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme()' />
				<?php } ?>
					&nbsp;
					<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
			</center>
		</form>
		
		<script language='JavaScript'>
			 Calendar.setup({inputField:"form_begin", ifFormat:"%Y-%m-%d", button:"img_begin"});
			 Calendar.setup({inputField:"form_end", ifFormat:"%Y-%m-%d", button:"img_end"});
		</script>
	</body>
</html>