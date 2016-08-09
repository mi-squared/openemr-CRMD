<?php 
require_once("{$GLOBALS['srcdir']}/../custom/code_types.inc.php");

function ReportMissingPID()
{
	echo "<h><b>The Session Patient ID Seems To Be Missing!</b></h><br/>\n";
	echo "<b>This is usually due to an idle session timing out, please log out and back in to reset session variables</b><br/>\n";
	exit;
}

function ReportMissingEncounter()
{
	echo "<h><b>The Session Encounter ID Seems To Be Missing!</b></h><br/>\n";
	echo "<b>This is usually due to an idle session timing out, please log out and back in to reset session variables</b><br/>\n";
	exit;
}

function ReportMissingID()
{
	echo "<h><b>The Form ID Seems To Be Missing!</b></h><br/>\n";
	echo "<b>This is usually due to an idle session timing out, please log out and back in to reset session variables</b><br/>\n";
	exit;
}

function FlagListSel($thisField, $thisList, $exists, $default) {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
                   "list_id = ? ORDER BY seq", array($thisList));
  echo "<option value=''";
  if(!$thisField && $exists) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['option_id'] . "'";
    if(($thisField == $rrow['option_id']) || (!$exists && ($rrow['option_id'] == $default))) echo " selected='selected'";
    echo ">" . $rrow['title'];
    echo "</option>";
  }
}

function DrugUseListSel($thisField) {
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  echo "<option value='neverrecreational_drugs'";
  if($thisField == 'neverrecreational_drugs') echo " selected='selected'";
  echo ">Never</option>\n";
  echo "<option value='currentrecreational_drugs'";
  if($thisField == 'currentrecreational_drugs') echo " selected='selected'";
  echo ">Current</option>\n";
  echo "<option value='quitrecreational_drugs'";
  if($thisField == 'quitrecreational_drugs') echo " selected='selected'";
  echo ">Quit</option>\n";
  echo "<option value='not_applicablerecreational_drugs'";
  if($thisField == 'not_applicablerecreational_drugs') echo " selected='selected'";
  echo ">N/A</option>\n";
}

function AlcoholUseListSel($thisField) {
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  echo "<option value='neveralcohol'";
  if($thisField == 'neveralcohol') echo " selected='selected'";
  echo ">Never</option>\n";
  echo "<option value='currentalcohol'";
  if($thisField == 'currentalcohol') echo " selected='selected'";
  echo ">Current</option>\n";
  echo "<option value='quitalcohol'";
  if($thisField == 'quitalcohol') echo " selected='selected'";
  echo ">Quit</option>\n";
  echo "<option value='not_applicablealcohol'";
  if($thisField == 'not_applicablealcohol') echo " selected='selected'";
  echo ">N/A</option>\n";
}

function ListSel($thisField, $thisList) {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
                    "list_id = ? ORDER BY seq, title", array($thisList));
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['option_id'] . "'";
    if($thisField == $rrow['option_id']) echo " selected='selected'";
    if(!$thisField && $rrow['default']) echo " selected='selected'";
    echo ">" . $rrow['title'];
    echo "</option>";
  }
}

function ListSelString($thisField, $thisList) {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
                    "list_id = ? ORDER BY seq, title", array($thisList));
  $ret = "<option value=''";
  if(!$thisField) $ret .= " selected='selected'";
  $ret .= ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    $ret .= "<option value='" . $rrow['option_id'] . "'";
    if($thisField == $rrow['option_id']) $ret .= " selected='selected'";
    $ret .= ">". $rrow['title']."</option>";
  }
	return $ret;
}

function ListSelAlpha($thisField, $thisList) {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
                    "list_id = ? ORDER BY title, seq", array($thisList));
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['option_id'] . "'";
    if($thisField == $rrow['option_id']) echo " selected='selected'";
    echo ">" . $rrow['title'];
    echo "</option>";
  }
}

function MultListSel($thisArray, $thisList, $thisVal='') {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
                    "list_id =? ORDER BY seq",array($thisList));
  echo "<option value=''";
  // if(!$thisArray[0]) echo " selected='selected'";
	if(count($thisArray) == 0) { 
		echo " selected='selected'"; 
	} elseif ($thisArray[0] == '') {
		echo " selected='selected'"; 
	}
  echo ">Unassigned</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['option_id'] . "'";
    if(is_array($thisArray)) {
      if(in_array($rrow['option_id'],$thisArray)) echo " selected='selected'";
    }
    echo ">" . $rrow['title'];
    echo "</option>";
  }
}

function MultLook($thisArray, $thisList, $thisVal='') {
  $dispVal='';
  if((!($thisArray[0])) || (!is_array($thisArray))) {
    echo "<option value='' selected='selected'>Unassigned</option>";
    return;
  }
  foreach ($thisArray as $rrow) {
    $lres = sqlStatement("SELECT * FROM list_options WHERE " .
         "list_id =? AND option_id =?",array($thisList, $rrow));
    $llist = sqlFetchArray($lres);
    echo "<option value='".$rrow."' selected='selected'>".$llist['title']."</option>";
  }
}

function MultPrint($thisArray, $thisList, $thisVal='') {
  $dispVal='';
  if(!is_array($thisArray)) { return $dispVal; }
  if(!($thisArray[0])) { return $dispVal; }
  foreach ($thisArray as $rrow) {
    $lres = sqlStatement("SELECT * FROM list_options WHERE " .
         "list_id =? AND option_id =?",array($thisList, $rrow));
    $llist = sqlFetchArray($lres);
    if($disp_val) { $dispVal=$dispVal.'<br />'; }
    $dispVal=$dispVal.$llist['title'];
  }
  return $dispVal;
}

function ListLook($thisData, $thisList) {
  if($thisList == 'occurrence') {
    if(!$thisData || $thisData == '') return 'Unknown or N/A'; 
  }
  if($thisData == '') return ''; 
  $fres=sqlStatement("SELECT * FROM list_options WHERE list_id=? ".
        "AND option_id=?", array($thisList, $thisData));
  if($fres) {
    $rret=sqlFetchArray($fres);
    $dispValue= $rret{'title'};
    if($thisList == 'occurrence' && $dispValue == '') {
      $dispValue = 'Unknown or N/A';
    }
  } else {
    $dispValue= '* Not Found *';
  }
  return $dispValue;
}

function SetSmokingDesc($thisValue) {
  $retval= '0';
  if(!$thisValue) $retval='0';
  if($thisValue == 1) $retval='currenttobacco';
  if($thisValue == 2) $retval='currenttobacco';
  if($thisValue == 3) $retval='quittobacco';
  if($thisValue == 4) $retval='nevertobacco';
  if($thisValue == 5) $retval='not_applicabletobacco';
  if($thisValue == 9) $retval='not_applicabletobacco';
  return $retval;
}

function getPastPregnancies($thisPid, $thisEnc='') {
  $sql= "SELECT * FROM form_whc_pp WHERE pid=? ORDER BY pp_date_of_pregnancy";
	$parms=array($thisPid);
	$all=array();
	if($thisEnc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, form_whc_pp.* FROM form_wmt_ll ".
			"LEFT JOIN form_whc_pp on form_wmt_ll.list_id=form_whc_pp.id WHERE ".
			"form_wmt_ll.pid=? AND form_wmt_ll.list_type=? AND ".
			"form_wmt_ll.encounter_id=? ORDER BY pp_date_of_pregnancy";
		$parms=array($thisPid, 'past_pregnancy', $thisEnc);
	}
  $res= sqlStatement($sql, $parms);
  for($iter=0; $row=sqlFetchArray($res); $iter++)
  $all[$iter]= $row;
  return $all;
}

function getNextOrder($thisPid, $cols='*') {
  $retVal=0;
  $sql= "SELECT $cols FROM form_whc_pp WHERE pid='$thisPid' ORDER BY pp_order DESC LIMIT 1";
  $result=sqlStatement($sql);
  if($result) {
    $row=sqlFetchArray($result);
    if($row{'pp_order'}) $retVal=$row{'pp_order'};
  }
  $retVal++;
  return($retVal);
}

function GetList($thisPid, $type, $enc='', $dt='') {
  $sql = "SELECT * FROM lists WHERE pid=? AND type=? ORDER BY begdate";
	if($type == 'allergy') {
  	$sql = "SELECT * FROM lists WHERE pid=? AND type=? " .
         "AND (enddate IS NULL || enddate='0000-00-00') ORDER BY begdate";
	}
	$parms=array($thisPid, $type);
	if($type == 'ultrasound' && $dt != '') {
  	$sql = "SELECT * FROM lists WHERE pid=? AND type=? " .
         "AND begdate > ? ORDER BY begdate";
		$parms=array($thisPid, $type, $dt);
	}
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, lists.* FROM form_wmt_ll LEFT JOIN ".
				"lists on form_wmt_ll.list_id=lists.id WHERE form_wmt_ll.pid=? ".
				"AND form_wmt_ll.list_type=? AND form_wmt_ll.encounter_id=? ".
				"ORDER BY begdate";
		$parms=array($thisPid, $type, $enc);
	}

	$all=array();
  $res = sqlStatement($sql,$parms);
	$iter=0;
  while($row = sqlFetchArray($res)) {
  	$all[$iter] = $row;
		$sql = "SELECT COUNT(*) FROM form_wmt_ll WHERE form_wmt_ll.list_type=? ".
				"AND form_wmt_ll.list_id=?";
		$chk = sqlStatement($sql, array($type, $all[$iter]['id']));
		$num = sqlFetchArray($chk);
		$all[$iter]['num_links']= $num['COUNT(*)'];
		$iter++;
	}
  return $all;
}

function GetCodeDescription($type='ICD9', $code) {
	$ret = "Invalid Key [$type] - No Description Found";
	$sql = "SELECT ct_id FROM code_types WHERE ct_key='$type'";
  $res = sqlStatement($sql);
	$row = sqlFetchArray($res);
	$key = $row{'ct_id'};
	if($key == '' || !$key) { return $ret; }
	$sql = "SELECT code_text, code_text_short FROM codes WHERE ".
			"code_type='$key' AND code='$code'";
  $res = sqlStatement($sql);
	$row = sqlFetchArray($res);
	$ret = trim($row{'code_text'});
	return $ret;
}

function GetProblemsWithDiags($thisPid,$mode='all',$thisID=0,$type='ICD') {
	// echo "In the Function, the encounter is :",$thisID,"<br/>\n";
	// $sql = "SELECT id, title, begdate, enddate, occurrence, diagnosis, ".
		// "comments, outcome from lists WHERE type='medical_problem' AND ".
		// "pid='$thisPid' AND (enddate='' OR enddate IS NULL) ".
		// "AND diagnosis LIKE 'ICD%' ORDER BY begdate";
	// if($mode=='all') {
		// $sql = "SELECT id, title, begdate, enddate, occurrence, diagnosis, ".
			// "comments, outcome from lists WHERE type='medical_problem' ".
			// "AND pid='$thisPid' AND diagnosis LIKE '".$type."%' ORDER BY begdate";
	// }
	// if($mode=='encounter') {
		// $sql = "SELECT id, title, begdate, enddate, occurrence, diagnosis, ".
			// "comments, outcome, resolved from issue_encounter, lists WHERE ".
			// "issue_encounter.list_id=lists.id AND type='medical_problem' AND ".
			// "issue_encounter.pid='$thisPid' AND issue_encounter.encounter='$thisID' ".
			// "AND diagnosis LIKE 'ICD%' ORDER BY begdate";
	// }
	// Select all here, set a flag in the row so we know if it's linked or not
	$sql = "SELECT id, title, begdate, enddate, occurrence, diagnosis, ".
			"comments, outcome, list_id, encounter from lists LEFT JOIN ".
			"issue_encounter ON list_id=id AND encounter=? WHERE ".
			"type='medical_problem' AND lists.pid=? AND diagnosis LIKE ".
			"? ORDER BY begdate";
	$all=array();
	// echo "This Statement: ",$sql,"<br/>\n";
  $res = sqlStatement($sql,array($thisID, $thisPid, $type.'%'));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function GetListbyTitle($thisPid, $type, $title) {
	$title='%'.$title.'%';
  $sql = "SELECT * FROM lists WHERE pid=? AND type=? " .
         "AND title LIKE ? ORDER BY begdate";
	$all=array();
  $res = sqlStatement($sql,array($thisPid, $type, $title));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function GetFYI($thisPid) {
  $sql = "SELECT fyi FROM form_fyi WHERE pid=?";
  $res = sqlStatement($sql,array($thisPid));
  $rrow = sqlFetchArray($res);
  return($rrow['fyi']);
}

function UpdateFYI($thisPid, $fyi) {
  $sql = "INSERT INTO form_fyi (date, pid, user, groupname, authorized, ".
        "activity, fyi) VALUES (NOW(), ?, ?, ?, ?, '1', ?) ".
				"ON DUPLICATE KEY UPDATE fyi=?, date=NOW(), user=?, authorized=?";
	$parms=array($thisPid, $_SESSION['authUser'], $_SESSION['authProvider'],
			$_SESSION['userauthorized'], $fyi, $fyi, $_SESSION['authUser'], 
			$_SESSION['userauthorized']);
  sqlInsert($sql, $parms);
  return(true);
}

function DiagSel($thisField) {
  $rlist= sqlStatement("SELECT * FROM codes WHERE code_type='2' ".
           "AND active='1' ORDER BY code");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['code']. "'";
    if($thisField == $rrow['code']) echo " selected='selected'";
    echo ">" . $rrow['code'] . " - " . $rrow['code_text'];
    echo "</option>";
  }
}

function ICD9DiagSel($thisField) {
  $rlist= sqlStatement("SELECT * FROM codes WHERE code_type='2' ".
           "AND active='1' ORDER BY code");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='ICD9: " . $rrow['code']. "'";
    if($thisField == $rrow['code']) echo " selected='selected'";
    echo '>' . $rrow['code'] . ' - ' . $rrow['code_text'];
    echo '</option>';
  }
}

function DiagLook($thisData) {
  if(!$thisData || $thisData == '') {
    return ''; 
  }
  $fres= sqlStatement("SELECT * FROM codes WHERE code_type='2' ".
           "AND active='1' AND code='".$thisData."'");
  $rret=sqlFetchArray($fres);
  $dispValue= $rret{'code'}.'-'.$rret{'code_text'};
  return $dispValue;
}

function DiagDescLook($thisData) {
  if(!$thisData || $thisData == '') {
    return ''; 
  }
  $fres= sqlStatement("SELECT code_text FROM codes WHERE code_type='2' ".
           "AND active='1' AND code='".$thisData."'");
  $rret=sqlFetchArray($fres);
  $dispValue= $rret{'code_text'};
  return $dispValue;
}

function UserSelect($thisField) {
  $rlist= sqlStatement("SELECT * FROM users WHERE " .
           "active=1 AND username != '' ORDER BY lname");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['username'] . "'";
    if($thisField == $rrow['username']) echo " selected='selected'";
    echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
    echo "</option>";
  }
}

function DoctorSelect($thisField) {
  $rlist= sqlStatement("SELECT * FROM users WHERE " .
           "active=1 AND username != '' ORDER BY lname");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['username'] . "'";
    if($thisField == $rrow['username']) echo " selected='selected'";
    echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
    echo "</option>";
  }
}

function ProviderSelect($thisField) {
  $rlist= sqlStatement("SELECT * FROM users WHERE " .
          "active=1 AND username != '' AND ".
					"(specialty LIKE 'Provider' || specialty LIKE 'Supervisor') ".
					"ORDER BY lname");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">-- select --</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['id'] . "'";
    if($thisField == $rrow['id']) echo " selected='selected'";
    echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
    echo "</option>";
  }
}

function SupervisorSelect($thisField) {
  $rlist= sqlStatement("SELECT * FROM users WHERE active=1 AND ".
			"username != '' AND specialty LIKE 'Supervisor' ORDER BY lname");
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">-- select --</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['id'] . "'";
    if($thisField == $rrow['id']) echo " selected='selected'";
    echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
    echo "</option>";
  }
}

function UserNameFromID($thisField) {
  if(!$thisField) return '';
  $ret = '';
  $rlist= sqlStatement("SELECT * FROM users WHERE id=?",array($thisField));
  $rrow= sqlFetchArray($rlist);
  if($rrow) {
    $ret = $rrow{'lname'}.', '.$rrow{'fname'}.' '.$rrow{'mname'};
  }
  return $ret;
}

function UserDispNameFromID($thisField) {
  if(!$thisField) return '';
  $ret = '';
  $rlist= sqlStatement("SELECT * FROM users WHERE id=?", array($thisField));
  $rrow= sqlFetchArray($rlist);
  if($rrow) {
		$_mi = ' ';
		if(!empty($rrow{'mname'})) { $_mi = ' '.$rrow{'mname'}.' '; }
    $ret = $rrow{'fname'}.$_mi.$rrow{'lname'};
  }
  return $ret;
}

function UserIdLook($thisField) {
	if(!$thisField) return '';
	$ret = '';
	$rlist= sqlStatement("SELECT * FROM users WHERE id='" .
			$thisField."'");
	$rrow= sqlFetchArray($rlist);
	if($rrow) {
		$ret = $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
	}
	return $ret;
}


function UserLook($thisField) {
  if(!$thisField) return '';
  $ret = '';
  $rlist= sqlStatement("SELECT * FROM users WHERE username=?", array($thisField));
  $rrow= sqlFetchArray($rlist);
  if($rrow) {
    $ret = $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
  }
  return $ret;
}

function FacilitySelect($thisField) {
  $flist= sqlStatement("SELECT id, name FROM facility WHERE " .
          "service_location != 0 ORDER BY name");
  while ($frow= sqlFetchArray($flist)) {
    echo "<option value='" . $frow['id'] . "'";
    if($thisField == $frow['id']) echo " selected='selected'";
    echo ">" . $frow['name'];
    echo "</option>\n";
  }
}

function FacilityLook($thisField) {
  if(!$thisField) return '';
  $flist= sqlStatement("SELECT name FROM facility WHERE id=?",array($thisField));
  $frow= sqlFetchArray($flist);
  return($frow{'name'});
}

function ImmLook($thisField) {
  if(!$thisField) return '';
  $fres=sqlStatement("SELECT ct_id FROM code_types WHERE ct_key='CVX'");
  $frow=sqlFetchArray($fres);
	$type=$frow{'ct_id'};
	if($type == '' || !$type) return '';
  $fres=sqlStatement("SELECT code_text, code_text_short FROM ".
		"codes WHERE code_type=? AND code=?",array($type, $thisField));
  $frow=sqlFetchArray($fres);
  return($frow{'code_text_short'});
}

function AddVitalsTest($vrec,$ht,$wt,$bps='',$bpd='',$bmi='',$bmi_status='',$hr='') {
  // echo "Height: ".$ht."<br />\n";
  // echo "Weight: ".$wt."<br />\n";
  // echo "Bps: ".$bps."<br />\n";
  // echo "Bpd: ".$bpd."<br />\n";
  // echo "BMI: ".$bmi."<br />\n";
  $add=false;
  // If there is no vitals record, and we captured data =>  ADD
  if($vrec == '' || $vrec == '0') { $add=true; }
  $vitals_entered = ($ht > 0);
  // echo "Test 1 -Vitals [".$vitals_entered."]  and Height(".$ht.")<br />\n";
  if(!$vitals_entered) { $vitals_entered= ($wt > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bps > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bpd > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bpd > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bmi > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bmi_status != ''); }
  // echo "Test 1 - Add [".$add."]  and Entered (".$vitals_entered.")<br />\n";
  if($add && $vitals_entered) { return true; }
  $add=false;
  if($vrec != '' && $vrec != '0' && $vitals_entered) {
    // echo "Checking for Vitals Record<br/>\n";
    $fres=sqlStatement("SELECT * FROM form_vitals WHERE id=?", array($vrec));
    $vitals=sqlFetchArray($fres);
    if($vitals) {
      // echo "Setting Vitals<br/>\n";
      if($ht != $vitals{'height'}) { $add=true; }
      if($wt != $vitals{'weight'}) { $add=true; }
      if($bps!= $vitals{'bps'}) { $add=true; }
      if($bpd != $vitals{'bpd'}) { $add=true; }
      if($bmi != $vitals{'BMI'}) { $add=true; }
      if($bmi_status != $vitals{'BMI_status'}) { $add=true; }
      if(($hr > 0) && ($hr != $vitals{'pulse'})) { $add=true; }
    }
  }
  return $add;
}

function UpdateDashboard($thisPid,$pap='',$mamm='',$bd='',$chol='',$colon='') {
  if(empty($thisPid)) { return false; }
  $update='';
  // echo "Add Dashboard Test, PID: ",$thisPid,"<br/>\n";
  // echo 'Updating dashboard entries....';
  $dates_entered = ($colon != 0 && $colon != '');
  if(!$dates_entered) { $dates_entered= ($bd != 0 && $bd != ''); }
  if(!$dates_entered) { $dates_entered= ($chol > 0 && $chol != ''); }
  if(!$dates_entered) { $dates_entered= ($pap > 0 && $pap != ''); }
  if(!$dates_entered) { $dates_entered= ($mamm > 0 && $mamm != ''); }
  if(!$dates_entered) { return true; }
  // echo '..';
  $fres=sqlStatement("SELECT * FROM form_dashboard WHERE pid=?",array($thisPid));
  // echo '..';
  $dash=sqlFetchArray($fres);
  // echo '..';
  if(!$dash{'id'}) {
    // echo "No dashboard was found..Creating";
		$parms=array($thisPid, $_SESSION['authUser'], $_SESSION['authProvider'],
				$_SESSION['userauthorized']);
    sqlStatement("INSERT INTO form_dashboard (date, pid, user, groupname, ".
				"authorized, activity) VALUES (NOW(), ?, ?, ?, ?, '1')", $parms);

  }
  if(!empty($colon) && $colon != $dash{'db_last_colon'}) { 
		$update.="db_last_colon='".$colon."', ";
  }
  if(!empty($bd) && $bd > $dash{'db_last_bone'}) {
		$update.="db_last_bone='".$bd."', ";
  }
  if(!empty($chol) && $chol > $dash{'db_last_chol'}) {
		$update.="db_last_chol='".$chol."', ";
  }
  if(!empty($pap) && $pap > $dash{'db_last_pap'}) {
		$update.="db_last_pap='".$pap."', ";
  }
  if(!empty($mamm) && $mamm > $dash{'db_last_mamm'}) {
		$update.="db_last_mamm='".$mamm."', ";
  }
	if(!empty($update)) {
		$form_dt=date('Y-m-d');
		$update.="db_form_dt='".$form_dt."'";
    $sql = "UPDATE form_dashboard SET date=NOW(), ".$update.
			", user='".$_SESSION['authUser']."', groupname='".
			$_SESSION['authProvider']."', authorized='".$_SESSION['userauthorized'].
			"', activity='1' WHERE pid='".$thisPid."'";
    // echo $sql."<br/>\n";
    sqlInsert($sql);
  }
  // echo '..';
  return true;
}

function checkSettingMode($thisSetting, $thisUser='')
{
	// First check for a practice setting
  $approve_on='';
  $sql= "SELECT * FROM user_settings WHERE setting_user='".
      "0' AND setting_label=?";
  $ures= sqlStatement($sql,array($thisSetting));
  $urow= sqlFetchArray($ures);
	if($urow{'setting_value'} && $urow{'setting_value'} != '') {
  	$approve_on= $urow{'setting_value'};
	}
	// echo "Practice Setting for $thisSetting is [$approve_on]<br/>\n";
	// echo "This user ID: ",$_SESSION['authUserID'],"</br>\n";
  $sql= "SELECT * FROM user_settings WHERE setting_user=? ".
     		"AND setting_label=?";
  $ures= sqlStatement($sql,array($_SESSION['authUserID'],$thisSetting));
  $urow= sqlFetchArray($ures);
	if($urow{'setting_value'} != '') { $approve_on= $urow{'setting_value'}; }
	// echo "User Setting for $thisSetting is [$approve_on]<br/>\n";
  return $approve_on;
}

function MultPrintInline($who)
{
  $out='';
  $comma=false;
  if(!isset($who)) return $out;
  if(!is_array($who)) return $out;
  foreach($who as $person) {
    $prnt=ListLook($person, 'Family_Relationships');
    if($comma) { $out.=", "; }
    $out.=$prnt;
    $comma=true;
  }
  return $out;
}

function printFamHist($check=0, $note='', $who='', $title='')
{
	if($check || $note || $who ) {
  	echo "<tr>\n";
  	echo "  <td class='PrintLabel'>&nbsp;&nbsp;$title</td>\n";
  	echo "  <td class='PrintBody'>".MultPrintInline($who)."</td>\n";
  	echo "  <td class='PrintBody'>".$note."</td>\n";
		echo "</tr>\n";
		return true;
	} else {
 	  return false;
	}
}

function PrintChapter($title, $printed, $tstyle='')
{
  if($printed) { return true; }
  echo "<div class='wmtPrnMainContainer'>\n";
  echo "	<div class='wmtPrnCollapseBar'>\n";
  echo "	<span class='wmtPrnChapter'>$title</span>\n";
  echo "	</div>\n";
  echo "	<div class='wmtPrnCollapseBox'>\n";
  echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'";
	if(!empty($tstyle)) { echo " style='$tstyle'"; }
	echo ">\n";
  if($title == 'Review of Systems') {
    echo "	<tr>\n";
    echo "		<td class='wmtPrnLabel' colspan='3'>The patient reports the current following problems:</td>\n";
    echo "	</tr>\n";
  }
  return true;
}

function CloseChapter()
{
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}

function PrintHeader($title, $printed)
{
  if($printed) { return true; }
  echo "<tr>\n";
  echo "<td class='wmtPrnLabel' colspan='3'>$title&nbsp;</td>\n";
  echo "</tr>\n";
  return true;
}

function PrintBlockHeader($title, $printed)
{
  if($printed) { return true; }
  echo "<tr>\n";
  echo "<td class='wmtPrnHeadingBar' colspan='3'>$title</td>\n";
  echo "</tr>\n";
  return true;
}

function PrintSubSection($title, $printed)
{
  if($printed) { return true; }
  echo "<tr>\n";
  echo "<td class='wmtPrnSubSection' colspan='3'>$title</td>\n";
  echo "</tr>\n";
  return true;
}

function BuildPrintList($list)
{
	$ret='';
	if(!count($list)) return $ret;
	foreach($list as $k => $val) {
		//  echo "Key: ",$k,"  Value: ",$val,"<br/>\n";
		if(!empty($ret)) $ret.=', ';
		$ret.=$val;	
	}
	return $ret;
}

function AppendItem($existing='',$new='', $comma=true)
{
	if($new=='') { return($existing); }
	$existing=trim($existing);
	if(!empty($existing) && $comma) { 
		$existing.=', ';
	} else {
		$existing.=' ';
	}
	$existing.=$new;
	return($existing);
}

function PrintSingleLine($lbl='',$txt='',$span='1')
{
	if(!empty($lbl) || !empty($txt)) {
		echo "		<tr>\n";
		echo "			<td colspan='$span'><span class='wmtPrnLabel'>$lbl&nbsp;&nbsp</span><span class='wmtPrnBody'>$txt</span></td>\n";
		echo "		</tr>\n";
	}
}

function PrintOverhead($lbl='',$txt='',$span=1,$tstyle='')
{
	if(!empty($lbl)) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabel'";
		if(!empty($tstyle)) { echo " style='$tstyle'"; }
		echo " colspan='$span'>$lbl</td>\n";
		echo "		</tr>\n";
	}
	if(!empty($txt)) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnIndentText'";
		if(!empty($tstyle)) { echo " style='$tstyle'"; }
		echo " colspan='$span'>$txt</td>\n";
		echo "		</tr>\n";
	}
}

function VerifyPatientID($testID='')
{
  if(!$testID || $testID == 0 || $testID =='') {
		echo "<h>Session Corrupt...Could NOT Access Patient ID!</h><br/>\n";
		echo "<h>ID Value of '",$testID,"' Was Submitted</h><br/>\n";
		echo "Aborting!!<br/>\n";
		return false;
	}
	// Could do other checks here, like retrieve that row of patient data
	return true;
}

function getPrescriptionbyPatient ($thisPid, $cols = "*")
{
  $sql = "SELECT * FROM prescriptions WHERE patient_id=? ".
				"AND active='1' ORDER BY active DESC, date_added DESC";
	$all=array();
  $res = sqlStatement($sql,array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function getActivePrescriptionsbyPatient ($thisPid,$enc='')
{
  $sql = "SELECT * FROM prescriptions WHERE patient_id=? ".
			"AND active=1 ORDER BY date_added DESC";
	$parms=array($thisPid);
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, prescriptions.id, ".
			"prescriptions.date_added, prescriptions.drug, prescriptions.drug_id, ".
			"prescriptions.form, prescriptions.dosage, prescriptions.quantity, ".
			"prescriptions.size, prescriptions.unit, prescriptions.route, ".
			"prescriptions.interval, prescriptions.refills, prescriptions.note, ".
			"prescriptions.active FROM form_wmt_ll LEFT JOIN prescriptions on ".
			"form_wmt_ll.list_id=prescriptions.id WHERE form_wmt_ll.pid=? AND ".
			"form_wmt_ll.encounter_id=? AND form_wmt_ll.list_type='prescriptions' ".
			"AND prescriptions.active=1 ORDER BY date_added DESC";
		$parms=array($thisPid, $enc);
	}
	$all=array();
  $res = sqlStatement($sql,$parms);
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function getInactivePrescriptionsbyPatient ($thisPid,$enc='',$max='')
{
  $sql = "SELECT * FROM prescriptions WHERE patient_id=? ".
			"AND active<=0 ORDER BY date_added DESC";
	$parms=array($thisPid);
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, prescriptions.id, ".
			"prescriptions.start_date, ".
			"prescriptions.date_added, prescriptions.drug, prescriptions.drug_id, ".
			"prescriptions.form, prescriptions.dosage, prescriptions.quantity, ".
			"prescriptions.size, prescriptions.unit, prescriptions.route, ".
			"prescriptions.interval, prescriptions.refills, prescriptions.note, ".
			"prescriptions.active FROM form_wmt_ll LEFT JOIN prescriptions on ".
			"form_wmt_ll.list_id=prescriptions.id WHERE form_wmt_ll.pid=? AND ".
			"form_wmt_ll.encounter_id=? AND form_wmt_ll.list_type='prescriptions' ".
			"AND prescriptions.active<=0 ORDER BY start_date DESC";
		$parms=array($thisPid, $enc);
	}
	if($max) { $sql.= " LIMIT $max"; }
	$all=array();
  $res = sqlStatement($sql,$parms);
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function getAllPrescriptionsbyPatient ($thisPid,$cols="*",$active="*",$enc='')
{
  $sql = "SELECT * FROM prescriptions WHERE patient_id=? ".
			"AND active=? ORDER BY date_added DESC";
	$parms=array($thisPid, $active);
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, prescriptions.id, ".
			"prescriptions.date_added, prescriptions.drug, prescriptions.drug_id, ".
			"prescriptions.form, prescriptions.dosage, prescriptions.quantity, ".
			"prescriptions.size, prescriptions.unit, prescriptions.route, ".
			"prescriptions.interval, prescriptions.refills, prescriptions.note, ".
			"prescriptions.active FROM form_wmt_ll LEFT JOIN prescriptions on ".
			"form_wmt_ll.list_id=prescriptions.id WHERE form_wmt_ll.pid=? AND ".
			"form_wmt_ll.encounter_id=? AND form_wmt_ll.list_type='prescriptions' ".
			"AND prescriptions.active=? ORDER BY date_added DESC";
		$parms=array($thisPid, $enc, $active);
	}
	$all=array();
  $res = sqlStatement($sql,$parms);
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function getAllMedicationsbyPatient ($thisPid, $cols = "*", $enc='')
{
  $sql = "SELECT * FROM medications WHERE patient_id=? ORDER BY date_added DESC";
	$all=array();
  $res = sqlStatement($sql,array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function GetAllImmunizationsbyPatient ($thisPid, $enc='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
  $sql = "SELECT * FROM immunizations WHERE patient_id=? ORDER BY ".
				"administered_date DESC";
	$parms=array($thisPid);
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, immunizations.id, ".
				"immunizations.administered_date, ".
				"immunizations.cvx_code, immunizations.manufacturer, ".
				"immunizations.lot_number, immunizations.note ".
				"FROM form_wmt_ll LEFT JOIN immunizations on ".
				"form_wmt_ll.list_id=immunizations.id WHERE form_wmt_ll.pid=? ".
				"AND form_wmt_ll.encounter_id=? AND form_wmt_ll.list_type=? ".
				"ORDER BY immunizations.administered_date DESC";
		$parms=array($thisPid, $enc, 'immunizations');
	}
	$all=array();
  $res = sqlStatement($sql,$parms);
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function AddSurgery($thisPid,$sdate='',$title='',$comm='',$refer='')
{
	$test=false;
	if($sdate == 'YYYY-MM-DD') { $sdate=''; }
  if(!VerifyPatientID($thisPid)) { return $test; }
	if(!empty($sdate) || !empty($title) || !empty($comm) || !empty($refer)) {
  	$sql="INSERT INTO lists (date, pid, type, title, activity, ".
			"user, groupname, begdate, enddate, comments, referredby) VALUES ".
			"(NOW(), ?, 'surgery', ?, '1', ?, ?, ?, ?, ?, ?)";
		// echo $sql,"<br/>\n";
		$parms=array($thisPid, $title, $_SESSION['authUser'], 
				$_SESSION['authProvider'], $sdate, $sdate, $comm, $refer);
  	$test=sqlInsert($sql,$parms);
	}
	return $test;
}

function UpdateSurgery($thisPid,$item='',$sdate='',$title='',$comm='',$refer='')
{
	if($sdate == 'YYYY-MM-DD') { $sdate=''; }
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($sdate) || !empty($title) || !empty($comm) || !empty($refer)) {
  	$sql="UPDATE lists SET date=NOW(), begdate=?, ".
			"title=?, comments=?, referredby=?  WHERE pid=? AND id=?";
		$parms=array($sdate, $title, $comm, $refer, $thisPid, $item);
		$test=sqlStatement($sql, $parms);
	}
}

function DeleteSurgery($thisPid,$item='',$links=0)
{
  if(!VerifyPatientID($thisPid)) { return false; }
 	$test=sqlStatement("DELETE FROM lists WHERE pid=? AND id=? AND type=?",
		array($thisPid, $item, 'surgery'));
	if($links) {
		DeleteAllListEntries($thisPid, $item, 'surgery');
	}
}

function AddHospitalization($thisPid,$sdate='',$why='',$type='',$note='')
{
	$test=false;
	if($sdate == 'YYYY-MM-DD') { $sdate=''; }
  if(!VerifyPatientID($thisPid)) { return $test; }
	if(!empty($sdate) || !empty($why) || !empty($note) || !empty($type)) {
		$parms=array($thisPid, $why, $_SESSION['authUser'], 
				$_SESSION['authProvider'], $sdate, $sdate, $note, $type);
  	$test=sqlInsert("INSERT INTO lists (date, pid, type, title, activity, ".
			"user, groupname, begdate, enddate, comments, extrainfo) VALUES ".
			"(NOW(), ?, 'hospitalization', ?, '1', ?, ?, ?, ?, ?, ?)", $parms);
	}
	return $test;
}

function UpdateHospitalization($thisPid,$item='',$sdate='',$why='',$type='',$note='')
{
	if($sdate == 'YYYY-MM-DD') { $sdate=''; }
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($sdate) || !empty($note) || !empty($why)) {
		$parms= array($sdate, $why, $note, $type, $thisPid, $item);
  	$test=sqlStatement("UPDATE lists SET date=NOW(), begdate=?, ".
			"title=?, comments=?, extrainfo=? WHERE pid=? AND id=?", $parms);
	}
}

function DeleteHospitalization($thisPid,$item='',$links=0)
{
  if(!VerifyPatientID($thisPid)) { return false; }
 	$test=sqlStatement("DELETE FROM lists WHERE pid=? AND id=? AND ".
			"type='hospitalization'",array($thisPid, $item));
	if($links) {
		DeleteAllListEntries($thisPid, $item, 'hospitalization');
	}
}

function GetMedicalHistory($thisPid, $enc='') {
  $sql = "SELECT id, injury_type as pmh_type, comments as pmh_nt FROM lists ".
		"WHERE pid=? AND type='wmt_med_history' ORDER BY id";
	$parms=array($thisPid);
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, lists.id, injury_type as pmh_type, ".
				"comments as pmh_nt FROM form_wmt_ll LEFT JOIN lists on ".
				"form_wmt_ll.list_id=lists.id WHERE form_wmt_ll.pid=? ".
				"AND form_wmt_ll.encounter_id=? AND form_wmt_ll.list_type=? ".
				"ORDER BY lists.id";
		$parms=array($thisPid, $enc, 'wmt_med_history');
	}
	$all=array();
  $res = sqlStatement($sql, $parms);
	$iter=0;
  while($row = sqlFetchArray($res)) {
		$all[$iter] = $row;
		$sql = "SELECT COUNT(*) FROM form_wmt_ll WHERE form_wmt_ll.list_type=? ".
				"AND form_wmt_ll.list_id=?";
		$chk = sqlStatement($sql, array('wmt_med_history', $all[$iter]['id']));
		$num = sqlFetchArray($chk);
		$all[$iter]['pmh_num_links']= $num['COUNT(*)'];
		$iter++;
	}
  return $all;
}

function AddMedicalHistory($thisPid,$type='',$sub='',$note='')
{
	$test=false;
  if(!VerifyPatientID($thisPid)) { return $test; }
	if(!empty($type) || !empty($sub) || !empty($note)) {
		// Auto-create a title for this issue
		$title=ListLook($type,'Medical_History_Problems');
		if($title == '') { $title='No Type of History Specified'; }
		$parms=array($thisPid, $_SESSION['authUser'], $_SESSION['authProvider'],
			$type, $sub, $note, $title);
  	$test=sqlInsert("INSERT INTO lists (date, pid, user, groupname, activity, ".
			"type, enddate, injury_type, injury_part, comments, title) VALUES ".
			"(NOW(), ?, ?, ?, '1', 'wmt_med_history', '0000-00-00', ?, ?, ?, ?)", 
			$parms);
	}
	return $test;
}

function UpdateMedicalHistory($thisPid,$item='',$type='',$sub='',$note='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($type) || !empty($sub) || !empty($note)) {
		// Auto-create a title for this issue
		$title=ListLook($type,'Medical_History_Problems');
		if($title == '') { $title='No Type of History Specified'; }
		$parms=array($type, $sub, $note, $title, $thisPid, $item);
  	$test=sqlStatement("UPDATE lists SET date=NOW(), injury_type=?, ".
				"injury_part=?, comments=?, title=? WHERE pid=? AND id=?",$parms);
	}
}

function DeleteMedicalHistory($thisPid,$item='',$links=0)
{
  if(!VerifyPatientID($thisPid)) { return false; }
 	$test=sqlStatement("DELETE FROM lists WHERE pid=? AND id=?", 
			array($thisPid, $item));
	if($links) {
		DeleteAllListEntries($thisPid, $item, 'wmt_med_history');
	}
}

function GetImageHistory($thisPid,$enc='') {
  $sql = "SELECT id, injury_type as img_type, comments as img_nt, ".
		"begdate as img_dt FROM lists WHERE pid=? AND type=? ORDER BY begdate";
	$parms=array($thisPid, 'wmt_img_history');
	// Create a new sql statement to only get those included on the linked list
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, lists.id, injury_type as img_type, ".
				"comments as img_nt, begdate as img_dt FROM form_wmt_ll LEFT JOIN ".
				"lists on form_wmt_ll.list_id=lists.id WHERE form_wmt_ll.pid=? ".
				"AND form_wmt_ll.encounter_id=? AND form_wmt_ll.list_type=? ".
				"ORDER BY begdate";
		$parms=array($thisPid, $enc, 'wmt_img_history');
	}
	$all=array();
  $res = sqlStatement($sql,$parms);
	$iter = 0;
  while($row = sqlFetchArray($res)) {
  	$all[$iter] = $row;
		$sql = "SELECT COUNT(*) FROM form_wmt_ll WHERE form_wmt_ll.list_type=? ".
				"AND form_wmt_ll.list_id=?";
		$chk = sqlStatement($sql, array('wmt_img_history', $all[$iter]['id']));
		$num = sqlFetchArray($chk);
		$all[$iter]['img_num_links']= $num['COUNT(*)'];
		$iter++;
	}
  return $all;
}

function AddImageHistory($thisPid,$type='',$dt='',$note='')
{
	$test=0;
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($type) || !empty($dt) || !empty($note)) {
		// Auto-create a title for this issue
		$title=ListLook($type,'Image_Types');
		if($title == '') { $title='No Type of Image Specified'; }
		if($dt == 0 || $dt == 'YYYY-MM-DD' || $dt == '') { $dt='0000-00-00'; }
  	$sql="INSERT INTO lists (date, pid, user, groupname, ".
			"activity, begdate, enddate, type, injury_type, comments, title) ".
			"VALUES (NOW(), ?, ?, ?, '1', ?, ?, ?, ?, ?, ?)";
		$test=sqlInsert($sql, array($thisPid, $_SESSION['authUser'], 
			$_SESSION['authProvider'],$dt,$dt,'wmt_img_history',$type,$note,$title));
		// We could log with test if we want to add that functionality
		// echo "Image History Added - Return [$test]<br/>\n";
	}
	return $test;
}

function UpdateImageHistory($thisPid,$item='',$type='',$dt='',$note='')
{
	if($dt == 0 || $dt == 'YYYY-MM-DD' || $dt == '0000-00-00') { $dt=''; }
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($type) || !empty($sub) || !empty($note)) {
		// Auto-create a title for this issue
		$title=ListLook($type,'Image_Types');
		if($title == '') { $title='No Type of Image Specified'; }
		$parms=array($type, $sub, $note, $title, $thisPid, $item);
  	$sql="UPDATE lists SET date=NOW(), ";
		if(!empty($dt) && $dt != 0) { 
			$sql.="begdate=?, ";
			array_unshift($parms, $dt);
		}
		$sql.="injury_type=?, injury_part=?, comments=?, title=? WHERE pid=? AND id=?";
		$test=sqlStatement($sql,$parms);
	}
}

function DeleteImageHistory($thisPid,$item='',$links=0)
{
  if(!VerifyPatientID($thisPid)) { return false; }
 	$test=sqlStatement("DELETE FROM lists WHERE pid=? AND id=?", 
			array($thisPid, $item));
	if($links) {
		DeleteAllListEntries($thisPid, $item, 'wmt_img_history');
	}
}

function LinkListEntry($thisPid, $item='', $enc='', $type='', $report=false, $skip=false)
{
	if($item == '' || $enc == '') { return false; }
  if(!VerifyPatientID($thisPid)) { return false; }
 	$test=sqlStatement("INSERT INTO form_wmt_ll (encounter_id, ".
			"list_type, list_id, pid) VALUES (?, ?, ?, ?) ".
			"ON DUPLICATE KEY UPDATE list_id=?",
			array($enc, $type, $item, $thisPid, $item));
	if($report) {
		echo "Linked $type (ID: $item) to Encounter [$enc] for Patient ID: $thisPid<br/>\n";
	}
	// If this was for an entry in lists, also update the issue_encounter table
	if($type != 'prescriptions' && $type != 'immunizations' && $type != 'pap' &&
		$type != 'past_pregnancy' && !$skip) {
		$sql="INSERT INTO issue_encounter (pid, list_id, encounter, resolved) ".
				"VALUES (?, ?, ?, 0) ON DUPLICATE KEY UPDATE encounter=?";
		$parms=array($thisPid, $item, $enc, $enc);
		$link=sqlInsert($sql,$parms);
	}
	return $test;
}

function UnlinkListEntry($thisPid, $item='',$enc='', $type='', $skip=false)
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if($item == '' || $enc == '') { return false; }
 	$test=sqlStatement("DELETE FROM form_wmt_ll WHERE pid=? AND encounter_id=? ".
		"AND list_id=? AND list_type=?", array($thisPid, $enc, $item, $type));
	// If this was for an entry in lists, also update the issue_encounter table
	if($type != 'prescriptions' && $type != 'immunizations' && $type != 'pap' &&
		$type != 'past_pregnancy' && !$skip) {
		$sql="DELETE FROM issue_encounter WHERE pid=? AND list_id=? AND ".
			"encounter=?";
		$parms=array($thisPid, $item, $enc);
		$test=sqlStatement($sql,$parms);
	}
}

function DeleteAllListEntries($thisPid, $item='', $type='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if($item == '' || $type == '') { return false; }
 	$test=sqlStatement("DELETE FROM form_wmt_ll WHERE pid=? AND ".
		"list_id=? AND list_type=?", array($thisPid, $item, $type));
	// If this was for an entry in lists, also update the issue_encounter table
	if($type != 'prescriptions' && $type != 'immunizations' && $type != 'pap' &&
		$type != 'past_pregnancy') {
		$sql="DELETE FROM issue_encounter WHERE pid=? AND list_id=?";
		$parms=array($thisPid, $item);
		$test=sqlStatement($sql,$parms);
	}
}

function UnlinkListItem($thisPid, $item='', $type='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if($item == '' || $type == '') { return false; }
 	$test=sqlStatement("DELETE FROM form_wmt_ll WHERE pid=? AND ".
		"list_id=? AND list_type=?", array($thisPid, $item, $type));
}

function AddAllergy($thisPid,$sdate='',$title='',$comm='',$react='',$occur='')
{
	$test=false;
	if($sdate == 'YYYY-MM-DD') { $sdate=''; }
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($sdate) || !empty($title) || !empty($comm) || 
			!empty($react) || !empty($occur)) {
		$parms=array($thisPid, $title, $_SESSION['authUser'], 
			$_SESSION['authProvider'], $sdate, $occur, $comm, $react);
  	$test = sqlInsert("INSERT INTO lists (date, pid, type, title, activity, ".
			"user, groupname, begdate, occurrence, comments, reaction) VALUES ".
			"(NOW(), ?, 'allergy', ?, '1', ?, ?, ?, ?, ?, ?)", $parms);
	}
	return $test;
}

function UpdateAllergy($thisPid,$item='',$note='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($note)) {
  	$test=sqlStatement("UPDATE lists SET date=NOW(), comments=? ".
			" WHERE pid=? AND id=?", array($note, $thisPid, $item));
	}
}

function AddMedication($thisPid,$sdate='',$edate='',$title='',$comm='',$stat='')
{
	if($sdate == 'YYYY-MM-DD') { $sdate=''; }
	if($edate == 'YYYY-MM-DD') { $edate=''; }
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($sdate) || !empty($edate) || !empty($title) || !empty($comm) || 
			!empty($stat)) {
		if(empty($edate) || !$edate || $edate == '') {
  		sqlInsert("INSERT INTO lists (date, pid, type, title, activity, user, ".
     			"groupname, begdate, comments, extrainfo) VALUES (NOW(), ".
     			"'$thisPid', 'surgery', '".$title."', '1', '".
     			$_SESSION['authUser']."', '".$_SESSION['authProvider']."', '".
     			$sdate."', '".$comm."', '".$stat."')");
		} else {
  		sqlInsert("INSERT INTO lists (date, pid, type, title, activity, user, ".
     			"groupname, begdate, enddate, comments, extrainfo) VALUES (NOW(), ".
     			"'$thisPid', 'surgery', '".$title."', '1', '".
     			$_SESSION['authUser']."', '".$_SESSION['authProvider']."', '".
     			$sdate."', '".$edate."', '".$comm."', '".$stat."')");
		}
	}
}

function UpdatePrescription($thisPid,$item='',$note='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($note)) {
  	$test=sqlStatement("UPDATE prescriptions SET date_modified=NOW(), note=? ".
			"WHERE patient_id=? AND id=?", array($note, $thisPid, $item));
	}
}

function UpdateImmunization($thisPid,$item='',$note='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($note)) {
		$parms=array($note, $_SESSION['authUserID'], $thisPid, $item);
  	$test=sqlStatement("UPDATE immunizations SET update_date=NOW(), note=?, ".
			"updated_by=? WHERE patient_id=? AND id=?",$parms);
	}
}

function AddProblem($thisPid, $pdate='', $pdiag='')
{
 	if(!empty($pdiag) && $pdiag != 0) {
		$desc=lookup_code_descriptions('ICD9:'.$pdiag);
		$pdiag='ICD9:'.$pdiag;
   	$title=$pdiag.' - '.$desc;
   	$comment='Auto-Add From Encounter: '.$pdate.' - Extended Exam (1)';
		$parms=array($thisPid, $title, $_SESSION['authUser'], 
			$_SESSION['authProvider'], $pdate, $pdiag, $comment);
   	sqlInsert("INSERT INTO lists (date, pid, type, title, activity, user, ".
      	"groupname, begdate, occurrence, referredby, diagnosis, comments ) ".
				"VALUES (NOW(), ?, 'medical_problem',  ?, '1', ?, ?, ?, '', '', ?, ?)",
				$parms);
 	}
}

function AddFamilyHistory($thisPid,$who='',$type='',$note='',$dead='',$age='', $age_dead='')
{
	$ret=false;
	if($age == 'YYYY-MM-DD') { $age=''; }
	if($age_dead == 'YYYY-MM-DD') { $age_dead=''; }
  if(!$thisPid || $thisPid == 0 || $thisPid=='') {
		echo "<h>Session Corrupt...Could NOT Access Patient ID!</h><br/>\n";
		echo "Aborting!!<br/>\n";
		exit;
	}
	if(!empty($who) && (!empty($type) || !empty($note))) { 
		// Auto-create a title for this issue
		$title='No Type of History';
		$tmp=ListLook($type,'Family_History_Problems');
		if($tmp != '') { $title=$tmp.' - '; }
		$tmp=ListLook($who,'Family_Relationships');
		if($tmp == '') { $tmp='No Family Member Specified'; }
		$title.=$tmp;
		
		$parms=array($thisPid, $_SESSION['authUser'], $_SESSION['authProvider'],
				$who, $dead, $age, $age_dead, $type, $note, $title);
  	$ret=sqlInsert("INSERT INTO lists (date, type, pid, user, groupname, ".
     	"activity, begdate, enddate, injury_part, injury_grade, reaction, ".
			"destination, injury_type, comments, title) VALUES (NOW(), ".
			"'wmt_family_history', ?, ?, ?, '1', '0000-00-00', '0000-00-00', ?, ".
			"?, ?, ?, ?, ?, ?)", $parms);
	}
	if(!empty($who) && (!empty($dead) || !empty($age) || !empty($age_dead))) { 
		$sql="SELECT * FROM form_fh_misc WHERE pid=? AND fhm_who=?";
		$res=sqlStatement($sql,array($thisPid, $who));
		$row=sqlFetchArray($res);
		$parms=array();
		if(($row{'pid'} != '') && ($row{'fhm_who'} != '')) {
			$update=',';
			if(!empty($dead)) { 
				array_push($parms, $dead);
				$update.=" fhm_deceased=?,";
			}
			if(!empty($age)) { 
				array_push($parms, $age);
				$update.=" fhm_age=?,";
			}
			if(!empty($age_dead)) {
				array_push($parms, $age_dead);
				$update.=" fhm_age_dead=?,";
			}
			array_push($parms, $_SESSION['authUser'], $_SESSION['authProvider'], 
					$_SESSION['userauthorized'], $thisPid, $who);
    	$sql = "UPDATE form_fh_misc SET date=NOW() ".$update." user=?, ".
				"groupname=?, authorized=?, activity='1' WHERE pid=? AND fhm_who=?";
			sqlStatement($sql,$parms);
		} else {
			// echo "No result!<br/>\n";
			// echo $res,"<br/>\n";
			// echo "Making an entry for this person<br/>\n";
			$parms=array($thisPid, $_SESSION['authUser'], $_SESSION['authProvider'],
				$_SESSION['userauthorized'], $who, $dead, $age, $age_dead);
  		sqlInsert("INSERT INTO form_fh_misc (date, pid, user, groupname, ".
     		"authorized, activity, fhm_who, fhm_deceased, fhm_age, fhm_age_dead) ".
				"VALUES (NOW(), ?, ?, ?, ?, '1', ?, ?, ?, ?)",$parms);
		}
	}
	return $ret;
}

function UpdateFamilyHistory($thisPid,$item='',$who='',$type='',$note='',$dead='',$age='',$age_dead='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($who) && (!empty($type) || !empty($note))) { 
		// Auto-create a title for this issue
		$title='No Type of History';
		$tmp=ListLook($type,'Family_History_Problems');
		if($tmp != '') { $title=$tmp.' - '; }
		$tmp=ListLook($who,'Family_Relationships');
		if($tmp == '') { $tmp='No Family Member Specified'; }
		$title.=$tmp;
		$parms=array($_SESSION['authUser'], $_SESSION['authProvider'], $who,
					$dead, $age, $age_dead, $type, $note, $title, $thisPid, $item);
    $sql = "UPDATE lists SET date=NOW(), user=?, groupname=?, ".
				"activity='1', injury_part=?, injury_grade=?, reaction=?, ".
				"destination=?, injury_type=?, comments=?, title=? ".
				"WHERE pid=? AND id=?";
		$test=sqlStatement($sql,$parms);
	}

	if(!empty($who) && (!empty($dead) || !empty($age) || !empty($age_dead))) { 
		$sql="SELECT * FROM form_fh_misc WHERE pid=? AND fhm_who=?";
		$res=sqlStatement($sql, array($thisPid, $who));
		$row=sqlFetchArray($res);
		if(($row{'pid'} != '') && ($row{'fhm_who'} != '')) {
			$parms=array();
			$update=',';
			if(!empty($dead)) {
				array_push($parms, $dead);
				$update.=" fhm_deceased=?,";
			}
			if(!empty($age)) {
				array_push($parms, $age);
				$update.=" fhm_age=?,";
			}
			if(!empty($age_dead)) {
				array_push($parms, $age_dead);
				$update.=" fhm_age_dead=?,";
			}
			array_push($parms, $_SESSION['authUser'], $_SESSION['authProvider'],
				$_SESSION['userauthorized'], $thisPid, $who);
    	$sql = "UPDATE form_fh_misc SET date=NOW() ".$update." user=?, ".
				"groupname=?, authorized=?, activity='1' WHERE pid=? AND fhm_who=?";
			sqlStatement($sql,$parms);
		} else {
			$parms=array($thisPid, $_SESSION['authUser'], $_SESSION['authProvider'],
				$_SESSION['userauthorized'], $who, $dead, $age, $age_dead);
  		sqlInsert("INSERT INTO form_fh_misc (date, pid, user, groupname, ".
     		"authorized, activity, fhm_who, fhm_deceased, fhm_age, fhm_age_dead) ".
				"VALUES (NOW(), ?, ?, ?, ?, '1', ?, ?, ?, ?)", $parms);
		}
	}
}

function DeleteFamilyHistory($thisPid,$item='',$links=0)
{
  if(!VerifyPatientID($thisPid)) { return false; }
 	$test=sqlStatement("DELETE FROM lists WHERE pid=?  AND id=?", 
				array($thisPid, $item));
	if($links) {
		DeleteAllListEntries($thisPid, $item, 'wmt_family_history');
	}
}

function GetFamilyHistory($thisPid,$enc='') {
  $sql = "SELECT id, injury_part as fh_who, injury_grade as fh_deceased, ".
			"reaction as fh_age, destination as fh_age_dead, ".
			"injury_type as fh_type, comments as fh_nt FROM lists ".
			"WHERE pid=? AND type='wmt_family_history' ORDER BY id";
	$parms=array($thisPid);
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, lists.id, injury_grade as ".
				"fh_deceased, reaction as fh_age, destination as fh_age_dead, ".
				"injury_part as fh_who, ".
				"injury_type as fh_type, comments as fh_nt FROM form_wmt_ll ".
				"LEFT JOIN lists on form_wmt_ll.list_id=lists.id WHERE ".
				"form_wmt_ll.pid=? AND form_wmt_ll.encounter_id=? AND ".
				"form_wmt_ll.list_type=? ORDER BY lists.id";
		$parms=array($thisPid, $enc, 'wmt_family_history');
	}
	$all=array();
  $res = sqlStatement($sql,$parms);
	$iter=0;
  while($row = sqlFetchArray($res)) {
  	$all[$iter] = $row;
		$sql= "SELECT COUNT(*) FROM form_wmt_ll WHERE form_wmt_ll.list_type=? ".
				"AND form_wmt_ll.list_id=?";
		$chk= sqlStatement($sql, array('wmt_family_history', $all[$iter]['id']));
		$num= sqlFetchArray($chk);
		$all[$iter]['fh_num_links'] = $num['COUNT(*)'];
		$iter++;
	}
  return $all;
}

function GetFamilyHistoryDefaults($thisPid) {
	$all=array();
	$fres=sqlStatement("SELECT option_id, title FROM list_options WHERE ".
       "list_id='Family_Relationships' ORDER BY seq");
  while($frow = sqlFetchArray($fres)) {
  	$sql = "SELECT fhm_who, fhm_deceased, fhm_age, fhm_age_dead FROM ".
				"form_fh_misc WHERE pid=? AND fhm_who=?";
		// echo "Query: ",$sql,"<br/>\n";
  	$res = sqlStatement($sql, array($thisPid, $frow{'option_id'}));
		$row = sqlFetchArray($res);

  	$all[$frow{'option_id'}] = $row;
	}
  return $all;
}

function AddDiagnosis($thisPid,$enc=0,$type='',$diag='',$title='',$note='',$bdt='',$edt='')
{
	if($bdt == 'YYYY-MM-DD') { $bdt=''; }
	if($edt == 'YYYY-MM-DD') { $edt=''; }
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($diag) || !empty($note) || !empty($title)) {
		// The start date must be set
		if(empty($bdt) || !$bdt) { $bdt=date('Y-m-d'); }
		if(empty($title)) { 
			if(!empty($diag)) {
				$title=$diag.' - '.lookup_code_descriptions($type.':'.$diag);
			} else {
				$title='Problem added for visit on '.date('Y-m-d');
			}
		}
		$title=htmlspecialchars($title,ENT_QUOTES,'UTF-8',false);
		$note=htmlspecialchars($note,ENT_QUOTES,'UTF-8',false);
		if(!empty($type) && !empty($diag)) { $diag=$type.':'.$diag; }
  	$sql= "INSERT INTO lists (date, pid, user, groupname, type, title, ".
			"begdate, enddate, activity, diagnosis, comments) VALUES ".
			"(NOW(), '".$thisPid."', '".$_SESSION['authUser']."', '".
			$_SESSION['authProvider']."', 'medical_problem', '".$title."', '".
			$bdt."', '".$edt."', '1', '".$diag."', '".$note."')";
		if(empty($edt) || $edt=='' || !$edt) {
  		$sql= "INSERT INTO lists (date, pid, user, groupname, type, title, ".
				"begdate, activity, diagnosis, comments) VALUES ".
				"(NOW(), '".$thisPid."', '".$_SESSION['authUser']."', '".
				$_SESSION['authProvider']."', 'medical_problem', '".$title."', '".
				$bdt."', '1', '".$diag."', '".$note."')";
		}
		$test=sqlInsert($sql);
		// Now add the link from this diagnosis to this encounter
		// echo "Diagnosis Insert returned ",$test,"<br/>\n";
		if($enc && $enc != 0) {
			$sql="INSERT INTO issue_encounter (pid, list_id, encounter, resolved) ".
					"VALUES ('$thisPid', '$test', '$enc', 0)";
			$test=sqlInsert($sql);
		}
	}
}

function LinkDiagnosis($thisPid,$thisId='',$thisEnc='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!$thisId) { return false; }
	if(!$thisEnc) { return false; }
	$sql="INSERT INTO issue_encounter (pid, list_id, encounter, resolved) ".
			"VALUES (?, ?, ?, 0)";
	$test=sqlInsert($sql, array($thisPid, $thisId, $thisEnc));
}

function UnlinkDiagnosis($thisPid,$thisId='',$thisEnc='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!$thisId) { return false; }
	if(!$thisEnc) { return false; }
	$sql="DELETE FROM issue_encounter WHERE pid=? AND list_id=? AND encounter=?";
	$test=sqlInsert($sql, array($thisPid, $thisId, $thisEnc));
}

function UpdateDiagnosis($thisPid,$thisId='',$diag='',$title='',$note='',$bdt='',$edt='',$pre='',$suff='')
{
	if(!empty($pre) && (substr($pre,-1) != ':')){ $pre.=':'; }
	$diag=$pre.$diag.$suff;
	if($bdt == 'YYYY-MM-DD') { $bdt=''; }
	if($edt == 'YYYY-MM-DD') { $edt=''; }
  if(!VerifyPatientID($thisPid)) { return false; }
	$title=htmlspecialchars($title,ENT_QUOTES,'UTF-8',false);
	$note=htmlspecialchars($note,ENT_QUOTES,'UTF-8',false);
	$bdt=htmlspecialchars($bdt,ENT_QUOTES,'UTF-8',false);
	$edt=htmlspecialchars($edt,ENT_QUOTES,'UTF-8',false);
	if(!empty($diag) || !empty($note) || !empty($title)) {
		$parms=array($diag, $bdt, $title, $note, $thisPid, $thisId);
  	$sql="UPDATE lists SET date=NOW(), diagnosis=?, begdate=?, ".
			"title=?, comments=? WHERE pid=? AND id=?";
		if(!empty($edt)) {
			$parms=array($diag, $bdt, $edt, $title, $note, $thisPid, $thisId);
  		$sql="UPDATE lists SET date=NOW(), diagnosis=?, begdate=?, ".
				"enddate=?, title=?, comments=? WHERE pid=? AND id=?";
		}
  	$test=sqlStatement($sql, $parms);
	}
}

function DeleteDiagnosis($thisPid,$item='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
 	$test=sqlStatement("DELETE FROM lists WHERE pid=? AND id=?",
				array($thisPid, $item));
	echo "Test of Individual Delete: ",$test,"<br/>\n";
}

function GetFormFromRepository($thisPid,$thisEnc,$thisId,$thisName)
{
	$_ret='Could Not Locate Archived Form'; 
  if(!VerifyPatientID($thisPid)) { return false; }
	$parms=array($thisPid, $thisId, $thisName, $thisEnc);
  $sql="SELECT id, form_id, content FROM form_repository WHERE pid=? ".
		"AND form_id=? AND form_name=? AND encounter=?";
  $fres=sqlStatement($sql, $parms);
	if($fres) { 
		$frow=sqlFetchArray($fres);
		if($frow{'form_id'} == $thisId) {
			$_ret=$frow{'content'};
		}
	}
	return $_ret;
}

function FormInRepository($thisPid,$thisEnc,$thisId,$thisName)
{
	$_ret = false;
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($thisEnc) && !empty($thisId) && !empty($thisName)) {
		$parms=array($thisPid, $thisId, $thisName, $thisEnc);
  	$sql="SELECT id, pid FROM form_repository WHERE pid=? AND form_id=? ".
			"AND form_name=? AND encounter=?";
  	$fres=sqlStatement($sql, $parms);
		if($fres) { 
			$frow=sqlFetchArray($fres);
			if($frow{'id'} != '' && $frow{'id'} != 0) $_ret = true;
		}
	}
	return $_ret;
}

function AddFormToRepository($thisPid,$thisEnc,$thisId,$thisName,$thisContent)
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($thisEnc) && !empty($thisId) && !empty($thisName)) {
		$parms=array($thisPid, $thisId, $thisName, $thisEnc, $thisContent);
  	$sql="INSERT INTO form_repository (pid, form_id, form_name, encounter, ".
			"content) VALUES (?, ?, ?, ?, ?)";
  	$test=sqlInsert($sql, $parms);
	}
}

function DeleteFromRepository($thisPid,$thisEnc,$thisId,$thisName)
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($thisEnc) && !empty($thisId) && !empty($thisName)) {
		$parms=array($thisPid, $thisId, $thisName, $thisEnc);
  	$sql="DELETE FROM form_repository WHERE pid=? AND form_id=? AND ".
				"form_name=? AND encounter=?";
  	$test=sqlInsert($sql, $parms);
	}
}

function AddPP($thisPid,$dt='',$weeks='',$len='',$lb='',$oz='',$sex='',$delivery='',$anes='',$loc='',$pre='',$com='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if($dt == '' && $weeks == '' && $len == '' && $lb == '' && $oz == '' &&
			$sex == '' && $delivery == '' && $anes == '' && $loc == '' &&
			$pre == '' && $com == '') { return false; }
  $sql= "INSERT INTO form_whc_pp (pid, groupname, user, authorized, ".
     	"activity, date, pp_date_of_pregnancy, pp_ga_weeks, ".
			"pp_labor_length, pp_weight_lb, pp_weight_oz, pp_sex, pp_delivery, ".
			"pp_anes, pp_place, pp_preterm, pp_comment) VALUES (?, ?, ?, ?, '1', ".
			"NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$parms=array($thisPid, $_SESSION['authProvider'], $_SESSION['authUser'],
			$_SESSION['userauthorized'], $dt, $weeks, $len, $lb, $oz,
			$sex, $delivery, $anes, $loc, $pre, $com);
  $ret=sqlInsert($sql, $parms);
	return $ret;
}

function UpdatePP($thisPid,$ppid=0,$dt='',$weeks='',$len='',$lb='',$oz='',$sex='',$delivery='',$anes='',$loc='',$pre='',$com='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!$ppid) { return false; }
   	// Update an existing row
  $sql= "UPDATE form_whc_pp SET groupname=?, user=?, authorized=?, ".
   	"activity=1, date = NOW(), pp_date_of_pregnancy=?, pp_ga_weeks=?, ".
   	"pp_labor_length=?, pp_weight_lb=?, pp_weight_oz=?, pp_sex=?, ".
		"pp_delivery=?, pp_anes=?, pp_place=?, pp_preterm=?, pp_comment=? ".
   	"WHERE id=?";
	$parms=array($_SESSION['authProvider'], $_SESSION['authUser'],
		$_SESSION['userauthorized'], $dt, $weeks, $len, $lb, $oz,
		$sex, $delivery, $anes, $loc, $pre, $com, $ppid);
  sqlInsert($sql, $parms);
}

function AddOrUpdatePP($thisPid,$ppid=0,$dt='',$weeks='',$len='',$lb='',$oz='',$sex='',$delivery='',$anes='',$loc='',$pre='',$com='')
{
  if(!VerifyPatientID($thisPid)) { return false; }
	$dt=htmlspecialchars($dt,ENT_QUOTES,'UTF-8',false);
	$weeks=htmlspecialchars($weeks,ENT_QUOTES,'UTF-8',false);
	$len=htmlspecialchars($len,ENT_QUOTES,'UTF-8',false);
	$lb=htmlspecialchars($lb,ENT_QUOTES,'UTF-8',false);
	$oz=htmlspecialchars($oz,ENT_QUOTES,'UTF-8',false);
	$sex=htmlspecialchars($sex,ENT_QUOTES,'UTF-8',false);
	$delivery=htmlspecialchars($delivery,ENT_QUOTES,'UTF-8',false);
	$anes=htmlspecialchars($anes,ENT_QUOTES,'UTF-8',false);
	$loc=htmlspecialchars($loc,ENT_QUOTES,'UTF-8',false);
	$pre=htmlspecialchars($pre,ENT_QUOTES,'UTF-8',false);
	$com=htmlspecialchars($com,ENT_QUOTES,'UTF-8',false);
	if(!$ppid && empty($dt) && empty($weeks) && empty($len) && empty($lb) &&
			empty($oz) && empty($sex) && empty($delivery) && empty($anes) &&
			empty($loc) && empty($pre) && empty($com)) { return true; }
  if($ppid) {
   	$q1= "pp_date_of_pregnancy='".$dt."', pp_ga_weeks='".$weeks.
     	"', pp_labor_length='".$len."', pp_weight_lb='".$lb.
     	"', pp_weight_oz='".$oz."', pp_sex='".$sex."', pp_delivery='".
     	$delivery."', pp_anes='".$anes."', pp_place='".$loc.
     	"', pp_preterm='".$pre."', pp_comment='".$com."', ";
   	// Update an existing row
   	$sql= "UPDATE form_whc_pp SET groupname='".$_SESSION["authProvider"].
     	"',user='".$_SESSION["authUser"]."', authorized='".
     	$_SESSION['userauthorized']."', activity=1, $q1 date = NOW() ".
     	"WHERE id=$ppid";
   	// echo "SQL Insert: ".$sql."<br />\n";
   	sqlInsert($sql);
  } else {
   	// Add a new row 
   	// Get the highest existing order number
   	$pp_order=getNextOrder($thisPid);
   	// echo "Next Order: ".$pp_order."<br />\n";
   	$sql= "INSERT INTO form_whc_pp (pid, groupname, user, authorized, ".
       	"activity, date, pp_date_of_pregnancy, pp_ga_weeks, ".
				"pp_labor_length, pp_weight_lb, pp_weight_oz, pp_sex, pp_delivery, ".
				"pp_anes, pp_place, pp_preterm, pp_comment, pp_order) VALUES ('".
				$thisPid."', '".$_SESSION['authProvider']."', '".$_SESSION['authUser'].
				"', '".$_SESSION['userauthorized']."', '1', NOW(), '".$dt."', '".
				$weeks."', '".$len."', '".$lb."', '".$oz."', '".$sex.
				"', '".$delivery."', '".$anes."', '".$loc."', '".$pre.
				"', '".$com."', '".$pp_order."')";
   	// echo "SQL Insert: ".$sql."<br />\n";
   	sqlInsert($sql);
  }
}

function GetDiagDescription($codes) {
	$code_types = array();
	$default_search_type = '';
	$ctres = sqlStatement("SELECT * FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key");
	while ($ctrow = sqlFetchArray($ctres)) {
  	$code_types[$ctrow['ct_key']] = array(
    	'active' => $ctrow['ct_active'  ],
    	'id'   => $ctrow['ct_id'  ],
    	'fee'  => $ctrow['ct_fee' ],
    	'mod'  => $ctrow['ct_mod' ],
    	'just' => $ctrow['ct_just'],
    	'rel'  => $ctrow['ct_rel' ],
    	'nofs' => $ctrow['ct_nofs'],
    	'diag' => $ctrow['ct_diag'],
    	'mask' => $ctrow['ct_mask'],
    	'label'=> ( (empty($ctrow['ct_label'])) ? $ctrow['ct_key'] : $ctrow['ct_label'] ),
    	'external'=> $ctrow['ct_external'],
    	'claim' => $ctrow['ct_claim'],
    	'proc' => $ctrow['ct_proc'],
  	);
  	if ($default_search_type === '') $default_search_type = $ctrow['ct_key'];
	}

	/**
 	* This array stores the external table options. 
 	* 'external' attribute  for explanation of the option listings.
 	* @var array
 	*/
	$cd_external_options = array(
  	'0' => xl('No'),
  	'4' => xl('ICD9 Diagnosis'),
  	'5' => xl('ICD9 Procedure/Service'),
  	'1' => xl('ICD10 Diagnosis'),
  	'6' => xl('ICD10 Procedure/Service'),
  	'2' => xl('SNOMED (RF1) Diagnosis'),
  	'3' => xl('SNOMED (RF2) Diagnosis'),
	);

  $code_text = '';
  if (!empty($codes)) {
    $relcodes = explode(';', $codes);
    foreach ($relcodes as $codestring) {
      if ($codestring === '') continue;
      list($codetype, $code) = explode(':', $codestring);
      if ( !($code_types[$codetype]['external']) ) { // Collect from default codes table
        $wheretype = "";
        $sqlArray = array();
        if (empty($code)) {
          $code = $codetype;
        } else {
          $wheretype = "code_type = ? AND ";
          array_push($sqlArray,$code_types[$codetype]['id']);
        }
        $sql = "SELECT code_text FROM codes WHERE " .
          "$wheretype code = ? ORDER BY id LIMIT 1";
        array_push($sqlArray,$code);
        $crow = sqlQuery($sql,$sqlArray);
        if (!empty($crow['code_text'])) {
          if ($code_text) $code_text .= '; ';
          $code_text .= $crow['code_text'];
        }
      }
      else if ($code_types[$codetype]['external'] == 1) { // Collect from ICD10 Diagnosis codeset tables
        // Ensure the icd10_dx_order_code sql table exists
        $check_table = sqlQuery("SHOW TABLES LIKE 'icd10_dx_order_code'");
        if ( !(empty($check_table)) ) {
          if ( !(empty($code)) ) {
            // Will grab from previous inactive revisions if unable to find in current revision
            $sql = "SELECT `long_desc` FROM `icd10_dx_order_code` " .
                   "WHERE `formatted_dx_code` = ? ORDER BY `revision` DESC LIMIT 1";
            $crow = sqlQuery($sql, array($code) );
            if (!empty($crow['long_desc'])) {
              if ($code_text) $code_text .= '; ';
              $code_text .= $crow['long_desc'];
            }
          }
        }
      }
      else if ($code_types[$codetype]['external'] == 2) { // Collect from SNOMED (RF1) Diagnosis codeset tables
        // Ensure the sct_concepts sql table exists
        $check_table = sqlQuery("SHOW TABLES LIKE 'sct_concepts'");
        if ( !(empty($check_table)) ) {
          if ( !(empty($code)) ) {
            $sql = "SELECT `FullySpecifiedName` FROM `sct_concepts` " .
                   "WHERE `ConceptId` = ? AND `ConceptStatus` = 0 LIMIT 1";
            $crow = sqlQuery($sql, array($code) );
            if (!empty($crow['FullySpecifiedName'])) {
              if ($code_text) $code_text .= '; ';
              $code_text .= $crow['FullySpecifiedName'];
            }
          }
        }
      }
      else if ($code_types[$codetype]['external'] == 3) { // Collect from SNOMED (RF2) Diagnosis codeset tables
        //placeholder
      }
      else if ($code_types[$codetype]['external'] == 4) { // Collect from ICD9 Diagnosis codeset tables
        // Ensure the icd9_dx_code sql table exists
        $check_table = sqlQuery("SHOW TABLES LIKE 'icd9_dx_code'");
        if ( !(empty($check_table)) ) {
          if ( !(empty($code)) ) {
            // Will grab from previous inactive revisions if unable to find in current revision
            $sql = "SELECT `long_desc` FROM `icd9_dx_code` " .
                   "WHERE `formatted_dx_code` = ? ORDER BY `revision` DESC LIMIT 1";
            $crow = sqlQuery($sql, array($code) );
            if (!empty($crow['long_desc'])) {
              if ($code_text) $code_text .= '; ';
              $code_text .= $crow['long_desc'];
            }
          }
        }
      }
      else if ($code_types[$codetype]['external'] == 5) { // Collect from ICD9 Procedure/Service codeset tables
        // Ensure the icd9_dx_code sql table exists
        $check_table = sqlQuery("SHOW TABLES LIKE 'icd9_sg_code'");
        if ( !(empty($check_table)) ) {
          if ( !(empty($code)) ) {
            // Will grab from previous inactive revisions if unable to find in current revision
            $sql = "SELECT `long_desc` FROM `icd9_sg_code` " .
                   "WHERE `formatted_sg_code` = ? ORDER BY `revision` DESC LIMIT 1";
            $crow = sqlQuery($sql, array($code) );
            if (!empty($crow['long_desc'])) {
              if ($code_text) $code_text .= '; ';
              $code_text .= $crow['long_desc'];
            }
          }
        }
      }
      else if ($code_types[$codetype]['external'] == 6) { // Collect from ICD10 PRocedure/Service codeset tables
        // Ensure the icd10_dx_order_code sql table exists
        $check_table = sqlQuery("SHOW TABLES LIKE 'icd10_pcs_order_code'");
        if ( !(empty($check_table)) ) {
          if ( !(empty($code)) ) {
            // Will grab from previous inactive revisions if unable to find in current revision
            $sql = "SELECT `long_desc` FROM `icd10_pcs_order_code` " .
                   "WHERE `pcs_code` = ? ORDER BY `revision` DESC LIMIT 1";
            $crow = sqlQuery($sql, array($code) );
            if (!empty($crow['long_desc'])) {
              if ($code_text) $code_text .= '; ';
              $code_text .= $crow['long_desc'];
            }
          }
        }
      }

      else {
        //using an external code that is not yet supported, so skip. 
      }
    }
  }
  return $code_text;
}
?>
