<?php
// -----------------------------------------------------------------------------------------------------------------
// Vendor = Pathgroup
// -----------------------------------------------------------------------------------------------------------------
if ($form_action == 1) { // load compendium
	// Delete the detail records for this lab.
	sqlStatement ( "DELETE FROM procedure_type WHERE lab_id = ? AND (procedure_type = 'det' OR procedure_type = 'res') ", array (
		$lab_id
	) );
	
	// Mark everything for the indicated lab as inactive.
	sqlStatement ( "UPDATE procedure_type SET activity = 0, seq = 999999 WHERE lab_id = ? AND procedure_type != 'grp'", array (
		$lab_id
	) );
	
	// Load category group ids
	$result = sqlStatement ( "SELECT procedure_type_id, name FROM procedure_type WHERE lab_id = ? AND parent = ? AND procedure_type = 'grp'", array (
			$lab_id,
			$form_group
	) );
	$groups = array();
	while ( $record = sqlFetchArray ( $result ) )
		$groups[$record['name']] = $record[procedure_type_id];
	if (!$groups['Profiles'] || !$groups['Procedures'])
		die ( "<br/><br/>Missing required compendium groups [Profiles, Procedures]" );
	
	// What should be uploaded is the "Compendium" spreadsheet provided by
	// PathGroup, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: Order Code : mapped to procedure_code of order type
	// 1: Order Name : mapped to name of order type
	// 2: Has AOE flag : not used
	// 3: Result Code : mapped to procedure_code of result type
	// 4: CPT Code
	// 5: Result Name : mapped to name of result type
	// 6: LOINC code
	// 7: Profile Flag:  TRUE/FALSE
	// 8: Accession Type
	// 9: Transport
	// 10: Required Volume
	// 11: Specimen Type
	// 12: Priority
	// 13: Client Restricted ?
	//
	$lastcode = '';
	$lastres = '';
	$pseq = 1;
	$rseq = 1;
	$dseq = 100;
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		
		// store the order
		$ordercode = trim ( $acsv[0] );
		if (!$ordercode || strtolower($ordercode) == "obr code")
			continue;
		
		if ($lastcode != $ordercode) { // new code (store only once)
			$stdcode = '';
			if (trim ( $acsv[4] ) != '') {
				$cpts = trim ( $acsv[4] );
				$stdcode = "CPT4:" . str_replace ( ' ', ', ', $cpts );
			}
			
			$profile = (trim($acsv[7]) == 'TRUE')? "pro" : "ord";
			$groupid = ($profile == 'pro')? $groups['Profiles'] : $groups['Procedures'];
			
			$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = ? ORDER BY procedure_type_id ASC", array (
					$groupid,
					$ordercode,
					$profile
			) );
			
			$name = trim ( $acsv[1] );
			$pclass = trim ( $acsv[8] ); // accession type (PAP, etc)
			$transport = trim ( $acsv[9] );
			
			echo ($profile == 'pro')? "PANEL: " : "PROCEDURE: ";
			echo "$ordercode, $name, $stdcode, $specimen\n";
			flush ();
			
			if (empty ( $trow ['procedure_type_id'] )) {
				$orderid = sqlInsert ( "INSERT INTO procedure_type SET parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, standard_code = ?, notes = ?, body_site = ?, transport = ?, procedure_type = ?, seq = ?, activity = 1", array (
						$groupid,
						$name,
						'',
						$lab_id,
						$ordercode,
						$stdcode,
						'',
						$pclass,
						$transport,
						$profile,
						$pseq ++
				) );
			} else {
				$orderid = $trow ['procedure_type_id'];
				sqlStatement ( "UPDATE procedure_type SET parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, notes = ?, body_site = ?, transport = ?, procedure_type = ?, seq = ?, activity = 1 WHERE procedure_type_id = ?", array (
						$groupid,
						$name,
						'',
						$lab_id,
						$ordercode,
						'',
						$pclass,
						$transport,
						$profile,
						$pseq ++,
						$orderid
				) );
			}
				
			// store detail records (one record per detail)
			if (trim ( $acsv[10] )) { // volume
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
				$orderid,
				'VOLUME',
				'Required specimen volume',
				$lab_id,
				$ordercode,
				trim ( $acsv[10] ),
				'det',
				$dseq ++
				) );
			}
				
			if (trim ( $acsv[12] )) { // priority
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
				$orderid,
				'PRIORITY',
				'Specimen priority',
				$lab_id,
				$ordercode,
				trim ( $acsv[12] ),
				'det',
				$dseq ++
				) );
			}
				
			if (trim ( $acsv[11] )) { // priority
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
				$orderid,
				'CONTAINER',
				'Specimen container',
				$lab_id,
				$ordercode,
				trim ( $acsv[11] ),
				'det',
				$dseq ++
				) );
			}
			
				
			// reset counters for new procedure
			$lastcode = $ordercode;
			$rseq = 1;
			$dseq = 100;
		}
			
				
		// store the results
		$resultcode = trim ( $acsv[3] );
		if ($lastres != $resultcode) { // new code (store only once)
			$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = 'res' ORDER BY procedure_type_id DESC LIMIT 1", array (
					$orderid,
					$resultcode 
			) );
		
			$stdcode = $resultcode;
			if (trim ( $acsv[6] ) != '')
				$stdcode = trim ( $acsv[6] );
			$name = trim ( $acsv[5] );
			$units = ''; // not for PATHGROuP
			$range = ''; // not available from PATHGROUP
		
			echo "RESULT: $resultcode, $name, $stdcode\n";
			flush ();
		
			if (empty ( $trow ['procedure_type_id'] )) {
				sqlStatement ( "INSERT INTO procedure_type SET parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 ", array (
						$orderid,
						$name,
						$lab_id,
						$stdcode,
						$resultcode,
						$units,
						$range,
						$rseq ++,
						'res' 
				) );
			} else {
				sqlStatement ( "UPDATE procedure_type SET parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 WHERE procedure_type_id = ?", array (
						$orderid,
						$name,
						$lab_id,
						$stdcode,
						$resultcode,
						$units,
						$range,
						$rseq ++,
						'res',
						$trow ['procedure_type_id'] 
				) );
			} // end if
			
			// reset counters for new result
			$lastres = $resultcode;
		}
	} // end while
	echo "</pre>";
} // end load compendium

else if ($form_action == 2) { // load questions
    // Mark the vendor's current questions inactive.
	sqlStatement ( "UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?", array (
			$lab_id 
	) );
	
	// What should be uploaded is the "AOE Questions" spreadsheet provided by
	// PathGroup, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: OBRCode (order code)
	// 1: Question Code
	// 2: Question
	// 3: "Tips"
	// 4: Required (TRUE/FALSE)
	// 5: Maxchar (integer length)
	// 6: Minchar (integer length)
	// 7: Data Type
	// 8: Option Codes
	// 9: Option Descriptions
	// 10: Source
	//
	$seq = 0;
	$last_code = '';
	

	echo "<pre style='font-size:10px'>";
	
	
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if (count ( $acsv ) < 10 || strtolower($acsv[4]) == "required")
			continue;
		$code = trim ( $acsv[0] );
		if (empty ( $code ))
			continue;
		
		if ($code != $last_code) {
			$seq = 0;
			$last_code = $code;
		}
		++ $seq;
		
		$required = ($acsv[4] == 'TRUE')? 1 : 0;
		$maxsize = 0 + $acsv[5];
		$minsize = 0 + $acsv[6];
		
		// types: M=mask, L=list, D=date, T=text
		$fldtype = 'T'; // DEFAULT
		
		// Figure out field type.
		$type = strtolower($acsv[7]);
		if ($type == 'cbolist' || $type == 'cbomulti' || $type == 'testsource')
			$fldtype = 'L';
		else if ($type == 'cborange' || $type == 'string' || $type == 'system')
			$fldtype = 'T';
		else if ($type == 'date')
			$fldtype = 'D';
		else if ($type == 'regex')
			$fldtype = 'R';
		else if ($type == 'integer')
			$fldtype = 'N';
		
		// determine what to put in options
		if ($fldtype == 'L') { // list
			$parts = explode('|', $acsv[1]);
			if ($acsv[8] == 'N,Y' || $acsv[8] == 'N|Y') $parts[1] = 'NY';
			$options = 'Pathgroup_'.$parts[1];
		}
		else if ($fldtype == 'R') { // regex
			$options = $acsv[8];
		}
		else {
			$options = '';
		}
			
		$qrow = sqlQuery ( "SELECT * FROM procedure_questions WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
				$lab_id,
				$code,
				$acsv[1] 
		) );
		
		if (empty ( $qrow ['question_code'] )) {
			sqlStatement ( "INSERT INTO procedure_questions SET lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, required = ?, maxsize = ?, fldtype = ?, options = ?, tips = ?,
									activity = 1, seq = ?", array (
					$lab_id,
					$code,
					$acsv[1],
					$acsv[2],
					$required,
					$maxsize,
					$fldtype,
					$options,
					$acsv[3],
					$seq 
			) );
		} else {
			sqlStatement ( "UPDATE procedure_questions SET question_text = ?, required = ?, maxsize = ?, fldtype = ?, options = ?, tips = ?, activity = 1, seq = ? WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
					$acsv[2],
					$required,
					$maxsize,
					$fldtype,
					$options,
					$acsv[3],
					$seq,
					$lab_id,
					$code,
					$acsv[1] 
			) );
		}
	} // end while
	echo "</pre>";
} // end load questions

else if ($form_action == 3) { // load question options
    // Remove vendors current options
	sqlStatementNoLog ( "DELETE FROM list_options WHERE list_id LIKE 'Pathgroup%' OR option_id LIKE 'Pathgroup%' " );
	
	// What should be uploaded is the "AOE Options" spreadsheet provided
    // by YPMG, saved in "Text CSV" format from OpenOffice, using its
    // default settings. Values for each row are:
    // 0: OBRCode (procedure code)
    // 1: OBXCode (question code)
    // 2: Question (option text)
    // 3: Hint
    // 4: Required
    // 5: Max Value
    // 6: Min Value
    // 7: Field Type
    // 8: Options
    // 9: Option Text
    //10: Source
    //
    
	$entries = array();
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if (count ( $acsv ) < 11 || (strtolower($acsv[0]) == "obr code"))
			continue;
		$pcode = trim ( $acsv[0] );
		$qcode = trim ( $acsv[1] );
		if (empty ( $pcode ) || empty ( $qcode )) continue;
		
		// Figure out field type.
		$type = strtolower($acsv[7]);
		if ($type != 'cbolist' && $type != 'cbomulti' && $type != 'testsource') continue;
		
		// determine list name
		$parts = explode('|', $acsv[1]);
		if (!$parts[1]) continue;

		// get values
		if ($type == 'testsource') {
			if (strpos($acsv[10], '|') !== false) $data = explode('|',str_replace("'","",$acsv[10])); // inline data
			else $data = explode(',',str_replace("'","",$acsv[10])); // inline data
			asort($data);
			$opt_keys = $data;
			$opt_values = $data;
		}
		else {
			if (strpos($acsv[8], '|') !== false) $opt_keys = explode('|',str_replace("'","",$acsv[8]));
			else $opt_keys = explode(',',str_replace("'","",$acsv[8]));
			
			if (strpos($acsv[9], '|') !== false) $opt_values = explode('|',str_replace("'","",$acsv[9]));
			else $opt_values = explode(',',str_replace("'","",$acsv[9]));
		}
		
		if ($acsv[8] == 'N,Y' || $acsv[8] == 'N|Y') $parts[1] = 'NY';
		for ($i = 0; $i < count($opt_keys); $i++) {
			$entries[$parts[1]][$opt_keys[$i]] = $opt_values[$i]; // collect max list of unique values
		}
	}


	echo "<pre style='font-size:10px'>";
	
	// save all of the entries
	if (is_array($entries)) foreach ($entries AS $key => $values) {
		if (empty($values)) continue;
		
		$list = 'Pathgroup_'.$key;
		$title = 'Pathgroup '.$key;
		
		// create the list
		echo "LIST NAME: $list\n";
		sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = 'lists', option_id = ?, title = ?", array (
			$list,
			$title,
		) );
		
		if (is_array($values)) foreach ($values AS $option => $title) {
			if (!$option) continue;
			if (!$title) $title = $option;
			echo "LIST OPTION: $option, $title"; 
			sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, title = ?", array (
				$list,
				$option,
				$title,
			) );
		}
	} // end while

	echo "</pre>";
} // end load questions

				
