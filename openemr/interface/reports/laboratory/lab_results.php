<?php 
/** **************************************************************************
 *	LABORATORY/LAB_RESULTS.PHP
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
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/wmt/wmt.include.php";

// report defaults
$report_title = 'Results Report';
$result_name = 'procedure_result';
$item_name = 'procedure_result_item';
$order_name = 'procedure_order';

// For each sorting option, specify the ORDER BY argument.
$ORDERHASH = array(
	'doctor'  => 'lower(doc_lname), lower(doc_fname), date_ordered DESC',
	'patient' => 'lower(pat_lname), lower(pat_fname), date_ordered DESC',
	'pubpid'  => 'lower(pubpid), date_ordered DESC',
	'billing' => 'lower(request_billing), lower(pubpid), date_ordered DESC',
	'time'    => 'date_ordered DESC, lower(doc_lname), lower(doc_fname)',
	'lab'     => 'lab_name, date_ordered DESC',
	'status'  => 'order_status, date_ordered DESC'
);


// get date range
$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$form_from_date = ($_POST['form_from_date']) ? formData('form_from_date') : date('Y-m-d', $last_month);
$form_to_date = ($_POST['form_to_date']) ? formData('form_to_date') : date('Y-m-d');

// get remaining report parameters
$form_provider  = formData('form_provider');
$form_facility  = formData('form_facility');
$form_status  	= formData('form_status');
$form_name      = formData('form_name');
$form_processor	= formData('form_processor');
$form_handling 	= formData('form_handling');
$form_billing 	= formData('form_billing');

// get sort order
$form_orderby 	= $ORDERHASH[formData('form_orderby')] ? formData('form_orderby') : 'doctor';
$orderby 		= $ORDERHASH[$form_orderby];


// retrieve records
$query = '';
$query1 = $query2 = $query3 = '';

$orders = array();
$results = FALSE;

// which interfaces are active?
$quest = mysql_query("SELECT 1 FROM `form_quest`");
$labcorp = mysql_query("SELECT 1 FROM `form_labcorp`");
$generic = mysql_query("SELECT 1 FROM `form_laboratory`");

if ($_POST['form_refresh'] || $_POST['form_orderby']) {
	$billing = ('none' == $form_billing)? '' : $form_billing;
	if ($generic !== FALSE) {
		$query1 = "SELECT f.formdir, f.form_id, fe.encounter, fe.date AS enc_date, fe.facility_id, fe.reason, ";
		$query1 .= "u.fname AS doc_fname, u.mname AS doc_mname, u.lname AS doc_lname, pp.name AS lab_name, ";
		$query1 .= "fo.id, fo.pid, fo.status AS order_status, fo.order_number, fo.reviewed_id, fo.notified_id, fo.result_abnormal, fo.request_billing, ";
		$query1 .= "po.provider_id, po.date_ordered, po.lab_id, pd.fname AS pat_fname, pd.lname AS pat_lname, pd.mname AS pat_mname, pd.pubpid "; 
		$query1 .= "FROM forms f ";
		$query1 .= "LEFT JOIN form_encounter fe ON fe.encounter = f.encounter ";
		$query1 .= "LEFT JOIN form_laboratory fo ON fo.id = f.form_id ";
		$query1 .= "LEFT JOIN procedure_order po ON po.procedure_order_id = fo.order_number ";
		$query1 .= "LEFT JOIN users u ON u.id = po.provider_id ";
		$query1 .= "LEFT JOIN patient_data pd ON pd.pid = fo.pid ";
		$query1 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
		$query1 .= "WHERE f.deleted != '1' AND f.formdir = 'laboratory' ";
		$query1 .= "AND fo.pid < '999999990' ";
		$query1 .= "AND (fo.status = 'x' OR fo.status ='z' OR fo.status = 'v' OR fo.status = 'n') ";
		if ($form_billing) $query1 .= "AND fo.request_billing = '$billing' ";
		if ($form_facility) $query1 .= "AND fe.facility_id = '$form_facility' ";
		if ($form_from_date) $query1 .= "AND date_ordered >= '$form_from_date 00:00:00' AND date_ordered <= '$form_to_date 23:59:59' ";
		if ($form_provider) $query1 .= "AND po.provider_id = '$form_provider' ";
		if ($form_processor) $query1 .= "AND po.lab_id = '$form_processor' ";
		if ($form_handling) $query1 .= "AND fo.request_handling = '$form_handling' ";
		if ($form_status == 'f') $query1 .= "AND fo.result_abnormal > 0 ";
	}
	
	if ($quest !== FALSE) {
		$query2 = "SELECT f.formdir, f.form_id, fe.encounter, fe.date AS enc_date, fe.facility_id, fe.reason, ";
		$query2 .= "u.fname AS doc_fname, u.mname AS doc_mname, u.lname AS doc_lname, pp.name AS lab_name, ";
		$query2 .= "fo.id, fo.pid, fo.status AS order_status, fo.order_number, fo.reviewed_id, fo.notified_id, fo.result_abnormal, fo.request_billing, ";
		$query2 .= "po.provider_id, po.date_ordered, po.lab_id, pd.fname AS pat_fname, pd.lname AS pat_lname, pd.mname AS pat_mname, pd.pubpid "; 
		$query2 .= "FROM forms f ";
		$query2 .= "LEFT JOIN form_encounter fe ON fe.encounter = f.encounter ";
		$query2 .= "LEFT JOIN form_quest fo ON fo.id = f.form_id ";
		$query2 .= "LEFT JOIN procedure_order po ON po.procedure_order_id = fo.order_number ";
		$query2 .= "LEFT JOIN users u ON u.id = po.provider_id ";
		$query2 .= "LEFT JOIN patient_data pd ON pd.pid = fo.pid ";
		$query2 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
		$query2 .= "WHERE f.deleted != '1' AND f.formdir = 'quest' ";
		$query2 .= "AND fo.pid < '999999990' ";
		$query2 .= "AND (fo.status = 'x' OR fo.status ='z' OR fo.status = 'v' OR fo.status = 'n') ";
		if ($form_billing) $query2 .= "AND fo.request_billing = '$billing' ";
		if ($form_facility) $query2 .= "AND fe.facility_id = '$form_facility' ";
		if ($form_from_date) $query2 .= "AND date_ordered >= '$form_from_date 00:00:00' AND date_ordered <= '$form_to_date 23:59:59' ";
		if ($form_provider) $query2 .= "AND po.provider_id = '$form_provider' ";
		if ($form_processor) $query2 .= "AND po.lab_id = '$form_processor' ";
		if ($form_handling) $query2 .= "AND fo.request_handling = '$form_handling' ";
		if ($form_status == 'f') $query2 .= "AND fo.result_abnormal > 0 ";
	}
	
	if ($labcorp !== FALSE) {
		$query3 = "SELECT f.formdir, f.form_id, fe.encounter, fe.date AS enc_date, fe.facility_id, fe.reason, ";
		$query3 .= "u.fname AS doc_fname, u.mname AS doc_mname, u.lname AS doc_lname, pp.name AS lab_name, ";
		$query3 .= "fo.id, fo.pid, fo.status AS order_status, fo.order_number, fo.reviewed_id, fo.notified_id, fo.result_abnormal, fo.request_billing, ";
		$query3 .= "po.provider_id, po.date_ordered, po.lab_id, pd.fname AS pat_fname, pd.lname AS pat_lname, pd.mname AS pat_mname, pd.pubpid "; 
		$query3 .= "FROM forms f ";
		$query3 .= "LEFT JOIN form_encounter fe ON fe.encounter = f.encounter ";
		$query3 .= "LEFT JOIN form_labcorp fo ON fo.id = f.form_id ";
		$query3 .= "LEFT JOIN procedure_order po ON po.procedure_order_id = fo.order_number ";
		$query3 .= "LEFT JOIN users u ON u.id = po.provider_id ";
		$query3 .= "LEFT JOIN patient_data pd ON pd.pid = fo.pid ";
		$query3 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
		$query3 .= "WHERE f.deleted != '1' AND f.formdir = 'labcorp' ";
		$query3 .= "AND fo.pid < '999999990' ";
		$query3 .= "AND (fo.status = 'x' OR fo.status ='z' OR fo.status = 'v' OR fo.status = 'n') ";
		if ($form_billing) $query3 .= "AND fo.request_billing = '$billing' ";
		if ($form_facility) $query3 .= "AND fe.facility_id = '$form_facility' ";
		if ($form_from_date) $query3 .= "AND date_ordered >= '$form_from_date 00:00:00' AND date_ordered <= '$form_to_date 23:59:59' ";
		if ($form_provider) $query3 .= "AND po.provider_id = '$form_provider' ";
		if ($form_processor) $query3 .= "AND po.lab_id = '$form_processor' ";
		if ($form_handling) $query3 .= "AND fo.request_handling = '$form_handling' ";
		if ($form_status == 'f') $query3 .= "AND fo.result_abnormal > 0 ";
	}
	
        $query = '';
        if ($query1) $query .= $query1;
        if ($query2) {
                if ($query) $query .= " UNION ";
                $query .= $query2;
        }
        if ($query3) {
                if ($query) $query .= " UNION ";
                $query .= $query3;
        }
        $query = 'SELECT * FROM ( '.$query.' ) AS results ';
        $query .= "ORDER BY $orderby";
/*
	if ($query1) $query = $query1;
	if ($query2) {
		if ($query) $query = "($query) UNION ($query2)";
		else $query = $query2;
	}
	if ($query3) {
		if ($query) $query = "($query) UNION ($query3)";
		else $query = $query3;
	}

	$query .= "ORDER BY $orderby";
*/
//	echo $query."<br />\n";
	$results = sqlStatement($query);
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php html_header_show();?>
		<title><?php echo $result_title; ?></title>

		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" media="screen" />
		
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.10.0.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/overlib_mini.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
		<!-- script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmtstandard.js"></script -->
		
		<!-- pop up calendar -->
		<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
		<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

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
		</style>

		<script>

			var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

			function dosort(orderby) {
				var f = document.forms[0];
				f.form_orderby.value = orderby;
				f.submit();
				return false;
			}

			function refreshme() {
				document.forms[0].submit();
			}

		</script>
	</head>
	
	
	<body class="body_top">
		<!-- Required for the popup date selectors -->
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

		<span class='title'><?php xl('Report','e'); ?> - <?php xl('Laboratory Results','e'); ?></span>

		<div id="report_parameters_daterange">
			<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
		</div>

		<form method='post' name='theform' id='theform' action='lab_results.php'>
			<div id="report_parameters">
				<table>
					<tr>
						<td width='760px'>
							<div style='float:left'>
								<table class='text'>
									<tr>
										<td class='label'><?php xl('Facility','e'); ?>: </td>
										<td>
											<?php //dropdown_facility(strip_escape_custom($form_facility), 'form_facility', false, true); ?>
<?php
	// Build a drop-down list of facilities.
	$query = "SELECT id, name FROM facility ORDER BY name";
	$fres = sqlStatement($query);

	echo "   <select name='form_facility' style='max-width:200px'>\n";
	echo "    <option value=''>-- " . xl('All Facilities') . " --\n";

	while ($frow = sqlFetchArray($fres)) {
		$facid = $frow['id'];
		echo "    <option value='$facid'";
		if ($facid == $_POST['form_facility']) echo " selected";
		echo ">" . $frow['name'] . "\n";
	}
	
	echo "   </select>\n";
?>
										</td>
										<td class='label'><?php xl('Provider','e'); ?>: </td>
										<td>
<?php
	// Build a drop-down list of providers.
	$query = "SELECT id, username, lname, fname FROM users ";
	$query .= "WHERE id IN (SELECT DISTINCT(provider_id) FROM procedure_order) ";
	$query .= "ORDER BY lname, fname ";
	$ures = sqlStatement($query);

	echo "   <select name='form_provider' style='max-width:200px'>\n";
	echo "    <option value=''>-- " . xl('All Providers') . " --\n";

	while ($urow = sqlFetchArray($ures)) {
		$provid = $urow['id'];
		echo "    <option value='$provid'";
		if ($provid == $_POST['form_provider']) echo " selected";
		echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
	}
	
	echo "   </select>\n";
?>
										</td>
           								<td class='label'><?php xl('Processor','e'); ?>: </td>
          								<td>
<?php
	// Build a drop-down list of processor names.
	$query = "SELECT * FROM procedure_providers ORDER BY name";
	$ures = sqlStatement($query);

	echo "   <select name='form_processor'>\n";
	echo "    <option value=''>-- " . xl('All') . " --\n";

	while ($urow = sqlFetchArray($ures)) {
		$ppid = $urow['ppid'];
		echo "    <option value='$ppid'";
		if ($ppid == $_POST['form_processor']) echo " selected";
		echo ">" . $urow['name'] . "\n";
	}

	echo "   </select>\n";
  ?>
  										</td>
									</tr>
								</table>

								<table class='text'>
									<tr>
										<td class='label'><?php xl('From','e'); ?>: </td>
										<td>
											<input type='text' name='form_from_date' id="form_from_date" size='10' 
												value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' 
												onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
											<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' 
												id='img_from_date' border='0' alt='[?]' style='cursor:pointer' 
												title='<?php xl('Click here to choose a date','e'); ?>'>
										</td>
										<td class='label'><?php xl('To','e'); ?>: </td>
										<td>
											<input type='text' name='form_to_date' id="form_to_date" size='10' 
												value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' 
												onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
											<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' 
												id='img_to_date' border='0' alt='[?]' style='cursor:pointer' 
												title='<?php xl('Click here to choose a date','e'); ?>'>
										</td>
										<td class='label'><?php xl('Form Status','e'); ?>: </td>
										<td>
<?php
	// Build a drop-down list of form statuses.
	$query = "SELECT option_id, title FROM list_options WHERE list_id = 'Lab_Form_Status' AND (title LIKE 'Results%' OR title like 'Pending%') ORDER BY seq";
	$ures = sqlStatement($query);

	echo "   <select name='form_status'>\n";
	echo "    <option value=''>-- " . xl('All') . " --\n";

	while ($urow = sqlFetchArray($ures)) {
		$statid = $urow['option_id'];
		echo "    <option value='$statid'";
		if ($statid == $_POST['form_status']) echo " selected";
		echo ">" . $urow['title'] . "\n";
	}
              
	echo "   </select>\n";
?>
										</td>
									</tr>
								</table>

								<table class='text'>
									<tr>
										<td class='label'><?php xl('Special Handling','e'); ?>: </td>
										<td>
<?php
	// Build a drop-down list of form handling.
	$query = "SELECT option_id, title FROM list_options WHERE list_id = 'Lab_Handling' ORDER BY seq";
	$ures = sqlStatement($query);

	echo "   <select name='form_handling'>\n";
	echo "    <option value=''>-- " . xl('All') . " --\n";

	while ($urow = sqlFetchArray($ures)) {
		$statid = $urow['option_id'];
		echo "    <option value='$statid'";
		if ($statid == $_POST['form_handling']) echo " selected";
		echo ">" . $urow['title'] . "\n";
	}
              
	echo "   </select>\n";
?>
										</td>
										<td class='label'><?php xl('Billing','e'); ?>: </td>
										<td>
<?php
	// Build a drop-down list of bill types.
	$query = "SELECT option_id, title FROM list_options WHERE list_id = 'Lab_Billing' ORDER BY seq";
	$ures = sqlStatement($query);

	echo "   <select name='form_billing'>\n";
	echo "    <option value=''>-- " . xl('All') . " --\n";
		while ($urow = sqlFetchArray($ures)) {
		$statid = $urow['option_id'];
		echo "    <option value='$statid'";
		if ($statid == $_POST['form_billing']) echo " selected";
		echo ">" . $urow['title'] . "\n";
	}
	echo "    <option value='' ";
	if ('none' == $_POST['form_billing']) echo " selected";
 	echo ">Unknown</option>\n";
	echo "   </select>\n";
?>
										</td>
										
 									</tr>
								</table>
							</div>
						</td>
						<td align='left' valign='middle' height="100%">
							<table style='border-left:1px solid; width:100%; height:100%' >
								<tr>
									<td>
										<div style='margin-left:15px'>
											<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
												<span><?php xl('Submit','e'); ?></span>
											</a>

<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
            								<a href='#' class='css_button' onclick='window.print()'>
												<span><?php xl('Print','e'); ?></span>
											</a>
<?php } ?>
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>

			</div> <!-- end report_parameters -->

<?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>

			<div id="report_results">
				<table>
					<thead>
						<th>
							<a href="nojs.php" onclick="return dosort('doctor')"
								<?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  xl('Provider','e'); ?> 
							</a>
						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('time')"
								<?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Date','e'); ?>
							</a>
  						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('patient')"
								<?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?>
							</a>
						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('pubpid')"
								<?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  xl('ID','e'); ?>
							</a>
						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('billing')"
								<?php if ($form_orderby == "billing") echo " style=\"color:#00cc00\"" ?>><?php  xl('Billing','e'); ?>
							</a>
						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('lab')"
								<?php if ($form_orderby == "lab") echo " style=\"color:#00cc00\"" ?>><?php  xl('Processor','e'); ?>
							</a>
						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('status')"
								<?php if ($form_orderby == "status") echo " style=\"color:#00cc00\"" ?>><?php  xl('Status','e'); ?>
							</a>
						</th>
						<th>
							<?php  xl('Form','e'); ?>
						</th>
					</thead>
					<tbody>
<?php 
		$printed  = 0; 
		if (mysql_num_rows($results) > 0) {
			$lastdocname = "";
			$doc_encounters = 0;
			while ($row = sqlFetchArray($results)) {
				$docname = '';
				if (!empty($row['doc_lname']) || !empty($row['doc_fname'])) {
					$docname = $row['doc_lname'];
					if (!empty($row['doc_fname']) || !empty($row['doc_mname']))
						$docname .= ', ' . $row['doc_fname'] . ' ' . $row['doc_mname'];
	    		}
	
			    $errmsg  = "";
	   			$ostatus = $row['order_status'];

    			if ($form_status && $form_status != 'f') {
					if ($form_status == 'g' && !in_array($ostatus,array('z','n','v','x','g'))) continue;
					elseif ($form_status != $ostatus) continue; // wrong order status
    			}
    			
	   			if ($form_special > 0) {
	    			if ($row['request_handling'] != $form_special) continue;
		    	}
	     
			    $status = ListLook($ostatus, 'Lab_Form_Status');
	    		if ($status == 'Error' || $status == '') { $status = 'Unassigned'; }
	
	    			$link_ref="$rootdir/forms/".$row['formdir']."/update.php?id=".$row['form_id']."&pid=".$row['pid']."&enc=".$row['encounter']."&pop=1";
?>
						<tr bgcolor='<?php echo $bgcolor ?>'>
							<td class="nowrap">
								<?php echo $docname; ?>&nbsp;
							</td>
							<td>
								<?php echo oeFormatShortDate(substr($row['date_ordered'], 0, 10)) ?>&nbsp;
							</td>
							<td>
								<?php echo $row['pat_lname'] . ', ' . $row['pat_fname'] . ' ' . $row['pat_mname']; ?>&nbsp;
							</td>
							<td>
								<?php echo $row['pubpid']; ?>&nbsp;
							</td>
							<td>
<?php if ($row['request_billing'] == 'T') echo 'ThirdParty';
		elseif ($row['request_billing'] == 'C') echo 'Clinic';
 		elseif ($row['request_billing'] == 'P') echo 'Patient';
 		else echo 'Unknown'; ?>&nbsp;
							</td>
							<td>
								<?php echo $row['lab_name'] ?>&nbsp;
							</td>
							<td>
								<?php echo $status; ?>&nbsp;
							</td>
							<td style="min-width:130px">
								<a href="<?php echo $link_ref; ?>" target="_blank" class="link_submit" 
									onclick="top.restoreSession()">Result Form - <?php echo $row['order_number']; ?></a>&nbsp;
							</td>
						</tr>
<?php
				$printed++;
				$lastdocname = $docname;
			}
		}
		if (!$printed) {
?>
						<tr>
							<td colspan="7" style="font-weight:bold;text-align:center;padding:25px">
								NO RESULTS FOUND
							</td>
						</tr>

<?php 
		}
?>
					</tbody>
				</table>
			</div>  <!-- end encresults -->
<?php 
	} 
	else { 
?>
			<div class='text'>
				<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
			</div>
<?php 
	} 
?>

			<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
			<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

		</form>
	</body>

	<script language='JavaScript'>
		Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
		Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
		<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>
	</script>

</html>