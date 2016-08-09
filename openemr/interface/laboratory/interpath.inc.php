<?php
// -----------------------------------------------------------------------------------------------------------------
// Vendor = Interpath Laboratory
// -----------------------------------------------------------------------------------------------------------------
if ($form_action == 1) { // load compendium
    // Delete the detail records for this lab.
	sqlStatement ( "DELETE FROM procedure_type WHERE " . "lab_id = ? AND (procedure_type = 'det' OR procedure_type = 'res') ", array (
			$lab_id 
	) );
	
    // Mark everything for the indicated lab as inactive.
	sqlStatement ( "UPDATE procedure_type SET activity = 0, seq = 999999 WHERE " . "lab_id = ? AND procedure_type != 'grp' AND procedure_type != 'pro'", array (
			$lab_id 
	) );
	
	// Load category group ids
	$result = sqlStatement ( "SELECT procedure_type_id, name FROM procedure_type " . "WHERE lab_id = ? AND parent = ? AND procedure_type = 'grp'", array (
			$lab_id,
			$form_group 
	) );
	while ( $record = sqlFetchArray ( $result ) )
		$groups [$record ['name']] = $record [procedure_type_id];
		
		// What should be uploaded is the Order Compendium spreadsheet provided
		// by Interpath, saved in "Text CSV" format from OpenOffice, using its
		// default settings. Sort table by Order Code!!!
		// Values for each row are:
		// 0: Order code : mapped as procedure_code
		// 1: Order Name : mapped as procedure name
		// 2: Result Code : mapped as discrete result code
		// 3: Result Name : mapped as discrete result name
		// 4: Result LOINC : mapped as identification number
		// 5: Result CPT4 : mapped as cpt4
	
	$lastcode = '';
	$pseq = 1;
	$rseq = 1;
	$dseq = 100;
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		
		// $category = trim($acsv[9]);
		$category = ''; // NOT USED WITH INTERPATH
		if (! $category || $category == 'Category') {
			$groupid = $form_group; // no category, store under root
		} else { // find or add category
			$groupid = $groups [$category];
			if (! $groupid) {
				$groupid = sqlInsert ( "INSERT INTO procedure_type SET " . "procedure_type = 'grp', lab_id = ?, parent = ?, name = ?", array (
						$lab_id,
						$form_group,
						$category 
				) );
				$groups [$category] = $groupid;
			}
		}
		
		// store the order
		$ordercode = trim ( $acsv [0] );
		if (strtolower ( $ordercode ) == "order code")
			continue;
		
		if ($lastcode != $ordercode) { // new code (store only once)
			$stdcode = '';
			if (trim ( $acsv [5] ) != '') {
				$cpts = explode ( "^", trim ( $acsv [5] ) );
				if (! $cpts)
					$stdcode = "CPT4:" . trim ( $acsv [5] );
				else
					foreach ( $cpts as $cpt ) {
						if ($stdcode)
							$stdcode .= "; ";
						$stdcode .= "CPT4:" . $cpt;
					}
			}
			
			$trow = sqlQuery ( "SELECT * FROM procedure_type " . "WHERE parent = ? AND procedure_code = ? AND procedure_type = 'ord' " . "ORDER BY procedure_type_id DESC LIMIT 1", array (
					$groupid,
					$ordercode 
			) );
			
			$name = mysql_real_escape_string ( trim ( $acsv [1] ) );
			// $notes = mysql_real_escape_string(trim($acsv[7]));
			// $category = mysql_real_escape_string(trim($acsv[9]));
			
			if (empty ( $trow ['procedure_type_id'] )) {
				$orderid = sqlInsert ( "INSERT INTO procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, procedure_type = ?, seq = ?, activity = 1", array (
						$groupid,
						$name,
						$lab_id,
						$ordercode,
						$stdcode,
						'ord',
						$pseq ++ 
				) );
			} else {
				$orderid = $trow ['procedure_type_id'];
				sqlStatement ( "UPDATE procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, procedure_type = ?, seq = ?, activity = 1 " . "WHERE procedure_type_id = ?", array (
						$groupid,
						$name,
						$lab_id,
						$ordercode,
						$stdcode,
						'ord',
						$pseq ++,
						$orderid 
				) );
			}
			
			/**
			 * NOT USED WITH INTERPATH
			 * // store detail records (one record per detail)
			 * if (trim($acsv[7])) { // preferred specimen
			 * sqlStatement("REPLACE INTO procedure_type SET " .
			 *
			 * "parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
			 * array($orderid, 'PREFERRED SPECIMEN', 'Preferred specimen collection method', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[7])), 'det', $dseq++, $orderid));
			 * }
			 *
			 * if (trim($acsv[6])) { // container
			 * sqlStatement("REPLACE INTO procedure_type SET " .
			 * "parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
			 * array($orderid, 'CONTAINER TYPE', 'Specimen container type', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[6])), 'det', $dseq++, $orderid));
			 * }
			 *
			 * if (trim($acsv[8])) { // container
			 * sqlStatement("REPLACE INTO procedure_type SET " .
			 * "parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
			 * array($orderid, 'SPECIMEN TRANSPORT', 'Method of specimen transport', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[8])), 'det', $dseq++, $orderid));
			 * }
			 *
			 * if (trim($acsv[12])) { // container
			 * sqlStatement("REPLACE INTO procedure_type SET " .
			 * "parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ",
			 * array($orderid, 'TESTING METHOD', 'Method of performing test', $lab_id, $ordercode, mysql_real_escape_string(trim($acsv[12])), 'det', $dseq++, $orderid));
			 * }
			 */
			
			// reset counters for new procedure
			$lastcode = $ordercode;
			$rseq = 1;
			$dseq = 100;
		}
		
		// store the results
		$resultcode = trim ( $acsv [2] );
		$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE " . "parent = ? AND procedure_code = ? AND procedure_type = 'res' " . "ORDER BY procedure_type_id DESC LIMIT 1", array (
				$orderid,
				$resultcode 
		) );
		
		$stdcode = '';
		if (trim ( $acsv [4] ) != '')
			$stdcode .= "LOINC:" . trim ( $acsv [4] );
		$name = mysql_real_escape_string ( trim ( $acsv [3] ) );
		$units = mysql_real_escape_string ( trim ( $acsv [10] ) );
		$range = mysql_real_escape_string ( trim ( $acsv [11] ) );
			
		if (empty ( $trow ['procedure_type_id'] )) {
			sqlStatement ( "INSERT INTO procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 ", array (
					$orderid,
					$name,
					$lab_id,
					$resultcode,
					$stdcode,
					$units,
					$range,
					$rseq ++,
					'res' 
			) );
		} else {
			sqlStatement ( "UPDATE procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 " . "WHERE procedure_type_id = ?", array (
					$groupid,
					$name,
					$lab_id,
					$resultcode,
					$stdcode,
					$units,
					$range,
					$rseq ++,
					'res',
					$trow ['procedure_type_id'] 
			) );
		}
	} // end file loop
	echo "</pre>\n";
}

else if ($form_action == 2) { // load questions
                              // Mark the vendor's current questions inactive.
	sqlStatement ( "UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?", array (
			$lab_id 
	) );
	
	// What should be uploaded is the "AOE Questions" spreadsheet provided
	// by CPL, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: Order Code
	// 1: Order Description
	// 2: Question Code
	// 3: Question
	// 4: Tips
	//
	
	echo "<pre style='font-size:10px'>";
	
	$seq = 1;
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if ($seq ++ < 2 || strtolower ( $acsv [0] ) == "order code")
			continue;
		
		$pcode = trim ( $acsv [0] );
		$qcode = trim ( $acsv [2] );
		$required = 1; // always required
		$options = ''; // NOT USED
		if (empty ( $pcode ) || empty ( $qcode ))
			continue;
			
			// Figure out field type.
		$fldtype = 'T'; // always text
		                // if (strpos($acsv[4], 'Drop') !== FALSE) $fldtype = 'S';
		                // else if (strpos($acsv[4], 'Multiselect') !== FALSE) $fldtype = 'S';
		
		$qrow = sqlQuery ( "SELECT * FROM procedure_questions WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array (
				$lab_id,
				$pcode,
				$qcode 
		) );
		
		// If this is the first option value and it's a multi-select list,
		// then prepend '+;' here to indicate that. CPL does not use those
		// but keep this note here for future reference.
		
		if (empty ( $qrow ['procedure_code'] )) {
			sqlStatement ( "INSERT INTO procedure_questions SET " . "lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " . "fldtype = ?, required = ?, options = ?, activity = 1", array (
					$lab_id,
					$pcode,
					$qcode,
					trim ( $acsv [3] ),
					$fldtype,
					$required,
					$options 
			) );
		} else {
			if ($qrow ['activity'] == '1' && $qrow ['options'] !== '' && $options !== '') {
				$options = $qrow ['options'] . ';' . $options;
			}
			sqlStatement ( "UPDATE procedure_questions SET " . "question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array (
					trim ( $acsv [3] ),
					$fldtype,
					$required,
					$options,
					$lab_id,
					$pcode,
					$qcode 
			) );
		}
	} // end while
	echo "</pre>\n";
} // end load questions

