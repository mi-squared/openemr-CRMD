<?php
// -----------------------------------------------------------------------------------------------------------------
// Vendor = BioReference Laboratories
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
	//  0: Unknown
	//  1: Test code
	//  2: Test name
	//  3: Test name (short)
	//  4: Test LOINC
	//  5: Req type
	//  6: Req type name
	//  7: Preferred specimen
	//  8: Alt specimen (remove N/A values)
	//  9: True/False ???
	// 10: STAT available 1/0
	// 11: Processing/turn-around
	// 12: Specimen collection
	// 13: Storage
	// 14: Result CPT
	// 15: Result Code
	// 16: Result full name
	// 17: Result name
	// 18: Result LOINC
	// 19: Profile flag 1/0
	// 20: Result methodology
	// 21: Result type N=numeric S=string
	// 22: Result Range (remove Not Defined values)
	// 23: Result Units
	// 24: Result CPT
	
	$lastcode = '';
	$pseq = 1;
	$rseq = 1;
	$dseq = 100;
	$groups = '';
	
	echo "<pre style='font-size:10px'>";
	
	while (! feof($fhcsv)) {
		$acsv = fgetcsv($fhcsv, 4096);
		
		$category = trim($acsv[6]);
		if (! $category || strtolower($category) == 'req description') {
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
		$ordercode = trim ($acsv [1]);
		if (count($ordercode) > 8) continue; // header
		
		if ($lastcode != $ordercode) { // new code (store only once)
			$stdcode = '';
			if (trim($acsv [14]) != '')
				$stdcode .= "CPT4:" . trim($acsv[14]);
			
			$trow = sqlQuery("SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = 'ord' ORDER BY procedure_type_id DESC", array(
					$groupid,
					$ordercode 
			));
			
			$name = mysql_real_escape_string (strtoupper(trim($acsv[2]))); // long name
			$short = mysql_real_escape_string (trim($acsv[3])); // short name
			$category = mysql_real_escape_string (trim($acsv[6]));
			$transport = mysql_real_escape_string (trim($acsv[13]));
				
			// display last profile
			$profile = mysql_real_escape_string (trim($acsv[19]));
			$profile = ($profile == '1')? 'pro' : 'ord';
			echo ($profile == 'pro')? "PROFILE: " : "TEST: "; 
			echo "$ordercode - $name ($category)\n";
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
			
			// store detail records (one record per detail)
			if (trim ( $acsv [12] )) { // preferred specimen
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$orderid,
						'PREFERRED SPECIMEN',
						'Preferred specimen collection method',
						$lab_id,
						$ordercode,
						mysql_real_escape_string ( trim ( $acsv [12] ) ),
						'det',
						$dseq ++,
						$orderid 
				) );
			}
			
			if (trim ( $acsv [7] )) { // container
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$orderid,
						'CONTAINER TYPE',
						'Specimen container type',
						$lab_id,
						$ordercode,
						mysql_real_escape_string ( trim ( $acsv [7] ) ),
						'det',
						$dseq ++,
						$orderid 
				) );
			}
			
			if (trim ( $acsv [13] )) { // transport
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$orderid,
						'SPECIMEN TRANSPORT',
						'Method of specimen transport',
						$lab_id,
						$ordercode,
						mysql_real_escape_string ( trim ( $acsv [13] ) ),
						'det',
						$dseq ++,
						$orderid 
				) );
			}
			
			if (trim ( $acsv [11] )) { // method
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$orderid,
						'TESTING METHOD',
						'Method of performing test',
						$lab_id,
						$ordercode,
						mysql_real_escape_string ( trim ( $acsv [11] ) ),
						'det',
						$dseq ++,
						$orderid 
				) );
			}
			
			if (trim ( $acsv [10] )) { // stat
				$stat = (trim ( $acsv [10] ) == '1')? 'TRUE' : 'FALSE';
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
		$resultcode = trim ( $acsv [18] );
		if (! $resultcode) $resultcode = trim ( $acsv [15] );
		$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = 'res' ORDER BY procedure_type_id DESC LIMIT 1", array (
				$orderid,
				$resultcode 
		) );
		
		$stdcode = '';
		if (trim ( $acsv [15] ) != '')
			$stdcode .= trim ( $acsv [15] );
		$name = mysql_real_escape_string ( strtoupper(trim ($acsv [17])));
		$units = mysql_real_escape_string ( trim ( $acsv [23] ) );
		$range = mysql_real_escape_string ( trim ( $acsv [22] ) );
		
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
	// 2: Question
	// 3: Question Code
	// 4: Text Answer (YES/NO)
	// 5: Required (YES/NO)
	// 6: List Answer (YES/NO)
	// 7: Response (just one; the row is duplicated for each possible value)
	//
	
	echo "<pre style='font-size:10px'>";
	
	$seq = 1;
	$lastcode = '';
	$lastques = '';
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if ($seq ++ < 2 || strtolower ( $acsv [0] ) == "testcode")
			continue;
		
		$pcode = trim ( $acsv [0] );
		$qcode = trim ( $acsv [3] );
		if (empty ( $pcode ) || empty ( $qcode )) continue;
		
		if ($lastcode != $pcode || $lastques != $qcode) { // new code/question (store only once)
			$required = (strtoupper(trim ($acsv[5])) == 'YES')? 1 : 0; 
			$fldtype = (strtoupper(trim ($acsv [6])) == 'YES')? 'L' : 'T';
			$options = ($fldtype == 'L')? $options = 'BioRef_'.$qcode : '';
			$answer = strtoupper( trim ( $acsv['7']));
			if ($answer == 'YES' || $answer == 'NO') $options = 'BioRef_YN';
						                               
			// display question
			echo "TEST: $pcode \tQUESTION: $qcode - $acsv[2]\n";
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
						trim ( $acsv [2] ),
						$fldtype,
						$required,
						$options 
				) );
			} else {
				sqlStatement ( "UPDATE procedure_questions SET question_text = ?, fldtype = ?, required = ?, options = ?, activity = 1 WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
						trim ( $acsv [2] ),
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
	sqlStatementNoLog ( "DELETE FROM list_options WHERE list_id LIKE 'BioRef%' OR option_id LIKE 'BioRef%' " );
	
	// What should be uploaded is the "AOE Options" spreadsheet provided
	// by BioRef, saved in "Text CSV" format from OpenOffice, using its
	// default settings. Values for each row are:
	// 0: Test Code
	// 1: Test Name
	// 2: Question
	// 3: Question Code
	// 4: Text Answer (YES/NO)
	// 5: Required (YES/NO)
	// 6: List Answer (YES/NO)
	// 7: Response (just one; the row is duplicated for each possible value)
	//
    
	$entries = array();
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv, 4096 );
		if (count ( $acsv ) < 7 || (strtolower($acsv[0]) == "testcode"))
			continue;
		$pcode = trim ( $acsv[0] );
		$qcode = trim ( $acsv[3] );
		if (empty ( $pcode ) || empty ( $qcode )) continue;
		if (strtoupper($acsv[6]) != 'YES') continue; // not a list
		
		// get values
		$answer = trim ( $acsv[7] );
		
		// array of answers within array of questions
		// purges duplicate questions and answers 
		$entries[$qcode][$answer] = $answer;
	}


	echo "<pre style='font-size:10px'>";
	
	// save default yes/no list
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, title = ?", array (
			'BioRef_YN',
			'Yes',
			'Yes',
	) );
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = ?, option_id = ?, title = ?", array (
			'BioRef_YN',
			'No',
			'No',
	) );
	
	// create the list
	sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = 'lists', option_id = ?, title = ?", array (
			'BioRef_YN',
			'BioRef YN',
	) );
	
	// save all of the entries
	if (is_array($entries)) foreach ($entries AS $key => $values) {
		if (empty($values)) continue;
		
		$list = 'BioRef_'.$key;
		$title = 'BioRef '.$key;
		
		if (is_array($values)) {
			if (count($values) == 2 && $values['Yes'] && $values['No']) continue;
			
			asort($values);
			$seq = 1;
			
			// create the list
			echo "\nLIST NAME: $list\t";
			sqlStatementNoLog ( "REPLACE INTO list_options SET list_id = 'lists', option_id = ?, title = ?", array (
					$list,
					$title,
			) );
			
			echo "LIST OPTION: ";
			foreach ($values AS $option => $title) {
				if (!$option) continue;
				if (!$title) $title = $option;
				echo "$title "; 
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
	die ("BIOREFERENCE DOES NOT REQUIRE PROFILE INFORMATION");
	echo "</pre>";
}
	

