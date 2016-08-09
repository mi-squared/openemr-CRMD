<?php
// -----------------------------------------------------------------------------------------------------------------
// Vendor = Shiel Laboratory
// -----------------------------------------------------------------------------------------------------------------
if ($form_action == 1) { // load compendium
    // Delete the detail records for this lab.
	sqlStatement ( "DELETE FROM procedure_type WHERE lab_id = ? AND (procedure_type = 'det' OR procedure_type = 'res') ", array (
			$lab_id 
	) );
	
	// Mark everything for the indicated lab as inactive.
	sqlStatement ( "UPDATE procedure_type SET activity = 0, seq = 999999 WHERE lab_id = ? AND procedure_type != 'grp' AND procedure_type != 'pro'", array (
			$lab_id 
	) );
	
	// Load category group ids
	$result = sqlStatement ( "SELECT procedure_type_id, name FROM procedure_type WHERE lab_id = ? AND parent = ? AND procedure_type = 'grp'", array (
			$lab_id,
			$form_group 
	) );
	$groups = array();
	while ( $record = sqlFetchArray ( $result ) )
		$groups[$record ['name']] = $record[procedure_type_id];
		
	// What should be uploaded is the Order Compendium spreadsheet provided
	// by CPL, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Sort table by Order Code!!!
	// Values for each row are:
	//  0: Test code
	//  1: SML code ???
	//  2: Test LOINC
	//  3: CPT4
	//  4: Test name 
	//  5: Department
	//  6: Container
	//  7: Specimen
	//  8: STAT available 1/0
	//  9: Order turn-around
	// 10: Result Code
	// 11: Result LOINC
	// 12: Result name
	// 13: Result STAT
	// 14: Result turn-around
	
	$lastcode = '';
	$pseq = 1;
	$rseq = 1;
	$dseq = 100;
	$groups = '';
	
	echo "<pre style='font-size:10px'>";
	
	while (! feof($fhcsv)) {
		$acsv = fgetcsv($fhcsv, 4096);
		
		$category = trim($acsv[5]);
		if (! $category || strtolower($category) == 'department') {
			$groupid = $form_group; // no category, store under root
		} else { // find or add category
			$groupid = $groups[$category];
			if (! $groupid) {
				$groupid = sqlInsert("INSERT INTO procedure_type SET procedure_type = 'grp', lab_id = ?, parent = ?, name = ?", array(
						$lab_id,
						$form_group,
						$category 
				));
				$groups[$category] = $groupid;
			}
		}
		
		// store the order
		$ordercode = trim ($acsv [0]);
		if (strtolower($ordercode) == 'internal code') continue; // header
		
		if ($lastcode != $ordercode) { // new code (store only once)
			$stdcode = '';
			if (trim($acsv [3]) != '')
				$stdcode .= "CPT4:" . trim($acsv[14]);
			
			$trow = sqlQuery("SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = 'ord' ORDER BY procedure_type_id DESC", array(
					$groupid,
					$ordercode 
			));
			
			$name = mysql_real_escape_string (strtoupper(trim($acsv[4]))); // long name
			$category = mysql_real_escape_string (trim($acsv[5]));
			$specimen = mysql_real_escape_string (trim($acsv[7]));
			$notes = '';
			$transport = '';
			$profile = 'ord';
			echo "TEST: "; 
			echo "$ordercode - $name ($category)\n";
			flush ();
			
			if (empty ( $trow ['procedure_type_id'] )) {
				$orderid = sqlInsert ( "INSERT INTO procedure_type SET parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, standard_code = ?, transport = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1", array (
						$groupid,
						$name,
						$specimen,
						$lab_id,
						$ordercode,
						$stdcode,
						$transport,
						$notes,
						$profile,
						$pseq ++ 
				) );
			} else {
				$orderid = $trow ['procedure_type_id'];
				sqlStatement ( "UPDATE procedure_type SET parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, standard_code = ?, transport = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 WHERE procedure_type_id = ?", array (
						$groupid,
						$name,
						$specimen,
						$lab_id,
						$ordercode,
						$stdcode,
						$transport,
						$notes,
						$profile,
						$pseq ++,
						$orderid 
				) );
			}
			
			// store detail records (one record per detail)
			if (trim ( $acsv [6] )) { // container
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$orderid,
						'CONTAINER TYPE',
						'Specimen container type',
						$lab_id,
						$ordercode,
						mysql_real_escape_string ( trim ( $acsv [6] ) ),
						'det',
						$dseq ++,
						$orderid 
				) );
			}
			
			if (trim ( $acsv [9] )) { // turn-around
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$orderid,
						'TURN-AROUND',
						'Standard turn-around time',
						$lab_id,
						$ordercode,
						mysql_real_escape_string ( trim ( $acsv [9] ) ),
						'det',
						$dseq ++,
						$orderid 
				) );
			}
			
			if (trim ( $acsv [8] )) { // stat
				$stat = (trim ( $acsv [8] ) == '1')? 'TRUE' : 'FALSE';
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$orderid,
						'STAT AVAILABLE',
						'STAT processing available',
						$lab_id,
						$ordercode,
						$stat,
						'det',
						$dseq ++,
						$orderid 
				) );
			}
			
			// reset counters for new procedure
			$lastcode = $ordercode;
			$rseq = 1;
			$dseq = 100;
		}
		
		// store the results
		$resultcode = trim ( $acsv [11] ); // loinc
		if (! $resultcode) $resultcode = trim ( $acsv [10] );  // lab internal
		
		if (! $resultcode) continue; // no result to store
		
		$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = 'res' ORDER BY procedure_type_id DESC LIMIT 1", array (
				$orderid,
				$resultcode 
		) );
		
		$units = '';
		$range = '';
		$stdcode = '';
		if (trim ( $acsv [10] ) != '')
			$stdcode .= trim ( $acsv [10] );
		$name = mysql_real_escape_string ( strtoupper(trim ($acsv [12])));
		
		echo "RESULT: $resultcode, $name [$stdcode]\n";
		flush ();
		
		if (empty ( $trow ['procedure_type_id'] )) {
			sqlStatement ( "INSERT INTO procedure_type SET parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 ", array (
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
			sqlStatement ( "UPDATE procedure_type SET parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, `range` = ?, seq = ?, procedure_type = ?, activity = 1 WHERE procedure_type_id = ?", array (
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
	// by BioRef, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: Category (not used)
	// 1: Test Code
	// 2: SML Code
	// 3: Question Code
	// 4: Test Name
	// 5: Question Text
	// 6: Inactive (FALSE/TRUE)
	// 7: Required (Y)
	// 8: Data Type (Text/Option/Date)
	// 9: Response (just one; the row is duplicated for each possible value)
	//
	
	echo "<pre style='font-size:10px'>";
	
	$seq = 1;
	$lastcode = '';
	$lastques = '';
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if ($seq ++ < 2 || strtolower ( $acsv [1] ) == "order code")
			continue;
		
		$pcode = trim ( $acsv [1] );
		$qcode = trim ( $acsv [3] );
		if (empty ( $pcode ) || empty ( $qcode )) continue;
		
		if ($lastcode != $pcode || $lastques != $qcode) { // new code/question (store only once)
			$required = (strtoupper(trim ($acsv[7])) == 'Y')? 1 : 0; 
			$fldtype = 'T'; // default TEXT
			if (trim ($acsv [6])) {
				$fldtype = strtoupper(trim ($acsv [6]));
				$fldtype = ($fldtype == 'OPTION')? 'L' : 'D';
			}

			$options = ($fldtype == 'L')? $options = 'Shiel_'.$qcode : '';
			$answer = str_replace(' ', '', strtoupper( trim ( $acsv[9] ) ) );
			if ($answer == 'YES:NO') $options = 'Shiel_YN';
						                               
			$question = mysql_real_escape_string ( trim( $acsv[5] ) );
			
			// display question
			echo "TEST: $pcode \tQUESTION: $qcode - $question\n";
			flush ();
		
			$qrow = sqlQuery ( "SELECT * FROM procedure_questions WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
					$lab_id,
					$pcode,
					$qcode 
			) );
		
			if (empty ( $qrow ['procedure_code'] )) {
				sqlStatement ( "INSERT INTO procedure_questions SET lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1", array (
						$lab_id,
						$pcode,
						$qcode,
						$question,
						$fldtype,
						$required,
						$options 
				) );
			} else {
				sqlStatement ( "UPDATE procedure_questions SET question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
						$question,
						$fldtype,
						$required,
						$options,
						$lab_id,
						$pcode,
						$qcode 
				) );
			}
		} // end if lastcode
		$lastcode = $pcode;
		$lastques = $qcode;

	} // end while
	echo "</pre>\n";
} // end load questions

else if ($form_action == 3) { // load question options
    // Remove vendors current options
	sqlStatementNoLog ( "DELETE FROM list_options WHERE list_id LIKE 'Shiel%' OR option_id LIKE 'Shiel%' " );
	
	// What should be uploaded is the "AOE Options" spreadsheet provided
	// by BioRef, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: Category (not used)
	// 1: Test Code
	// 2: SML Code
	// 3: Question Code
	// 4: Test Name
	// 5: Question Text
	// 6: Inactive (FALSE/TRUE)
	// 7: Required (Y)
	// 8: Data Type (Text/Option/Date)
	// 9: Response (just one; the row is duplicated for each possible value)
		//
    
	$entries = array();
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if (strtolower($acsv[1]) == "order code") continue;
		
		$pcode = trim ( $acsv[1] );
		$qcode = trim ( $acsv[3] );
		if (empty ( $pcode ) || empty ( $qcode )) continue;
		if (strtoupper($acsv[8]) != 'OPTION') continue; // not a list
		
		// get values
		$answers = str_replace(' ','',trim($acsv[9]));
		if ($answers == 'Yes:No') continue;
		
		$options = explode( ':', $answers );
		
		// array of answers within array of questions
		// purges duplicate questions and answers 
		$entries[$qcode] = $options;
	}


	echo "<pre style='font-size:10px'>";
	
	// save default yes/no list
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, title = ?", array (
			'Shiel_YN',
			'Yes',
			'Yes',
	) );
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, title = ?", array (
			'Shiel_YN',
			'No',
			'No',
	) );
	
	// create the list
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = 'lists', option_id = ?, title = ?", array (
			'Shiel_YN',
			'Shiel YN',
	) );
	
	// save all of the entries
	if (is_array($entries)) foreach ($entries AS $key => $values) {
		if (empty($values)) continue;
		
		$list = 'Shiel_'.$key;
		$title = 'Shiel '.$key;
		
		if (is_array($values)) {
			asort($values);
			$seq = 1;
			
			// create the list
			echo "\nLIST NAME: $list\t";
			sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = 'lists', option_id = ?, title = ?", array (
					$list,
					$title,
			) );
			
			echo "LIST OPTION: ";
			foreach ($values AS $option) {
				if (!$option) continue;
				echo "$option "; 
				sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, seq = ?, title = ?", array (
					$list,
					$option,
					$seq++,
					$option,
				) );
			}
		}
	} // end while

	echo "</pre>";
} // end load questions

				
else if ($form_action == 4) { // load profiles
	die ("SHIEL LABS DOES NOT REQUIRE PROFILE INFORMATION");
	echo "</pre>";
}
	

