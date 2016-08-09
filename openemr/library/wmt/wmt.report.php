<?php
/** **************************************************************************
 *	WMT.REPORT.PHP
 *
 *	This file contains the standard functions used to generate report output.
 * 
 *  @package WMT
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */

function do_status($status, $priority, $always=false) {
	$content = "";
	if ($status || $priority) {
		$content .= "<tr><td align='center' colspan='4'>\n";
		$content .= "<table class='wmtStatus' style='margin-bottom:10px'><tr>";
		$content .= "<td class='wmtLabel' style='width:50px;min-width:50px'>Status:</td>";
		$content .= "<td class='wmtOutput'>" . ListLook($status, 'Form_Status') . "</td>";
		$content .= "<td class='wmtLabel' style='width:50px;min-width:50px'>Priority:</td>";
		$content .= "<td class='wmtOutput'>" . ListLook($priority, 'Form_Priority') . "</td>\n";
		$content .= "</tr></table></td></tr>\n";
	}
	return $content;
}

function do_block($data) {
	$content = "";
	if ($data)
		$content = "<tr><td class='wmtOutput' colspan='4'>".$data."</td></tr>\n";
	return $content;
}

function do_question($question,$data='') {
	$content = "";
	if ($question)
		$content = "<tr><td class='wmtOutput' style='font-size:14px' colspan='3'>".$question."</td><td class='wmtLabel' style='text-align:left;padding-left:20px;width:20%;vertical-align:top;font-size:14px;'>".$data."</td></tr>\n";
	return $content;
}

function do_text($data, $title='', $always=false) {
	$content = "";
	if ($data || $always) {
		if ($title) {
			$content .= "<tr><td class='wmtLabel'>".str_replace(':','',$title).": </td><td class='wmtOutput' colspan='3' style='white-space:pre-wrap'>".$data."</td></tr>\n";
		}
		else {
			$content .= "<tr><td class='wmtLabel'>&nbsp;</td><td class='wmtOutput' colspan='3' style='white-space:pre-wrap'>".$data."</td></tr>\n";
		}
	}
	return $content;
}

function do_line($data, $title='', $always=false) {
	$content = "";
	if ($data || $always) {
		if ($title) {
			$content .= "<tr><td class='wmtLabel'>".$title.": </td><td class='wmtOutput' colspan='3'>".$data."</td></tr>\n";
		}
		else {
			$content .= "<tr><td class='wmtLabel'>&nbsp;</td><td class='wmtOutput' colspan='3'>".$data."</td></tr>\n";
		}
	}
	return $content;
}

function do_columns($data1, $title1='', $data2=false, $title2='', $always=false) {
	$content = "";
	if ($data1 || $data2 || $always) {
		if ($title1) $title1 .= ": ";
		$content .= "<td class='wmtLabel'>".$title1."</td><td class='wmtOutput' style='white-space:nowrap'>".$data1."</td>\n";
	}
	if ($data2 || $always) {
		if ($title2) $title2 .= ": ";
		$content .= "<td class='wmtLabel' style='padding-left:20px;'>".$title2."</td><td class='wmtOutput'>".$data2."</td>\n";
	}
	if ($content) $content = "<tr>".$content."</tr>";
	return $content;
}

function do_blank() {
	$content .= "<tr><td class='wmtLabel' colspan='4' style='height:10px'></td></tr>\n";
	return $content;
}

function do_break() {
	$content .= "<tr><td colspan='4' style='height:15px'><hr style='border-color:#eee'/></td></tr>\n";
	return $content;
}

function do_section($data, $title='') {
	if ($data) {
		$content = "<tr><td><div class='wmtSection'>\n";
		if ($title) {
			$content .= "<div class='wmtSectionTitle'>\n";
			$content .= $title;
			$content .= "</div>";
		}
		$content .= "<div class='wmtSectionBody'>\n";
		$content .= "<table style='width:100%'>\n";
		$content .= $data;
		$content .= "</table></div></div></td></tr>\n";
		
		print $content;
	}
	return;
}
?>