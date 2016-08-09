<?php
/*
 * this file's contents are included in both the encounter page as a 'quick summary' of a form, and in the medical records' reports page.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for generate_form_field, ?? */
require_once($GLOBALS['srcdir'].'/options.inc.php');
/* The name of the function is significant and must match the folder name */
function urinalysis_report_report( $pid, $encounter, $cols, $id) {
    $count = 0;
/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_urinalysis_report';


/* an array of all of the fields' names and their types. */
$field_names = array('collection_date' => 'date','test_date' => 'date','physician' => 'provider','testers_initials' => 'textfield','exam_color' => 'checkbox_combo_list','exam_appearance' => 'checkbox_combo_list','chemical_exam_specific_gravity' => 'radio_list','chemical_exam_ph' => 'radio_list','chemical_exam_leukocytes' => 'radio_list','chemical_exam_nitrate' => 'radio_list','chemical_exam_protein' => 'radio_list','chemical_exam_glucose' => 'radio_list','chemical_exam_ketones' => 'radio_list','chemical_exam_urobilinogen' => 'radio_list','chemical_exam_bilirubin' => 'radio_list','chemical_exam_blood' => 'radio_list','chemical_exam_hemoglobin' => 'radio_list','comments' => 'textarea');/* in order to use the layout engine's draw functions, we need a fake table of layout data. */
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
/* an array of the lists the fields may draw on. */
$lists = array();
    $data = formFetch($table_name, $id);
    if ($data) {

        echo '<table><tr>';

        foreach($data as $key => $value) {
            if ($key == 'id' || $key == 'pid' || $key == 'user' ||
                $key == 'groupname' || $key == 'authorized' ||
                $key == 'activity' || $key == 'date' || 
                $value == '' || $value == '0000-00-00 00:00:00' ||
                $value == 'n')
            {
                /* skip built-in fields and "blank data". */
	        continue;
            }

            /* display 'yes' instead of 'on'. */
            if ($value == 'on') {
                $value = 'yes';
            }

            /* remove the time-of-day from the 'date' fields. */
            if ($field_names[$key] == 'date')
            if ($value != '') {
              $dateparts = split(' ', $value);
              $value = $dateparts[0];
            }
            
            if ( $field_names[$key] == 'checkbox_combo_list' ||
            	$field_names[$key] == 'radio_list' ) {
                $value = generate_display_field( $manual_layouts[$key], $value );
            }

            /* replace underscores with spaces, and uppercase all words. */
            /* this is a primitive form of converting the column names into something displayable. */
            $key=ucwords(str_replace('_',' ',$key));
            $mykey = $key;
            $myval = $value;
            echo '<td><span class=bold>'.xl("$mykey").': </span><span class=text>'.xl("$myval").'</span></td>';


            $count++;
            if ($count == $cols) {
                $count = 0;
                echo '</tr><tr>' . PHP_EOL;
            }
        }
    }
    echo '</tr></table><hr>';
}
?>

