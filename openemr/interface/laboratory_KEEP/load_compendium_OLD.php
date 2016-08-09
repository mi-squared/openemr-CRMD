<?php
/**
 * Administrative loader for lab compendium data.
 *
 * Supports loading of lab order codes and related order entry questions from CSV
 * format into the procedure_order and procedure_questions tables, respectively.
 *
 * Copyright (C) 2012 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @author    Rod Roark <rod@sunsetsystems.com>
 * 
 * Adapted for use with the dedicated laboratory interfaces developed
 * for Williams Medical Technologies, Inc.
 * 
 * @since		2014-06-15
 * @author		Ron Criswell <ron.criswell@MDTechSvcs.com>
 */

set_time_limit(0);

$sanitize_all_escapes  = true;
$fake_register_globals = false;

/* Turn off output buffering */
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);
// Implicitly flush the buffer(s)
ini_set('implicit_flush', true);
ob_implicit_flush(true);
// Disable apache output buffering/compression
if (function_exists('apache_setenv')) {
	apache_setenv('no-gzip', '1');
	apache_setenv('dont-vary', '1');
}

require_once("../globals.php");
require_once("$srcdir/acl.inc");

// This array is an important reference for the supported labs and their NPI
// numbers as known to this program.  The clinic must define at least one
// address book entry for a lab that has a supported NPI number.
//
$lab_npi = array(
		'1235186800' => 'Pathgroup Labs LLC',
		'1598760985' => 'Yosemite Pathology Medical Group',
		'1194769497' => 'Clinical Pathology Laboratories',
		'1548208440' => 'Interpath Laboratory',
		'QUEST' 	 => 'Quest Diagnostics',
		'LABCORP' 	 => 'LabCorp Laboratory',
);

/**
 * Get lab's ID from the users table given its NPI.  If none return 0.
 *
 * @param  string  $npi           The lab's NPI number as known to the system
 * @return integer                The numeric value of the lab's address book entry
 */
function getLabID($npi) {
	$lrow = sqlQuery("SELECT ppid FROM procedure_providers WHERE " .
			"npi = ? ORDER BY ppid LIMIT 1",
			array($npi));
	if (empty($lrow['ppid'])) return 0;
	return intval($lrow['ppid']);
}

if (!acl_check('admin', 'super')) die(xlt('Not authorized','','','!'));

$form_step   = isset($_POST['form_step']) ? trim($_POST['form_step']) : '0';
$form_status = isset($_POST['form_status' ]) ? trim($_POST['form_status' ]) : '';

if (!empty($_POST['form_import'])) $form_step = 1;

// When true the current form will submit itself after a brief pause.
$auto_continue = false;

// Set up main paths.
$EXPORT_FILE = $GLOBALS['temporary_files_dir'] . "/openemr_config.sql";
?>
<html>

<head>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<title><?php echo xlt('Load Lab Configuration'); ?></title>
</head>

<body class="body_top">
		&nbsp;<br />
		<form method='post' action='load_compendium.php'
			enctype='multipart/form-data'>

			<table>

<?php
	if ($form_step == 0) {
		echo " <tr>\n";
		echo "  <td style='width:5%;text-align:right' nowrap>" . xlt('Vendor') . "</td>\n";
		echo "  <td><select name='vendor'>";
		foreach ($lab_npi as $key => $value) {
			echo "<option value='" . attr($key) . "'";
			if (!getLabID($key)) {
				// Entries with no matching address book entry will be disabled.
				echo " disabled";
			}
			echo ">" . text($key) . ": " . text($value) . "</option>";
		}
		echo "</td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td style='text-align:right' nowrap>" . xlt('Action') . "</td>\n";
		echo "  <td><select name='action'>";
		echo "<option value='1'>" . xlt('Load Order Definitions'    ) . "</option>";
		echo "<option value='4'>" . xlt('Load Profile Definitions'    ) . "</option>";
		echo "<option value='2'>" . xlt('Load Order Entry Questions') . "</option>";
		echo "<option value='3'>" . xlt('Load OE Question Options'  ) . "</option>";
		echo "</td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td nowrap style='text-align:right'>" . xlt('Container Group Name') . "</td>\n";
		echo "  <td><select name='group'>";
		$gres = sqlStatement("SELECT procedure_type_id, name FROM procedure_type " .
			"WHERE procedure_type = 'grp' AND parent = 0 ORDER BY name, procedure_type_id");
		while ($grow = sqlFetchArray($gres)) {
			echo "<option value='" . attr($grow['procedure_type_id']) . "'>" .
					text($grow['name']) . "</option>";
		}
		echo "</td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td nowrap style='text-align:right'>" . xlt('File to Upload') . "</td>\n";
		echo "<td><input type='hidden' name='MAX_FILE_SIZE' value='30000000' />";
		echo "<input type='file' name='userfile' /></td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td nowrap>&nbsp;</td>\n";
		echo "  <td><input type='submit' value='" . xla('Submit') . "' /></td>\n";
		echo " </tr>\n";
	}

	echo " <tr>\n";
	echo "  <td colspan='2'>\n";

	if ($form_step == 1) {
		// Process uploaded config file.
		if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
			$form_vendor = $_POST['vendor'];
			$form_action = intval($_POST['action']);
			$form_group  = intval($_POST['group']);
			$lab_id = getLabID($form_vendor);

			$form_status .= xlt('Applying') . "...<br />";
			echo nl2br($form_status);

			$fhcsv = fopen($_FILES['userfile']['tmp_name'], "r");

			if ($fhcsv) {
				// -----------------------------------------------------------------------------------------------------------------
				// Vendor = Pathgroup
				// -----------------------------------------------------------------------------------------------------------------
				if ($form_vendor == '1235186800') {

				if ($form_action == 1) { // load compendium
						// Mark all "ord" rows having the indicated parent as inactive.
						sqlStatement("UPDATE procedure_type SET activity = 0 WHERE " .
							"parent = ? AND procedure_type = 'ord'",
							array($form_group));

						// What should be uploaded is the "Compendium" spreadsheet provided by
						// PathGroup, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Values for each row are:
						//   0: Order Code  : mapped to procedure_code of order type
						//   1: Order Name  : mapped to name of order type
						//   2: Result Code : mapped to procedure_code of result type
						//   3: Result Name : mapped to name of result type
						//
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);
							if (count($acsv) < 4 || $acsv[0] == "Order Code") continue;
							$standard_code = empty($acsv[2]) ? '' : ('CPT4:' . $acsv[2]);

							// Update or insert the order row, if not already done.
							$trow = sqlQuery("SELECT * FROM procedure_type WHERE " .
									"parent = ? AND procedure_code = ? AND procedure_type = 'ord' " .
									"ORDER BY procedure_type_id DESC LIMIT 1",
									array($form_group, $acsv[0]));
							if (empty($trow['procedure_type_id']) || $trow['activity'] == 0) {
								if (empty($trow['procedure_type_id'])) {
									$ptid = sqlInsert("INSERT INTO procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?",
										array($form_group, $acsv[1], $lab_id, $acsv[0], 'ord'));
								}
								else {
									$ptid = $trow['procedure_type_id'];
									sqlStatement("UPDATE procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " .
										"activity = 1 WHERE procedure_type_id = ?",
										array($form_group, $acsv[1], $lab_id, $acsv[0], 'ord', $ptid));
								}
								sqlStatement("UPDATE procedure_type SET activity = 0 WHERE " .
									"parent = ? AND procedure_type = 'res'",
									array($ptid));
							}

							// Update or insert the result row.
							// Not sure we need this, but what the hell.
							$trow = sqlQuery("SELECT * FROM procedure_type WHERE " .
								"parent = ? AND procedure_code = ? AND procedure_type = 'res' " .
								"ORDER BY procedure_type_id DESC LIMIT 1",
								array($ptid, $acsv[2]));
							
							// The following should always be true, otherwise duplicate input row.
							if (empty($trow['procedure_type_id']) || $trow['activity'] == 0) {
								if (empty($trow['procedure_type_id'])) {
									sqlInsert("INSERT INTO procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?",
										array($ptid, $acsv[3], $lab_id, $acsv[2], 'res'));
								}
								else {
									$resid = $trow['procedure_type_id'];
									sqlStatement("UPDATE procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " .
										"activity = 1 WHERE procedure_type_id = ?",
										array($ptid, $acsv[3], $lab_id, $acsv[2], 'res', $resid));
								}
							} // end if
						} // end while
					} // end load compendium

					else if ($form_action == 2) { // load questions
						// Mark the vendor's current questions inactive.
						sqlStatement("UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?",
							array($lab_id));

						// What should be uploaded is the "AOE Questions" spreadsheet provided by
						// PathGroup, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Values for each row are:
						//   0: OBRCode (order code)
						//   1: Question Code
						//   2: Question
						//   3: "Tips"
						//   4: Required (0 = No, 1 = Yes)
						//   5: Maxchar (integer length)
						//   6: FieldType (FT = free text, DD = dropdown, ST = string)
						//
						$seq = 0;
						$last_code = '';
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);
							if (count($acsv) < 7 || $acsv[4] == "Required") continue;
							$code = trim($acsv[0]);
							if (empty($code)) continue;

							if ($code != $last_code) {
								$seq = 0;
								$last_code = $code;
							}
							++$seq;

							$required = 0 + $acsv[4];
							$maxsize = 0 + $acsv[5];
							$fldtype = 'T';

							// Figure out field type.
							if ($acsv[6] == 'DD') $fldtype = 'S';
							else if (stristr($acsv[3], 'mm/dd/yy') !== FALSE) $fldtype = 'D';
							else if (stristr($acsv[3], 'wks_days') !== FALSE) $fldtype = 'G';
							else if ($acsv[6] == 'FT') $fldtype = 'T';
							else $fldtype = 'N';

							$qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " .
								"lab_id = ? AND procedure_code = ? AND question_code = ?",
								array($lab_id, $code, $acsv[1]));

							if (empty($qrow['question_code'])) {
								sqlStatement("INSERT INTO procedure_questions SET " .
									"lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " .
									"required = ?, maxsize = ?, fldtype = ?, options = '', tips = ?,
									activity = 1, seq = ?",
									array($lab_id, $code, $acsv[1], $acsv[2], $required, $maxsize, $fldtype, $acsv[3], $seq));
							}
							else {
								sqlStatement("UPDATE procedure_questions SET " .
									"question_text = ?, required = ?, maxsize = ?, fldtype = ?, " .
									"options = '', tips = ?, activity = 1, seq = ? WHERE " .
									"lab_id = ? AND procedure_code = ? AND question_code = ?",
									array($acsv[2], $required, $maxsize, $fldtype, $acsv[3], $seq,
										$lab_id, $code, $acsv[1]));
							}
						} // end while
					} // end load questions

					else if ($form_action == 3) { // load question options
						// What should be uploaded is the "AOE Options" spreadsheet provided
						// by YPMG, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Values for each row are:
						//   0: OBXCode (question code)
						//   1: OBRCode (procedure code)
						//   2: Option1 (option text)
						//   3: Optioncode (the row is duplicated for each possible value)
						//
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);
							if (count($acsv) < 4 || ($acsv[0] == "OBXCode")) continue;
							$pcode   = trim($acsv[1]);
							$qcode   = trim($acsv[0]);
							$options = trim($acsv[2]) . ':' . trim($acsv[3]);
							if (empty($pcode) || empty($qcode)) continue;
							$qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " .
								"lab_id = ? AND procedure_code = ? AND question_code = ?",
								array($lab_id, $pcode, $qcode));
							if (empty($qrow['procedure_code'])) {
								continue; // should not happen
							}
							else {
								if ($qrow['activity'] == '1' && $qrow['options'] !== '') {
									$options = $qrow['options'] . ';' . $options;
								}
								sqlStatement("UPDATE procedure_questions SET " .
									"options = ? WHERE " .
									"lab_id = ? AND procedure_code = ? AND question_code = ?",
									array($options, $lab_id, $pcode, $qcode));
							}
						} // end while
					} // end load questions
				} // End Pathgroup

				// -----------------------------------------------------------------------------------------------------------------
				// Vendor = Yosemite Pathology Medical Group
				// -----------------------------------------------------------------------------------------------------------------
				if ($form_vendor == '1598760985') {
					if ($form_action == 1) { // load compendium
						// Mark all "ord" rows having the indicated parent as inactive.
						sqlStatement("UPDATE procedure_type SET activity = 0 WHERE " .
							"parent = ? AND procedure_type = 'ord'",
							array($form_group));
						// What should be uploaded is the Order Compendium spreadsheet provided
						// by YPMG, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Values for each row are:
						//   0: Order code    : mapped to procedure_code
						//   1: Order Name    : mapped to name
						//   2: Result Code   : ignored (will cause multiple occurrences of the same order code)
						//   3: Result Name   : ignored
						//
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);
							$ordercode = trim($acsv[0]);
							if (count($acsv) < 2 || $ordercode == "Order Code") continue;
							$trow = sqlQuery("SELECT * FROM procedure_type WHERE " .
								"parent = ? AND procedure_code = ? AND procedure_type = 'ord' " .
								"ORDER BY procedure_type_id DESC LIMIT 1",
								array($form_group, $ordercode));

							if (empty($trow['procedure_type_id'])) {
								sqlStatement("INSERT INTO procedure_type SET " .
									"parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " .
									"activity = 1",
									array($form_group, trim($acsv[1]), $lab_id, $ordercode, 'ord'));
							}
							else {
								sqlStatement("UPDATE procedure_type SET " .
									"parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " .
									"activity = 1 " .
									"WHERE procedure_type_id = ?",
									array($form_group, trim($acsv[1]), $lab_id, $ordercode, 'ord',
										$trow['procedure_type_id']));
							}
						}
					}

					else if ($form_action == 2) { // load questions
						// Mark the vendor's current questions inactive.
						sqlStatement("UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?",
							array($lab_id));

						// What should be uploaded is the "AOE Questions" spreadsheet provided
						// by YPMG, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Values for each row are:
						//   0: Order Code
						//   1: Question Code
						//   2: Question
						//   3: Is Required (always "false")
						//   4: Field Type ("Free Text", "Pre-Defined Text" or "Drop Down";
						//      "Drop Down" was previously "Multiselect Pre-Defined Text" and
						//      indicates that more than one choice is allowed)
						//   5: Response (just one; the row is duplicated for each possible value)
						//
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);
							if (count($acsv) < 5 || ($acsv[3] !== "false" && $acsv[3] !== "true")) continue;

							$pcode   = trim($acsv[0]);
							$qcode   = trim($acsv[1]);
							$required = strtolower(substr($acsv[3], 0, 1)) == 't' ? 1 : 0;
							$options = trim($acsv[5]);
							if (empty($pcode) || empty($qcode)) continue;

							// Figure out field type.
							$fldtype = 'T';
							if (strpos($acsv[4], 'Drop') !== FALSE) $fldtype = 'S';
							else if (strpos($acsv[4], 'Multiselect') !== FALSE) $fldtype = 'S';

							$qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " .
								"lab_id = ? AND procedure_code = ? AND question_code = ?",
								array($lab_id, $pcode, $qcode));

							// If this is the first option value and it's a multi-select list,
							// then prepend '+;' here to indicate that.  YPMG does not use those
							// but keep this note here for future reference.

							if (empty($qrow['procedure_code'])) {
								sqlStatement("INSERT INTO procedure_questions SET " .
									"lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " .
									"fldtype = ?, required = ?, options = ?, activity = 1",
									array($lab_id, $pcode, $qcode, trim($acsv[2]), $fldtype, $required, $options));
							}
							else {
								if ($qrow['activity'] == '1' && $qrow['options'] !== '' && $options !== '') {
									$options = $qrow['options'] . ';' . $options;
								}
								sqlStatement("UPDATE procedure_questions SET " .
									"question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE " .
									"lab_id = ? AND procedure_code = ? AND question_code = ?",
									array(trim($acsv[2]), $fldtype, $required, $options, $lab_id, $pcode, $qcode));
							}
						} // end while
					} // end load questions
				} // End YPMG

				// -----------------------------------------------------------------------------------------------------------------
				// Vendor = Clinical Pathology Laboratories
				// -----------------------------------------------------------------------------------------------------------------
				if ($form_vendor == '1194769497') {
					if ($form_action == 1) { // load compendium
						// Delete the detail records for this lab.
						sqlStatement("DELETE FROM procedure_type WHERE " .
								"lab_id = ? AND procedure_type = 'det' || procedure_type = 'res' ",	array($lab_id));
						
						// Mark everything for the indicated lab as inactive.
						sqlStatement("UPDATE procedure_type SET activity = 0, seq = 999999 WHERE " .
							"lab_id = ? AND procedure_type != 'grp' AND procedure_type != 'pro'", array($lab_id));

						// Load category group ids
						$result = sqlStatement("SELECT procedure_type_id, name FROM procedure_type ".
							"WHERE lab_id = ? AND parent = ? AND procedure_type = 'grp'", array($lab_id, $form_group));
						while ($record = sqlFetchArray($result)) $groups[$record['name']] = $record[procedure_type_id];

						// What should be uploaded is the Order Compendium spreadsheet provided
						// by CPL, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Sort table by Order Code!!!
						// Values for each row are:
						//   0: Order code    : mapped as procedure_code
						//   1: Order Name    : mapped as procedure name
						//   2: Result Code   : mapped as discrete result code
						//   3: Result Name   : mapped as discrete result name
						//   4: Result LOINC  : mapped as identification number
						//   5: Result CPT4   : ignored
						//   6: Container type
						//   7: Preferred Specimen
						//   8: Transport
						//   9: Category      : mapped as group
						//  10: UofM
						//  11: Reference Range
						//  12: Method

						$lastcode = '';
						$pseq = 1;
						$rseq = 1;
						$dseq = 100;
						$groups = '';

						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);

							$category = trim($acsv[9]);
							if (!$category || $category == 'Category') {
								$groupid = $form_group; // no category, store under root
							}
							else { // find or add category
								$groupid = $groups[$category];
								if (!$groupid) {
									$groupid = sqlInsert("INSERT INTO procedure_type SET " .
										"procedure_type = 'grp', lab_id = ?, parent = ?, name = ?",
										array($lab_id, $form_group, $category));
									$groups[$category] = $groupid;
								}
							}

							// store the order 
							$ordercode = trim($acsv[0]);
							if (count($acsv) < 2 || strtolower($ordercode) == "order code") continue;
							
							if ($lastcode != $ordercode) { // new code (store only once)
								$stdcode = '';
								if (trim($acsv[5]) != '') $stdcode .= "CPT4:".trim($acsv[5]);
								
								$trow = sqlQuery("SELECT * FROM procedure_type " .
									"WHERE parent = ? AND procedure_code = ? AND procedure_type = 'ord' " .
									"ORDER BY procedure_type_id DESC LIMIT 1",
										array($groupid, $ordercode));
								
								$name =  mysql_real_escape_string(trim($acsv[1]));
								$notes =  mysql_real_escape_string(trim($acsv[7]));
								$category =  mysql_real_escape_string(trim($acsv[9]));
								
								// display last profile
								echo "TEST: $ordercode - $name ($category)\n";
								flush();
								
								if (empty($trow['procedure_type_id'])) {
									$orderid = sqlInsert("INSERT INTO procedure_type SET " .
										"parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, standard_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1",
											array($groupid, $name, $category, $lab_id, $ordercode, $stdcode, $notes, 'ord', $pseq++));
								}
								else {
									$orderid = $trow['procedure_type_id'];
									sqlStatement("UPDATE procedure_type SET " .
										"parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 " .
										"WHERE procedure_type_id = ?",
											array($groupid, $name, $category, $lab_id, $ordercode, $notes, 'ord', $pseq++, $orderid));
								}
								
								// store detail records (one record per detail)
								if (trim($acsv[7])) { // preferred specimen
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'PREFERRED SPECIMEN', 'Preferred specimen collection method', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[7])), 'det', $dseq++, $orderid));
								}
								
								if (trim($acsv[6])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'CONTAINER TYPE', 'Specimen container type', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[6])), 'det', $dseq++, $orderid));
								}
								
								if (trim($acsv[8])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'SPECIMEN TRANSPORT', 'Method of specimen transport', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[8])), 'det', $dseq++, $orderid));
								}
								
								if (trim($acsv[12])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'TESTING METHOD', 'Method of performing test', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[12])), 'det', $dseq++, $orderid));
								}
								
								// reset counters for new procedure
								$lastcode = $ordercode;
								$rseq = 1;
								$dseq = 100;
							}
							
							// store the results
							$resultcode = trim($acsv[2]);
							$trow = sqlQuery("SELECT * FROM procedure_type WHERE " .
								"parent = ? AND procedure_code = ? AND procedure_type = 'res' " .
								"ORDER BY procedure_type_id DESC LIMIT 1",array($orderid, $resultcode));
							
							$stdcode = '';
							if (trim($acsv[4]) != '') $stdcode .= "LOINC:".trim($acsv[4]);
							$name =  mysql_real_escape_string(trim($acsv[3]));
							$units = mysql_real_escape_string(trim($acsv[10]));
							$range = mysql_real_escape_string(trim($acsv[11]));
							
							if (empty($trow['procedure_type_id'])) {
								sqlStatement("INSERT INTO procedure_type SET " .
									"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 ",
										array($orderid, $name, $lab_id, $resultcode, $stdcode, $units, $range, $rseq++, 'res'));
							}
							else {
								sqlStatement("UPDATE procedure_type SET " .
									"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 " .
									"WHERE procedure_type_id = ?",
										array($groupid, $name, $lab_id, $resultcode, $stdcode, $units, $range, $rseq++, 'res', $trow['procedure_type_id']));
							}
						}  // end file loop
						echo "</pre>\n";
					}

					else if ($form_action == 2) { // load questions
						// Mark the vendor's current questions inactive.
						sqlStatement("UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?",
								array($lab_id));

						// What should be uploaded is the "AOE Questions" spreadsheet provided
						// by CPL, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Values for each row are:
						//   0: Order Code
						//   1: Question Code
						//   2: Question
						//   3: Is Required (always "false")
						//   4: Field Type ("Free Text", "Pre-Defined Text" or "Drop Down";
						//      "Drop Down" was previously "Multiselect Pre-Defined Text" and
						//      indicates that more than one choice is allowed)
						//   5: Response (just one; the row is duplicated for each possible value)
						//

						echo "<pre style='font-size:10px'>";
						
						$seq = 1;
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);
							if ($seq++ < 2 || strtolower($acsv[0]) == "order code") continue;

							$pcode   = trim($acsv[0]);
							$qcode   = trim($acsv[1]);
							$required = 1; // always required
							$options = trim($acsv[4]);
							if (empty($pcode) || empty($qcode)) continue;

							// Figure out field type.
							$fldtype = trim($acsv[3]);
							if (!$fldtype) $fldtype = 'T'; // always text

							// display question
							echo "QUESTION: $qcode - trim($acsv[2])\n";
							flush();
								
							$qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " .
									"lab_id = ? AND procedure_code = ? AND question_code = ?",
									array($lab_id, $pcode, $qcode));

							if (empty($qrow['procedure_code'])) {
								sqlStatement("INSERT INTO procedure_questions SET " .
										"lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " .
										"fldtype = ?, required = ?, options = ?, activity = 1",
										array($lab_id, $pcode, $qcode, trim($acsv[2]), $fldtype, $required, $options));
							}
							else {
								if ($qrow['activity'] == '1' && $qrow['options'] !== '' && $options !== '') {
									$options = $qrow['options'] . ';' . $options;
								}
								sqlStatement("UPDATE procedure_questions SET " .
										"question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE " .
										"lab_id = ? AND procedure_code = ? AND question_code = ?",
										array(trim($acsv[2]), $fldtype, $required, $options, $lab_id, $pcode, $qcode));
							}
						} // end while
						echo "</pre>\n";
						
					} // end load questions
					
					
					if ($form_action == 4) { // load profiles
						// Mark everything for the indicated lab as inactive.
						sqlStatement("UPDATE procedure_type SET activity = 0, seq = 999999 WHERE " .
							"lab_id = ? AND procedure_type = 'pro'",
							array($lab_id));

						// Load category group ids
						$result = sqlStatement("SELECT procedure_type_id, name FROM procedure_type ".
							"WHERE lab_id = ? AND parent = ? AND procedure_type = 'grp'",
								array($lab_id, $form_group));
						while ($record = sqlFetchArray($result)) $groups[$record['name']] = $record['procedure_type_id'];

						// What should be uploaded is the Profile Compendium spreadsheet provided
						// by CPL, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Sort table by Profile Code!!!
						// Values for each row are:
						//   0: Profile code    : mapped as procedure_code
						//   1: Profile Name    : mapped as procedure name
						//   2: Component Code   : mapped as component order code
						//   3: Component Name   : mapped as component order name
						//   4: Test Code
						//   5: Test Name
						//   6: Result LOINC  : ignored
						//   7: Result CPT4   : ignored
						//   8: Category      : mapped as group
						//   9: Preferred Specimen
						//  10: Container type
						//  11: Transport
						//  12: UofM			: ignored
						//  13: Reference Range : ignored
						//  14: Method 			: ignored

						$orderid = '';
						$lastcode = '';
						$pseq = 1;
						$rseq = 1;
						$dseq = 100;
						$components = array();

						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);

							$category = trim($acsv[8]);
							if (!$category || $category == 'Category') {
								$groupid = $form_group; // no category, store under root
							}
							else { // find or add category
								$groupid = $groups[$category];
								if (!$groupid) {
									$groupid = sqlInsert("INSERT INTO procedure_type SET " .
										"procedure_type = 'grp', lab_id = ?, parent = ?, name = ?",
										array($lab_id, $form_group, $category));
									$groups[$category] = $groupid;
								}
							}

							// store the profile 
							$ordercode = trim($acsv[0]);
							if (count($acsv) < 2 || strtolower($ordercode) == "profile code") continue;
							
							if ($lastcode != $ordercode) { // new code (store only once)
							
								// store componets for previous record
								if ($orderid) {
									$comp_list = implode("^", $components);
									$comp_list = mysql_real_escape_string($comp_list);
									sqlInsert("UPDATE procedure_type SET related_code = ? WHERE procedure_type_id = ?",
											array($comp_list,$orderid));
									$components = array();
								}
								$trow = sqlQuery("SELECT * FROM procedure_type " .
									"WHERE parent = ? AND procedure_code = ? AND procedure_type = 'pro' " .
									"ORDER BY procedure_type_id DESC LIMIT 1",
										array($groupid, $ordercode));
								
								$name =  mysql_real_escape_string(trim($acsv[1]));
								$notes =  mysql_real_escape_string(trim($acsv[9]));
								$category =  mysql_real_escape_string(trim($acsv[8]));
								
								// display last profile
								echo "PROFILE: $ordercode - $name ($category)\n";
								flush();
								
								if (empty($trow['procedure_type_id'])) {
									// create new record
									$orderid = sqlInsert("INSERT INTO procedure_type SET " .
										"parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, notes = ?, related_code = ?, procedure_type = ?, seq = ?, activity = 1",
											array($groupid, $name, $category, $lab_id, $ordercode, $notes, '', 'pro', $pseq++));
								}
								else {
									$orderid = $trow['procedure_type_id'];
									
									// delete detail and other records
									sqlStatement("DELETE FROM procedure_type WHERE parent = ?",array($orderid));
									
									// update profile record
									sqlStatement("UPDATE procedure_type SET " .
										"parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, notes = ?, related_code = ?, procedure_type = ?, seq = ?, activity = 1 " .
										"WHERE procedure_type_id = ?",
											array($groupid, $name, $category, $lab_id, $ordercode, $notes, '', 'pro', $pseq++, $orderid));
								}
								
								// store detail records (one record per detail)
								if (trim($acsv[9])) { // preferred specimen
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'PREFERRED SPECIMEN', 'Preferred specimen collection method', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[9])), 'det', $dseq++, $orderid));
								}
								
								if (trim($acsv[10])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'CONTAINER TYPE', 'Specimen container type', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[10])), 'det', $dseq++, $orderid));
								}
								
								if (trim($acsv[11])) { // transport
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'SPECIMEN TRANSPORT', 'Method of specimen transport', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[11])), 'det', $dseq++, $orderid));
								}
								
								// reset counters for new procedure
								$rseq = 1;
								$dseq = 100;
							}
								
							// collect the components
							$comp = trim($acsv[2]);
							$components[$comp] = $comp;
							$lastcode = $ordercode;
						}
						
						// process last profile code
						if ($orderid) { // previous record
							$comp_list = implode("^", $components);
							$comp_list = mysql_real_escape_string($comp_list);
							sqlInsert("UPDATE procedure_type SET related_code = ? WHERE procedure_type_id = ?",
									array($comp_list,$orderid));
							$components = array();
						}
					
					}
					echo "</pre>";
						
				} // End CPL


				// -----------------------------------------------------------------------------------------------------------------
				// Vendor = Quest Diagnostics
				// -----------------------------------------------------------------------------------------------------------------
				if ($form_vendor == 'QUEST') {
					if ($form_action == 1) { // load compendium
						// Get the compendium server parameters
						// 0: server address
						// 1: lab identifier (STL, SEA, etc)
						// 2: user name
						// 3: password
						$params = array();
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
							$params[] = trim($acsv[0]);
						}
						
						// verify directory
						$server = $params[0];
						$group = $params[1];
						$login = $params[2];
						$password = $params[3];
						
						echo "<br/>LOADING FROM: ".$server."/".$group."<br/><br/>";
						
						$cdcdir = $GLOBALS['temporary_files_dir']."/quest";
						if (!file_exists($cdcdir)) {
							if (!mkdir($cdcdir,0700)) {
								die('<br/><br/>Unable to create directory for CDC files ('.$cdcdir.')');
							}
						}
						
						$CDC = array();
						$CDC[] = "/ORDCODE_".$group.".TXT";
						$CDC[] = "/METHODOLOGY_".$group.".TXT";
						$CDC[] = "/SPECIMENREQ_".$group.".TXT";
						$CDC[] = "/SPECIMENSTAB_".$group.".TXT";
						$CDC[] = "/SPECIMENVOL_".$group.".TXT";
						$CDC[] = "/TRANSPORT_".$group.".TXT";
						$CDC[] = "/ANALYTE_".$group.".TXT";
						$CDC[] = "/WORKLIST_".$group.".TXT";
						
						foreach ($CDC AS $file) {
							unlink($cdcdir.$file); // remove old file if there is one
							if (($fp = fopen($cdcdir.$file, "w+")) == false) {
								die('<br/><br/>Could not create local CDC file ('.$cdcdir.$file.')');
							}
								
							$ch = curl_init();
							$credit = ($login.':'.$password);
							curl_setopt($ch, CURLOPT_URL, $server.$group.$file);
							curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
							curl_setopt($ch, CURLOPT_USERPWD, $credit);
							curl_setopt($ch, CURLOPT_TIMEOUT, 15);
							curl_setopt($ch, CURLOPT_FILE, $fp);
							
							// testing only!!
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					
							if (($xml = curl_exec($ch)) === false) {
								curl_close($ch);
								fclose($fp);
								unlink($cdcdir.$file);
								die("<br/><br/>READ ERROR: ".curl_error($ch)." QUITING...");
							}
							 
							curl_close($ch);
							fclose($fp);
						}													
						
						// verify required files
						if (!file_exists($cdcdir."/ORDCODE_".$group.".TXT"))
							die("<br/><br/>Compendium order file [ORDCODE_".$group.".TXT] not accessable!!");
						if (!file_exists($cdcdir."/METHODOLOGY_".$group.".TXT"))
							die("<br/><br/>Compendium order file [METHODOLOGY_".$group.".TXT] not accessable!!");
						if (!file_exists($cdcdir."/SPECIMENREQ_".$group.".TXT"))
							die("<br/><br/>Compendium order file [SPECIMENREQ_".$group.".TXT] not accessable!!");
						if (!file_exists($cdcdir."/SPECIMENSTAB_".$group.".TXT"))
							die("<br/><br/>Compendium order file [SPECIMENSTAB_".$group.".TXT] not accessable!!");
						if (!file_exists($cdcdir."/SPECIMENVOL_".$group.".TXT"))
							die("<br/><br/>Compendium order file [SPECIMENVOL_".$group.".TXT] not accessable!!");
						if (!file_exists($cdcdir."/TRANSPORT_".$group.".TXT"))
							die("<br/><br/>Compendium order file [TRANSPORT_".$group.".TXT] not accessable!!");
						if (!file_exists($cdcdir."/ANALYTE_".$group.".TXT"))
							die("<br/><br/>Compendium order file [ANALYTE_".$group.".TXT] not accessable!!");
						if (!file_exists($cdcdir."/WORKLIST_".$group.".TXT"))
							die("<br/><br/>Compendium order file [WORKLIST_".$group.".TXT] not accessable!!");
						
						// Delete the detail records for this lab.
						sqlStatement("DELETE FROM procedure_type WHERE " .
								"lab_id = ? AND procedure_type = 'det' || procedure_type = 'res' ",
								array($lab_id));
						
						// Mark everything else for the indicated lab as inactive.
						sqlStatement("UPDATE procedure_type SET activity = 0, seq = 999999, related_code = '' WHERE " .
								"lab_id = ? AND procedure_type != 'grp' ",
								array($lab_id));
				
						// Load category group ids (procedure and profile)
						$result = sqlStatement("SELECT procedure_type_id, name FROM procedure_type ".
							"WHERE parent = ? AND procedure_type = 'grp'",
								array($form_group));
						while ($record = sqlFetchArray($result)) $groups[$record['name']] = $record[procedure_type_id];
						if (!$groups['Profiles'] || !$groups['Procedures'])
							die("<br/><br/>Missing required compendium groups [Profiles, Procedures]");

						// open the order code file for processing
						$fhcsv = fopen($cdcdir."/ORDCODE_".$group.".TXT",'r');
						if (! $fhcsv) {						
							die("<br/><br/>Compendium order file [ORDCODE_".$group.".TXT] could not be openned!!");
						}
						
						// What should be uploaded is the Order Compendium (ORDCODE) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Order Code    : mapped as procedure_code
						//   2: State         : mapped as route_admin
						//   3: Unit Code     : mapped as standard code
						//   4: Active Flag   : mapped as activity
						//   5: Insert Date
						//   6: Order Name    : mapped as procedure name
						//   7: Specimen      : mapped as specimen
						//   8: NBS Service
						//   9: Performed
						//  10: Updated Date
						//  11: Update User
						//  12: Sufix
						//  13: Profile Flag   : mapped as procedure_type
						//  14: Selectable
						//  15: NBS Site
						//  16: Test Flag
						//  17: No Bill
						//  18: Bill Only
						//  19: Reflex Count
						//  20: Conform Flag
						//  21: Alt Temp
						//  22: Pap Flag        : mapped with route_admin
				
						$lastcode = '';
						$pseq = 1;

						echo "<pre style='font-size:10px'>";
						
						$codes = array();
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
							
							// explode all content row fields
							$ahl7 = explode('^', $row);
							
							// store the order
							$ordercode = trim($ahl7[1]);
							if ($lastcode != $ordercode) { // new code (store only once)
								$stdcode = '';
								if (trim($ahl7[3]) != '') $stdcode .= "UNIT:".trim($ahl7[3]);
				
								$state = (strtoupper(trim($ahl7[22])) == 'P')? 'PAP' : strtoupper(trim($ahl7[2]));
								$type = (strtoupper($profile) == 'Y')? 'pro' : 'ord';
								
								$profile = trim($ahl7[13]);
								$groupid = $groups['Procedures'];
								if ($type == 'pro') $groupid = $groups['Profiles'];
								
								$trow = sqlQuery("SELECT * FROM procedure_type " .
										"WHERE parent = ? AND procedure_code = ? AND procedure_type = ? " .
										"ORDER BY procedure_type_id DESC LIMIT 1",
										array($groupid, $ordercode, $type));
				
								$name =  mysql_real_escape_string(preg_replace( "/\r|\n/", " ", trim($ahl7[6])));
								$specimen =  mysql_real_escape_string(trim($ahl7[7]));
								$activity =  mysql_real_escape_string(trim($ahl7[4]));
								$activity = ($activity == 'A')? 1 : 0; 
								
								$speclist[$specimen] = $specimen; // store unique names

								if (empty($trow['procedure_type_id'])) {
									$orderid = sqlInsert("INSERT INTO procedure_type SET " .
											"parent = ?, name = ?, specimen = ?, transport = ?, lab_id = ?, procedure_code = ?, standard_code = ?, procedure_type = ?, seq = ?, activity = ?",
											array($groupid, $name, $specimen, $state, $lab_id, $ordercode, $stdcode, $type, $pseq++, $activity));
								}
								else {
									$orderid = $trow['procedure_type_id'];
									sqlStatement("UPDATE procedure_type SET " .
											"parent = ?, name = ?, specimen = ?, transport = ?, lab_id = ?, procedure_code = ?, standard_code = ?, procedure_type = ?, seq = ?, activity = ? " .
											"WHERE procedure_type_id = ?",
											array($groupid, $name, $specimen, $state, $lab_id, $ordercode, $stdcode, $type, $pseq++, $activity, $orderid));
								}
								
								// store test code/order id cross reference
								$codes[$ordercode] = $orderid;
								
								if ($type == 'pro') echo "PROFILE: $row";
								else echo "TEST: $row";
								flush();

								// reset counters for new procedure
								$lastcode = $ordercode;
							}
						}

						// done with the order file
						fclose($fhcsv);							
						echo "</pre>";					

						// update list_option table
						if (is_array($speclist)) {
							foreach ($speclist AS $specimen) {
								sqlStatement("REPLACE INTO list_options SET list_id = 'proc_specimen', option_id = ?, title = ?",
										array($specimen, $specimen));
							}
						}
							
						// open the specimen requirements file for processing
						$fhcsv = fopen($cdcdir."/SPECIMENREQ_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium requirements file [SPECIMENREQ_".$group.".TXT] could not be openned!!");
						}
							
						// What should be uploaded is the requirements (SPECIMENREQ) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Order Code    : mapped as procedure_code
						//   2: Sequence
						//   3: Description   : mapped as description
							
						$lastcode = '';
						$text = '';
						$dseq = 100;
						
						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
									
							// explode all content row fields
							$ahl7 = explode('^', $row);
									
							// store the description
							$ordercode = trim($ahl7[1]);
							if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
								if ($codes[$lastcode]) { // only save if there is a parent order
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($codes[$lastcode], 'PREFERRED SPECIMEN', 'Preferred specimen collection method', $lab_id, $lastcode, $text, 'det', $dseq++));
								}
								$text = '';
								$dseq = 100;
							}
							else { // still working with the last code
								$text .= trim($ahl7[3])."\n";
							}
							
							echo "PROCESS: $row";
							flush();
								
							// reset counters for new procedure
							$lastcode = $ordercode;
						}

						if ($lastcode) { // new code (store and restart)
							if ($orders[$lastcode]) { // only save if there is a parent order
								sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
										array($orders[$lastcode], 'PREFERRED SPECIMEN', 'Preferred specimen collection method', $lab_id, $lastcode, $text, 'det', $dseq++));
							}
						}

						// done with the requirements file
						fclose($fhcsv);							
						echo "</pre>";							
							
						// open the specimen stability file for processing
						$fhcsv = fopen($cdcdir."/SPECIMENSTAB_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium stability file [SPECIMENSTAB_".$group.".TXT] could not be openned!!");
						}
							
						// What should be uploaded is the stability (SPECIMENSTAB) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Order Code    : mapped as procedure_code
						//   2: Sequence
						//   3: Description   : mapped as description
							
						$lastcode = '';
						$text = '';
						$dseq = 200;
							
						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
									
							// explode all content row fields
							$ahl7 = explode('^', $row);
									
							// store the description
							$ordercode = trim($ahl7[1]);
							if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
								if ($codes[$lastcode]) { // only save if there is a parent order
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($codes[$lastcode], 'SPECIMEN STABILITY', 'Specimen storage stability', $lab_id, $lastcode, $text, 'det', $dseq++));
								}
								$text = '';
								$dseq = 200;
							}
							else { // still working with the last code
								$text .= trim($ahl7[3])."\n";
							}
							
							echo "STABILITY: $row";
							flush();
								
							// reset counters for new procedure
							$lastcode = $ordercode;
						}

						if ($lastcode) { // new code (store and restart)
							if ($orders[$lastcode]) { // only save if there is a parent order
								sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
										array($orders[$lastcode], 'SPECIMEN STABILITY', 'Specimen storage stability', $lab_id, $lastcode, $text, 'det', $dseq++));
							}
						}

						// done with the stability file
						fclose($fhcsv);							
						echo "</pre>";							
							
						
						// open the specimen volume file for processing
						$fhcsv = fopen($cdcdir."/SPECIMENVOL_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium volume file [SPECIMENVOL_".$group.".TXT] could not be openned!!");
						}
							
						// What should be uploaded is the stability (SPECIMENSTAB) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Order Code    : mapped as procedure_code
						//   2: Sequence
						//   3: Description   : mapped as description
							
						$lastcode = '';
						$text = '';
						$dseq = 300;

						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
									
							// explode all content row fields
							$ahl7 = explode('^', $row);
									
							// store the description
							$ordercode = trim($ahl7[1]);
							if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
								if ($codes[$lastcode]) { // only save if there is a parent order
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($codes[$lastcode], 'SPECIMEN VOLUME', 'Specimen volume requirements', $lab_id, $lastcode, $text, 'det', $dseq++));
								}
								$text = '';
								$dseq = 300;
							}
							else { // still working with the last code
								$text .= trim($ahl7[3])."\n";
							}
							
							echo "VOLUME: $row";
							flush();
								
							// reset counters for new procedure
							$lastcode = $ordercode;
						}

						if ($lastcode) { // new code (store and restart)
							if ($orders[$lastcode]) { // only save if there is a parent order
								sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
										array($orders[$lastcode], 'SPECIMEN VOLUME', 'Specimen volume requirements', $lab_id, $lastcode, $text, 'det', $dseq++));
							}
						}
						
						// done with the volume file
						fclose($fhcsv);							
						echo "</pre>";							
							
						
						// open the specimen transport file for processing
						$fhcsv = fopen($cdcdir."/TRANSPORT_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium transport file [TRANSPORT_".$group.".TXT] could not be openned!!");
						}
							
						// What should be uploaded is the transport (TRANSPORT) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Order Code    : mapped as procedure_code
						//   2: Sequence
						//   3: Description   : mapped as description
							
						$lastcode = '';
						$text = '';
						$dseq = 400;

						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
									
							// explode all content row fields
							$ahl7 = explode('^', $row);
									
							// store the description
							$ordercode = trim($ahl7[1]);
							if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
								if ($codes[$lastcode]) { // only save if there is a parent order
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($codes[$lastcode], 'SPECIMEN TRANSPORT', 'Specimen transport requirements', $lab_id, $lastcode, $text, 'det', $dseq++));
								}
								$text = '';
								$dseq = 400;
							}
							else { // still working with the last code
								$text .= trim($ahl7[3])."\n";
							}
							
							echo "TRANSPORT: $row";
							flush();
								
							// reset counters for new procedure
							$lastcode = $ordercode;
						}

						if ($lastcode) { // new code (store and restart)
							if ($orders[$lastcode]) { // only save if there is a parent order
								sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
										array($orders[$lastcode], 'SPECIMEN TRANSPORT', 'Specimen transport requirements', $lab_id, $lastcode, $text, 'det', $dseq++));
							}
						}
						
						// done with the transport file
						fclose($fhcsv);							
						echo "</pre>";							
							
						
						// open the methodology file for processing
						$fhcsv = fopen($cdcdir."/METHODOLOGY_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium methodology file [METHODOLOGY_".$group.".TXT] could not be openned!!");
						}
							
						// What should be uploaded is the methodology (METHODOLOGY) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Order Code    : mapped as procedure_code
						//   2: Sequence
						//   3: Description   : mapped as description
							
						$lastcode = '';
						$text = '';
						$dseq = 600;

						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
									
							// explode all content row fields
							$ahl7 = explode('^', $row);
									
							// store the description
							$ordercode = trim($ahl7[1]);
							if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
								if ($codes[$lastcode]) { // only save if there is a parent order
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($codes[$lastcode], 'TESTING METHODOLOGY', 'Method of performing test', $lab_id, $lastcode, $text, 'det', $dseq++));
								}
								$text = '';
								$dseq = 600;
							}
							else { // still working with the last code
								$text .= trim($ahl7[3])."\n";
							}
							
							echo "METHOD: $row";
							flush();
								
							// reset counters for new procedure
							$lastcode = $ordercode;
						}

						if ($lastcode) { // new code (store and restart)
							if ($orders[$lastcode]) { // only save if there is a parent order
								sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
										array($orders[$lastcode], 'TESTING METHODOLOGY', 'Method of performing test', $lab_id, $lastcode, $text, 'det', $dseq++));
							}
						}
						
						// done with the requirements file
						fclose($fhcsv);							
						echo "</pre>";							
							
						
						// open the results file for processing
						$fhcsv = fopen($cdcdir."/ANALYTE_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium results file [ANALYTE_".$group.".TXT] could not be openned!!");
						}
							
						// What should be uploaded is the results (ANALYTE) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Top Lab
						//   2: Analyte Code  : mapped as procedure_code
						//   3: Mnemonic
						//   4: Description 1
						//   5: Description 2
						//   6: blank
						//   7: blank
						//   8: LOINC
						//   9: UOM
							
						$lastcode = '';
						$text = '';
						$dseq = 700;
						$results = array();
							
						echo "<pre style='font-size:10px'>";
						
						// retrieve all of the result records
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
									
							// explode all content row fields
							$ahl7 = explode('^', $row);

							// store all of the result records
							$resultcode = trim($ahl7[2]);
							$results[$resultcode] = $ahl7;

							echo "RESULTS: $row";
							flush();
						}
							
						// done with the results file
						fclose($fhcsv);
							
						// open the results cross reference file for processing
						$fhcsv = fopen($cdcdir."/WORKLIST_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium results cross-reference file [WORKLIST_".$group.".TXT] could not be openned!!");
						}
							
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Top Lab
						//   2: Test Code
						//   3: Suffix
						//   4: Result Code
						//   5: Unit Code
						//   6: Active Flag
						//   7: Update Date
						
						// retrieve each cross-reference record
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
								
							// explode all content row fields
							$ahl7 = explode('^', $row);
							
							$ordercode = trim($ahl7[2]);
							if (! $codes[$ordercode]) continue; // no match
							
							$resultcode = trim($ahl7[4]);
							if (! $results[$resultcode]) continue; // no match
											
							// store the result data
							$title =  mysql_real_escape_string(preg_replace( "/\r|\n/", " ", trim($results[$resultcode][4])));
							$title2 = trim($results[$resultcode][5]);
							if ($title2) $title .= " ".$title2;
							$stdcode = trim($results[$resultcode][8]);
							if (empty($stdcode)) continue; // LOINC required for results
							
							if ($stdcode) $stdcode = 'LOINC:'.$stdcode;
							$units = trim($results[$resultcode][9]);
								
							sqlStatement("REPLACE INTO procedure_type SET " .
									"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, procedure_type = ?, seq = ?, activity = 1 ",
									array($codes[$ordercode], $title, $lab_id, $resultcode, $stdcode, $units, 'res', $dseq));
						}

						echo "</pre>";
					}
				
					else if ($form_action == 2) { // load questions
						// Get the compendium server parameters
						// 0: server address
						// 1: lab identifier (STL, SEA, etc)
						// 2: user name
						// 3: password
						$params = array();
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
							$params[] = trim($acsv[0]);
						}
						
						// verify directory
						$server = $params[0];
						$group = $params[1];
						$login = $params[2];
						$password = $params[3];
						
						echo "<br/>LOADING FROM: ".$server.$group."<br/><br/>";
						
						$cdcdir = $GLOBALS['temporary_files_dir']."/quest";
						if (!file_exists($cdcdir)) {
							if (!mkdir($cdcdir,0700)) {
								die('<br/><br/>Unable to create directory for CDC files ('.$cdcdir.')');
							}
						}
						
						$CDC = array();
						$CDC[] = "/AOE_".$group.".TXT";
						
						foreach ($CDC AS $file) {
							unlink($cdcdir.$file); // remove old file if there is one
							if (($fp = fopen($cdcdir.$file, "w+")) == false) {
								die('<br/><br/>Could not create local CDC file ('.$cdcdir.$file.')');
							}
								
							$ch = curl_init();
							$credit = ($login.':'.$password);
							curl_setopt($ch, CURLOPT_URL, 'https://cert.hub.care360.com/webdav/cdc/'.$group.$file);
							curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
							curl_setopt($ch, CURLOPT_USERPWD, $credit);
							curl_setopt($ch, CURLOPT_TIMEOUT, 15);
							curl_setopt($ch, CURLOPT_FILE, $fp);
							
							// testing only!!
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					
							if (($xml = curl_exec($ch)) === false) {
								curl_close($ch);
								fclose($fp);
								unlink($path.$file);
								die("<br/><br/>READ ERROR: ".curl_error($ch)." QUITING...");
							}
							 
							curl_close($ch);
							fclose($fp);
						}													
						
						// verify required file
						if (!file_exists($cdcdir."/AOE_".$group.".TXT"))
							die("<br/><br/>Compendium AOE file [AOE_".$group.".TXT] not accessable!!");
						
						// Mark the vendor's current questions inactive.
						sqlStatement("UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?",
								array($lab_id));
				
						// open the specimen requirements file for processing
						$fhcsv = fopen($cdcdir."/AOE_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium AOE questions file [AOE_".$group.".TXT] could not be openned!!");
						}
							
						// What should be uploaded is the AOE questions (AOE) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Performing Lab
						//   2: Unit Code
						//   3: Order Code    : mapped as procedure_code
						//   4: Analyte Code  : mapped as question_code
						//   5: Question Code 
						//   6: Active Flag   : mapped as activity (0/1)
						//   7: Profile Key
						//   8: Insert Date
						//   9: Question      : mapped as question_text
						//  10: Suffix
						//  11: Result Filter : mapped as tips
						//  12: Mnemonic
						//  13: Test Flag
						//  14: Update Date
						//  15: Update User
						//  16: Component
						//
							
						$lastcode = '';
						$text = '';
						$seq = 1;
						
						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
							
							// explode all content row fields
							$ahl7 = explode('^', $row);
							
							// store the data
							$pcode   = trim($ahl7[3]);
							$qcode   = trim($ahl7[4]);
							$fldtype = 'T'; // always text
							$required = 1; // always required
							$activity =  mysql_real_escape_string(trim($ahl7[6]));
							$activity = ($activity == 'A')? 1 : 0; 
							$question = str_replace(':', '', mysql_real_escape_string(trim($ahl7[9])));
				
							if (empty($pcode) || empty($qcode)) continue;
				
							// check for existing record
							$qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " .
									"lab_id = ? AND procedure_code = ? AND question_code = ?",
									array($lab_id, $pcode, $qcode));

							// new record
							if (empty($qrow['procedure_code'])) {
								sqlStatement("INSERT INTO procedure_questions SET " .
										"seq = ?, lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " .
										"fldtype = ?, required = ?, tips = ?, activity = ?",
										array($seq++, $lab_id, $pcode, $qcode, $question, $fldtype, $required, trim($ahl7[11]), $activity));
							}
							else { // update record
								sqlStatement("UPDATE procedure_questions SET " .
										"seq = ?, question_text = ?, fldtype = ?, required = ?, tips = ?, activity = ? WHERE " .
										"lab_id = ? AND procedure_code = ? AND question_code = ?",
										array($seq++, $question, $fldtype, $required, trim($ahl7[11]), $activity, $lab_id, $pcode, $qcode));
							}

							echo "QUESTION: $row";
							flush();
								
						} // end while

						echo "</pre>";							
					
					} // end load questions
						
						
					if ($form_action == 4) { // load profiles
						// Get the compendium server parameters
						// 0: server address
						// 1: lab identifier (STL, SEA, etc)
						// 2: user name
						// 3: password
						$params = array();
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
							$params[] = trim($acsv[0]);
						}
						
						// verify directory
						$server = $params[0];
						$group = $params[1];
						$login = $params[2];
						$password = $params[3];
						
						echo "<br/>LOADING FROM: ".$server.$group."<br/><br/>";
						
						$cdcdir = $GLOBALS['temporary_files_dir']."/quest";
						if (!file_exists($cdcdir)) {
							if (!mkdir($cdcdir,0700)) {
								die('<br/><br/>Unable to create directory for CDC files ('.$cdcdir.')');
							}
						}
						
						$CDC = array();
						$CDC[] = "/PROFILE_".$group.".TXT";
						
						foreach ($CDC AS $file) {
							unlink($cdcdir.$file); // remove old file if there is one
							if (($fp = fopen($cdcdir.$file, "w+")) == false) {
								die('<br/><br/>Could not create local CDC file ('.$cdcdir.$file.')');
							}
								
							$ch = curl_init();
							$credit = ($login.':'.$password);
							curl_setopt($ch, CURLOPT_URL, $server.$group.$file);
							curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
							curl_setopt($ch, CURLOPT_USERPWD, $credit);
							curl_setopt($ch, CURLOPT_TIMEOUT, 15);
							curl_setopt($ch, CURLOPT_FILE, $fp);
							
							// testing only!!
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					
							if (($xml = curl_exec($ch)) === false) {
								curl_close($ch);
								fclose($fp);
								unlink($path.$file);
								die("<br/><br/>READ ERROR: ".curl_error($ch)." QUITING...");
							}
							 
							curl_close($ch);
							fclose($fp);
						}													
						
						// verify required file
						if (!file_exists($cdcdir."/PROFILE_".$group.".TXT"))
							die("<br/><br/>Compendium profile file [PROFILE_".$group.".TXT] not accessable!!");
						
						// open the profile file for processing
						$fhcsv = fopen($cdcdir."/PROFILE_".$group.".TXT",'r');
						if (! $fhcsv) {
							die("<br/><br/>Compendium profile file [PROFILE_".$group.".TXT] could not be openned!!");
						}
							
						// What should be uploaded is the profile (PROFILE) file provided
						// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
						// Values for each row are:
						//   0: Quest Group   : group identifier (STL, ORD, QTE, etc)
						//   1: Performing Lab
						//   2: Order Code    : mapped as procedure_code
						//   3: Test Code
						//   4: Unit Code     : array stored as related_code
						//   5: Description 
						//   6: Specimen Type
						//   7: State
						//
						
						$pcode = '';
						$ucode = '';
						$lastcode = '';
						$components = array();
				
						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$row = fgets($fhcsv);
							if (substr($row,0,3) != $group) continue;
							
							// explode all content row fields
							$ahl7 = explode('^', $row);
							
							// store the data
							$ordercode   = trim($ahl7[2]);
							if (empty($ordercode)) continue;
				
							if ($lastcode && $lastcode != $ordercode) { // new code (store only once)
								// store componets for previous record
								$trow = sqlQuery("SELECT procedure_type_id FROM procedure_type " .
										"WHERE parent = ? AND procedure_code = ? AND lab_id = ? AND procedure_type = 'pro' " .
										"ORDER BY procedure_type_id DESC LIMIT 1",
										array($groupid, $lastcode, $lab_id));
				
								if (! empty($trow['procedure_type_id'])) {
									$comp_list = implode("^", $components);
									sqlInsert("UPDATE procedure_type SET related_code = ? WHERE procedure_type_id = ?",
											array($comp_list,$trow['procedure_type_id']));
									$components = array();
								}
							}
								
							echo "COMPONENT: $row";
							flush();
								
							// collect the comopnents
							$comp = trim($ahl7[4]);
							$components[$comp] = $comp;
							$lastcode = $ordercode;
						}
				
						// process last profile code
						$trow = sqlQuery("SELECT * FROM procedure_type " .
								"WHERE parent = ? AND procedure_code = ? AND lab_id = ? AND procedure_type = 'pro' " .
								"ORDER BY procedure_type_id DESC LIMIT 1",
									array($groupid, $lastcode, $lab_id));
				
						if (! empty($trow['procedure_type_id'])) {
							$comp_list = implode("^", $components);
							$comp_list = mysql_real_escape_string($comp_list);
							sqlInsert("UPDATE procedure_type SET related_code = ? WHERE procedure_type_id = ?",
									array($comp_list,$trow['procedure_type_id']));
							$components = array();
						}
							
						echo "</pre>";
					}
				
				} // End QUEST
				

				
				
				
				// -----------------------------------------------------------------------------------------------------------------
				// Vendor = Interpath Laboratory
				//
				if ($form_vendor == '1548208440') {
					if ($form_action == 1) { // load compendium
						// Mark everything for the indicated lab as inactive.
						sqlStatement("UPDATE procedure_type SET activity = 0, seq = 999999 WHERE " .
							"lab_id = ? AND procedure_type != 'grp' AND procedure_type != 'pro'",
							array($lab_id));

						// Load category group ids
						$result = sqlStatement("SELECT procedure_type_id, name FROM procedure_type ".
							"WHERE lab_id = ? AND parent = ? AND procedure_type = 'grp'",
								array($lab_id, $form_group));
						while ($record = sqlFetchArray($result)) $groups[$record['name']] = $record[procedure_type_id];

						// What should be uploaded is the Order Compendium spreadsheet provided
						// by Interpath, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Sort table by Order Code!!!
						// Values for each row are:
						//   0: Order code    : mapped as procedure_code
						//   1: Order Name    : mapped as procedure name
						//   2: Result Code   : mapped as discrete result code
						//   3: Result Name   : mapped as discrete result name
						//   4: Result LOINC  : mapped as identification number
						//	 5: Result CPT4   : mapped as cpt4

						$lastcode = '';
						$pseq = 1;
						$rseq = 1;
						$dseq = 100;
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);

//							$category = trim($acsv[9]);
							$category = ''; // NOT USED WITH INTERPATH
							if (!$category || $category == 'Category') {
								$groupid = $form_group; // no category, store under root
							}
							else { // find or add category
								$groupid = $groups[$category];
								if (!$groupid) {
									$groupid = sqlInsert("INSERT INTO procedure_type SET " .
										"procedure_type = 'grp', lab_id = ?, parent = ?, name = ?",
										array($lab_id, $form_group, $category));
									$groups[$category] = $groupid;
								}
							}

							// store the order 
							$ordercode = trim($acsv[0]);
							if (strtolower($ordercode) == "order code") continue;
							
							if ($lastcode != $ordercode) { // new code (store only once)
								$stdcode = '';
								if (trim($acsv[5]) != '') {
									$cpts = explode("^", trim($acsv[5]));
									if (!$cpts) $stdcode = "CPT4:".trim($acsv[5]);
									else foreach($cpts AS $cpt) {
										if ($stdcode) $stdcode .= "; ";
										$stdcode .= "CPT4:".$cpt;
									}
								}
								
								$trow = sqlQuery("SELECT * FROM procedure_type " .
									"WHERE parent = ? AND procedure_code = ? AND procedure_type = 'ord' " .
									"ORDER BY procedure_type_id DESC LIMIT 1",
										array($groupid, $ordercode));
								
								$name =  mysql_real_escape_string(trim($acsv[1]));
//								$notes =  mysql_real_escape_string(trim($acsv[7]));
//								$category =  mysql_real_escape_string(trim($acsv[9]));
								
								if (empty($trow['procedure_type_id'])) {
									$orderid = sqlInsert("INSERT INTO procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, procedure_type = ?, seq = ?, activity = 1",
											array($groupid, $name, $lab_id, $ordercode, $stdcode, 'ord', $pseq++));
								}
								else {
									$orderid = $trow['procedure_type_id'];
									sqlStatement("UPDATE procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, procedure_type = ?, seq = ?, activity = 1 " .
										"WHERE procedure_type_id = ?",
											array($groupid, $name, $lab_id, $ordercode, $stdcode, 'ord', $pseq++, $orderid));
								}
								
								/** NOT USED WITH INTERPATH
								// store detail records (one record per detail)
								if (trim($acsv[7])) { // preferred specimen
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'PREFERRED SPECIMEN', 'Preferred specimen collection method', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[7])), 'det', $dseq++, $orderid));
								}
								
								if (trim($acsv[6])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'CONTAINER TYPE', 'Specimen container type', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[6])), 'det', $dseq++, $orderid));
								}
								
								if (trim($acsv[8])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'SPECIMEN TRANSPORT', 'Method of specimen transport', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[8])), 'det', $dseq++, $orderid));
								}
								
								if (trim($acsv[12])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'TESTING METHOD', 'Method of performing test', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[12])), 'det', $dseq++, $orderid));
								}
								**/
								
								// reset counters for new procedure
								$lastcode = $ordercode;
								$rseq = 1;
								$dseq = 100;
							}
							
							// store the results
							$resultcode = trim($acsv[2]);
							$trow = sqlQuery("SELECT * FROM procedure_type WHERE " .
								"parent = ? AND procedure_code = ? AND procedure_type = 'res' " .
								"ORDER BY procedure_type_id DESC LIMIT 1",
									array($orderid, $resultcode));
							
							$stdcode = '';
							if (trim($acsv[4]) != '') {
								$stdcode .= "LOINC:".trim($acsv[4]);
								$name =  mysql_real_escape_string(trim($acsv[3]));
								$units = mysql_real_escape_string(trim($acsv[10]));
								$range = mysql_real_escape_string(trim($acsv[11]));
								
								if (empty($trow['procedure_type_id'])) {
									sqlStatement("INSERT INTO procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 ",
											array($orderid, $name, $lab_id, $resultcode, $stdcode, $units, $range, $rseq++, 'res'));
								}
								else {
									sqlStatement("UPDATE procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 " .
										"WHERE procedure_type_id = ?",
											array($groupid, $name, $lab_id, $resultcode, $stdcode, $units, $range, $rseq++, 'res', $trow['procedure_type_id']));
								}
							}
						}
					}

					else if ($form_action == 2) { // load questions
						// Mark the vendor's current questions inactive.
						sqlStatement("UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?",
								array($lab_id));

						// What should be uploaded is the "AOE Questions" spreadsheet provided
						// by CPL, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Values for each row are:
						//   0: Order Code
						//   1: Order Description
						//   2: Question Code
						//   3: Question
						//   4: Tips
						//
						$seq = 1;
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv, 4096);
							if ($seq++ < 2 || strtolower($acsv[0]) == "order code") continue;

							$pcode   = trim($acsv[0]);
							$qcode   = trim($acsv[2]);
							$required = 1; // always required
							$options = ''; // NOT USED
							if (empty($pcode) || empty($qcode)) continue;

							// Figure out field type.
							$fldtype = 'T'; // always text
//							if (strpos($acsv[4], 'Drop') !== FALSE) $fldtype = 'S';
//							else if (strpos($acsv[4], 'Multiselect') !== FALSE) $fldtype = 'S';

							$qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " .
									"lab_id = ? AND procedure_code = ? AND question_code = ?",
									array($lab_id, $pcode, $qcode));

							// If this is the first option value and it's a multi-select list,
							// then prepend '+;' here to indicate that.  CPL does not use those
							// but keep this note here for future reference.

							if (empty($qrow['procedure_code'])) {
								sqlStatement("INSERT INTO procedure_questions SET " .
										"lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " .
										"fldtype = ?, required = ?, options = ?, activity = 1",
										array($lab_id, $pcode, $qcode, trim($acsv[3]), $fldtype, $required, $options));
							}
							else {
								if ($qrow['activity'] == '1' && $qrow['options'] !== '' && $options !== '') {
									$options = $qrow['options'] . ';' . $options;
								}
								sqlStatement("UPDATE procedure_questions SET " .
										"question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE " .
										"lab_id = ? AND procedure_code = ? AND question_code = ?",
										array(trim($acsv[3]), $fldtype, $required, $options, $lab_id, $pcode, $qcode));
							}
						} // end while
					} // end load questions
					
				} // End Interpath
				
				
				
				
				// -----------------------------------------------------------------------------------------------------------------
				// Vendor = LabCorp Laboratories
				// -----------------------------------------------------------------------------------------------------------------
				if ($form_vendor == 'LABCORP') {
					if ($form_action == 1) { // load compendium
						// Get the compendium server parameters
						// 0: server address
						// 1: user name
						// 2: password
						$params = array();
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
							$params[] = trim($acsv[0]);
						}
						
						// verify directory
						$server = $params[0];
						$login = $params[1];
						$password = $params[2];
						
						echo "<br/>LOADING FROM: ".$server."<br/><br/>";
						
						$cdcdir = $GLOBALS['temporary_files_dir']."/labcorp";
						if (!file_exists($cdcdir)) {
							if (!mkdir($cdcdir,0700)) {
								die('<br/><br/>Unable to create directory for CDC files ('.$cdcdir.')');
							}
						}

						$file = 'labcorp_compendium.csv';
						unlink($cdcdir."/".$file); // remove old file if there is one
						if (($fp = fopen($cdcdir."/".$file, "w+")) == false) {
							die('<br/><br/>Could not create local CDC file ('.$cdcdir."/".$file.')');
						}
								
						$ch = curl_init();
						$credit = ($login.':'.$password);
						curl_setopt($ch, CURLOPT_URL, $server."/compendium/".$file);
						curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
						curl_setopt($ch, CURLOPT_USERPWD, $credit);
						curl_setopt($ch, CURLOPT_TIMEOUT, 90);
						curl_setopt($ch, CURLOPT_FILE, $fp);
						
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					
						curl_setopt($ch, CURLOPT_VERBOSE, 1);
						
						if (($xml = curl_exec($ch)) === false) {
							curl_close($ch);
							fclose($fp);
							unlink($cdcdir."/".$file);
							die("<br/><br/>READ ERROR: ".$server."/compendium/".$file." resulted in error: ".curl_error($ch)." QUITING...");
						}
							 
						curl_close($ch);
						fclose($fp);
						
						// verify required files
						if (!file_exists($cdcdir."/".$file))
							die("<br/><br/>LabCorp compendium file [labcorp_compendium.csv] not accessable!!");
						
						// Delete the detail records for this lab.
						sqlStatement("DELETE FROM procedure_type WHERE " .
								"lab_id = ? AND procedure_type = 'det' || procedure_type = 'res' ",	array($lab_id));
						
						// Mark procedures for the indicated lab as inactive.
						sqlStatement("UPDATE procedure_type SET activity = 0, seq = 999999, related_code = '' WHERE " .
								"lab_id = ? AND procedure_type != 'grp' ",array($lab_id));
				
						// Load category group ids (procedure and profile)
						$result = sqlStatement("SELECT procedure_type_id, name FROM procedure_type ".
							"WHERE parent = ? AND procedure_type = 'grp'",array($form_group));
						while ($record = sqlFetchArray($result)) $groups[$record['name']] = $record[procedure_type_id];
						if (!$groups['Profiles'] || !$groups['Procedures'])
							die("<br/><br/>Missing required compendium groups [Profiles, Procedures]");

						// open the order code file for processing
						$fhcsv = fopen($cdcdir."/".$file,'r');
						if (! $fhcsv) {						
							die("<br/><br/>LabCorp compendium file [labcorp_compendium.csv] could not be openned!!");
						}
						
						// What should be uploaded is the Order Compendium spreadsheet provided
						// by LabCorp, saved in "Text CSV" format from OpenOffice, using its
						// default settings.  Sort table by Order Code!!!
						//
						// Values for each row are:
						//   0: Line Number
						//   1: Order code    : mapped as procedure_code
						//   2: Order Name    : mapped as procedure name
						//   3: Orderable
						//   4: Published
						//   5: DOS
						//   6: AOE Segment
						//   7: CPT4 Codes
						//   8: Proc Class
						//   9: Result Code   : mapped as discrete result code
						//  10: Result Name   : mapped as discrete result name
						//  11: Result UofM
						//  12: Result Type
						//  13: Result LOINC  : mapped as identification number
						//  14: Result Proc Class
						//  15: Reflex (65 fields) -- ignored
						//  80: Special Instructions
						//  81: Specimen Type
						//  82: Specimen Volume
						//  83: Minimum Volume
						//  84: Specimen Container
						//  85: Specimen Collection
						//  86: Specimen Storage
						//  87: Testing Frequency
						//  88: Testing Method
						//  89: Volume
						//  90: Profile Flag
						//  91: Changed Date
				
						$lastcode = '';
						$pseq = 1;
						$rseq = 1;
						$dseq = 100;

						echo "<pre style='font-size:10px'>";
						
						$groupid = $groups['Procedures'];
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
				
							if (trim($acsv[3]) != 'Y') continue; // not orderable
							if (trim($acsv[4]) != 'P') continue; // not published
							if (trim($acsv[90]) != 'T') continue; // not test 
							
							// store the order
							$ordercode = trim($acsv[1]);
							if (strtolower($ordercode) == "order code") continue;
								
							if ($lastcode != $ordercode) { // new code (store only once)
								$stdcode = '';
								if (trim($acsv[7]) != '') {
									$cpts = str_replace(' 001','',trim($acsv[7]));
									$stdcode = "CPT4:".str_replace(' ',', ',$cpts);
								}
				
								$trow = sqlQuery("SELECT * FROM procedure_type " .
										"WHERE parent = ? AND procedure_code = ? AND procedure_type = 'ord' " .
										"ORDER BY procedure_type_id DESC LIMIT 1",
										array($groupid, $ordercode));
				
								$name =  trim($acsv[2]);
								$zseg = trim($acsv[6]);
								$pclass = trim($acsv[8]);
								$notes =  trim($acsv[80]);
								$specimen = trim($acsv[81]);
				
								echo "PROCEDURE: $ordercode, $name, $stdcode, $specimen\n";
								flush();
								
								if (empty($trow['procedure_type_id'])) {
									$orderid = sqlInsert("INSERT INTO procedure_type SET " .
											"parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, standard_code = ?, notes = ?, body_site = ?, transport = ?, procedure_type = ?, seq = ?, activity = 1",
											array($groupid, $name, $specimen, $lab_id, $ordercode, $stdcode, $notes, $pclass, $zseg, 'ord', $pseq++));
								}
								else {
									$orderid = $trow['procedure_type_id'];
									sqlStatement("UPDATE procedure_type SET " .
											"parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, notes = ?, body_site = ?, transport = ?, procedure_type = ?, seq = ?, activity = 1 " .
											"WHERE procedure_type_id = ?",
											array($groupid, $name, $specimen, $lab_id, $ordercode, $notes, $pclass, $zseg, 'ord', $pseq++, $orderid));
								}
				
								// store detail records (one record per detail)
								if (trim($acsv[85])) { // specimen collection
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'SPECIMEN COLLECTION', 'Preferred specimen collection method', $lab_id, $ordercode, trim($acsv[85]), 'det', $dseq++));
								}
				
								if (trim($acsv[84])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'CONTAINER TYPE', 'Specimen container type', $lab_id, $ordercode, trim($acsv[84]), 'det', $dseq++));
								}
				
								if (trim($acsv[82])) { // volume
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'SPECIMUN VOLUME', 'Specimen volume requirement', $lab_id, $ordercode, trim($acsv[82]), 'det', $dseq++));
								}
				
								if (trim($acsv[86])) { // storage
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'SPECIMEN STORAGE', 'Method of specimen storage', $lab_id, $ordercode, trim($acsv[86]), 'det', $dseq++));
								}
				
								if (trim($acsv[88])) { // method
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'TESTING METHOD', 'Method of performing test', $lab_id, $ordercode, trim($acsv[88]), 'det', $dseq++));
								}

								if (trim($acsv[87])) { // frequency
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'TESTING FREQUENCY', 'How frequently tests a processed', $lab_id, $ordercode, trim($acsv[87]), 'det', $dseq++));
								}
								
								// reset counters for new procedure
								$lastcode = $ordercode;
								$rseq = 1;
								$dseq = 100;
							}
								
							// store the results
							$resultcode = trim($acsv[9]);
							$trow = sqlQuery("SELECT * FROM procedure_type WHERE " .
									"parent = ? AND procedure_code = ? AND procedure_type = 'res' " .
									"ORDER BY procedure_type_id DESC LIMIT 1", array($orderid, $resultcode));
								
							$stdcode = '';
							if (trim($acsv[13]) != '') $stdcode .= "LOINC:".trim($acsv[13]);
							$name =  trim($acsv[10]);
							$units = trim($acsv[11]);
							$range = ''; // not available from LABCORP
															
							echo "RESULT: $resultcode, $name, $stdcode, $units\n";
							flush();
							
							if (empty($trow['procedure_type_id'])) {
								sqlStatement("INSERT INTO procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 ",
										array($orderid, $name, $lab_id, $resultcode, $stdcode, $units, $range, $rseq++, 'res'));
							}
							else {
								sqlStatement("UPDATE procedure_type SET " .
										"parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 " .
										"WHERE procedure_type_id = ?",
										array($groupid, $name, $lab_id, $resultcode, $stdcode, $units, $range, $rseq++, 'res', $trow['procedure_type_id']));
							}
				
						}
						echo "</pre>";
					}
				
					else if ($form_action == 2) { // load questions
						// Get the compendium server parameters
						// 0: server address
						// 1: user name
						// 2: password
						$params = array();
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
							$params[] = trim($acsv[0]);
						}
						
						// verify directory
						$server = $params[0];
						$login = $params[1];
						$password = $params[2];
						
						$cdcdir = $GLOBALS['temporary_files_dir']."/labcorp";
						if (!file_exists($cdcdir)) {
							if (!mkdir($cdcdir,0700)) {
								die('<br/><br/>Unable to create directory for CDC files ('.$cdcdir.')');
							}
						}
						
						$file = 'labcorp_questions.csv';
						
						// if a local file exists then use it
						if (!file_exists($cdcdir."/".$file)) {
							if (($fp = fopen($cdcdir."/".$file, "w+")) == false) {
								die('<br/><br/>Could not create local CDC file ('.$cdcdir."/".$file.')');
							}
						
							echo "<br/>LOADING FROM: ".$server."<br/><br/>";
						
							$ch = curl_init();
							$credit = ($login.':'.$password);
							curl_setopt($ch, CURLOPT_URL, $server."/compendium/".$file);
							curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
							curl_setopt($ch, CURLOPT_USERPWD, $credit);
							curl_setopt($ch, CURLOPT_TIMEOUT, 90);
							curl_setopt($ch, CURLOPT_FILE, $fp);
						
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
								
							curl_setopt($ch, CURLOPT_VERBOSE, 1);
						
							if (($xml = curl_exec($ch)) === false) {
								curl_close($ch);
								fclose($fp);
								unlink($cdcdir."/".$file);
								die("<br/><br/>READ ERROR: ".$server."/compendium/".$file." resulted in error: ".curl_error($ch)." QUITING...");
							}
						
							curl_close($ch);
							fclose($fp);
						}
						else {
							echo "<br/>LOADING FROM: ".$cdcdir."/".$file."<br/><br/>";
						}
						
						// open the order code file for processing
						$fhcsv = fopen($cdcdir."/".$file,'r');
						if (! $fhcsv) {
							die("<br/><br/>LabCorp compendium file [$file] could not be openned!!");
						}
						
						// Mark the vendor's current questions inactive.
						sqlStatement("UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?", array($lab_id));
				
						// LabCorp does their questions by order type, not individual test so there
						// is a separate labcorp_aoe table with the questions. This table is used
						// to map procedure codes to question types.
						//   0: Field Code
						//   1: Question Segment (Zseg)
						//   2: Section
						//   3: Sequence
						//   4: Active
						//   5: Question Text
						//   6: Field Type ('Text', 'List', 'Date', 'Mask')
						//   7: Options (list name or mask)
						//   8: Max Size
						//   9: Tips
						//
						$seq = 1;
												
						echo "<pre style='font-size:10px'>";
						
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
							if (strtolower($acsv[1]) == "field") continue; // header
							if (strtolower($acsv[4]) != "y") continue; // not active
								
							$field   	= trim($acsv[0]);
							$zseg    	= trim($acsv[1]);
							$section 	= trim($acsv[2]);
							$seq     	= trim($acsv[3]);
							$question 	= trim($acsv[5]);
							$fldtype	= trim($acsv[6]);
							$options	= trim($acsv[7]);
							$maxsize	= trim($acsv[8]);
							$tips		= trim($acsv[9]);
							
							$required = 1; // always required

							if (empty($field) || empty($zseg)) continue;

							// look for existing record
							$qrow = sqlQuery("SELECT * FROM procedure_questions WHERE " .
									"lab_id = ? AND procedure_code = ? AND question_code = ?",
									array($lab_id, $field, $zseg));

							echo "QUESTION: $zseg, $field, $seq, $question\n";
							
							$activity = 1; // assume AOE required (except for standard fields below)
							if ($field == 'ZCI4' || $field == 'ZCI3.1' || $field == 'OBR15') $activity = 0;
							
							// create or update the record
							sqlStatement("REPLACE INTO procedure_questions SET " .
										"lab_id = ?, procedure_code = ?, question_code = ?, seq = ?, question_text = ?, " .
										"fldtype = ?, required = ?, options = ?, tips = ?, maxsize = ?, section = ?, activity = ?",
										array($lab_id, $zseg, $field, $seq, $question, $fldtype, $required, $options, $tips, $maxsize, $section, $activity));
						} // end while
							
						echo "</pre>";
					
					} // end load questions
						
						
					if ($form_action == 4) { // load profiles
						// Get the compendium server parameters
						// 0: server address
						// 1: user name
						// 2: password
						$params = array();
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
							$params[] = trim($acsv[0]);
						}
						
						// verify directory
						$server = $params[0];
						$login = $params[1];
						$password = $params[2];
						
						$cdcdir = $GLOBALS['temporary_files_dir']."/labcorp";
						if (!file_exists($cdcdir)) {
							if (!mkdir($cdcdir,0700)) {
								die('<br/><br/>Unable to create directory for CDC files ('.$cdcdir.')');
							}
						}

						$file = 'labcorp_compendium.csv';
						
						// if a local file exists then use it
						if (!file_exists($cdcdir."/".$file)) {
							if (($fp = fopen($cdcdir."/".$file, "w+")) == false) {
								die('<br/><br/>Could not create local CDC file ('.$cdcdir."/".$file.')');
							}
								
							echo "<br/>LOADING FROM: ".$server."<br/><br/>";
						
							$ch = curl_init();
							$credit = ($login.':'.$password);
							curl_setopt($ch, CURLOPT_URL, $server."/compendium/".$file);
							curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
							curl_setopt($ch, CURLOPT_USERPWD, $credit);
							curl_setopt($ch, CURLOPT_TIMEOUT, 90);
							curl_setopt($ch, CURLOPT_FILE, $fp);
						
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					
							curl_setopt($ch, CURLOPT_VERBOSE, 1);
						
							if (($xml = curl_exec($ch)) === false) {
								curl_close($ch);
								fclose($fp);
								unlink($cdcdir."/".$file);
								die("<br/><br/>READ ERROR: ".$server."/compendium/".$file." resulted in error: ".curl_error($ch)." QUITING...");
							}
							 
							curl_close($ch);
							fclose($fp);
						}
						else {
							echo "<br/>LOADING FROM: ".$cdcdir."/".$file."<br/><br/>";
						}
								
						// Load category group ids (procedure and profile)
						$result = sqlStatement("SELECT procedure_type_id, name FROM procedure_type ".
							"WHERE parent = ? AND procedure_type = 'grp'",array($form_group));
						while ($record = sqlFetchArray($result)) $groups[$record['name']] = $record[procedure_type_id];
						if (!$groups['Profiles'] || !$groups['Procedures'])
							die("<br/><br/>Missing required compendium groups [Profiles, Procedures]");

						// open the order code file for processing
						$fhcsv = fopen($cdcdir."/".$file,'r');
						if (! $fhcsv) {						
							die("<br/><br/>LabCorp compendium file [labcorp_compendium.csv] could not be openned!!");
						}
						
						$orderid = '';
						$lastcode = '';
						$pseq = 1;
						$rseq = 1;
						$dseq = 100;
						$components = array();
						
						echo "<pre style='font-size:10px'>";
						
						$groupid = $groups['Profiles'];
						while (!feof($fhcsv)) {
							$acsv = fgetcsv($fhcsv);
						
							if (trim($acsv[3]) != 'Y') continue; // not orderable
							if (trim($acsv[4]) != 'P') continue; // not published
							if (trim($acsv[90]) != 'P') continue; // not test
								
							// store the order
							$ordercode = trim($acsv[1]);
							if (strtolower($ordercode) == "order code") continue;
						
							// store the data
							$ordercode   = trim($acsv[1]);
							if (empty($ordercode)) continue;
						
							if ($lastcode != $ordercode) { // new code (store only once)
								if ($lastcode && $components) {
									// store componets for previous record
									$trow = sqlQuery("SELECT procedure_type_id FROM procedure_type " .
											"WHERE parent = ? AND procedure_code = ? AND lab_id = ? AND procedure_type = 'pro' " .
											"ORDER BY procedure_type_id DESC LIMIT 1", array($groupid, $lastcode, $lab_id));
						
									if (! empty($trow['procedure_type_id'])) {
										$comp_list = implode("^", $components);
										sqlInsert("UPDATE procedure_type SET related_code = ? WHERE procedure_type_id = ?",
										array($comp_list, $trow['procedure_type_id']));
									}
								}

								// clear new components
								$components = array();
								
								// store profile record
								$stdcode = '';
								if (trim($acsv[7]) != '') {
									$cpts = str_replace(' 001','',trim($acsv[7]));
									$stdcode = "CPT4:".str_replace(' ',', ',$cpts);
								}
						
								$trow = sqlQuery("SELECT * FROM procedure_type " .
										"WHERE parent = ? AND procedure_code = ? AND procedure_type = 'ord' " .
										"ORDER BY procedure_type_id DESC LIMIT 1",
										array($groupid, $ordercode));
						
								$name =  trim($acsv[2]);
								$notes =  trim($acsv[80]);
								$specimen = trim($acsv[81]);
								$name =  trim($acsv[2]);
								$zseg = trim($acsv[6]);
								$pclass = trim($acsv[8]);
								$notes =  trim($acsv[80]);
								$specimen = trim($acsv[81]);
								
								echo "PROFILE: $ordercode, $name, $stdcode, $specimen\n";
								flush();
																
								if (empty($trow['procedure_type_id'])) {
								$orderid = sqlInsert("INSERT INTO procedure_type SET " .
								"parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, standard_code = ?, notes = ?, body_site = ?, transport = ?, procedure_type = ?, seq = ?, activity = 1",
											array($groupid, $name, $specimen, $lab_id, $ordercode, $stdcode, $notes, $pclass, $zseg, 'pro', $pseq++));
								}
								else {
								$orderid = $trow['procedure_type_id'];
								sqlStatement("UPDATE procedure_type SET " .
								"parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, notes = ?, body_site = ?, transport = ?, procedure_type = ?, seq = ?, activity = 1 " .
											"WHERE procedure_type_id = ?",
											array($groupid, $name, $specimen, $lab_id, $ordercode, $notes, $pclass, $zseg, 'pro', $pseq++, $orderid));
								}
								
								// store detail records (one record per detail)
								if (trim($acsv[85])) { // specimen collection
								sqlStatement("REPLACE INTO procedure_type SET " .
										"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
										array($orderid, 'SPECIMEN COLLECTION', 'Preferred specimen collection method', $lab_id, $ordercode, trim($acsv[85]), 'det', $dseq++));
								}
						
								if (trim($acsv[84])) { // container
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'CONTAINER TYPE', 'Specimen container type', $lab_id, $ordercode, trim($acsv[84]), 'det', $dseq++));
								}
						
								if (trim($acsv[82])) { // volume
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'SPECIMUN VOLUME', 'Specimen volume requirement', $lab_id, $ordercode, trim($acsv[82]), 'det', $dseq++));
								}
						
								if (trim($acsv[86])) { // storage
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'SPECIMEN STORAGE', 'Method of specimen storage', $lab_id, $ordercode, trim($acsv[86]), 'det', $dseq++));
								}
						
								if (trim($acsv[88])) { // method
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'TESTING METHOD', 'Method of performing test', $lab_id, $ordercode, trim($acsv[88]), 'det', $dseq++));
								}
						
								if (trim($acsv[87])) { // frequency
									sqlStatement("REPLACE INTO procedure_type SET " .
											"parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
											array($orderid, 'TESTING FREQUENCY', 'How frequently tests a processed', $lab_id, $ordercode, trim($acsv[87]), 'det', $dseq++));
								}
						
								// reset counters for new procedure
								$lastcode = $ordercode;
								$rseq = 1;
								$dseq = 100;
							}
						
							// collect the comopnents
							$comp = trim($acsv[9]);
							$components[$comp] = $comp;
							$lastcode = $ordercode;
								
							echo "COMPONENT: $comp, $acsv[10] \n";
							flush();
						}
						
						// process last profile code
						if ($components) {
							$trow = sqlQuery("SELECT * FROM procedure_type " .
									"WHERE parent = ? AND procedure_code = ? AND lab_id = ? AND procedure_type = 'pro' " .
									"ORDER BY procedure_type_id DESC LIMIT 1",
									array($groupid, $lastcode, $lab_id));
							
							if (! empty($trow['procedure_type_id'])) {
								$comp_list = implode("^", $components);
								sqlInsert("UPDATE procedure_type SET related_code = ? WHERE procedure_type_id = ?",
								array($comp_list,$trow['procedure_type_id']));
								$components = array();
							}
						}
							
						echo "</pre>";
					}
				
				} // End LabCorp
				
				
				
				
				
				/* END UPLOAD DEFINITIONS */
				fclose($fhcsv);
			}
			else {
				echo xlt('Internal error accessing uploaded file!');
				$form_step = -1;
			}
		}
		else {
			echo xlt('Upload failed!');
			$form_step = -1;
		}
		$auto_continue = true;
	}

	if ($form_step == 2) {
		$form_status = xlt('Done') . ".";
		echo nl2br($form_status);
	}

	++$form_step;
?>

					</td>
				</tr>
			</table>

			<input type='hidden' name='form_step'
				value='<?php echo attr($form_step); ?>' /> <input type='hidden'
				name='form_status' value='<?php echo $form_status; ?>' />

		</form>

<?php if ($auto_continue) { ?>
	<script>
		setTimeout("document.forms[0].submit();", 3000);
	</script>
<?php } ?>

</body>
</html>

