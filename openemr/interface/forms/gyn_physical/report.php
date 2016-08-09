<?php
// Copyright (C) 2011 Tony McCormick <tony@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
function gyn_physical_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $data = formFetch("form_gyn_physical", $id);
  //echo "${data['general_headache']}";
  if ($data) {

  print "<table cellpadding='3px' cellspacing='0px' border=0px>";

echo "<tr><td colspan='3'><span class='bold'><u>Constitutional</u></span></td></tr>";
if ( ($data["fld_1"] != "N/A" && $data["fld_1"] != "" && $data["fld_1"] != "--") || ( $data["fld_1_textbox"] != "" && $data["fld_1_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_1'] != null && $data['fld_1'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_1']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>General Appearance (eg, development, nutrition, body habitus, deformities, attention to grooming)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_1_textbox'] != null ) {
			echo "<span class='text'>${data['fld_1_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Thyriod</u></span></td></tr>";
if ( ($data["fld_12"] != "N/A" && $data["fld_12"] != "" && $data["fld_12"] != "--") || ( $data["fld_12_textbox"] != "" && $data["fld_12_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_12'] != null && $data['fld_12'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_12']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of thyroid (eg, enlargement, tenderness, mass)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_12_textbox'] != null ) {
			echo "<span class='text'>${data['fld_12_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}

echo "<tr><td colspan='3'><span class='bold'><u>Chest (Breasts)</u></span></td></tr>";
if ( ($data["fld_24"] != "N/A" && $data["fld_24"] != "" && $data["fld_24"] != "--") || ( $data["fld_24_textbox"] != "" && $data["fld_24_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_24'] != null && $data['fld_24'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_24']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Inspection of breasts (eg, symmetry, nipple discharge)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_24_textbox'] != null ) {
			echo "<span class='text'>${data['fld_24_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_25"] != "N/A" && $data["fld_25"] != "" && $data["fld_25"] != "--") || ( $data["fld_25_textbox"] != "" && $data["fld_25_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_25'] != null && $data['fld_25'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_25']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Palpation of breasts and axillae (eg, masses or lumps, tenderness)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_25_textbox'] != null ) {
			echo "<span class='text'>${data['fld_25_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Gastrointestinal (Abdomen)</u></span></td></tr>";
if ( ($data["fld_26"] != "N/A" && $data["fld_26"] != "" && $data["fld_26"] != "--") || ( $data["fld_26_textbox"] != "" && $data["fld_26_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_26'] != null && $data['fld_26'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_26']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of abdomen with notation of presence of masses or tenderness</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_26_textbox'] != null ) {
			echo "<span class='text'>${data['fld_26_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_27"] != "N/A" && $data["fld_27"] != "" && $data["fld_27"] != "--") || ( $data["fld_27_textbox"] != "" && $data["fld_27_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_27'] != null && $data['fld_27'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_27']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of liver and spleen</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_27_textbox'] != null ) {
			echo "<span class='text'>${data['fld_27_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_28"] != "N/A" && $data["fld_28"] != "" && $data["fld_28"] != "--") || ( $data["fld_28_textbox"] != "" && $data["fld_28_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_28'] != null && $data['fld_28'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_28']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination for presence or absence of hernia</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_28_textbox'] != null ) {
			echo "<span class='text'>${data['fld_28_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_29"] != "N/A" && $data["fld_29"] != "" && $data["fld_29"] != "--") || ( $data["fld_29_textbox"] != "" && $data["fld_29_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_29'] != null && $data['fld_29'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_29']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination (when indicated) of anus, perineum and rectum, including sphincter tone, presence of hemorrhoids, rectal masses</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_29_textbox'] != null ) {
			echo "<span class='text'>${data['fld_29_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_30"] != "N/A" && $data["fld_30"] != "" && $data["fld_30"] != "--") || ( $data["fld_30_textbox"] != "" && $data["fld_30_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_30'] != null && $data['fld_30'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_30']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Obtain stool sample for occult blood test when indicated</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_30_textbox'] != null ) {
			echo "<span class='text'>${data['fld_30_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}

echo "<tr><td colspan='3'><span class='bold'><u>Female Genitourinary </u></span></td></tr>";
if ( ($data["fld_34"] != "N/A" && $data["fld_34"] != "" && $data["fld_34"] != "--") || ( $data["fld_34_textbox"] != "" && $data["fld_34_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_34'] != null && $data['fld_34'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_34']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Pelvic examination (with or without specimen collection for smears and cultures), including</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_34_textbox'] != null ) {
			echo "<span class='text'>${data['fld_34_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_35"] != "N/A" && $data["fld_35"] != "" && $data["fld_35"] != "--") || ( $data["fld_35_textbox"] != "" && $data["fld_35_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_35'] != null && $data['fld_35'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_35']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of external genitalia (eg, general appearance, hair distribution, lesions) vulva and vagina (eg, general appearance, estrogen effect, discharge, lesions, pelvic support, cystocele, rectocele)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_35_textbox'] != null ) {
			echo "<span class='text'>${data['fld_35_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_36"] != "N/A" && $data["fld_36"] != "" && $data["fld_36"] != "--") || ( $data["fld_36_textbox"] != "" && $data["fld_36_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_36'] != null && $data['fld_36'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_36']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of urethra (eg, masses, tenderness, scarring)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_36_textbox'] != null ) {
			echo "<span class='text'>${data['fld_36_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_37"] != "N/A" && $data["fld_37"] != "" && $data["fld_37"] != "--") || ( $data["fld_37_textbox"] != "" && $data["fld_37_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_37'] != null && $data['fld_37'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_37']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of bladder (eg, fullness, masses, tenderness)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_37_textbox'] != null ) {
			echo "<span class='text'>${data['fld_37_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_38"] != "N/A" && $data["fld_38"] != "" && $data["fld_38"] != "--") || ( $data["fld_38_textbox"] != "" && $data["fld_38_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_38'] != null && $data['fld_38'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_38']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Cervix (eg, general appearance, lesions, discharge)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_38_textbox'] != null ) {
			echo "<span class='text'>${data['fld_38_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_39"] != "N/A" && $data["fld_39"] != "" && $data["fld_39"] != "--") || ( $data["fld_39_textbox"] != "" && $data["fld_39_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_39'] != null && $data['fld_39'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_39']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Uterus (eg, size, contour, position, mobility, tenderness, consistency, descent or support)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_39_textbox'] != null ) {
			echo "<span class='text'>${data['fld_39_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["fld_40"] != "N/A" && $data["fld_40"] != "" && $data["fld_40"] != "--") || ( $data["fld_40_textbox"] != "" && $data["fld_40_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['fld_40'] != null && $data['fld_40'] != 'N/A' ) {
			echo "<span class='text'>${data['fld_40']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Adnexa/parametria (eg, masses, tenderness, organomegaly, nodularity)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['fld_40_textbox'] != null ) {
			echo "<span class='text'>${data['fld_40_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}

  print "</table>";
  }

}
?>
