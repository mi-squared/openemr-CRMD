<?php
/** **************************************************************************
 *	LABORATORY/LAB_ORPHANS.PHP
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
 *  @package laboratory
 *  @subpackage reports
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *	@uses laboratory/common.php
 *  @uses quest/common.php
 *  @uses labcorp/common.php
 *
 *************************************************************************** */
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/wmt/wmt.include.php";

// report defaults
$report_title = 'Orphan Results';
$result_name = 'procedure_result';
$item_name = 'procedure_result_item';
$order_name = 'procedure_order';

// For each sorting option, specify the ORDER BY argument.
$ORDERHASH = array(
		'doctor'  => 'lower(doc_lname), lower(doc_fname), date_ordered',
		'patient' => 'lower(pat_lname), lower(pat_fname), date_ordered',
		'order'  => 'cast(order_number as unsigned), date_ordered',
		'time'    => 'date_ordered, lower(doc_lname), lower(doc_fname)',
		'lab'     => 'lower(lab_name), date_ordered',
		'status'    => 'pid, date_ordered, lower(doc_lname), lower(doc_fname)',
);

// get date range
$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$form_from_date = ($_POST['form_from_date']) ? $_POST['form_from_date'] : date('Y-m-d', $last_month);
$form_to_date = ($_POST['form_to_date']) ? $_POST['form_to_date'] : date('Y-m-d');
$form_provider  = $_POST['form_provider'];
$form_facility  = $_POST['form_facility'];
$form_status  = $_POST['form_status'];
$form_name      = $_POST['form_name'];
$form_lab	= $_POST['form_lab'];
$form_ignore = $_POST['form_ignore']; // there was a request to ignore an order
$form_refresh = ($_POST['form_refresh'] || $_POST['form_orderby'])? true: false;

// hide a result
if ($form_ignore) {
	$key = $form_ignore;
	
	if (strpos($form_ignore,'laboratory') !== false) {
		$lab = 'laboratory';
		$key = str_replace('laboratory', '', $form_ignore);
	}
		
	if (strpos($form_ignore,'quest') !== false) {
		$lab = 'quest';
		$key = str_replace('quest', '', $form_ignore);
	}

	if (strpos($form_ignore,'labcorp') !== false) {
		$lab = 'labcorp';
		$key = str_replace('labcorp', '', $form_ignore);
	}

	if ($key)
		sqlStatement("UPDATE form_".$lab." SET pid = 999999998 WHERE id = ?",array($key));

	$form_ignore = '';
	$form_refresh = true;
}

// get sort order
$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

// retrieve records
$query = '';
$query1 = $query2 = $query3 = '';

$orders = array();
$results = false;

// which interfaces are active?
$generic = mysql_query("SELECT 1 FROM `form_laboratory`");

$lab = sqlQuery("SELECT ppid FROM procedure_providers WHERE name LIKE ?",array('%quest%'));
$quest = ($lab['ppid'])?$lab['ppid']:false;

$lab = sqlQuery("SELECT ppid FROM procedure_providers WHERE name LIKE ? AND protocol != 'INT'",array('%labcorp%'));
$labcorp = ($lab['ppid'])?$lab['ppid']:false;

// generate sql query
if ($generic !== false && (!$form_lab || ($form_lab != $quest && $form_lab != $labcorp))) {
	$query1 = "SELECT 'laboratory' AS type, fo.id, fo.status, fo.pid, fo.pat_lname, fo.pat_mname, fo.pat_fname, fo.order_number, fo.result_doc_id, ";
	$query1 .= "po.provider_id, po.date_ordered, fo.doc_lname, fo.doc_fname, fo.doc_mname, fo.doc_npi, fo.facility_id, pp.name AS lab_name FROM form_laboratory fo ";
	$query1 .= "LEFT JOIN procedure_order po ON fo.order_number = po.procedure_order_id ";
	$query1 .= "LEFT JOIN users u ON u.id = po.provider_id ";
	$query1 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
	$query1 .= "WHERE pid > '999999995' ";
	if ($form_lab) $query1 .= "AND po.lab_id = $form_lab ";
}

if ($quest !== false && (!$form_lab || $form_lab == $quest)) {
	$query2 = "SELECT 'quest' AS type, fo.id, fo.status, fo.pid, fo.pat_lname, fo.pat_mname, fo.pat_fname, fo.order_number, fo.result_doc_id, ";
	$query2 .= "po.provider_id, po.date_ordered, fo.doc_lname, fo.doc_fname, fo.doc_mname, fo.doc_npi, fo.facility_id, pp.name AS lab_name FROM form_quest fo ";
	$query2 .= "LEFT JOIN procedure_order po ON fo.order_number = po.procedure_order_id ";
	$query2 .= "LEFT JOIN users u ON u.id = po.provider_id ";
	$query2 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
	$query2 .= "WHERE pid > '999999995' ";
}

if ($labcorp !== false && (!$form_lab || $form_lab == $labcorp)) {
	$query3 = "SELECT 'labcorp' AS type, fo.id, fo.status, fo.pid, fo.pat_lname, fo.pat_mname, fo.pat_fname, fo.order_number, fo.result_doc_id, ";
	$query3 .= "po.provider_id, po.date_ordered, fo.doc_lname, fo.doc_fname, fo.doc_mname, fo.doc_npi, fo.facility_id, pp.name AS lab_name FROM form_labcorp fo ";
	$query3 .= "LEFT JOIN procedure_order po ON fo.order_number = po.procedure_order_id ";
	$query3 .= "LEFT JOIN users u ON u.id = po.provider_id ";
	$query3 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
	$query3 .= "WHERE pid > '999999995' ";
}

if ($query1) { 
	$query .= "(" . $query1 .")";
}
if ($query2) {
	if ($query) $query .= " UNION ";
	$query .= "(" . $query2 . ")";
}
if ($query3) {
	if ($query) $query .= " UNION ";
	$query .= "(" . $query3 . ")";
}
$query = 'SELECT * FROM ( ' .$query. ' ) AS results WHERE 1 '; ;

if ($form_from_date) {
	if (!$form_to_date) $form_to_date = date('Y-m-d');
	$query .= "AND date_ordered >= '$form_from_date 00:00:00' AND date_ordered <= '$form_to_date 23:59:59' ";
}
if ($form_provider) {
	$query .= "AND provider_id = '$form_provider' ";
}
if ($form_facility) {
	$query .= "AND facility_id = '".$form_facility."' ";
}
if (!$form_status) {
	$query .= "AND pid = '999999999' ";
}
$query .= "ORDER BY $orderby";

// skip search first time
$res = false;
if ($form_refresh) $res = sqlStatement($query);
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Orphan Lab Results','e'); ?></title>

<style type="text/css">
@import url(../../../library/dynarch_calendar.css);
</style>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">

/* specifically include & exclude from printing */
@media print {
	#report_parameters {
		visibility: hidden;
		display: none;
	}
	#report_parameters_daterange {
		visibility: visible;
		display: inline;
	}
	#report_results table {
		margin-top: 0px;
	}
}

/* specifically exclude some from the screen */
@media screen {
	#report_parameters_daterange {
		visibility: hidden;
		display: none;
	}
}

#report_results table td {
	vertical-align: middle;
}
</style>

<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function refreshme() {
	 document.forms[0].form_ignore.value = '';
	document.forms[0].submit();
 }

 function doignore(type,id) {
	 	document.forms[0].form_ignore.value = type + id;
		document.forms[0].submit();
	 }

 function dosearch(type,id) {
	 url = "<?php echo $webroot ?>/interface/forms/" + type + "/link_result.php?id=" + id;
	 dlgopen(url, 'search', 800, 500);
 }

 function showdoc(pid,docid) {
	 location.href="<?php echo $webroot ?>/controller.php?document&retrieve&patient_id="+pid+"&document_id=" + docid;
 }

 
</script>

</head>
<body class="body_top" style="min-width: 900px">
	<!-- Required for the popup date selectors -->
	<div id="overDiv"
		style="position: absolute; visibility: hidden; z-index: 1000;"></div>

	<span class='title'><?php xl('Report','e'); ?> - <?php xl('Orphan Results','e'); ?>
	</span>

	<div id="report_parameters_daterange">
		<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
	</div>

	<form method='post' name='theform' id='theform'
		action='lab_orphans.php'>
		<input type='hidden' name='form_ignore' id='form_ignore' value='' />
		<div id="report_parameters">
			<table>
				<tr>
					<td width='auto'>
						<div style='float: left'>

							<table class='text'>
								<tr>
									<td class='label'><?php xl('Facility','e'); ?>:</td>
									<td><?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?>
									</td>
									<td class='label'><?php xl('Provider','e'); ?>:</td>
									<td><?php
									// Build a drop-down list of providers.
									$query = "SELECT id, username, lname, fname FROM users WHERE authorized ".
											"= 1 AND active = 1 $provider_facility_filter ORDER BY lname, fname";
									$ures = sqlStatement($query);

									echo "   <select name='form_provider'>\n";
									echo "    <option value=''>-- " . xl('All') . " --\n";

									while ($urow = sqlFetchArray($ures)) {
										$provid = $urow['id'];
										echo "    <option value='$provid'";
										if ($provid == $_POST['form_provider']) echo " selected";
										echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
									}
									echo "   </select>\n";
									?>
									</td>
									<td class='label'><?php xl('Processor','e'); ?>:</td>
									<td><select id='form_lab' name='form_lab'>
											<option value=''></option>
											<?php 
											$result = sqlStatement("SELECT * FROM procedure_providers WHERE DorP != 'D' AND protocol != 'INT' ORDER BY name");
											while ($processor = sqlFetchArray($result)) {
												echo "<option value='".$processor['ppid']."' ";
												echo ($form_lab == $processor['ppid'])?'selected':'';
												echo ">".$processor['name']."</option>\n";
											}
											?>
									</select>
									</td>
								</tr>
								<tr>
									<td class='label'><?php xl('From','e'); ?>:</td>
									<td><input type='text' name='form_from_date'
										id="form_from_date" size='10'
										value='<?php echo $form_from_date ?>'
										onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
										title='yyyy-mm-dd'> <img src='../../pic/show_calendar.gif'
										align='absbottom' width='24' height='22' id='img_from_date'
										border='0' alt='[?]' style='cursor: pointer'
										title='<?php xl('Click here to choose a date','e'); ?>'></td>
									<td class='label'><?php xl('To','e'); ?>:</td>
									<td><input type='text' name='form_to_date' id="form_to_date"
										size='10' value='<?php echo $form_to_date ?>'
										onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
										title='yyyy-mm-dd'> <img src='../../pic/show_calendar.gif'
										align='absbottom' width='24' height='22' id='img_to_date'
										border='0' alt='[?]' style='cursor: pointer'
										title='<?php xl('Click here to choose a date','e'); ?>'></td>
									<td class='label' style='white-space: nowrap'><?php xl('Include Inactive','e'); ?>:
									</td>
									<td><?php
									// Include hidden records?
									echo "   <input type='checkbox' name='form_status' value='1' ";
									echo ($form_status)?"checked":"";
									echo " />\n";
									?>
									</td>
								</tr>
							</table>

						</div>
					</td>
					<td align='left' valign='middle' height="100%">
						<table style='border-left: 1px solid; width: 100%; height: 100%'>
							<tr>
								<td>
									<div style='margin-left: 15px'>
										<a href='#' class='css_button'
											onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
											<span> <?php xl('Submit','e'); ?>
										</span>
										</a>

										<?php if ($form_refresh ) { ?>
										<a href='#' class='css_button' onclick='window.print()'> <span>
												<?php xl('Print','e'); ?>
										</span>
										</a>
										<?php } ?>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

		</div>
		<!-- end report_parameters -->

		<?php
		if ($form_refresh) {
			?>
		<div id="report_results">
			<table>

				<thead>
					<th><a href="nojs.php" onclick="return dosort('doctor')"
			<?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  xl('Provider','e'); ?>
					</a>
					</th>
					<th><a href="nojs.php" onclick="return dosort('time')"
			<?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Date','e'); ?>
					</a>
					</th>
					<th><a href="nojs.php" onclick="return dosort('patient')"
			<?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?>
					</a>
					</th>
					<th><a href="nojs.php" onclick="return dosort('order')"
			<?php if ($form_orderby == "order") echo " style=\"color:#00cc00\"" ?>><?php  xl('Order','e'); ?>
					</a>
					</th>
					<th><a href="nojs.php" onclick="return dosort('lab')"
			<?php if ($form_orderby == "lab") echo " style=\"color:#00cc00\"" ?>><?php  xl('Processor','e'); ?>
					</a>
					</th>
					<th><a href="nojs.php" onclick="return dosort('status')"
			<?php if ($form_orderby == "status") echo " style=\"color:#00cc00\"" ?>><?php  xl('Status','e'); ?>
					</a>
					</th>
					<th>&nbsp;</th>
				</thead>
				<tbody>
					<?php
					$count = 0;
					if ($res) {
						$lastdocname = "";
						$doc_encounters = 0;
						while ($row = sqlFetchArray($res)) {
							$docname = '<nobr>[ NO PROVIDER ]</nobr>';
							if (!empty($row['doc_lname']) || !empty($row['doc_fname'])) {
								$docname = $row['doc_lname'];
								if (!empty($row['doc_fname']) || !empty($row['doc_mname']))
									$docname .= ', ' . $row['doc_fname'] . ' ' . $row['doc_mname'];
							}
							$errmsg  = "";
							$status = ($row['pid'] == '999999999') ? 'Orphan Active' : 'Orphan Inactive';
							?>
					<tr bgcolor='<?php echo $bgcolor ?>'>
						<td><?php echo ($docname)?$docname:''; ?>&nbsp;</td>
						<td><?php echo oeFormatShortDate(substr($row['date_ordered'], 0, 10)) ?>&nbsp;
						</td>
						<td><?php 
						if ($row['pat_lname']) {
							echo $row['pat_lname'] . ', ' . $row['pat_fname'] . ' ' . $row['pat_mname'];
						}
						else {
							echo "<nobr>[ NO PATIENT DATA ]</nobr>";
						}
						?>
						</td>
						<td><?php echo ($row['order_number']) ? $row['order_number'] : "[ NONE ]"; ?>&nbsp;
						</td>
						<td><?php echo ($row['lab_name']) ? $row['lab_name'] : "[ UNAVAILABLE ]" ?>&nbsp;
						</td>
						<td><?php echo $status; ?>&nbsp;</td>
						<td style="text-align: right"><input tabindex="-1" type="button"
							class="link_submit"
							onclick="dosearch('<?php echo $row['type'] ?>',<?php echo $row['id'] ?>)"
							value=" link " />&nbsp; <?php if ($row['result_doc_id']) { ?> <input
							tabindex="-1" type="button"
							onclick="showdoc(999999999,<?php echo $row['result_doc_id'] ?>)"
							value="view" /> <?php } ?> <input tabindex="-1" type="button"
							class="link_submit"
							onclick="doignore('<?php echo $row['type'] ?>',<?php echo $row['id'] ?>)"
							value="hide" />&nbsp;</td>
					</tr>
					<?php
						$lastdocname = $docname;
						$count++;
						}
					}
					
					if ($count < 1) { // no results 
					?>
					<tr>
						<td colspan="7" style="font-weight:bold;text-align:center;padding:25px">
							NO RECORDS FOUND
						</td>
					</tr>
<?php 
					}
?>	
				</tbody>
			</table>
		</div>
		<!-- end encresults -->
		<?php } else { ?>
		<div class='text'>
			<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
		</div>
		<?php } ?>

		<input type="hidden" name="form_orderby"
			value="<?php echo $form_orderby ?>" /> <input type='hidden'
			name='form_refresh' id='form_refresh' value='' />

	</form>
</body>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
