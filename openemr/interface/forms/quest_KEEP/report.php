<?php
/** **************************************************************************
 *	QUEST/REPORT.PHP
 *
 *	Copyright (c)2014 - Williams Medical Technology, Inc.
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
 *  @package laboratory
 *  @subpackage quest
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
include_once("{$GLOBALS['srcdir']}/sql.inc");
include_once("{$GLOBALS['srcdir']}/api.inc");
include_once("{$GLOBALS['srcdir']}/wmt/wmt.class.php");
include_once("{$GLOBALS['srcdir']}/wmt/wmt.report.php");
include_once("{$GLOBALS['srcdir']}/wmt/wmt.forms.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

if (!function_exists("quest_report")) { // prevent redeclarations

function quest_report($pid, $encounter, $cols, $id) {
	$form_name = 'quest';
	$form_table = 'form_quest';
	$form_title = 'Quest Diagnostics';

	/* RETRIEVE FORM DATA */
	try {
		$order_data = new wmtOrder($form_name, $id);
		$pat_data = wmtPatient::getPidPatient($pid);
		$enc_data = wmtEncounter::getEncounter($encounter);
		$ins_list = wmtInsurance::getPidInsDate($pid,$order_data->date_ordered);

		$lab_id = ($order_data->lab_id) ? $order_data->lab_id : $_REQUEST['lab_id'];
		$lab_data = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?",array($lab_id));
	
		$item_list = wmtOrderItem::fetchItemList($order_data->order_number);
	}
	catch (Exception $e) {
		die ("FATAL ERROR ENCOUNTERED: " . $e->getMessage());
		exit;
	}

	// Custom style information
	print '<link rel="stylesheet" type="text/css" href="'.$GLOBALS['webroot'].'/library/wmt/wmt.default.css" />';
	
	// Report outter frame
	print "\n\n<div class='wmtReport'>\n";
	print "<table class='wmtFrame' cellspacing='0' cellpadding='3'>\n";
	
	// Status header
	$content = "";
	$status = 'Incomplete';
	if ($order_data->status) $status = ListLook($order_data->status, 'Lab_Form_Status');
	$content .= "<tr><td colspan='4'>\n";
	$content .= "<table class='wmtStatus' style='margin-bottom:10px'><tr>";
	$content .= "<td class='wmtLabel' style='width:50px;min-width:50px'>Status:</td>";
	$content .= "<td class='wmtOutput' style='white-space:nowrap'>" . $status . "</td>";
	$content .= "<td class='wmtLabel' style='width:50px;min-width:50px'>Priority:</td>";
	$content .= "<td class='wmtOutput'>Normal</td>\n";
	$content .= "</tr></table></td></tr>\n";
	if ($content) print $content;
	
	// Order summary
	$content = "<tr><td style='width:140px'></td><td style='width:250px'></td><td style='width:100px'></td><td></td></tr>\n";
	$processed = ($order_data->date_transmitted > 0)? date('Y-m-d h:i A',strtotime($order_data->date_transmitted)): 'PENDING';
	$content .= do_columns(date('Y-m-d',strtotime($order_data->date_ordered)),'Order Date',$processed,'Transmitted Date');
	$content .= do_columns($order_data->order_number,'Requisition',$lab_data['name'],'Processing Vendor');
	$ordby = UserIdLook($order_data->provider_id);
	if (!$ordby) $ordby = "UNKNOWN";
	$content .= do_columns($ordby,'Ordering Provider',$order_data->request_account,'Billing Account');
	$entby = UserLook($order_data->user);
	if ($ordby == "UNKNOWN" || $ordby == $entby) $entby = "";
	$content .= do_columns($entby,'Entering Clinician',ListLook($order_data->request_handling,'Order_Handling'));
	$notes = ($order_data->order_notes)? "<div style='white-space:pre-wrap'>".$order_data->order_notes."</div>" : "";
	$content .= do_line($notes,'Clinic Notes');
	do_section($content, 'Order Summary');
	
	// Loop through diagnosis
	$content = "<tr><td style='width:140px'></td><td style='width:50px'></td><td style='width:100px'></td><td></td></tr>\n";
	$diag_array = array();
	if ($order_data->diagnoses) {
		$diag_array = explode("|", $order_data->diagnoses); // code & text

		foreach ($diag_array AS $diag) {
			list($code,$text) = explode("^", $diag);
			if (empty($code)) continue;
			if (strpos($code,":") !== false)	
				list($dx_type,$dx_code) = explode(":", $code);
	
			if (!$dx_type) $dx_type = 'ICD9';
	 
			$content .= do_columns($dx_code, $dx_type.' Code',$text, 'Description');
		}	
	
		do_section($content, 'Order Diagnosis');
	}
	
	// Order specimen
	$content = "<td style='width:140px'></td><td style='width:50px'></td><td style='width:100px'></td><td></td>\n";
	$collected = ($order_data->order_datetime)?date('Y-m-d',strtotime($order_data->order_datetime)):null;
	$pending = ($order_data->order_pending)?date('Y-m-d',strtotime($order_data->order_pending)):null;
	
	if ($order_data->order_psc) {
		$content .= do_line('Yes','PSC Hold Order');
	}
	else {
		$content .= do_line('YES','Sample Collected');
		$content .= do_columns($collected,'Collection Date',$order_data->order_volume,'Volume(ml)');
		$content .= do_columns(($order_data->order_fasting)?'YES':'NO','Patient Fasting',$order_data->order_duration,'Duration (hours)');
	}
	$content .= do_break();
	
	// loop through requisitions
	$content = "<tr><td style='width:140px'></td><td style='width:50px'></td><td style='width:100px'></td><td></td></tr>\n";
	foreach ($item_list AS $order_item) {
		$need_blank = false;
		
		// Test section
		$type = ($order_item->procedure_type == 'pro')? "Profile " : "Test ";
		$content .= do_columns($order_item->procedure_code,$type.'Code',$order_item->procedure_name,'Description');

		// add profile tests if necessary
		if ($order_item->procedure_type == 'pro') {
			// retrieve all component test if profile
			$codes = $comps = "";
			$profile = array();
			$record = sqlQuery("SELECT related_code AS components FROM procedure_type WHERE procedure_code = ? AND lab_id = ? AND procedure_type = 'pro' ",
					array($order_item->procedure_code, $lab_id));
			if ($record['components']) {
				$list = explode("^", $record['components']);
				if (!is_array($list)) $list = array($list); // convert to array if necessary
				foreach ($list AS $comp) $comps[$comp] = "'UNIT:$comp'";
				$codes = implode(",", $comps);
			}
			
			// component codes found
			if ($codes) {
				$query = "SELECT procedure_type_id AS id, procedure_code AS component, description, name AS title FROM procedure_type ";
				$query .= "WHERE activity = 1 AND lab_id = ".$lab_id." AND procedure_type = 'ord' ";
				$query .= "AND standard_code IN ( ".$codes." ) ";
				$query .= "GROUP BY procedure_code ORDER BY procedure_code ";
				$result = sqlStatement($query);
					
				while ($profile = sqlFetchArray($result)) {
					$description = ($profile['description'])? $profile['description'] : $profile['title'];
					$content .= do_columns("","",$profile['component']." - ".$description,"Component",true);
					$need_blank = true;
				}
			}
		}
	
		// add AOE questions if necessary
		$result = sqlStatement("SELECT aoe.procedure_code AS code, aoe.question_code, aoe.question_text, aoe.tips, answer FROM procedure_answers ans ".
			"LEFT JOIN procedure_questions aoe ON aoe.question_code = ans.question_code ".
			"WHERE aoe.lab_id = ? AND aoe.procedure_code = ? AND ans.procedure_order_id = ? AND ans.procedure_order_seq = ? AND aoe.activity = 1 ORDER BY ans.answer_seq",
				array($lab_id, $order_item->procedure_code, $order_item->procedure_order_id, $order_item->procedure_order_seq));
			
		$aoe_out = '';
		while ($aoe = sqlFetchArray($result)) {
			$question = str_replace(':','',$aoe['question_text']);
			if ($question && $aoe['answer']) {
				$aoe_out .= "<tr><td class='wmtLabel' style='width:200px;white-space:nowrap'>".$question.": </td>\n";
				$aoe_out .= "<td class='wmtOutput' style='white-space:nowrap'>".$aoe['answer']."</td></tr>\n";
				$need_blank = true;
			}
		}
		if ($aoe_out) {
			$content .= "<tr><td colspan=3></td><td><table>$aoe_out</table></td></tr>";
//			$content .= do_columns('','','<table>'.$aoe_out.'</table>','',true);
		}

		if ($need_blank) $content .= do_blank(); // skip first time
	}
	// lab notes
	if ($order_data->clinical_hx || $order_data->patient_instructions)
		$content .= do_break();
	
	if ($order_data->clinical_hx) {
		$content .= "<tr><td class='wmtLabel'>Clinical Lab Notes: </td><td class='wmtOutput' colspan='3' style='white-space:pre-wrap'>".$order_data->clinical_hx."</td></tr>";
	}
	
	// patient instructions
	if ($order_data->patient_instructions) {
		$content .= "<tr><td class='wmtLabel'>Patient Instructions: </td><td class='wmtOutput' colspan='3' style='white-space:pre-wrap'>".$order_data->patient_instructions."</td></tr>";
	}
	
	do_section($content, 'Order Requisition - '.$order_data->order_number);

		
	// loop through observations
	if ($order_data->status == 'x' || $order_data->status == 'z') { // skip until we have a result
?>
		<tr>
			<td>
				<div class='wmtSection'>
					<div class='wmtSectionTitle'>
						Result Observations - <?php echo $order_data->order_number ?>
					</div>
					<div class='wmtSectionBody'>
	
<?php
		// loop through each ordered item
		$last_code = "FIRST";
		foreach ($item_list as $order_item) {
			$key = $order_item->procedure_order_seq;
			$report_data = wmtResult::fetchResult($order_item->procedure_order_id, $key);
			if (!$report_data) continue; // no results yet

			$result_date = (strtotime($report_data->date_report))? date('Y-m-d',strtotime($report_data->date_report)): '';
?>
						<table style="width:100%">
							<tr>
								<td colspan="10" class="wmtLabel" style="text-align:left;font-size:.9em">
									<?php if ($last_code != "FIRST") echo "<br/>" ?>
									<?php echo $order_item->procedure_code ?> - <?php echo $order_item->procedure_name ?>
								</td>
							</tr>
<?php 
			if ($lab_data['protocol'] == 'INT') { // only when self resulted 
				if ($report_data->report_notes) {
?>
							<tr>
								<td style="min-width:10px">&nbsp;</td>
								<td class="wmtLabel" style="width:70px">
									Results:
								</td>
								<td colspan='8' class='wmtOutput' style="vertical-align:top">
									<?php echo nl2br($report_data->report_notes) ?>
								</td>
							</tr>
<?php 			} 
?>
							<tr>
								<td style="min-width:10px">&nbsp;</td>
								<td class="wmtLabel" style="width:70px">
									Status:
								</td>
								<td class="wmtOutput" style="vertical-align:top;min-width:80px">
									<?php echo ListLook($report_data->report_status, 'proc_res_status') ?>
								</td>
								<td class="wmtLabel">
									Reported:
								</td>
								<td class="wmtOutput" style="vertical-align:top;min-width:100px">
									<?php echo $result_date ?>
								</td>
<?php if ($report_data->source) { ?>
								<td class="wmtLabel">
									Clinician:
								</td>
								<td colspan="5" class="wmtOutput" style="vertical-align:top;width:40%">
									<?php echo UserIdLook($report_data->source) ?>
								</td>
<?php } ?>
								<td colspan='6' style="width:50%">&nbsp;</td>
							</tr>
						</table>

						<table style="width:100%">
<?php 		
			} // end INT results
						
			$last_code = $order_item->procedure_code;
			$result_list = wmtResultItem::fetchItemList($report_data->procedure_report_id);
			if (!result_list) continue; // no details yet

			// process each observation
			$first = true;
			foreach ($result_list AS $result_data) {
				if ($first) { // changed test code
					$first = false;
?>
							<tr><td>
								<table class="wmtInnerTable" style="width:100%">
							<tr style="font-size:9px;font-weight:bold">
								<td style="width:10px;min-width:10px">&nbsp;</td>
								<td style="text-align:left;width:25%">
									RESULT DESCRIPTION
								</td>
								<td style="text-align:left;width:10%">
									VALUE
								</td>
								<td style="text-align:left;width:10%">
									UNITS
								</td>
								<td style="text-align:left;width:10%">
									REFERENCE
								</td>
								<td style="text-align:center;width:12%">
									FLAG
								</td>
								<td style="text-align:center;width:12%">
									REPORTED
								</td>
								<td style="text-align:center;width:8%">
									STATUS
								</td>
								<td style="text-align:center;width:10%">
									LAB
								</td>
								<td></td>
							</tr>
<?php 
					$last_code = $result_data->result_code;
				}
	
				$abnormal = $result_data->abnormal; // in case they sneak in a new status
				if ($result_data->abnormal == 'H') $abnormal = 'High';
				if ($result_data->abnormal == 'L') $abnormal = 'Low';
				if ($result_data->abnormal == 'HH') $abnormal = 'Alert High';
				if ($result_data->abnormal == 'LL') $abnormal = 'Alert Low';
				if ($result_data->abnormal == '>') $abnormal = 'Panic High';
				if ($result_data->abnormal == '<') $abnormal = 'Panic Low';
				if ($result_data->abnormal == 'A') $abnormal = 'Abnormal';
				if ($result_data->abnormal == 'AA') $abnormal = 'Critical';
				if ($result_data->abnormal == 'S') $abnormal = 'Susceptible';
				if ($result_data->abnormal == 'R') $abnormal = 'Resistant';
				if ($result_data->abnormal == 'I') $abnormal = 'Intermediate';
				if ($result_data->abnormal == 'NEG') $abnormal = 'Negative';
				if ($result_data->abnormal == 'POS') $abnormal = 'Positive';
?>
									<tr style="line-height:15px;vertical-align:baseline;<?php if ($abnormal) echo 'font-weight:bold;color:#bb0000' ?>">
										<td>&nbsp;</td>
										<td class="printDetail" style="font-family:monospace">
											<?php echo $result_data->result_text ?>
										</td>
<?php 
				if ($result_data->result_data_type) { // there is an observation
					if ($result_data->units) {
?>
										<td class="printDetail" style="font-family:monospace">
											<?php if ($result_data->result != ".") echo htmlentities($result_data->result) ?>
										</td>
										<td class="printDetail" style="font-family:monospace;text-align:left">
											<?php echo htmlentities($result_data->units) ?>
										</td>
										<td class="printDetail" style="font-family:monospace;text-align:left">
											<?php echo htmlentities($result_data->range) ?>
										</td>
<?php 
					}
					elseif ($result_data->range) {
?>
										<td colspan='2' class="printDetail" style="font-family:monospace">
											<?php if ($result_data->result != ".") echo htmlentities($result_data->result) ?>
										</td>
										<td class="printDetail" style="font-family:monospace;text-align:left">
											<?php echo htmlentities($result_data->range) ?>
										</td>
<?php 				
					} else { 
?>
										<td colspan='3' class="printDetail" style="font-family:monospace;text-align:left">
											<?php if ($result_data->result != ".") echo nl2br($result_data->result) ?>
										</td>
<?php 
					} 
?>
										<td class="printDetail" style="font-family:monospace;text-align:center">
											<?php echo $abnormal ?>
										</td>
										<td class="printDetail" style="font-family:monospace;text-align:center">
											<?php echo (strtotime($result_data->date))? date('Y-m-d',strtotime($result_data->date)): '' ?>
										</td>
										<td class="printDetail" style="font-family:monospace;text-align:center">
											<?php echo htmlentities($result_data->result_status) ?>
										</td>
										<td class="printDetail" style="font-family:monospace;text-align:center">
											<?php echo htmlentities($result_data->facility) ?>
										</td>
										<td></td>
									</tr>
<?php
					if ($result_data->comments) { // put comments below test line
?>
									<tr <?php if ($abnormal) echo 'style="font-weight:bold;color:#bb0000"'?>>
										<td>&nbsp;</td>
										<td class="printDetail" colspan="8" style="padding-left:100px;font-family:monospace;text-align:left">
											<?php echo nl2br($result_data->comments); ?>
										</td>
										<td></td>
									</tr>
<?php 
					} // end if comments
				} // end if obser value
				else { 
?>
										<td class="printDetail" colspan="7" style="padding-left:20px;font-family:monospace;text-align:left">
											 <?php echo nl2br($result_data->comments); ?>
										</td>
										<td class="printDetail" style="font-family:monospace;text-align:center;width:10%">
											<?php echo htmlentities($result_data->facility) ?>
										</td>
										<td></td>
									</tr>
<?php
				} // end if observ 
			} // end result foreach
			echo "</table></td></tr>";
				
		} // end foreach ordered item
?>
						</table>
					</div>
				</div>
			</td>
		</tr>

<?php 
		$content = '';
		if ($order_data->reviewed_id) {
			$content = do_columns(UserIdLook($order_data->reviewed_id),'Reviewing Provider',date('Y-m-d',strtotime($order_data->reviewed_datetime)),'Reviewed Date');
		}
		if ($order_data->notified_id) {
			$content .= do_columns(UserIdLook($order_data->notified_id),'Notification By',date('Y-m-d',strtotime($order_data->notified_datetime)),'Notified Date');
			$content .= do_line($order_data->notified_person, 'Person Notified');
		}
		$notes = ($order_data->review_notes)? "<div style='white-space:pre-wrap'>".htmlspecialchars_decode($order_data->review_notes)."</div>" : "";
		$content .= do_line($notes,'Review Notes');
	
		do_section($content, 'Review Information');
	
	} // end results
?>
<?php 
	print "</table> <!-- frame -->\n</div> <!-- report -->";
	
} // end declaration 

} // end if function

?>
