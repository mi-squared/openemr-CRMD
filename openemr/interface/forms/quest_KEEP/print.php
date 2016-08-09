<?php
/** **************************************************************************
 *	QUEST/PRINT.PHP
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
 *  @uses quest/report.php
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['incdir']}/forms/quest/report.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.class.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

// form information
$form_name = 'quest';
$form_title = 'Quest Diagnostics';
$form_table = 'form_quest';

// grab inportant stuff
$id = $_REQUEST['id'];

// Retrieve content
$encounter = ($encounter)? $encounter : $_REQUEST['enc'];
if (!$encounter) die ("FATAL ERROR: missing encounter identifier!!");
$enc_data = wmtEncounter::getEncounter($encounter);

$order_data = new wmtOrder($form_name,$id);
$pid = $order_data->pid;

// default to patient name in order record
$pat_name = $order_data->pat_lname.", ".$order_data->pat_fname;
$pat_name .= ($order_data->pat_mname) ? ' '.$order_data->pat_mname : '';

// use patient record name if present
$pat_data = wmtPatient::getPidPatient($pid);
if ($pat_data) {
	$pat_name = $pat_data->format_name;
}


?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php html_header_show();?>
		<title><?php echo $form_title ?> for <?php echo $pat_name; ?> on <?php echo $order_data->date; ?></title>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		
		<style>
			.printHeader { font-size: 8px }
			.printDetail { font-size: 11px }
			.printHidden { display: none }
		</style>
	</head>

	<body class="wmtPrint">
	    <div class="no-print" style="float:right">
	    	<input type="button" onclick="window.print()" value="print" />
	    </div>
	
		<h1><?php echo $enc_data->facility ?></h1>
	    <h2><?php echo $form_title ?></h2>
	    
		<table class="wmtHeader" style="margin-bottom:10px">
			<tr>
 				<td class="wmtHeaderLabel" style="width: 10%; text-align: left">Date:<input type="text" class="wmtHeaderOutput" readonly value="<?php echo date('Y-m-d'); ?>"></td>
				<td class="wmtHeaderLabel" style="width: auto; text-align: center">Patient Name:<input type="text" class="wmtHeaderOutput" style="width: 70%" readonly value="<?php echo $pat_name; ?>"></td>
				<td class="wmtHeaderLabel" style="width: 10%; text-align: right">Patient ID:<input type="text" class="wmtHeaderOutput" readonly value="<?php echo $order_data->pid; ?>"></td>
			</tr>
		</table>

		<div class="wmtSection">
			<div class="wmtSectionTitle">
				Patient Information
		  	</div>
		  	<div class="wmtSectionBody">
				<table>
					<tr>
						<!-- Left side -->
						<td style="width:300pt">
							<table style="border-right: solid 1px black; width:100%">
								<tr>
									<td class="wmtTitle" style="width:25%">Birth Date</td>
									<td class="wmtTitle" style="width:20%">Age</td>
									<td class="wmtTitle" style="width:28%">Gender</td>
									<td class="wmtTitle" colspan="2">Social</td>
											</tr>
											<tr>
									<td class="wmtOutput"><?php echo $pat_data->birth_date;?></td>
									<td class="wmtOutput"><?php echo $pat_data->age;?>&nbsp;</td>
									<td class="wmtOutput"><?php echo ListLook($pat_data->sex, 'sex'); ?>&nbsp;</td>
									<td class="wmtOutput" colspan="2"><?php echo $pat_data->ss; ?>&nbsp;</td>
								</tr>
								<tr>
									<td class="wmtTitle" colspan="2">Email Address</td>
									<td class="wmtTitle">Mobile</td>
									<td class="wmtTitle">Home</td>
								</tr>
								<tr>
									<td class="wmtOutput" colspan="2"><?php echo $pat_data->email;?>&nbsp;</td>
									<td class="wmtOutput"><?php echo $pat_data->phone_mobile;?>&nbsp;</td>
									<td class="wmtOutput"><?php echo $pat_data->phone_home;?>&nbsp;</td>
								</tr>
<?php /* ---------
								<tr>
									<td class="wmtTitle" colspan="2">Education</td>
									<td class="wmtTitle" colspan="2">Ethnicity</td>
								</tr>
								<tr>
									<td class="wmtOutput" colspan="2"><?php echo ListLook($pat_data->education, 'education'); ?>&nbsp;</td>
									<td class="wmtOutput" colspan="2"><?php echo ListLook($pat_data->ethnoracial, 'ethrace'); ?></td>
								</tr>
								<tr>
									<td class="wmtTitle">Dependents</td>
									<td class="wmtTitle">Employed</td>
									<td class="wmtTitle">Income</td>
									<td class="wmtTitle">Insurance</td>
								</tr>
								<tr>
									<td class="wmtOutput"><?php echo $pat_data->dependents; ?>&nbsp;</td>
									<td class="wmtOutput"><?php echo ListLook($pat_data->employment, 'employment'); ?>&nbsp;</td>
									<td class="wmtOutput"><?php echo ListLook($pat_data->income, 'income'); ?></td>
									<td class="wmtOutput"><?php echo ListLook($pat_data->insurance, 'insurance'); ?></td>
								</tr>
------------ */ ?>
					      	</table>
						</td>

						<!-- Right side -->
						<td style="width: 50%;padding-left:5px;vertical-align:top">
							<table>
								<tr>
									<td colspan="3" class="wmtTitle">Primary Address</td>
								</tr>
								<tr>
									<td colspan="3" class="wmtOutput">
										<?php echo $pat_data->street;?>&nbsp;<br/>
									</td>
								</tr>
								<tr>
									<td class="wmtTitle" style="width:47%">City</td>
									<td class="wmtTitle" style="width:30%">State</td>
									<td class="wmtTitle">Postal Code</td>
								</tr>
								<tr>
									<td class="wmtOutput">
										<?php echo $pat_data->city?>
									</td>
									<td class="wmtOutput">
										<?php echo ListLook($pat_data->state,'state')?>
									</td>
									<td class="wmtOutput">
										<?php echo $pat_data->zip ?>
									</td>
								</tr>
<?php /* ---------
								<tr>
									<td class="wmtTitle">Emergency Contact</td>
									<td class="wmtTitle" colspan="2">Relationship</td>
								</tr>
								<tr>
									<td class="wmtOutput"><?php echo $pat_data->emergency_contact;?>&nbsp;</td>
									<td class="wmtOutput" colspan="2"><?php echo ListLook($pat_data->emergency_relation, 'PSYCH_emergency'); ?>&nbsp;</td>
								</tr>
								<tr>
									<td class="wmtTitle">Contact Email</td>
									<td class="wmtTitle">Mobile Phone</td>
									<td class="wmtTitle">Home Phone</td>
								</tr>
								<tr>
									<td class="wmtOutput"><?php echo $pat_data->emergency_email;?></td>
									<td class="wmtOutput"><?php echo $pat_data->emergency_mobile;?></td>
									<td class="wmtOutput"><?php echo $pat_data->emergency_home;?></td>
								</tr>
------------ */ ?>
							</table>
						</td>
					</tr>
				</table>
			</div>				
		</div>

		<?php quest_report($pid, $encounter, '*', $id); ?>
	</body>
</html>