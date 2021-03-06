<?php 
/** **************************************************************************
 *	LABORATORY/LAB_PENDING.PHP
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
$report_title = 'Pending Orders';
$result_name = 'procedure_result';
$item_name = 'procedure_result_item';
$order_name = 'procedure_order';

// For each sorting option, specify the ORDER BY argument.
$ORDERHASH = array(
	'doctor'  => 'lower(doc_lname), lower(doc_fname), date_ordered DESC',
	'patient' => 'lower(pat_lname), lower(pat_fname), date_ordered DESC',
  	'order'  => 'cast(order_number as unsigned), date_ordered DESC',
	'time'    => 'date_ordered DESC, lower(doc_lname), lower(doc_fname)',
	'lab'     => 'lower(lab_name), date_ordered DESC',
	'status'    => 'activity, date_ordered DESC, lower(doc_lname), lower(doc_fname)',
);

// get date range
$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$form_from_date = ($_POST['form_from_date']) ? $_POST['form_from_date'] : date('Y-m-d', $last_month);
$form_to_date = ($_POST['form_to_date']) ? $_POST['form_to_date'] : date('Y-m-d');
$form_provider = $_POST['form_provider'];
$form_facility = $_POST['form_facility'];
$form_status = $_POST['form_status'];
$form_name = $_POST['form_name'];
$form_lab = $_POST['form_lab'];
$form_ignore = $_POST['form_ignore']; // there was a request to ignore an order
$form_refresh = ($_POST['form_refresh'] || $_POST['form_orderby'])? true: false;

// hide a result
if ($form_ignore) {
	$key = $form_ignore;
	
	if (strpos($form_ignore,'quest') !== false) {
		$lab = 'quest';
		$key = str_replace('quest', '', $form_ignore);
	}
	
	if (strpos($form_ignore,'labcorp') !== false) {
		$lab = 'labcorp';
		$key = str_replace('labcorp', '', $form_ignore);
	}
	
	if ($key)	
		sqlStatement("UPDATE form_".$lab." SET activity=0 WHERE id = ?",array($key));
	
	$form_ignore = '';
	$form_refresh = true;
}

// get sort order
$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'doctor';
$orderby 		= $ORDERHASH[$form_orderby];

// retrieve records
$query = '';
$query1 = $query2 = $query3 = '';

$orders = array();
$results = false;

// which interfaces are active?
$quest = mysql_query("SELECT 1 FROM `form_quest`");
$labcorp = mysql_query("SELECT 1 FROM `form_labcorp`");
$generic = mysql_query("SELECT 1 FROM `form_laboratory`");

if ($_POST['form_refresh'] || $_POST['form_orderby']) {
	if ($generic !== false && (!$form_lab || ($form_lab != 'q' && $form_lab != 'l'))) { 
		$query1 = "SELECT f.formdir AS type, fe.encounter, fe.date AS enc_date, fe.facility_id, fe.reason, ";
		$query1 .= "u.fname AS doc_fname, u.mname AS doc_mname, u.lname AS doc_lname, pp.name AS lab_name, ";
		$query1 .= "fo.id, fo.pid, fo.status AS order_status, fo.order_number, fo.activity, fo.order_req_id, ";
		$query1 .= "po.provider_id, po.date_ordered, po.lab_id, pd.fname AS pat_fname, pd.lname AS pat_lname, pd.mname AS pat_mname, pd.pubpid ";
		$query1 .= "FROM forms f ";
		$query1 .= "LEFT JOIN form_encounter fe ON fe.encounter = f.encounter ";
		$query1 .= "LEFT JOIN form_laboratory fo ON fo.id = f.form_id ";
		$query1 .= "LEFT JOIN procedure_order po ON po.procedure_order_id = fo.order_number ";
		$query1 .= "LEFT JOIN users u ON u.id = po.provider_id ";
		$query1 .= "LEFT JOIN patient_data pd ON pd.pid = fo.pid ";
		$query1 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
		$query1 .= "WHERE f.deleted != '1' AND f.formdir = 'laboratory' ";
		$query1 .= "AND fo.pid < '999999995' ";
		$query1 .= "AND fo.status = 's' ";
		if ($form_facility) $query1 .= "AND fe.facility_id = '$form_facility' ";
		if ($form_from_date) $query1 .= "AND date_ordered >= '$form_from_date 00:00:00' AND date_ordered <= '$form_to_date 23:59:59' ";
		if ($form_provider) $query1 .= "AND po.provider_id = '$form_provider' ";
		if ($form_processor) $query1 .= "AND po.lab_id = '$form_processor' ";
	}
	
	if ($quest !== false && (!$form_lab || $form_lab == 'q')) { 
		$query2 = "SELECT f.formdir AS type, fe.encounter, fe.date AS enc_date, fe.facility_id, fe.reason, ";
		$query2 .= "u.fname AS doc_fname, u.mname AS doc_mname, u.lname AS doc_lname, pp.name AS lab_name, ";
		$query2 .= "fo.id, fo.pid, fo.status AS order_status, fo.order_number, fo.activity, fo.order_req_id, ";
		$query2 .= "po.provider_id, po.date_ordered, po.lab_id, pd.fname AS pat_fname, pd.lname AS pat_lname, pd.mname AS pat_mname, pd.pubpid ";
		$query2 .= "FROM forms f ";
		$query2 .= "LEFT JOIN form_encounter fe ON fe.encounter = f.encounter ";
		$query2 .= "LEFT JOIN form_quest fo ON fo.id = f.form_id ";
		$query2 .= "LEFT JOIN procedure_order po ON po.procedure_order_id = fo.order_number ";
		$query2 .= "LEFT JOIN users u ON u.id = po.provider_id ";
		$query2 .= "LEFT JOIN patient_data pd ON pd.pid = fo.pid ";
		$query2 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
		$query2 .= "WHERE f.deleted != '1' AND f.formdir = 'quest' ";
		$query2 .= "AND fo.pid < '999999995' ";
		$query2 .= "AND fo.status = 's' ";
		if ($form_facility) $query2 .= "AND fe.facility_id = '$form_facility' ";
		if ($form_from_date) $query2 .= "AND date_ordered >= '$form_from_date 00:00:00' AND date_ordered <= '$form_to_date 23:59:59' ";
		if ($form_provider) $query2 .= "AND po.provider_id = '$form_provider' ";
		if ($form_processor) $query2 .= "AND po.lab_id = '$form_processor' ";
	}
	
	if ($labcorp !== false && (!$form_lab || $form_lab == 'l')) {
		$query3 = "SELECT f.formdir AS type, fe.encounter, fe.date AS enc_date, fe.facility_id, fe.reason, ";
		$query3 .= "u.fname AS doc_fname, u.mname AS doc_mname, u.lname AS doc_lname, pp.name AS lab_name, ";
		$query3 .= "fo.id, fo.pid, fo.status AS order_status, fo.order_number, fo.activity, fo.order_req_id, ";
		$query3 .= "po.provider_id, po.date_ordered, po.lab_id, pd.fname AS pat_fname, pd.lname AS pat_lname, pd.mname AS pat_mname, pd.pubpid "; 
		$query3 .= "FROM forms f ";
		$query3 .= "LEFT JOIN form_encounter fe ON fe.encounter = f.encounter ";
		$query3 .= "LEFT JOIN form_labcorp fo ON fo.id = f.form_id ";
		$query3 .= "LEFT JOIN procedure_order po ON po.procedure_order_id = fo.order_number ";
		$query3 .= "LEFT JOIN users u ON u.id = po.provider_id ";
		$query3 .= "LEFT JOIN patient_data pd ON pd.pid = fo.pid ";
		$query3 .= "LEFT JOIN procedure_providers pp ON pp.ppid = po.lab_id ";
		$query3 .= "WHERE f.deleted != '1' AND f.formdir = 'labcorp' ";
		$query3 .= "AND fo.pid < '999999995' ";
		$query3 .= "AND fo.status = 's'  ";
		if ($form_facility) $query3 .= "AND fe.facility_id = '$form_facility' ";
		if ($form_from_date) $query3 .= "AND date_ordered >= '$form_from_date 00:00:00' AND date_ordered <= '$form_to_date 23:59:59' ";
		if ($form_provider) $query3 .= "AND po.provider_id = '$form_provider' ";
		if ($form_processor) $query3 .= "AND po.lab_id = '$form_processor' ";
	}

        $query = '';
        if ($query1) $query .= "(".$query1.")";
        if ($query2) {
                if ($query) $query .= " UNION ";
                $query .= "(".$query2.")";
        }
        if ($query3) {
                if ($query) $query .= " UNION ";
                $query .= "(".$query3.")";
        }
        $query = 'SELECT * FROM ( '.$query.' ) AS results WHERE 1 ';

        if (! $form_status) { // include inactive
                $query .= "AND activity = 1 ";
        }
        $query .= "ORDER BY $orderby";

/*      echo $query."<br />\n";
	$query = 'SELECT * FROM (( ';
	if ($query1) $query .= $query1;
	if ($query2) {
		if ($query) $query .= " ) UNION ( ";
		$query .= $query2;
	}
	if ($query3) {
		if ($query) $query .= " ) UNION ( ";
		$query .= $query3;
	}
	$query .= ' )) AS results WHERE 1 ';
	
	if (! $form_status) { // include inactive
		$query .= "AND activity = 1 ";
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


//			setTimeout(function(){
//				document.forms[0].submit();
//			}, 25000);
		</script>
	</head>
	
	
	<body class="body_top">
		<!-- Required for the popup date selectors -->
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

		<span class='title'><?php xl('Report','e'); ?> - <?php xl('Pending Lab Orders','e'); ?></span>

		<div id="report_parameters_daterange">
			<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
		</div>

		<form method='post' name='theform' id='theform' action='lab_pending.php'>
		<input type='hidden' name='form_ignore' id='form_ignore' value='' />
		<div id="report_parameters">
				<table>
					<tr>
						<td style='width:auto'>
							<div style='float:left'>
								<table class='text'>
									<tr>
										<td class='label'><?php xl('Facility','e'); ?>: </td>
										<td>
											<?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', false, true); ?></td>
										<td class='label'><?php xl('Provider','e'); ?>: </td>
										<td>
<?php
	// Build a drop-down list of providers.
	$query = "SELECT id, username, lname, fname FROM users WHERE authorized = 1 AND active = 1 $provider_facility_filter ORDER BY lname, fname";
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
										<td class='label' style='width:47px'><?php xl('From','e'); ?>: </td>
										<td style='width:120px'>
											<input type='text' name='form_from_date' id="form_from_date" size='10' 
												value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' 
												onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
											<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' 
												id='img_from_date' border='0' alt='[?]' style='cursor:pointer' 
												title='<?php xl('Click here to choose a date','e'); ?>'>
										</td>
										<td class='label' style='width:47px'><?php xl('To','e'); ?>: </td>
										<td style='width:120px'>
											<input type='text' name='form_to_date' id="form_to_date" size='10' 
												value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' 
												onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
											<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' 
												id='img_to_date' border='0' alt='[?]' style='cursor:pointer' 
												title='<?php xl('Click here to choose a date','e'); ?>'>
										</td>
          								<td class='label' style='white-space:nowrap;width:120px'><?php xl('Include Inactive','e'); ?>: </td>
										<td><?php
               								// Include hidden records?
              								echo "   <input type='checkbox' name='form_status' value='1' ";
              								echo ($form_status)?"checked":"";
              								echo " />\n";
              							?></td>
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
							<a href="nojs.php" onclick="return dosort('order')"
								<?php if ($form_orderby == "order") echo " style=\"color:#00cc00\"" ?>><?php  xl('Order','e'); ?>
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
							&nbsp;
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
	   			
    		if ($form_special > 0) {
    			if ($row['request_handling'] != $form_special) continue;
	    	}
     
	    	$status = 'Pending Active';
	    	if ($row['activity'] != 1) $status = 'Pending Inactive';
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
 								<?php 
   								if ($row['pat_lname']) {	
									echo $row['pat_lname'] . ', ' . $row['pat_fname'] . ' ' . $row['pat_mname'];
								}
								else {
									echo "<nobr>[ NO PATIENT DATA ]</nobr>";
								}
								?>
							</td>
							<td>
   								<?php echo ($row['order_number']) ? $row['order_number'] : "[ NONE ]"; ?>&nbsp;
							</td>
							<td>
   								<?php echo ($row['lab_name']) ? $row['lab_name'] : "[ UNAVAILABLE ]" ?>&nbsp;
							</td>
							<td>
   								<?php echo $status; ?>&nbsp;
							</td>
  <td style="text-align:right">
  <?php if ($row['order_req_id']) { ?>
		<input tabindex="-1" type="button" onclick="showdoc(<?php echo $row['pid'] ?>,<?php echo $row['order_req_id'] ?>)" value="view" />
  <?php } ?>
	  <input tabindex="-1" type="button" class="link_submit" onclick="doignore('<?php echo $row['type'] ?>',<?php echo $row['id'] ?>)"  value="hide" />&nbsp;
							</td>
						</tr>
<?php
				$printed++;
			$lastdocname = $docname;
		}

		if (! $printed) {
?>
						<tr>
							<td colspan="7" style="font-weight:bold;text-align:center;padding:25px">
								NO PENDING ORDERS FOUND
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
