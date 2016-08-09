<?php
/** **************************************************************************
 *	LABORATORY/CREATE.PHP
 *
 *	Copyright (c)2014 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package mdts
 *  @subpackage laboratory
 *  @version 2.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/acl.inc");

$mode = 'NEW';
if (empty($_REQUEST['pop'])) include_once("$incdir/patient_file/encounter/new_form.php");

// retrieve selected provider
if (! $lab_id) $lab_id = $_POST['lab_id'];
//$query = "SELECT * FROM procedure_providers WHERE (type = 'laboratory' OR type = 'quest' OR type = 'labcorp') AND ppid = ? ";
$query = "SELECT * FROM procedure_providers WHERE type = 'laboratory' AND ppid = ? ";
$lab = sqlQuery($query,array($lab_id));

// load proper laboratory form
if ($lab['type'] == 'quest') {
	if (file_exists('../quest/common.php')) require('../quest/common.php');
	else die("Quest Laboratory modules not installed!!");
}
elseif ($lab['type'] == 'labcorp') {
	if (file_exists('../labcorp/common.php')) require('../labcorp/common.php');
	else die("LabCorp Laboratory modules not installed!!");
}
else {
	require('common.php');
}
?>
