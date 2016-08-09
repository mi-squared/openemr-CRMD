<?php
/** **************************************************************************
 *	LABOATORY/REPORT.PHP
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
 *  @subpackage generic
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
include_once("{$GLOBALS['srcdir']}/sql.inc");
include_once("{$GLOBALS['srcdir']}/api.inc");
include_once("{$GLOBALS['srcdir']}/wmt/wmt.class.php");
include_once("{$GLOBALS['srcdir']}/wmt/wmt.report.php");
//include_once("{$GLOBALS['srcdir']}/wmt/wmt.forms.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

if (!function_exists("laboratory_report")) { // prevent redeclarations

function laboratory_report($pid, $encounter, $cols, $id) {
	$form_name = 'laboratory';
	$form_title = 'Laboratory';
	$form_table = 'form_order';

	/* RETRIEVE FORM DATA */
	try {
		$order_data = new wmtOrder($form_name, $id);
		$pat_data = wmtPatient::getPidPatient($pid);
		$enc_data = wmtEncounter::getEncounter($encounter);
		$ins_list = wmtInsurance::getPidInsDate($pid,$order_data->date_ordered);
	
		$lab_id = ($order_data->lab_id) ? $order_data->lab_id : $_REQUEST['lab_id'];
		$lab_data = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?",array($lab_id));
	
		$item_list = wmtOrderItem::fetchItemList($order_data->order_number);
			
		$aoe_list = array();
		if ($order_data->procedure_order_id) {
			$query = "SELECT procedure_code, question_code, answer FROM procedure_answers WHERE procedure_order_id = ? ORDER BY procedure_code, answer_seq";
			$result = sqlStatement($query,array($order_data->procedure_order_id)); // labcorp stores by order not item
	
			$code = '';
			$aoe_items = array();
			while ($row = sqlFetchArray($result)) {
				if ($code && $code != $row['procedure_code']) {
					$aoe_list[$code] = $aoe_items;
					$aoe_items = array();
				}
				$aoe_items[$row['question_code']] = $row['answer'];
				$code = $row['procedure_code'];
			}
			if ($aoe_items && $code) $aoe_list[$code] = $aoe_items;
		}
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
	$content .= do_columns($entby,'Entering Clinician',ListLook($order_data->request_handling,'Lab_Handling'),'Special Handling');
	$notes = ($order_data->order_notes)? "<div style='white-space:pre-wrap'>".$order_data->order_notes."</div>" : "";
	$content .= do_line($notes,'Clinic Notes');
	do_section($content, 'Order Summary');
	
	// Loop through diagnosis
	$content = "<tr><td style='width:140px'></td><td style='width:80px'></td><td style='width:100px'></td><td></td></tr>\n";
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
	
	/* Order specimen
	$content = "<td style='width:120px'></td><td style='width:40px'></td><td style='width:120px'></td><td></td>\n";
	$collected = ($order_data->order_datetime)?date('Y-m-d h:i A',strtotime($order_data->order_datetime)):null;
	$pending = ($order_data->order_pending)?date('Y-m-d h:i A',strtotime($order_data->order_pending)):null;
	
	if ($order_data->order_psc) {
		$content .= do_line('Yes','PSC Hold Order');
	}
	else {
		$content .= do_columns('Yes','Sample Collected',$collected,'Collection Date');
		$content .= do_columns(ListLook($order_data->order_fasting,'LabCorp_Yes_No'),'Patient Fasting',$order_data->order_volume,'Specimen Volume');
	}
	$content .= do_break();
	*/
	
	// loop through requisitions
	$content = "<tr><td style='width:140px'></td><td style='width:80px'></td><td style='width:100px'></td><td></td></tr>\n";
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
				foreach ($list AS $comp) $comps[$comp] = "'$comp'";
				$codes = implode(",", $comps);
			}
	
			// component codes found
			if ($codes) {
				$query = "SELECT procedure_type_id AS id, procedure_code AS component, description, name AS title FROM procedure_type ";
				$query .= "WHERE activity = 1 AND lab_id = ".$lab_id." AND procedure_type = 'ord' ";
				$query .= "AND procedure_code IN ( ".$codes." ) ";
				$query .= "GROUP BY procedure_code ORDER BY procedure_code ";
				$result = sqlStatement($query);
		
				while ($profile = sqlFetchArray($result)) {
					$description = ($profile['description'])? $profile['description'] : $profile['title'];
					$content .= do_columns("","",$profile['component']." - ".$description,"Component",true);
					$need_blank = true;
				}
			}
		}

		// add AOE questions & answers if necessary
		$query = "SELECT pc.*, pq.question_code AS field, pq.question_text, pq.fldtype, pq.options, pq.tips, pq.section, pa.answer_seq, pa.answer FROM procedure_order_code pc ";
		$query .= "LEFT JOIN procedure_questions pq ON pq.lab_id = ? AND pc.procedure_code = pq.procedure_code ";
		$query .= "LEFT JOIN procedure_answers pa ON pa.question_code = pq.question_code AND pa.procedure_order_id = pc.procedure_order_id AND pa.procedure_order_seq = pc.procedure_order_seq ";
		$query .= "WHERE pc.procedure_order_id = ? AND pc.procedure_order_seq = ? AND pq.activity = 1 AND pa.answer != '' ";
		$query .= "ORDER BY pa.procedure_order_id, pa.procedure_order_seq, pa.answer_seq";
		$params = array($lab_id, $order_item->procedure_order_id, $order_item->procedure_order_seq);
		$result = sqlStatement($query,$params);
			
		$aoe_out = '';
		while ($aoe = sqlFetchArray($result)) {
			$question = str_replace(':','',$aoe['question_text']);
			if ($question) {
				$aoe_out .= "<tr><td class='wmtLabel' style='width:350px;white-space:nowrap'>".$question.": </td>\n";
//				$aoe_out .= "<td class='wmtOutput' style='white-space:nowrap'>".$aoe['answer']."</td></tr>\n";
				$answer = $aoe['answer'];
				if ($aoe['fldtype'] == 'L') // need list lookup
					$answer = ListLook($answer,$aoe['options']);
				$aoe_out .= "<td class='wmtOutput' style='white-space:nowrap'>".$answer."</td></tr>\n";
				$need_blank = true;
			}
		}
		if ($aoe_out) {
			$content .= "<tr><td></td><td colspan=3><table>$aoe_out</table></td></tr>";
//			$content .= do_columns('','','<table>'.$aoe_out.'</table>','',true);
		}

		if ($need_blank) $content .= do_blank(); // skip first time
	}
	// lab notes
	if ($order_data->clinical_hx || $order_data->patient_instructions)
		$content .= do_break();
	
	if ($order_data->clinical_hx) {
		$content .= "<tr><td class='wmtLabel'>Order Comments: </td><td class='wmtOutput' colspan='3' style='white-space:pre-wrap'>".$order_data->clinical_hx."</td></tr>";
	}
	
	// patient instructions
	if ($order_data->patient_instructions) {
		$content .= "<tr><td class='wmtLabel'>Patient Instructions: </td><td class='wmtOutput' colspan='3' style='white-space:pre-wrap'>".$order_data->patient_instructions."</td></tr>";
	}
	
	do_section($content, 'Order Requisition - '.$order_data->order_number);

	// loop through observations
	if ($order_data->status != 'i' && $order_data->status != 's' && $order_data->status != 'p' ) { // skip until we have a result
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
								<td colspan="10" class="wmtLabel" style="text-align:left;font-size:.9em;padding-left:10px">
<?php 		if ($last_code != "FIRST") echo "<br/>";
			echo $order_item->procedure_name;
			if ($order_item->reflex_code) echo "<br/>&nbsp;&nbsp;&nbsp;Reflex test triggered by: ".$reflex_data->result_code."&nbsp;&nbsp;".$reflex_data->result;
//			if ($report_data->date_report) echo " [".date('Y-m-d H:i:s',strtotime($report_data->date_report))."]";
			if ($report_data->report_status == 'Rejected') echo " [REJECTED]";
?>														
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
<?php 		
			} // end INT results
						
			$last_code = $order_item->procedure_code;
			
			if ($lab_data['protocol'] != 'INT') { // only when NOT self resulted 
				if ($report_data->report_notes) {
?>
							<tr><td colspan="9">
								<table class="wmtInnerTable" style="width:100%;margin-bottom:12px">
									<tr style="font-size:9px;font-weight:bold;">
										<td style="width:10px">&nbsp;</td>
										<td style="text-align:left">
											LABORATORY COMMENTS
										</td>
									</tr>
									<tr class="printDetail" style="font-family:monospace" >
										<td>&nbsp;</td>
										<td>
											<?php echo nl2br($report_data->report_notes) ?>
										</td>
									</tr>
								</table>
							</td></tr>
<?php
		 		}

	 			$specimen_list = wmtSpecimenItem::fetchItemList($report_data->procedure_report_id);
				if ($specimen_list) {
?>					
							<tr><td colspan="9">
								<table class="wmtInnerTable" style="width:100%;margin-bottom:12px">
									<tr style="font-size:9px;font-weight:bold">
										<td style="width:30px;min-width:30px">&nbsp;</td>
										<td style="width:15%">
											SPECIMEN
										</td>
										<td style="text-align:center;width:25%">
											SAMPLE COLLECTED
										</td>
										<td style="text-align:center;width:25%">
											SAMPLE RECEIVED
										</td>
										<td style="padding-left:20px">
											ADDITIONAL INFORMATION
										</td>
									</tr>
<?php 
					foreach ($specimen_list AS $specimen_data) {
						// add in details as notes if necessary
						$notes = '';
						if (count($specimen_data->details) > 0) { // need to process details
							foreach ($specimen_data->details AS $detail) {
								// merge details into a single note field
								if ($notes) $notes .= "<br/>\n";
								$note = $detail->observation_id[1]; // text
								$obvalue = $detail->observation_value;
								if (is_array($obvalue)) $obvalue = $obvalue[0]; // save text portion
								$note .= ": " . $obvalue . " " . $detail->observation_units;
								$notes .= htmlentities($note);
							}
						}
?>					
									<tr class="printDetail" style="font-family:monospace" >
										<td>&nbsp;</td>
										<td style="vertical-align:top">
											<?php echo $specimen_data->specimen_number ?>
										</td>
										<td style="text-align:center;vertical-align:top">
											<?php echo $specimen_data->collected_datetime ?>
										</td>
										<td style="text-align:center;vertical-align:top">
											<?php echo $specimen_data->received_datetime ?>
										</td>
										<td style="padding-left:20px;vertical-align:top">
											Type:
											<?php echo ($specimen_data->specimen_type) ? $specimen_data->specimen_type : 'UNKNOWN'; ?>
											<?php if ($specimen_data->type_modifier) echo "<br/>Modifier: $specimen_data->type_modifier"; ?>		
											<?php if ($specimen_data->specimen_additive) echo "<br/>Additive: $specimen_data->specimen_additive"; ?>		
											<?php if ($specimen_data->collection_method) echo "<br/>Method: $specimen_data->collection_method"; ?>		
											<?php if ($specimen_data->source_site) {
												echo "<br/>Source: $specimen_data->source_site"; 
												if ($specimen_data->source_quantifier && $specimen_data->source_site != $specimen_data->source_quantifier) 
													echo "( $specimen_data->source_quantifier )"; }
											?>		
											<?php if ($specimen_data->specimen_volume) echo "<br/>Volume: $specimen_data->specimen_volume"; ?>		
											<?php if ($specimen_data->specimen_condition) echo "<br/>Condition: $specimen_data->specimen_condition"; ?>		
											<?php if ($specimen_data->specimen_rejected) echo "<br/>Rejected: $specimen_data->specimen_rejected"; ?>		
											<?php if ($notes) echo "<br/>$notes"; ?>		
										</td>	
									</tr>		
<?php
	 				} // end foreach specimen
?>
			 					</table>
			 				</td></tr>
<?php 
				} // end if specimens
			} // end if not internal
										
			$result_list = wmtResultItem::fetchItemList($report_data->procedure_report_id);
//			if (!$result_list) continue; // no details yet

			// process each observation
			$first = true;
			foreach ($result_list AS $result_data) {
				// collect facility information
				if ($result_data->facility && !$facility_list[$result_data->facility]) {
					$facility = sqlQuery("SELECT * FROM procedure_facility WHERE code = ?",array($result_data->facility));
					if ($facility) $facility_list[$facility['code']] = $facility;
				}
				
				if ($first) { // changed test code
					$first = false;
?>
							<tr style="font-size:9px;font-weight:bold">
								<td style="width:30px;min-width:30px">&nbsp;</td>
								<td style="text-align:left">
									RESULT DESCRIPTION
								</td>
								<td style="text-align:left;width:10%">
									VALUE
								</td>
								<td style="text-align:left;width:11%">
									UNITS
								</td>
								<td style="text-align:left;width:10%">
									REFERENCE
								</td>
								<td style="text-align:center;width:10%">
									FLAG
								</td>
								<td style="text-align:center;width:12%">
									REPORTED
								</td>
								<td style="text-align:center;width:8%">
									STATUS
								</td>
								<td style="text-align:center;width:10%">
									FACILITY
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
					if ($result_data->result_data_type == 'TX') { // put TEXT on next line
?>
							</tr><tr style="line-height:15px;vertical-align:baseline;<?php if ($abnormal) echo 'font-weight:bold;color:#bb0000' ?>">
								<td colspan="1"></td>
<?php 				
					} 
					if ($result_data->units || $result_data->range || $abnormal) {
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
								<td style="font-family:monospace;text-align:center">
									<?php echo $abnormal ?>
								</td>
<?php 
					} else { 
?>
								<td colspan='4' class="printDetail" style="font-family:monospace;text-align:left">
									<?php if ($result_data->result != "." && $result_data->result_data_type != 'FT') echo htmlentities($result_data->result) ?>
								</td>
<?php 
					} 
?>
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
					if ($result_data->result_data_type == 'FT') { // put formatted text below test line
?>
							<tr <?php if ($abnormal) echo 'style="font-weight:bold;color:#bb0000"'?>>
								<td>&nbsp;</td>
								<td class="printDetail" colspan="8" style="padding-left:100px;font-family:monospace;text-align:left">
									<pre><?php echo $result_data->comments; ?></pre>
								</td>
								<td></td>
							</tr>
<?php 
					} // end if comments
					
					if ($result_data->comments) { // put formatted below test line
?>
							<tr <?php if ($abnormal) echo 'style="font-weight:bold;color:#bb0000"'?>>
								<td>&nbsp;</td>
								<td class="printDetail" colspan="8" style="padding-left:100px;font-family:monospace;text-align:left">
									<pre><?php echo $result_data->comments; ?></pre>
								</td>
								<td></td>
							</tr>
<?php 
					} // end if comments
				} // end if obser value
				else { 
?>
								<td colspan="7" style="padding-left:100px;font-family:monospace;text-align:left">
									 <pre><?php echo $result_data->comments; ?></pre>
								</td>
								<td style="font-family:monospace;text-align:center;width:10%">
									<?php echo htmlentities($result_data->facility) ?>
								</td>
								<td></td>
							</tr>
<?php
				} // end if observ 
			} // end result foreach
		} // end foreach ordered item
		
		// do we need a facility box at all?
		if (count($facility_list) > 0) {
?>
							<tr><td colspan="10" style="padding:10px 0 0 0">
								<table style="width:100%">
									<tr style="font-size:9px;font-weight:bold">
										<td style="min-width:10px;width:10px">&nbsp;</td>
										<td style="text-align:left;width:13%">
											FACILITY
										</td>
										<td style="width:25%">
											FACILITY TITLE
										</td>
										<td style="width:35%">
											CONTACT INFORMATION
										</td>
										<td style="width:22%">
											FACILITY DIRECTOR
										</td>
										<td></td>
									</tr>
<?php 
				foreach ($facility_list AS $facility_data) {
					if ($facility['phone']) {
						$phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $facility['phone']);
					}
					
					$director = $facility['director'];
					if ($facility['npi']) $director .= "<br/>NPI: ".$facility['npi']; // identifier

					$address = '';
					if ($facility['street']) $address .= $facility['street']."<br/>";
					if ($facility['street2']) $address .= $facility['street2']."<br/>";
					if ($facility['city']) $address .= $facility['city'].", ";
					$address .= $facility['state']."&nbsp;&nbsp;";
					if ($facility['zip'] > 5) $address .= preg_replace('~.*(\d{5})(\d{4}).*~', '$1-$2', $facility['zip']);
					else $address .= $facility['zip'];
?>					
									<tr style="font-family:monospace;vertical-align:baseline" >
										<td>&nbsp;</td>
										<td class="printDetail">
											<?php echo $facility['code'] ?>
										</td>
										<td class="printDetail">
											<?php echo $facility['name'] ?>
										</td>
										<td class="printDetail">
											<?php echo $address ?>
										</td>
										<td class="printDetail">
											<?php echo $director ?>
										</td>
									</tr>	
									<tr><td colspan="5">&nbsp;</td></tr>	
<?php
			} // end facility foreach
?>
								</table>
							</td></tr>	
<?php 
 		} // end facilities
?>
 						</table>
					</div>
				</div>
			</td>
		</tr>
 		
<?php 
		$content = "<tr><td style='width:140px'></td><td style='width:200px'></td><td style='width:100px'></td><td></td></tr>\n";
		if ($order_data->reviewed_id) {
			$content .= do_columns(UserIdLook($order_data->reviewed_id),'Reviewing Provider',date('Y-m-d',strtotime($order_data->reviewed_datetime)),'Reviewed Date');
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
