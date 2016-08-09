<?php
// -----------------------------------------------------------------------------------------------------------------
// Vendor = Yosemite Pathology Medical Group
// -----------------------------------------------------------------------------------------------------------------
if ($form_action == 1) { // load compendium
                         // Mark all "ord" rows having the indicated parent as inactive.
	sqlStatement ( "UPDATE procedure_type SET activity = 0 WHERE " . "parent = ? AND procedure_type = 'ord'", array (
			$form_group 
	) );
	// What should be uploaded is the Order Compendium spreadsheet provided
	// by YPMG, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: Order code : mapped to procedure_code
	// 1: Order Name : mapped to name
	// 2: Result Code : ignored (will cause multiple occurrences of the same order code)
	// 3: Result Name : ignored
	//
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		$ordercode = trim ( $acsv [0] );
		if (count ( $acsv ) < 2 || $ordercode == "Order Code")
			continue;
		$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE " . "parent = ? AND procedure_code = ? AND procedure_type = 'ord' " . "ORDER BY procedure_type_id DESC LIMIT 1", array (
				$form_group,
				$ordercode 
		) );
		
		if (empty ( $trow ['procedure_type_id'] )) {
			sqlStatement ( "INSERT INTO procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " . "activity = 1", array (
					$form_group,
					trim ( $acsv [1] ),
					$lab_id,
					$ordercode,
					'ord' 
			) );
		} else {
			sqlStatement ( "UPDATE procedure_type SET " . "parent = ?, name = ?, lab_id = ?, procedure_code = ?, procedure_type = ?, " . "activity = 1 " . "WHERE procedure_type_id = ?", array (
					$form_group,
					trim ( $acsv [1] ),
					$lab_id,
					$ordercode,
					'ord',
					$trow ['procedure_type_id'] 
			) );
		}
	}
} 

else if ($form_action == 2) { // load questions
                              // Mark the vendor's current questions inactive.
	sqlStatement ( "UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?", array (
			$lab_id 
	) );
	
	// What should be uploaded is the "AOE Questions" spreadsheet provided
	// by YPMG, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: Order Code
	// 1: Question Code
	// 2: Question
	// 3: Is Required (always "false")
	// 4: Field Type ("Free Text", "Pre-Defined Text" or "Drop Down";
	// "Drop Down" was previously "Multiselect Pre-Defined Text" and
	// indicates that more than one choice is allowed)
	// 5: Response (just one; the row is duplicated for each possible value)
	//
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if (count ( $acsv ) < 5 || ($acsv [3] !== "false" && $acsv [3] !== "true"))
			continue;
		
		$pcode = trim ( $acsv [0] );
		$qcode = trim ( $acsv [1] );
		$required = strtolower ( substr ( $acsv [3], 0, 1 ) ) == 't' ? 1 : 0;
		$options = trim ( $acsv [5] );
		if (empty ( $pcode ) || empty ( $qcode ))
			continue;
			
			// Figure out field type.
		$fldtype = 'T';
		if (strpos ( $acsv [4], 'Drop' ) !== FALSE)
			$fldtype = 'S';
		else if (strpos ( $acsv [4], 'Multiselect' ) !== FALSE)
			$fldtype = 'S';
		
		$qrow = sqlQuery ( "SELECT * FROM procedure_questions WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array (
				$lab_id,
				$pcode,
				$qcode 
		) );
		
		// If this is the first option value and it's a multi-select list,
		// then prepend '+;' here to indicate that. YPMG does not use those
		// but keep this note here for future reference.
		
		if (empty ( $qrow ['procedure_code'] )) {
			sqlStatement ( "INSERT INTO procedure_questions SET " . "lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, " . "fldtype = ?, required = ?, options = ?, activity = 1", array (
					$lab_id,
					$pcode,
					$qcode,
					trim ( $acsv [2] ),
					$fldtype,
					$required,
					$options 
			) );
		} else {
			if ($qrow ['activity'] == '1' && $qrow ['options'] !== '' && $options !== '') {
				$options = $qrow ['options'] . ';' . $options;
			}
			sqlStatement ( "UPDATE procedure_questions SET " . "question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE " . "lab_id = ? AND procedure_code = ? AND question_code = ?", array (
					trim ( $acsv [2] ),
					$fldtype,
					$required,
					$options,
					$lab_id,
					$pcode,
					$qcode 
			) );
		}
	} // end while
} // end load questions

