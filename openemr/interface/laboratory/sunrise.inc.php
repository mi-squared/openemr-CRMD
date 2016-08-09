<?php
// -----------------------------------------------------------------------------------------------------------------
// Vendor = Sunrise Laboratories
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
	//  1: Test name
	//  2: Test cpt4
	//  3: Result Code
	//  4: Result name
	//  5: Result LOINC
	
	$lastcode = '';
	$pseq = 1;
	$rseq = 1;
	$dseq = 100;
	$groups = '';
	
	echo "<pre style='font-size:10px'>";
	
	while (! feof($fhcsv)) {
		$acsv = fgetcsv($fhcsv, 4096);
		
		$groupid = $form_group; // no category, store under root

		// store the order
		$ordercode = trim ($acsv [0]);
		if (strtolower($ordercode) == 'ordercode') continue; // header
		
		if ($lastcode != $ordercode) { // new code (store only once)
			$stdcode = '';
			if (trim($acsv [2]) != '')
				$stdcode .= "CPT4:" . trim($acsv[2]);
			
			$trow = sqlQuery("SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = 'ord' ORDER BY procedure_type_id DESC", array(
					$groupid,
					$ordercode 
			));
			
			$name = mysql_real_escape_string (strtoupper(trim($acsv[1]))); // long name
			$transport = 'Standard';
			$notes = '';
			$category = '';
				
			// display last profile
			$profile = 'ord';
			echo "TEST: "; 
			echo "$ordercode - $name\n";
			flush ();
			
			if (empty ( $trow ['procedure_type_id'] )) {
				$orderid = sqlInsert ( "INSERT INTO procedure_type SET parent = ?, name = ?, specimen = ?, lab_id = ?, procedure_code = ?, standard_code = ?, transport = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1", array (
						$groupid,
						$name,
						$category,
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
						$category,
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
			
			// reset counters for new procedure
			$lastcode = $ordercode;
			$rseq = 1;
			$dseq = 100;
		}
		
		// store the results
		$resultcode = trim ( $acsv [5] ); // result loinc
		if (! $resultcode) $resultcode = trim ( $acsv [3] );
		$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = 'res' ORDER BY procedure_type_id DESC LIMIT 1", array (
				$orderid,
				$resultcode 
		) );
		
		$stdcode = '';
		if (trim ( $acsv [3] ) != '')
			$stdcode .= trim ( $acsv [3] );
		$name = mysql_real_escape_string ( strtoupper(trim ($acsv [4])));
		$units = '';
		$range = '';
		
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
	// 0: Test Code
	// 1: Test Name
	// 2: Question Code
	// 3: Question Text
	// 4: Type (TEXT/NUMBER/MC)
	// 5: Length
	// 7: Responses (multiple | delimited)
	//
	
	echo "<pre style='font-size:10px'>";
	
	$seq = 1;
	$lastcode = '';
	$lastques = '';
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if ($seq ++ < 2 || strtolower ( $acsv [0] ) == "oc")
			continue;
		
		$pcode = trim ( $acsv [0] );
		$qcode = trim ( $acsv [2] );
		if (empty ( $pcode ) || empty ( $qcode )) continue;
		
		if ($lastcode != $pcode || $lastques != $qcode) { // new code/question (store only once)
			$fldtype = 'T'; // default TEXT
			if (trim ($acsv [4])) {
				$fldtype = strtoupper(trim ($acsv [4]));
				$fldtype = ($fldtype == 'MC')? 'L' : 'N';
			}

			$options = ($fldtype == 'L')? $options = 'Sunrise_'.$qcode : '';
			$answer = str_replace(' ', '', strtoupper( trim ( $acsv[6] ) ) );
			if ($answer == 'NO[N]|YES[Y]') $options = 'Sunrise_YN';
						                               
			$question = mysql_real_escape_string ( trim( $acsv[3] ) );
			$length = intval( trim( $acsv[5] ));
			
			// display question
			echo "TEST: $pcode \tQUESTION: $qcode - $question\n";
			flush ();
		
			$qrow = sqlQuery ( "SELECT * FROM procedure_questions WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
					$lab_id,
					$pcode,
					$qcode 
			) );
		
			if (empty ( $qrow ['procedure_code'] )) {
				sqlStatement ( "INSERT INTO procedure_questions SET lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, fldtype = ?, maxsize = ?, options = ?, activity = 1", array (
						$lab_id,
						$pcode,
						$qcode,
						$question,
						$fldtype,
						$length,
						$options 
				) );
			} else {
				sqlStatement ( "UPDATE procedure_questions SET question_text = ?, fldtype = ?, maxsize = ?, options = ?, activity = 1 WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
						$question,
						$fldtype,
						$length,
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
	sqlStatementNoLog ( "DELETE FROM list_options WHERE list_id LIKE 'Sunrise%' OR option_id LIKE 'Sunrise%' " );
	
	// What should be uploaded is the "AOE Options" spreadsheet provided
	// by BioRef, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: Test Code
	// 1: Test Name
	// 2: Question Code
	// 3: Question Text
	// 4: Type (TEXT/NUMBER/MC)
	// 5: Length
	// 7: Responses (multiple | delimited)
	//
    
	$entries = array();
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if (strtolower($acsv[0]) == "oc") continue;
		
		$pcode = trim ( $acsv[0] );
		$qcode = trim ( $acsv[2] );
		if (empty ( $pcode ) || empty ( $qcode )) continue;
		if (strtoupper($acsv[4]) != 'MC') continue; // not a list
		
		// get values
		$answers = str_replace(':','',trim($acsv[6]));
		if ($answers == 'No [N]|Yes [Y]') continue;
		
		$options = explode( '|', $answers );
		
		// array of answers within array of questions
		// purges duplicate questions and answers 
		$entries[$qcode] = $options;
	}


	echo "<pre style='font-size:10px'>";
	
	// save default yes/no list
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, title = ?", array (
			'Sunrise_YN',
			'Yes',
			'Yes',
	) );
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, title = ?", array (
			'Sunrise_YN',
			'No',
			'No',
	) );
	
	// create the list
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = 'lists', option_id = ?, title = ?", array (
			'Sunrise_YN',
			'Sunrise YN',
	) );
	
	// save all of the entries
	if (is_array($entries)) foreach ($entries AS $key => $values) {
		
		$list = 'Sunrise_'.$key;
		$title = 'Sunrise '.$key;
		
		if ($values) {
			$seq = 1;
			
			// create the list
			echo "\nLIST NAME: $list\t";
			sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = 'lists', option_id = ?, title = ?", array (
					$list,
					$title,
			) );
			
			echo "LIST OPTION: ";
			foreach ($values AS $value) {
				$elements = array();
				preg_match("/(.*)\[(.*)\]/",$value,$elements);
				$option = str_replace(' ', '', $elements[2]);
				$title = mysql_real_escape_string($elements[1]);
				if (!$title) continue; // must have a value
				if (!$option) $option = $title;
				
				echo "$option - $title; "; 
				sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, seq = ?, title = ?", array (
					$list,
					$option,
					$seq++,
					$title,
				) );
			}
		}
	} // end while

	echo "</pre>";
} // end load questions
	
				
else if ($form_action == 4) { // load profiles
	die ("SUNRISE DOES NOT REQUIRE PROFILE INFORMATION");
	echo "</pre>";
}
	

