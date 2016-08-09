<?php
// Copyright (C) 2011 Tony McCormick <tony@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");

/**
 * class FormGYN{Primary}hysical
 *
 */
class FormGYNPhysical extends ORDataObject {

	/**
	 *
	 * @access public
	 */


	/**
	 *
	 * static
	 */
	var $id;
	var $date;
	var $pid;
	var $user;
	var $groupname;
	var $authorized;
	var $activity;
	 
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormGYNPhysical($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";
			$this->date = date("Y-m-d H:i:s");	
		}
		
		$this->_table = "form_gyn_physical";
		$this->activity = 1;
		$this->pid = $GLOBALS['pid'];
		if ($id != "") {
			$this->populate();
			//$this->date = $this->get_date();
		}
	}
	function populate() {
		parent::populate();
		//$this->temp_methods = parent::_load_enum("temp_locations",false);		
	}

	function toString($html = false) {
		$string .= "\n"
			."ID: " . $this->id . "\n";

		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}
	function set_id($id) {
		if (!empty($id) && is_numeric($id)) {
			$this->id = $id;
		}
	}
	function get_id() {
		return $this->id;
	}
	function set_pid($pid) {
		if (!empty($pid) && is_numeric($pid)) {
			$this->pid = $pid;
		}
	}
	function get_pid() {
		return $this->pid;
	}
	function set_activity($tf) {
		if (!empty($tf) && is_numeric($tf)) {
			$this->activity = $tf;
		}
	}
	function get_activity() {
		return $this->activity;
	}
	
	function get_date() {
		return $this->date;
	}
	function set_date($dt) {
		if (!empty($dt)) {
			$this->date = $dt;
		}
	}
	function get_user() {
		return $this->user;
	}
	function set_user($u) {
		if(!empty($u)){
			$this->user = $u;
		}
	}
	
	function persist() {
		parent::persist();
	}
	


	// ----- General Appearance (eg, development, nutrition, body habitus, deformities, attention to grooming) -----

	var $fld_1;
	var $fld_1_textbox;
	function get_fld_1() {
		return $this->fld_1;
	}
	function set_fld_1($data) {
			$this->fld_1 = $data;
	}
	function get_fld_1_wnl() {
		return $this->fld_1 == "WNL" ? "CHECKED" : "";	}
	function get_fld_1_abn() {
		return $this->fld_1 == "ABN" ? "CHECKED" : "";	}
	function get_fld_1_na() {
		return ($this->fld_1 == "N/A" or  $this->fld_1 == "") ? "CHECKED" : "";	}
	function get_fld_1_textbox() {
		return $this->fld_1_textbox;
	}
	function set_fld_1_textbox($data) {
			$this->fld_1_textbox = $data;
	}
		
	var $fld_12;
	var $fld_12_textbox;
	function get_fld_12() {
		return $this->fld_12;
	}
	function set_fld_12($data) {
			$this->fld_12 = $data;
	}
	function get_fld_12_wnl() {
		return $this->fld_12 == "WNL" ? "CHECKED" : "";	}
	function get_fld_12_abn() {
		return $this->fld_12 == "ABN" ? "CHECKED" : "";	}
	function get_fld_12_na() {
		return ($this->fld_12 == "N/A" or $this->fld_12 == "") ? "CHECKED" : "";	}
	function get_fld_12_textbox() {
		return $this->fld_12_textbox;
	}
	function set_fld_12_textbox($data) {
			$this->fld_12_textbox = $data;
	}
	
	// ----- Palpation of heart (eg, location, size, thrills) -----

	var $fld_17;
	var $fld_17_textbox;
	function get_fld_17() {
		return $this->fld_17;
	}
	function set_fld_17($data) {
			$this->fld_17 = $data;
	}
	function get_fld_17_wnl() {
		return $this->fld_17 == "WNL" ? "CHECKED" : "";	}
	function get_fld_17_abn() {
		return $this->fld_17 == "ABN" ? "CHECKED" : "";	}
	function get_fld_17_na() {
		return ($this->fld_17 == "N/A" or $this->fld_17 == "") ? "CHECKED" : "";	}
	function get_fld_17_textbox() {
		return $this->fld_17_textbox;
	}
	function set_fld_17_textbox($data) {
			$this->fld_17_textbox = $data;
	}
	
	// ----- Inspection of breasts (eg, symmetry, nipple discharge) -----

	var $fld_24;
	var $fld_24_textbox;
	function get_fld_24() {
		return $this->fld_24;
	}
	function set_fld_24($data) {
			$this->fld_24 = $data;
	}
	function get_fld_24_wnl() {
		return $this->fld_24 == "WNL" ? "CHECKED" : "";	}
	function get_fld_24_abn() {
		return $this->fld_24 == "ABN" ? "CHECKED" : "";	}
	function get_fld_24_na() {
		return ($this->fld_24 == "N/A" or $this->fld_24 == "") ? "CHECKED" : "";	}
	function get_fld_24_textbox() {
		return $this->fld_24_textbox;
	}
	function set_fld_24_textbox($data) {
			$this->fld_24_textbox = $data;
	}
	
	
	var $fld_25;
	var $fld_25_textbox;
	function get_fld_25() {
		return $this->fld_25;
	}
	function set_fld_25($data) {
			$this->fld_25 = $data;
	}
	function get_fld_25_wnl() {
		return $this->fld_25 == "WNL" ? "CHECKED" : "";	}
	function get_fld_25_abn() {
		return $this->fld_25 == "ABN" ? "CHECKED" : "";	}
	function get_fld_25_na() {
		return ($this->fld_25 == "N/A" or $this->fld_25 == "") ? "CHECKED" : "";	}
	function get_fld_25_textbox() {
		return $this->fld_25_textbox;
	}
	function set_fld_25_textbox($data) {
			$this->fld_25_textbox = $data;
	}
	
	// ----- Examination of abdomen with notation of presence of masses or tenderness -----

	var $fld_26;
	var $fld_26_textbox;
	function get_fld_26() {
		return $this->fld_26;
	}
	function set_fld_26($data) {
			$this->fld_26 = $data;
	}
	function get_fld_26_wnl() {
		return $this->fld_26 == "WNL" ? "CHECKED" : "";	}
	function get_fld_26_abn() {
		return $this->fld_26 == "ABN" ? "CHECKED" : "";	}
	function get_fld_26_na() {
		return ($this->fld_26 == "N/A" or $this->fld_26 == "") ? "CHECKED" : "";	}
	function get_fld_26_textbox() {
		return $this->fld_26_textbox;
	}
	function set_fld_26_textbox($data) {
			$this->fld_26_textbox = $data;
	}
	
	
	var $fld_27;
	var $fld_27_textbox;
	function get_fld_27() {
		return $this->fld_27;
	}
	function set_fld_27($data) {
			$this->fld_27 = $data;
	}
	function get_fld_27_wnl() {
		return $this->fld_27 == "WNL" ? "CHECKED" : "";	}
	function get_fld_27_abn() {
		return $this->fld_27 == "ABN" ? "CHECKED" : "";	}
	function get_fld_27_na() {
		return ($this->fld_27 == "N/A" or $this->fld_27 == "") ? "CHECKED" : "";	}
	function get_fld_27_textbox() {
		return $this->fld_27_textbox;
	}
	function set_fld_27_textbox($data) {
			$this->fld_27_textbox = $data;
	}
	
	
	var $fld_28;
	var $fld_28_textbox;
	function get_fld_28() {
		return $this->fld_28;
	}
	function set_fld_28($data) {
			$this->fld_28 = $data;
	}
	function get_fld_28_wnl() {
		return $this->fld_28 == "WNL" ? "CHECKED" : "";	}
	function get_fld_28_abn() {
		return $this->fld_28 == "ABN" ? "CHECKED" : "";	}
	function get_fld_28_na() {
		return ($this->fld_28 == "N/A" or $this->fld_28 == "") ? "CHECKED" : "";	}
	function get_fld_28_textbox() {
		return $this->fld_28_textbox;
	}
	function set_fld_28_textbox($data) {
			$this->fld_28_textbox = $data;
	}
	
	
	var $fld_29;
	var $fld_29_textbox;
	function get_fld_29() {
		return $this->fld_29;
	}
	function set_fld_29($data) {
			$this->fld_29 = $data;
	}
	function get_fld_29_wnl() {
		return $this->fld_29 == "WNL" ? "CHECKED" : "";	}
	function get_fld_29_abn() {
		return $this->fld_29 == "ABN" ? "CHECKED" : "";	}
	function get_fld_29_na() {
		return ($this->fld_29 == "N/A" or $this->fld_29 == "") ? "CHECKED" : "";	}
	function get_fld_29_textbox() {
		return $this->fld_29_textbox;
	}
	function set_fld_29_textbox($data) {
			$this->fld_29_textbox = $data;
	}
	
	
	var $fld_30;
	var $fld_30_textbox;
	function get_fld_30() {
		return $this->fld_30;
	}
	function set_fld_30($data) {
			$this->fld_30 = $data;
	}
	function get_fld_30_wnl() {
		return $this->fld_30 == "WNL" ? "CHECKED" : "";	}
	function get_fld_30_abn() {
		return $this->fld_30 == "ABN" ? "CHECKED" : "";	}
	function get_fld_30_na() {
		return ($this->fld_30 == "N/A" or $this->fld_30 == "") ? "CHECKED" : "";	}
	function get_fld_30_textbox() {
		return $this->fld_30_textbox;
	}
	function set_fld_30_textbox($data) {
			$this->fld_30_textbox = $data;
	}
	
	// ----- Pelvic examination (with or without specimen collection for smears and cultures), including -----

	var $fld_34;
	var $fld_34_textbox;
	function get_fld_34() {
		return $this->fld_34;
	}
	function set_fld_34($data) {
			$this->fld_34 = $data;
	}
	function get_fld_34_wnl() {
		return $this->fld_34 == "WNL" ? "CHECKED" : "";	}
	function get_fld_34_abn() {
		return $this->fld_34 == "ABN" ? "CHECKED" : "";	}
	function get_fld_34_na() {
		return ($this->fld_34 == "N/A" or $this->fld_34 == "") ? "CHECKED" : "";	}
	function get_fld_34_textbox() {
		return $this->fld_34_textbox;
	}
	function set_fld_34_textbox($data) {
			$this->fld_34_textbox = $data;
	}
	
	
	var $fld_35;
	var $fld_35_textbox;
	function get_fld_35() {
		return $this->fld_35;
	}
	function set_fld_35($data) {
			$this->fld_35 = $data;
	}
	function get_fld_35_wnl() {
		return $this->fld_35 == "WNL" ? "CHECKED" : "";	}
	function get_fld_35_abn() {
		return $this->fld_35 == "ABN" ? "CHECKED" : "";	}
	function get_fld_35_na() {
		return ($this->fld_35 == "N/A" or $this->fld_35 == "") ? "CHECKED" : "";	}
	function get_fld_35_textbox() {
		return $this->fld_35_textbox;
	}
	function set_fld_35_textbox($data) {
			$this->fld_35_textbox = $data;
	}
	
	
	var $fld_36;
	var $fld_36_textbox;
	function get_fld_36() {
		return $this->fld_36;
	}
	function set_fld_36($data) {
			$this->fld_36 = $data;
	}
	function get_fld_36_wnl() {
		return $this->fld_36 == "WNL" ? "CHECKED" : "";	}
	function get_fld_36_abn() {
		return $this->fld_36 == "ABN" ? "CHECKED" : "";	}
	function get_fld_36_na() {
		return ($this->fld_36 == "N/A" or $this->fld_36 == "") ? "CHECKED" : "";	}
	function get_fld_36_textbox() {
		return $this->fld_36_textbox;
	}
	function set_fld_36_textbox($data) {
			$this->fld_36_textbox = $data;
	}
	
	
	var $fld_37;
	var $fld_37_textbox;
	function get_fld_37() {
		return $this->fld_37;
	}
	function set_fld_37($data) {
			$this->fld_37 = $data;
	}
	function get_fld_37_wnl() {
		return $this->fld_37 == "WNL" ? "CHECKED" : "";	}
	function get_fld_37_abn() {
		return $this->fld_37 == "ABN" ? "CHECKED" : "";	}
	function get_fld_37_na() {
		return ($this->fld_37 == "N/A" or $this->fld_37 == "") ? "CHECKED" : "";	}
	function get_fld_37_textbox() {
		return $this->fld_37_textbox;
	}
	function set_fld_37_textbox($data) {
			$this->fld_37_textbox = $data;
	}
	
	
	var $fld_38;
	var $fld_38_textbox;
	function get_fld_38() {
		return $this->fld_38;
	}
	function set_fld_38($data) {
			$this->fld_38 = $data;
	}
	function get_fld_38_wnl() {
		return $this->fld_38 == "WNL" ? "CHECKED" : "";	}
	function get_fld_38_abn() {
		return $this->fld_38 == "ABN" ? "CHECKED" : "";	}
	function get_fld_38_na() {
		return ($this->fld_38 == "N/A" or $this->fld_38 == "") ? "CHECKED" : "";	}
	function get_fld_38_textbox() {
		return $this->fld_38_textbox;
	}
	function set_fld_38_textbox($data) {
			$this->fld_38_textbox = $data;
	}
	
	
	var $fld_39;
	var $fld_39_textbox;
	function get_fld_39() {
		return $this->fld_39;
	}
	function set_fld_39($data) {
			$this->fld_39 = $data;
	}
	function get_fld_39_wnl() {
		return $this->fld_39 == "WNL" ? "CHECKED" : "";	}
	function get_fld_39_abn() {
		return $this->fld_39 == "ABN" ? "CHECKED" : "";	}
	function get_fld_39_na() {
		return ($this->fld_39 == "N/A" or $this->fld_39 == "") ? "CHECKED" : "";	}
	function get_fld_39_textbox() {
		return $this->fld_39_textbox;
	}
	function set_fld_39_textbox($data) {
			$this->fld_39_textbox = $data;
	}
	
	
	var $fld_40;
	var $fld_40_textbox;
	function get_fld_40() {
		return $this->fld_40;
	}
	function set_fld_40($data) {
			$this->fld_40 = $data;
	}
	function get_fld_40_wnl() {
		return $this->fld_40 == "WNL" ? "CHECKED" : "";	}
	function get_fld_40_abn() {
		return $this->fld_40 == "ABN" ? "CHECKED" : "";	}
	function get_fld_40_na() {
		return ($this->fld_40 == "N/A" or $this->fld_40 == "") ? "CHECKED" : "";	}
	function get_fld_40_textbox() {
		return $this->fld_40_textbox;
	}
	function set_fld_40_textbox($data) {
			$this->fld_40_textbox = $data;
	}
						
}	// end of Form

?>
