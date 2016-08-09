<?php
/*
 * The page shown when the user requests to see this form in a "report view". does not allow editing contents, or saving. has 'print' and 'delete' buttons.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for display_layout_rows(), ?? */
require_once($GLOBALS['srcdir'].'/options.inc.php');

/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_urinalysis_report';

/** CHANGE THIS name to the name of your form. **/
$form_name = 'Urinalysis Report';

/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = 'urinalysis_report';

/* Check the access control lists to ensure permissions to this page */
$thisauth = acl_check('patients', 'med');
if (!$thisauth) {
 die($form_name.': Access Denied.');
}
/* perform a squad check for pages touching patients, if we're in 'athletic team' mode */
if ($GLOBALS['athletic_team']!='false') {
  $tmp = getPatientData($pid, 'squad');
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
}
/* Use the formFetch function from api.inc to load the saved record */
$xyzzy = formFetch($table_name, $_GET['id']);

/* in order to use the layout engine's draw functions, we need a fake table of layout data. */
$manual_layouts = array( 
 'collection_date' => 
   array( 'field_id' => 'collection_date',
          'data_type' => '4',
          'fld_length' => '0',
          'description' => '',
          'list_id' => '' ),
 'test_date' => 
   array( 'field_id' => 'test_date',
          'data_type' => '4',
          'fld_length' => '0',
          'description' => '',
          'list_id' => '' ),
 'physician' => 
   array( 'field_id' => 'physician',
          'data_type' => '10',
          'fld_length' => '0',
          'description' => '',
          'list_id' => '' ),
 'testers_initials' => 
   array( 'field_id' => 'testers_initials',
          'data_type' => '2',
          'fld_length' => '40',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'exam_color' => 
   array( 'field_id' => 'exam_color',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Urinalysis_Physical_Exam_Color' ),
 'exam_appearance' => 
   array( 'field_id' => 'exam_appearance',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Urinalysis_Physical_Exam_Appear' ),
 'chemical_exam_specific_gravity' => 
   array( 'field_id' => 'chemical_exam_specific_gravity',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Spec_Grav' ),
 'chemical_exam_ph' => 
   array( 'field_id' => 'chemical_exam_ph',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Ph' ),
 'chemical_exam_leukocytes' => 
   array( 'field_id' => 'chemical_exam_leukocytes',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Leukocytes' ),
 'chemical_exam_nitrate' => 
   array( 'field_id' => 'chemical_exam_nitrate',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Nitrate' ),
 'chemical_exam_protein' => 
   array( 'field_id' => 'chemical_exam_protein',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Protein' ),
 'chemical_exam_glucose' => 
   array( 'field_id' => 'chemical_exam_glucose',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Glucose' ),
 'chemical_exam_ketones' => 
   array( 'field_id' => 'chemical_exam_ketones',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Ketones' ),
 'chemical_exam_urobilinogen' => 
   array( 'field_id' => 'chemical_exam_urobilinogen',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Urobilinog' ),
 'chemical_exam_bilirubin' => 
   array( 'field_id' => 'chemical_exam_bilirubin',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Bilirubin' ),
 'chemical_exam_blood' => 
   array( 'field_id' => 'chemical_exam_blood',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Blood' ),
 'chemical_exam_hemoglobin' => 
   array( 'field_id' => 'chemical_exam_hemoglobin',
          'data_type' => '27',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'Urinalysis_Chem_Exam_Hemoglobin' ),
 'comments' => 
   array( 'field_id' => 'comments',
          'data_type' => '3',
          'fld_length' => '151',
          'max_length' => '4',
          'description' => '',
          'list_id' => '' )
 );

/* since we have no-where to return, abuse returnurl to link to the 'edit' page */
/* FIXME: pass the ID, create blank rows if necissary. */
$returnurl = "../../forms/$form_folder/view.php?mode=noencounter";

/* remove the time-of-day from all date fields */
if ($xyzzy['collection_date'] != '') {
    $dateparts = split(' ', $xyzzy['collection_date']);
    $xyzzy['collection_date'] = $dateparts[0];
}
if ($xyzzy['test_date'] != '') {
    $dateparts = split(' ', $xyzzy['test_date']);
    $xyzzy['test_date'] = $dateparts[0];
}

/* define check field functions. used for translating from fields to html viewable strings */

function chkdata_Date(&$record, $var) {
        return htmlspecialchars($record{"$var"},ENT_QUOTES);
}

function chkdata_Txt(&$record, $var) {
        return htmlspecialchars($record{"$var"},ENT_QUOTES);
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<!-- declare this document as being encoded in UTF-8 -->
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" ></meta>

<!-- supporting javascript code -->
<!-- for dialog -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<!-- For jquery, required by edit, print, and delete buttons. -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>

<!-- Global Stylesheet -->
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css"/>
<!-- Form Specific Stylesheet. -->
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css"/>

<script type="text/javascript">

<!-- FIXME: this needs to detect access method, and construct a URL appropriately! -->
function PrintForm() {
    newwin = window.open("<?php echo $rootdir.'/forms/'.$form_folder.'/print.php?id='.$_GET['id']; ?>","print_<?php echo $form_name; ?>");
}

</script>
<title><?php echo htmlspecialchars('Show '.$form_name); ?></title>

</head>
<body class="body_top">

<div id="title">
<span class="title"><?php xl($form_name,'e'); ?></span>
<?php
 if ($thisauth == 'write' || $thisauth == 'addonly')
  { ?>
<a href="<?php echo $returnurl; ?>" onclick="top.restoreSession()">
<span class="back"><?php xl($tmore,'e'); ?></span>
</a>
<?php }; ?>
</div>

<form method="post" id="<?php echo $form_folder; ?>" action="">

<!-- container for the main body of the form -->
<div id="form_container">

<div id="show">

<!-- display the form's manual based fields -->
<table border='0' cellpadding='0' width='100%'>
<tr><td class='sectionlabel'>General</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Collection Date','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['collection_date'], $xyzzy['collection_date']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Test Date','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['test_date'], $xyzzy['test_date']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Physician','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['physician'], $xyzzy['physician']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Testers Initials','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['testers_initials'], $xyzzy['testers_initials']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields)0--></tr>
<tr><td class='sectionlabel'>Physical Examination</td><!-- called consumeRows 012--> </tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <!-- called consumeRows 212--> <!-- Exiting not($fields)0--></tr>
<tr><td class='sectionlabel'>Chemical Examination</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Specific Gravity','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_specific_gravity'], $xyzzy['chemical_exam_specific_gravity']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('pH','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_ph'], $xyzzy['chemical_exam_ph']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Leukocytes','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_leukocytes'], $xyzzy['chemical_exam_leukocytes']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Nitrate','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_nitrate'], $xyzzy['chemical_exam_nitrate']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Protein (mg/dL)','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_protein'], $xyzzy['chemical_exam_protein']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Glucose (mg/dL)','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_glucose'], $xyzzy['chemical_exam_glucose']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Ketones','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_ketones'], $xyzzy['chemical_exam_ketones']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Urobilinogen (mg/dL)','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_urobilinogen'], $xyzzy['chemical_exam_urobilinogen']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Bilirubin','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_bilirubin'], $xyzzy['chemical_exam_bilirubin']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Blood (ery/ul)','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_blood'], $xyzzy['chemical_exam_blood']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Hemoglobin (ery/ul)','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['chemical_exam_hemoglobin'], $xyzzy['chemical_exam_hemoglobin']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Comments','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['comments'], $xyzzy['comments']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields)0--></tr>
</table>


</div><!-- end show -->

</div><!-- end form_container -->

<!-- Print button -->
<div id="button_bar" class="button_bar">
<fieldset class="button_bar">
<input type="button" class="print" value="<?php xl('Print','e'); ?>" />
</fieldset>
</div><!-- end button_bar -->

</form>
<script type="text/javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".print").click(function() { PrintForm(); });
});
</script>
</body>
</html>

