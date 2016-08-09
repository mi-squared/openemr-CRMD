<?php 
/** **************************************************************************
 *	LABORATORY/OrderRequest.PHP
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
 *  @version 2.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/tcpdf/tcpdf.php");

if (!class_exists("OrderRequest")) {
	/**
	 * The class LabCorpDocument is used to generate the lab documents for
	 * the LabCorp interface. It utilizes the TCPDF library routines to 
	 * generate the PDF documents.
	 *
	 */
	class OrderRequest extends TCPDF {
		/**
		 * Overrides the default header method to produce a custom document header.
		 * @return null
		 * 
		 */
		public function Header() {
			global $order_data, $pat_data;
			
			
			$pageNo = $this->PageNo();
			if ($pageNo > 1) { // starting on second page
				$acct = $order_data->request_account;
				$date = 'PSC HOLD';
				if ($order_data->date_ordered > 0)
					$date = date('m/d/Y',strtotime($order_data->date_ordered));
				$pubpid = $order_data->pid;
				if ($order_data->pubpid != $order_data->pid) $pubpid .= " ( ".$order_data->pid." )";
				$pat = $pat_data->lname . ", ";
				$pat .= $pat_data->fname . " ";
				$pat .= $pat_data->mname;
				
				$header = <<<EOD
<table style="width:80%;border:3px solid black">
	<tr>
		<td style="font-weight:bold;text-align:right">
			Account #:
		</td>
		<td style="text-align:left">
			&nbsp;$acct
		</td>
		<td style="font-weight:bold;text-align:right">
			Patient Name:
		</td>
		<td style="text-align:left">
			&nbsp;$pat
		</td>
	</tr>
	<tr>
		<td style="font-weight:bold;text-align:right">
			Requisition #:
		</td>
		<td style="text-align:left">
			&nbsp;$order_data->reqno
		</td>
		<td style="font-weight:bold;text-align:right">
			Patient ID:
		</td>
		<td style="text-align:left">
			&nbsp;$pubpid
		</td>
	</tr>
	<tr>
		<td style="font-weight:bold;text-align:right">
			Specimen Date:
		</td>
		<td style="text-align:left">
			&nbsp;$date
		</td>
		<td style="font-weight:bold;text-align:right">
			Page:
		</td>
		<td style="text-align:left">
EOD;
				$header .= "&nbsp;". $this->getAliasNumPage() ." of ". $this->getAliasNbPages();
				$header .= <<<EOD
		</td>
	</tr>
</table>
EOD;
				// add the header to the document
				$this->writeHTMLCell(0,0,120,'',$header,0,1,0,1,'C');
			} // end if second page
		} // end header

		
		/**
		 * Overrides the default footer method to produce a custom document footer.
		 * @return null
		 * 
		 */
		public function Footer() {
			global $order_data, $pat_data;
			
			$pageNo = $this->PageNo();
			if ($pageNo == 1) { // first page only
				$acct = $order_data->request_account;
				$date = 'ePSC';
				if ($order_data->date_ordered > 0)
					$date = date('m/d/Y',strtotime($order_data->date_ordered));
				$dob = '';
				if ($pat_data->DOB > 0)
					$dob = date('m/d/Y',strtotime($pat_data->DOB));
				$pat = $pat_data->lname . ", ";
				$pat .= $pat_data->fname . " ";
				$pat .= $pat_data->mname;
			
				$footer = <<<EOD
<table style="width:100%;font-size:7px">
	<tr>
		<td>
			<table>
				<tr>
					<td colspan="2"><b>$pat</b></td>
				</tr>
				<tr>
					<td>$dob</td>
					<td>$date</td>
				</tr>
				<tr>
					<td>$acct</td>
					<td>$order_data->reqno</td>
				</tr>
			</table>
		</td>
		<td>
			<table>
				<tr>
					<td colspan="2"><b>$pat</b></td>
				</tr>
				<tr>
					<td>$dob</td>
					<td>$date</td>
				</tr>
				<tr>
					<td>$acct</td>
					<td>$order_data->reqno</td>
				</tr>
			</table>
				</td>
		<td>
			<table>
				<tr>
					<td colspan="2"><b>$pat</b></td>
				</tr>
				<tr>
					<td>$dob</td>
					<td>$date</td>
				</tr>
				<tr>
					<td>$acct</td>
					<td>$order_data->reqno</td>
				</tr>
			</table>
				</td>
		<td>
			<table>
				<tr>
					<td colspan="2"><b>$pat</b></td>
				</tr>
				<tr>
					<td>$dob</td>
					<td>$date</td>
				</tr>
				<tr>
					<td>$acct</td>
					<td>$order_data->reqno</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
EOD;
//				$this->writeHTMLCell(0,0,30,'',$footer,0,1,0,1);
//				$this->writeHTMLCell(0,0,30,'',' ',0,1,0,1);
//				$this->writeHTMLCell(0,0,30,'',$footer,0,1,0,1);
			} // end if first page
		} // end footer
	} // end LabCorpRequest
} // end if exists

/**
 *
 * The makeOrderDocuments() creates a PDF requisition.
 *
 * 1. Create a PDF requisition document
 * 2. Store the document in the repository
 * 4. Return a reference to the document
 *
 * @access public
 * @param Request $request object
 * @return string $document PDF document as string
 * 
 */
if (!function_exists("makeOrderDocument")) {
	/**
	 * The makeOrderDocument function is used to generate the requisition for
	 * the LabCorp interface. It utilizes the TCPDF library routines to 
	 * generate the PDF document.
	 *
	 * @param Order $order object containing original input data
	 * @param Request $request object containing prepared request data
	 * 
	 */
	function makeOrderDocument(&$order_data,&$test_list,&$aoe_list) {
		$pat_data = wmtPatient::getPidPatient($order_data->pid);
		$lab = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?",array($order_data->lab_id));
		
		// retrieve insurance information
		$ins_primary = new wmtInsurance($order_data->ins_primary);
		$ins_secondary = new wmtInsurance($order_data->ins_secondary);
		
		if ($lab['npi'] == 'BIOREF') {
			if ($ins_primary) {
				$ins = sqlQuery("SELECT lab_identifier FROM insurance_companies WHERE id = ?",array($ins_primary->provider));
				$ins_primary->cms_id = $ins['lab_identifier'];
			}
			if ($ins_secondary) {
				$ins = sqlQuery("SELECT lab_identifier FROM insurance_companies WHERE id = ?",array($ins_secondary->provider));
				$ins_secondary->cms_id = $ins['lab_identifier'];
			}
		}
				
		// create new PDF document
		$pdf = new OrderRequest('P', 'pt', 'letter', true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('OpenEMR');
		$pdf->SetAuthor('Williams Medical Technologies, Inc.');
		$pdf->SetTitle($lab['name'].' Order #'.$order_data->request_account."-".$order_data->order_number);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set auto page breaks / bottom margin
		$pdf->SetAutoPageBreak(TRUE, 65);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setJPEGQuality ( 90 );

		// $pdf->setLanguageArray($l);

		// set font
		$pdf->SetFont('helvetica', '', 10);

		// set margins
		$pdf->SetMargins(30, PDF_MARGIN_TOP, 30);
		$pdf->SetHeaderMargin(15);
		$pdf->SetFooterMargin(90);
	
		// start page
		$pdf->AddPage();

		// set additional page margins
		$pdf->SetMargins(30, 70, 30, true);
		
		ob_start(); 
?>
<table style="width:100%">
	<tr>
		<td style="text-align:center;font-size:20px;font-weight:bold">
			<?php echo $lab['name'] ?>
		</td>
	</tr>
	<tr>
		<td style="text-align:center;font-weight:bold">
			Williams Medical Technologies, Inc.
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(10);
		
		$label = 'eORDER';
		if ($order_data->order_psc) $label = "PSC HOLD";
		if ($lab['protocol'] == 'INT') $label = 'INTERNAL';

		if (strtoupper($lab['npi']) == 'PATHGROUP') $label .= ' - PM';
		if (strtoupper($lab['npi']) == 'BIOREF') {
			$label = $lab['send_fac_id'].',';
			$label .= $order_data->order_number;
			$label .= strtoupper( substr($pat_data->lname, 0, 1) );
			$label .= strtoupper( substr($pat_data->fname, 0, 1) );
		}
		
		if ($order_data->request_handling == 'stat') {
			$label .= ' - STAT';
		}				
		ob_start();
?>
<table style="width:100%">
	<tr>
		<td style="width:50%;text-align:left;font-size:20px;font-weight:bold"><?php echo $label ?></td>
		<td style="width:60%;text-align:right">Page <?php echo $pdf->getAliasNumPage() ?> of <?php echo trim($pdf->getAliasNbPages()) ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black">
	<tr>
		<td style="width:50%">
			<table style="width:100%">
<?php 
	$order_data->reqno = $order_data->order_number;
	if ($lab['npi'] == '1194769497' || $lab['npi'] == 'CPL') {
		$order_data->reqno = $order_data->request_account."-".$order_data->order_number;
?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Network #:</td>
					<td>CPLOIRO2</td>
				</tr>
<?php 
	} // else not CPL 
	elseif ($order_data->request_account) {
?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Account #:</td>
					<td><?php echo $order_data->request_account ?></td>
				</tr>
<?php 
	} // end CPL checking 
?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Requisition #:</td>
					<td><?php echo $order_data->reqno ?></td>
				</tr>
			</table>
		</td>
		<td style="width:50%">
			<table style="width:100%">
<?php 
	if ($order_data->order_psc) {
?>
				<tr><td>&nbsp;</td></tr>
<?php 
	}
	else {
		$coll_date = date('m/d/Y',strtotime($order_data->date_collected));
		$coll_time = date('h:i A',strtotime($order_data->date_collected));
?>
		 
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Collection Date:</td>
					<td><?php echo $coll_date ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Collection Time:</td>
					<td><?php echo $coll_time ?></td>
				</tr>
<?php 
	}
	
	if ($order_data->copy_acct || $order_data->copy_fax || $order_data->copy_pat) {
		$copies = '';
		if ($order_data->copy_pat) {
			$copies = '<tr><td style="width:120px;font-weight:bold;text-align:right;vertical-align:top">Courtesy Copy:</td>';
			$copies .= "<td>Patient</td></tr>\n"; 
		}
		
		if ($order_data->copy_acct) {
			$copies .= '<tr><td style="width:120px;font-weight:bold;text-align:right;vertical-align:top">Copy Account:</td>';
			$copies .= "<td>".$order_data->copy_acct; 
			if ($order_data->copy_acctname) $copies .= "<br/>". $order_data->copy_acctname;
			$copies .= "</td></tr>\n";
		}
	
			if ($order_data->copy_fax) {
			$copies .= '<tr><td style="width:120px;font-weight:bold;text-align:right;vertical-align:top">Send Fax:</td>';
			$copies .= "<td>".substr($order_data->copy_fax, 0, 3) . '-' . substr($order_data->copy_fax, 3, 3) . '-' . substr($order_data->copy_fax, 6);; 
			if ($order_data->copy_faxname) $copies .= "<br/>". $order_data->copy_faxname;
			$copies .= "</td></tr>\n";
		}
		if ($copies) echo $copies;
	} // end copy to
?>
			</table>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(3);
		
		if ($order_data->facility_id)
			$facility = sqlQuery("SELECT * FROM facility WHERE id = $order_data->facility_id LIMIT 1");

		if ($order_data->provider_id)
			$provider = sqlQuery("SELECT * FROM users WHERE id = $order_data->provider_id LIMIT 1");
		
		ob_start();
?>
<table style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="font-size:.8em;width:50%;font-weight:bold;border:1px solid black">&nbsp;CLIENT / ORDERING SITE INFORMATION:</td>
		<td style="font-size:.8em;width:50%;font-weight:bold;border:1px solid black">&nbsp;ORDERING PHYSICIAN:</td>
	</tr>
	<tr>
		<td style="border-right:1px solid black;vertical-align:top;padding:5px">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Account Name:</td>
					<td><?php echo $facility['name'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Client Address:</td>
					<td><?php echo $facility['street'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($facility['city'])? $facility['city'].", ": "" ?><?php echo $facility['state'] ?>  <?php echo $facility['postal_code'] ?></td>
				</tr>
<?php if ($facility['phone']) { ?>
				<tr>
					<td style="font-weight:bold;text-align:right">Phone:</td>
					<td><?php echo $facility['phone'] ?></td>
				</tr>
<?php } ?>
			</table>
		</td>
		<td>
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Physician Name:</td>
					<td><?php echo $provider['lname'] ?>, <?php echo $provider['fname'] ?> <?php echo $provider['mname'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">NPI:</td>
					<td><?php echo $provider['npi'] ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(3);
		
		$self_guarantor = false;
		if ($pat_data->fname == $pat_data->guarantor_fname &&
				$pat_data->lname == $pat_data->guarantor_lname)
			$self_guarantor = true;
		
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse;margin-bottom:5px">
	<tr style="border:1px solid black;">
		<td colspan="2" style="font-size:.8em;font-weight:bold">
			&nbsp;PATIENT <?php if ($self_guarantor && $order_data->request_billing != 'C') echo "/ GUARANTOR "?>INFORMATION:
		</td>
	</tr>
	<tr>
		<td style="width:50%;border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Patient Name:</td>
					<td><?php echo $pat_data->lname ?>, <?php echo $pat_data->fname ?> <?php echo $pat_data->mname ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Patient Address:</td>
					<td><?php echo $pat_data->street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($pat_data->city)? $pat_data->city.", ": "" ?><?php echo $pat_data->state ?> <?php echo $pat_data->postal_code ?></td>
				</tr>
<?php if ($pat_data->phone_home) { ?>
				<tr>
					<td style="font-weight:bold;text-align:right">Phone:</td>
					<td><?php echo $pat_data->phone_home ?></td>
				</tr>
<?php } ?>
<?php if ($self_guarantor && $order_data->request_billing != 'C') { ?>
				<tr>
					<td style="font-weight:bold;text-align:right">Guarantor:</td>
					<td><?php echo ($order_data->work_flag)? "Work Comp": "Self" ?></td>
				</tr>
<?php } ?>
</table>
		</td>
		<td style="width:50%;border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Patient ID:</td>
					<td>
						<?php echo ($pat_data->pubpid)? $pat_data->pubpid: $pat_data->pid ?>
					</td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Gender:</td>
					<td><?php echo $pat_data->sex ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Date of Birth:</td>
					<td>
						<?php echo ($pat_data->DOB)? date('m/d/Y',strtotime($pat_data->DOB)): '' ?>
						<?php //echo ($order_data->pat_age)? ' ( '.$order_data->pat_age.' years )': '' ?>
					</td>
				</tr>
<?php if ($pat_data->race) { ?>				
				<tr>
					<td style="font-weight:bold;text-align:right">Race:</td>
					<td>
						<?php echo ListLook($pat_data->race,'Race') ?>
						<?php echo ($pat_data->ethnicity)? ' ('.ListLook($pat_data->ethnicity,'Ethnicity').')': '' ?>
					</td>
				</tr>
<?php } ?>
<?php if ($pat_data->pid != $pat_data->pubpid) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Alt Patient ID:</td>
					<td>
						<?php echo $pat_data->pid ?>
					</td>
				</tr>
<?php } ?>
			</table>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);
		
		ob_start();
		
?>
<table style="width:100%">
	<tr>
		<td colspan="2" style="font-size:1.3em;font-weight:bold">Order Information</td>
	</tr>
</table>
<?php 
		$adtl_done = false; // done additional data section
		if (count($test_list) < 5) { // one section only
			$adtl_done = true;
?>
<table style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr style="border:1px solid black">
		<td style="width:10%;font-size:.8em;font-weight:bold">&nbsp;TEST ID</td>
		<td style="width:40%;border-right:1px solid black;font-size:.8em;font-weight:bold">&nbsp;TEST DESCRIPTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(total:<?php echo count($test_list) ?>)</td>
		<td style="width:50%;font-size:.8em;font-weight:bold">&nbsp;ADDITIONAL INFORMATION:</td>
	</tr>
	<tr>
		<td colspan="2" style="width:50%;border:1px solid black">
			<table style="width:100%">
<?php 
			foreach ($test_list AS $test_data) {
?>
				<tr>
					<td style="width:68px;text-align:left"><?php echo $test_data['code'] ?></td>
					<td style="width:90%"><?php echo $test_data['name'] ?></td>
				</tr>
<?php 
			} // end foreach test
?>			
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
<?php if ($order_data->specimen_fasting) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Patient Fasting:</td>
					<td><?php echo $order_data->specimen_fasting; ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->pat_height > 0) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Height (in):</td>
					<td><?php printf('%03s',intval($order_data->pat_height)) ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->pat_weight > 0) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Weight (lbs):</td>
					<td><?php printf('%03s',intval($order_data->pat_weight)) ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->specimen_volume) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Volume (mls):</td>
					<td><?php printf('%04s',intval($order_data->specimen_volume)) ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->specimen_source) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Sample Source:</td>
					<td><?php  echo $order_data->specimen_source ?></td>
				</tr>
<?php } 
 
	if (is_array($aoe_list)) {
		foreach($aoe_list AS $aoe_data) {
			if ($aoe_data['answer'] && $aoe_data['answer'] != '_blank') {
?>
				<tr>
					<td colspan=2>[<?php echo $aoe_data['procedure_code'] ?>] <?php echo $aoe_data['question_text'] ?>: &nbsp; <?php echo $aoe_data['answer'] ?></td>
				</tr>
<?php 
			}
		} 
	}
?>
				<tr><td>&nbsp;</td></tr>
			</table>
		</td>
<?php 
		} else { // two sections
			$half = round(count($test_list) / 2);
?>
<table style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr style="border:1px solid black">
		<td style="width:10%;font-size:.8em;font-weight:bold">&nbsp;TEST ID</td>
		<td style="width:40%;border-right:1px solid black;font-size:.8em;font-weight:bold">&nbsp;TEST DESCRIPTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(total:<?php echo count($test_list) ?>)</td>
		<td style="width:10%;font-size:.8em;font-weight:bold">&nbsp;TEST ID</td>
		<td style="width:40%;font-size:.8em;font-weight:bold">&nbsp;TEST DESCRIPTION</td>
	</tr>
	<tr style="padding-top:5px">
<?php 
			$test = 99;
			foreach ($test_list AS $test_data) {
				if ($test > $half) {
					if ($test != 99) {
?>
			</table>
		</td>
<?php 
					} // end if first split
?>
		<td colspan="2" style="width:50%;border:1px solid black">
			<table style="width:100%">
<?php 
					$test = 0;
				} // end new column
				$test++;
?>
				<tr>
					<td style="width:68px"><?php echo $test_data['code'] ?></td>
					<td style="width:330px"><?php echo $test_data['name'] ?></td>
				</tr>
<?php 
			} // end foreach test
?>			
			</table>
		</td>
<?php 
		} // end section selection
?>
	</tr>
</table>		
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(3);
		
		ob_start();
		$do_section = false;
		if ($adtl_done && ($order_data->clinical_hx || $order_data->patient_instructions) ) { // do we need this section?
			$do_section = true;
			$sec_title = '';
			if ($order_data->clinical_hx) $sec_title = 'ORDER COMMENTS';
			if ($order_data->patient_instructions) {
				if ($sec_title) $sec_title .= " / ";
				$sec_title .= "PATIENT INSTRUCTIONS";
			}
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;ORDER COMMENTS:</td>
	</tr>
	<tr>
		<td>
			<table style="width:100%">
<?php if ($order_data->clinical_hx) { ?>
				<tr>
					<td>
						<strong>Clinical: </strong><?php echo $order_data->clinical_hx ?>
					</td>
				</tr>
<?php } ?>
<?php if ($order_data->patient_instructions) { ?>
				<tr>
					<td>
						<strong>Patient: </strong><?php echo $order_data->patient_instructions ?>
					</td>
				</tr>
<?php } ?>
			</table>
		</td>
	</tr>
</table>		
<?php 
		} // end if
		
		if (!$adtl_done) { // need this section
			$do_section = true;
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;ORDER INFORMATION:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;AOE RESPONSES:</td>
	</tr>
	<tr>
		<td>
<?php if ($order_data->clinical_hx || $order_data->patient_instructions) { ?>
			<table style="width:100%">
<?php if ($order_data->clinical_hx) { ?>
				<tr>
					<td>
						<strong>Clinical: </strong><?php echo $order_data->clinical_hx ?>
		</td>
				</tr>
<?php } ?>
<?php if ($order_data->patient_instructions) { ?>
				<tr>
					<td>
						<strong>Patient: </strong><?php echo $order_data->patient_instructions ?>
					</td>
				</tr>
<?php } ?>
			</table>
<?php } ?>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
<?php if ($order_data->specimen_fasting) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Patient Fasting:</td>
					<td><?php echo $order_data->specimen_fasting; ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->pat_height > 0) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Height (in):</td>
					<td><?php echo $order_data->pat_height ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->pat_weight > 0) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Weight (lbs):</td>
					<td><?php echo $order_data->pat_weight ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->specimen_volume) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Volume (mls):</td>
					<td><?php echo $order_data->specimen_volume ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->specimen_source) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Sample Source:</td>
					<td><?php echo $order_data->specimen_source ?></td>
				</tr>
<?php } 
 
	if (is_array($aoe_list)) {
		foreach($aoe_list AS $aoe_data) {
?>
				<tr>
					<td colspan=2>[<?php echo $aoe_data['procedure_code'] ?>] <?php echo $aoe_data['question_text'] ?>: &nbsp; <?php echo $aoe_data['answer'] ?></td>
				</tr>
<?php 
		} 
	}
?>
			</table>
		</td>
	</tr>
</table>		
<?php
		} // end if section needed 

		$output = ob_get_clean(); // clean buffer regardless
		if ($do_section) { 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(3);
		}
			
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td colspan="6" style="font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;DIAGNOSIS CODES:</td>
	</tr>
	<tr>
<?php 
		$diag_array = explode("|",$order_data->diagnoses); // code & text
		if (!is_array($diag_array)) $diag_array = array();
	
		for ($i = 0; $i < 6;) {
			$diag = $diag_array[$i++];
			list($dx_code,$dx_text) = explode("^",$diag);
?>
		<td style="border:1px solid black">&nbsp;<?php echo str_replace('CD', '', $dx_code) ?></td>
<?php 
		} // end for loop

		if ($diag_array[$i]) {
			echo "</tr><tr>";
			for ($i = 6; $i < 12;) {
				$diag = $diag_array[$i++];
				list($dx_code,$dx_text) = explode("^",$diag);
?>
		<td style="border:1px solid black">&nbsp;<?php str_replace('CD', '', $dx_code) ?></td>
<?php 
			} // end for loop
		} // end if
?>
	</tr>
</table>
<?php 		
		$output = ob_get_clean(); // clean buffer regardless
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);
		
		ob_start();
?>
<table style="width:100%">
	<tr>
		<td colspan="2" style="font-size:1.3em;font-weight:bold">Billing Information</td>
	</tr>
	<tr>
		<td style="border:1px solid black"><span style="font-size:.8em;font-weight:bold">
				&nbsp;BILL TYPE:&nbsp;&nbsp;</span><?php if ($order_data->request_billing) echo ListLook($order_data->request_billing,'Lab_Billing') ?>&nbsp;
		</td>
		<td style="border:1px solid black"><span style="font-size:.8em;font-weight:bold">
				&nbsp;INS CODE:&nbsp;&nbsp;</span>
<?php 
		if ($order_data->request_billing == 'T') {
			if ($order_data->work_insurance) {
				echo ListLook($order_data->work_insurance,'Workers_Insurance');
			}
			else { 
				echo ($ins_primary->cms_id) ? $ins_primary->cms_id : $ins_primary->id;
			}
		}		
?>
			&nbsp;
		</td>
	</tr>
</table>		
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(3);
	
		if (!$self_guarantor && $order_data->request_billing != 'C') { // only needed when not patient
			ob_start();
			$gname = '';
			$gstreet = '';
			$gaddr = '';
			
			$relation = 'Unknown'; // default to other
			if ($pat_data->guarantor_relation)
				$relation = ListLook($pat_data->guarantor_relation,'Relationship');
			
			if ($pat_data->guarantor_lname) {
				$gname = $pat_data->guarantor_lname .", ". $pat_data->guarantor_fname ." ". $pat_data->guarantor_mname;
			}
			else { // self
				$relation = 'Self'; 
				$gname = $pat_data->lname .", ". $pat_data->fname ." ". $pat_data->mname;
			}
							
			if ($pat_data->guarantor_city) {
				$gstreet = $pat_data->guarantor_street;
				$gaddr = $pat_data->guarantor_city .", ". $pat_data->guarantor_state ." ". $pat_data->guarantor_zip;
			} 
			else { // self
				$gstreet = $pat_data->street;
				$gaddr = $pat_data->city .", ". $pat_data->state ." ". $pat_data->postal_code;
			}
				
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td colspan="2" style="font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;RESPONSIBLE PARTY / GUARANTOR INFORMATION:</td>
	</tr>
	<tr>
		<td style="width:50%">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Guarantor:</td>
					<td>
						<?php echo $gname ?>
					</td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Address:</td>
					<td><?php echo $gstreet ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td>
						<?php echo $gaddr ?>
					</td>
				</tr>
			</table>
		</td>
		<td style="width:50%">
			<table style="width:100%">
				<tr>
					<td style="font-weight:bold;text-align:right">Relationship:</td>
					<td><?php echo $relation ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right"></td>
					<td></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(3);
		} // end self guaranteed
		
		if ($order_data->order_abn_signed || $order_data->work_flag ) {
			ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="text-align:right;font-weight:bold">ABN Signed: </td>
		<td><?php echo ($order_data->order_abn_signed)? ListLook($order_data->order_abn_signed,'LabCorp_Yes_No'): '' ?></td>
		<td style="text-align:right;font-weight:bold">Worker's Comp: </td>
		<td><?php echo ($order_data->work_flag)? ListLook($order_data->work_flag,'Order_Yes_No'): '' ?></td>
		<td style="text-align:right;font-weight:bold">Date of Injury: </td>
		<td><?php echo ($order_data->work_flag)? date('m/d/Y',strtotime($order_data->work_date)): '' ?></td>
	</tr>
</table>		
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(3);
		} // end extra bar
		
		if ($order_data->request_billing == 'T') { // third-party so need insurance
			ob_start();
		
			if ($order_data->work_flag) { // workers comp insurance
				$ins_work = wmtInsurance::getCompany($order_data->work_insurance);
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;WORKERS COMP INSURANCE:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;INSURED EMPLOYEE:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insurance Code:</td>
					<td><?php echo $ins_work['cms_id'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Company Name:</td>
					<td><?php echo $ins_work['company_name'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Ins Address:</td>
					<td><?php echo $ins_work['line1'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_work['city'])? $ins_work['city'].', ': '' ?><?php echo $ins_work['state'] ?> <?php echo $ins_work['zip'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Case Number:</td>
					<td><?php echo $order_data->work_case ?></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insured Name:</td>
					<td><?php echo $pat_data->lname ?>, <?php echo $pat_data->fname ?> <?php echo $pat_data->mname ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Insured Address:</td>
					<td><?php echo $pat_data->street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($pat_data->city)? $pat_data->city.', ': '' ?><?php echo $pat_data->state ?> <?php echo $pat_data->postal_code ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Employer:</td>
					<td><?php echo $order_data->work_employer ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
				$output = ob_get_clean(); 
				$pdf->writeHTMLCell(0,0,'','',$output,0,1);
				$pdf->ln(15);
			} // end workers comp insurance
			elseif ($order_data->ins_primary && $order_data->ins_secondary) { // two insurance plans
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;PRIMARY INSURANCE:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;SECONDARY INSURANCE:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insurance Code:</td>
					<td><?php echo ($ins_primary->cms_id) ? $ins_primary->cms_id : $ins_primary->id; ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Company Name:</td>
					<td><?php echo $ins_primary->company_name ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Ins Address:</td>
					<td><?php echo $ins_primary->line1 ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_primary->city)? $ins_primary->city.', ': '' ?><?php echo $ins_primary->state ?> <?php echo $ins_primary->zip ?><?php if ($ins_primary->plus_four) echo "-".$ins_primary->plus_four ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Policy Number:</td>
					<td><?php echo $ins_primary->policy_number ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Group Number:</td>
					<td><?php echo $ins_primary->group_number ?></td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insurance Code:</td>
					<td><?php echo ($ins_secondary->cms_id) ? $ins_secondary->cms_id : $ins_secondary->id; ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Company Name:</td>
					<td><?php echo $ins_secondary->company_name ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Ins Address:</td>
					<td><?php echo $ins_secondary->line1 ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_secondary->city)? $ins_secondary->city.', ': '' ?><?php echo $ins_secondary->state ?> <?php if ($ins_secondary->plus_four) echo "-".$secondary['plus_four'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Policy Number:</td>
					<td><?php echo $ins_secondary->policy_number ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Group Number:</td>
					<td><?php echo $ins_secondary->group_number ?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;PRIMARY POLICY HOLDER / INSURED:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;SECONDARY POLICY HOLDER / INSURED:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insured Name:</td>
					<td><?php echo $ins_primary->subscriber_lname ?>, <?php echo $ins_primary->subscriber_fname ?> <?php echo $ins_primary->subscriber_mname ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Insured Address:</td>
					<td><?php echo $ins_primary->subscriber_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_primary->subscriber_city)? $ins_primary->subscriber_city.', ': '' ?><?php echo $ins_primary->subscriber_state ?> <?php echo $ins_primary->subscriber_postal_code ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Relationship:</td>
					<td><?php echo ($ins_primary->subscriber_relationship)? ListLook($ins_primary->subscriber_relationship,'sub_relation') : "Other" ?></td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insured Name:</td>
					<td><?php echo $ins_secondary->subscriber_lname ?>, <?php echo $ins_secondary->subscriber_fname ?> <?php echo $ins_secondary->subscriber_mname ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Insured Address:</td>
					<td><?php echo $ins_secondary->subscriber_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_secondary->subscriber_city)? $ins_secondary->subscriber_city.', ': '' ?><?php echo $ins_secondary->subscriber_state ?> <?php echo $ins_secondary->subscriber_postal_code ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Relationship:</td>
					<td><?php echo ($ins_secondary->subscriber_relationship)? ListLook($ins_secondary->subscriber_relationship,'sub_relation') : "Other"  ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
				$output = ob_get_clean(); 
				$pdf->writeHTMLCell(0,0,'','',$output,0,1);
				$pdf->ln(15);
			} 
			elseif ($order_data->ins_primary) { // only one insurance plan
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;PRIMARY INSURANCE:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;PRIMARY POLICY HOLDER / INSURED:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insurance Code:</td>
					<td><?php echo $ins_primary->cms_id ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Company Name:</td>
					<td><?php echo $ins_primary->company_name ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Ins Address:</td>
					<td><?php echo $ins_primary->line1 ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_primary->city)? $ins_primary->city.', ': '' ?><?php echo $ins_primary->state ?> <?php echo $ins_primary->zip ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Policy Number:</td>
					<td><?php echo $ins_primary->policy_number ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Group Number:</td>
					<td><?php echo $ins_primary->group_number ?></td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insured Name:</td>
					<td><?php echo $ins_primary->subscriber_lname ?>, <?php echo $ins_primary->subscriber_fname ?> <?php echo $ins_primary->subscriber_mname ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Insured Address:</td>
					<td><?php echo $ins_primary->subscriber_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_primary->subscriber_city)? $ins_primary->subscriber_city.', ': '' ?><?php echo $ins_primary->subscriber_state ?> <?php echo $ins_primary->subscriber_postal_code ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Relationship:</td>
					<td><?php echo ($ins_primary->subscriber_relationship)? ListLook($ins_primary->subscriber_relationship,'sub_relation') : "Other"  ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
				$output = ob_get_clean(); 
				$pdf->writeHTMLCell(0,0,'','',$output,0,1);
				$pdf->ln(15);
			} // end single insurance
		} // end if insurance
		
/*		ob_start();
?>
<table nobr="true" style="width:100%;font-size:0.7em">
	<tr>
		<td colspan="2"><span style="font-size:1.3em;font-weight:bold">Authorization</span> - Please sign and date</td>
	</tr><tr>
		<td colspan="2">
			I hereby authorize the release of medical information related to the services described hereon and authorize payment directly to <?php echo $lab['name'] ?>.
			I agree to assume responsibility for payment of charges for laboratory services that are not covered by my healthcare insurer.
		</td>
	</tr><tr>
		<td><br/></td>
	</tr><tr>
		<td>
			<table style="width:100%">
				<tr><td colspan="3">&nbsp;</td></tr>
				<tr><td colspan="3">&nbsp;</td></tr>
				<tr>
					<td style="width:400px;border-top:1px solid black">Patient Signature</td>
					<td style="width:40px"></td>
					<td style="width:100px;border-top:1px solid black">Date</td>
				</tr>
				<tr><td colspan="3">&nbsp;</td></tr>
				<tr><td colspan="3">&nbsp;</td></tr>
				<tr><td colspan="3">&nbsp;</td></tr>
				<tr>
					<td style="width:400px;border-top:1px solid black">Physician Signature</td>
					<td style="width:40px"></td>
					<td style="width:100px;border-top:1px solid black">Date</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
*/
		// finish page
		$pdf->lastPage();

//		$TEST = true;
//		if ($TEST) {
//			$pdf->Output('label.pdf', 'I'); // force display download
//		}
//		else {
//			$document = $pdf->Output('requisition.pdf','S'); // return as variable
			
//			$CMDLINE = "lpr -P $printer ";
//			$pipe = popen("$CMDLINE" , 'w' );
//			if (!$pipe) {
//				echo "Label printing failed...";
//			}
//			else {
//				fputs($pipe, $label);
//				pclose($pipe);
//				echo "Labels printing at $printer ...";
//			}
//		}

		$document = $pdf->Output('order'.$order_data->order_number.'.pdf','S'); // return as variable
		return $document;

	} // end makeOrderDocument
} // end if exists
