<?php
// -----------------------------------------------------------------------------------------------------------------
// Vendor = Cerner-Tuality Laboratory
// -----------------------------------------------------------------------------------------------------------------
if ($form_action == 1) { // load compendium
    // Delete the detail records for this lab.
	sqlStatement ( "DELETE FROM procedure_type WHERE " . "lab_id = ? AND (procedure_type = 'det' OR procedure_type = 'res') ", array (
			$lab_id 
	) );
	
    // Mark everything for the indicated lab as inactive.
	sqlStatement ( "UPDATE procedure_type SET activity = 0, seq = 999999 WHERE " . "lab_id = ? AND procedure_type != 'fav' AND procedure_type != 'grp' AND procedure_type != 'pro'", array (
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
		$category = ''; // NOT USED WITH CERNER
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
		if (count ($ordercode) > 10 || strtolower ( $ordercode ) == "order code cs 200")
			continue;
		
		if ($lastcode != $ordercode) { // new code (store only once)
			$stdcode = '';

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
			 * NOT USED WITH CERNER
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
			
			echo "TEST: $lastcode - $name \n";
			flush ();
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
		$units = '';
		$range = '';
			
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
		echo "RESULT: $resultcode - $name \n";
		flush ();
	} // end file loop
	echo "</pre>\n";
} // end load codes


