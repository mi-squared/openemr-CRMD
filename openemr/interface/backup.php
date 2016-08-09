<?php
require_once("globals.php");
use ESign\Api;
$s=sqlStatement("select pid from patient_data order by lname");
$ar=array();
while($row=sqlFetchArray($s))
{
 $ar[]=$row['pid'];
}
deleteDir(realpath('../backup'));
function deleteDir($dirPath)
{
   if(! is_dir($dirPath))
   throw new InvalidArgumentException("$dirPath must be a directory");
   if(substr($dirPath,strlen($dirPath)-1,1)!= '/')
   $dirPath .= '/';
   $files = glob($dirPath . '*', GLOB_MARK);
   foreach($files as $file)
   {
      if(is_dir($file))
       deleteDir($file);
       else
       unlink($file);
   }
   rmdir($dirPath);
}
function getContent()
{
  global $web_root, $webserver_root;
  $content = ob_get_clean();
  // Fix a nasty html2pdf bug - it ignores document root!
  $i = 0;
  $wrlen = strlen($web_root);
  $wsrlen = strlen($webserver_root);
  while (true) {
    $i = stripos($content, " src='/", $i + 1);
    if ($i === false) break;
    if (substr($content, $i+6, $wrlen) === $web_root &&
        substr($content, $i+6, $wsrlen) !== $webserver_root)
    {
      $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
    }
  }
  return $content;
}
function postToGet($arin)
{
  $getstring="";
  foreach ($arin as $key => $val) {
    if (is_array($val)) {
      foreach ($val as $k => $v) {
        $getstring .= urlencode($key . "[]") . "=" . urlencode($v) . "&";
      }
    }
    else {
      $getstring .= urlencode($key) . "=" . urlencode($val) . "&";
    }
  }
  return $getstring;
}
function category_tree($catid,$tree=array())
{
 $s=sqlQuery("select name,parent from categories where id=$catid");
 $tree[]=$s['name'];
 if($s['parent']>0)
 return category_tree($s['parent'],$tree);
 else
 return $tree;
}
if(isset($_POST)&&$_POST['pdf'])
{
 if(!is_dir("../backup"))
 mkdir("../backup",0777);
 $my_file=fopen('../backup/.backup','a');
 fwrite($my_file,'backup');
 fclose($my_file);
 require_once("$srcdir/forms.inc");
 require_once("$srcdir/billing.inc");
 require_once("$srcdir/pnotes.inc");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/lists.inc");
 require_once("$srcdir/report.inc");
 require_once("$srcdir/classes/Document.class.php");
 require_once("$srcdir/classes/Note.class.php");
 require_once("$srcdir/formatting.inc.php");
 require_once("$srcdir/htmlspecialchars.inc.php");
 require_once("$srcdir/formdata.inc.php");
 require_once(dirname(__file__) . "/../custom/code_types.inc.php");
 require_once $GLOBALS['srcdir'].'/ESign/Api.php';
 require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");
 if ($GLOBALS['gbl_portal_cms_enable'])
 require_once($GLOBALS["include_root"] . "/cmsportal/portal.inc.php");

 function patient_pdf($pid)
 {
 global $srcdir;
 $res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, forms.formdir, forms.date AS fdate, form_encounter.date, form_encounter.reason FROM forms, form_encounter WHERE forms.pid = '$pid' AND form_encounter.pid = '$pid' AND form_encounter.encounter = forms.encounter AND forms.deleted=0 ORDER BY form_encounter.date DESC, fdate ASC");
 while($row=sqlFetchArray($res))
 {
  $_POST[$row['formdir']."_".$row['form_id']]=$row['encounter'];
 }
 $_POST['documents']=array();
 $res = sqlStatement("select id from documents where foreign_id='$pid'");
 while($row=sqlFetchArray($res))
 {
  $_POST['documents'][]=$row['id'];
 }
// For those who care that this is the patient report.
$GLOBALS['PATIENT_REPORT_ACTIVE'] = true;

$PDF_OUTPUT = empty($_POST['pdf']) ? 0 : intval($_POST['pdf']);

if ($PDF_OUTPUT) {
  require_once("$srcdir/html2pdf/vendor/autoload.php");
  $pdf = new HTML2PDF ($GLOBALS['pdf_layout'],
                       $GLOBALS['pdf_size'],
                       $GLOBALS['pdf_language'],
                       true, // default unicode setting is true
                       'UTF-8', // default encoding setting is UTF-8
                       array($GLOBALS['pdf_left_margin'],$GLOBALS['pdf_top_margin'],$GLOBALS['pdf_right_margin'],$GLOBALS['pdf_bottom_margin']),
                       $_SESSION['language_direction'] == 'rtl' ? true : false
                      );
  //set 'dejavusans' for now. which is supported by a lot of languages - http://dejavu-fonts.org/wiki/Main_Page
  //TODO: can have this selected as setting in globals after we have more experience with this to fully support internationalization.
  $pdf->setDefaultFont('dejavusans');

  ob_start();
}

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

$esignApi = new Api();

// Number of columns in tables for insurance and encounter forms.
$N = $PDF_OUTPUT ? 4 : 6;

$first_issue = 1;

?>

<?php if ($PDF_OUTPUT) { ?>
<link rel="stylesheet" href="<?php echo  $webserver_root . '/interface/themes/style_pdf.css' ?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $webserver_root; ?>/library/ESign/css/esign_report.css" />
<?php } ?>

<?php // do not show stuff from report.php in forms that is encaspulated
      // by div of navigateLink class. Specifically used for CAMOS, but
      // can also be used by other forms that require output in the 
      // encounter listings output, but not in the custom report. ?>
<style>
  div.navigateLink {display:none;}
  .hilite {background-color: #FFFF00;}
  .hilite2 {background-color: transparent;}
  mark {background-color: #FFFF00;}
  .css_button{cursor:pointer;}
  .next {background-color: #FFFF00;}
  #search_options{
    position:fixed;
    left:0px;
    top:0px;
    z-index:10;
    border-bottom: solid thin #6D6D6D;
    padding:0% 2% 0% 2.5%;
  }
</style>

<div id="report_custom" style="width:100%;">  <!-- large outer DIV -->

<?php
if (sizeof($_GET) > 0) { $ar = $_GET; }
else { $ar = $_POST; }

if ($PDF_OUTPUT) {
  /*******************************************************************
  $titleres = getPatientData($pid, "fname,lname,providerID");
  $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  *******************************************************************/
  $titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
  if ($_SESSION['pc_facility']) {
    $sql = "select * from facility where id=" . $_SESSION['pc_facility'];
  } else {
    $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  }
  /******************************************************************/
  $db = $GLOBALS['adodb']['db'];
  $results = $db->Execute($sql);
  $facility = array();
  if (!$results->EOF) {
    $facility = $results->fields;
  }
  // Setup Headers and Footers for html2PDF only Download
  // in HTML view it's just one line at the top of page 1
  echo '<page_header style="text-align:right;" class="custom-tag"> ' . xlt("PATIENT") . ':' . text($titleres['lname']) . ', ' . text($titleres['fname']) . ' - ' . $titleres['DOB_TS'] . '</page_header>    ';
  echo '<page_footer style="text-align:right;" class="custom-tag">' . xlt('Generated on') . ' ' . oeFormatShortDate() . ' - ' . text($facility['name']) . ' ' . text($facility['phone']) . '</page_footer>';

  // Use logo if it exists as 'practice_logo.gif' in the site dir
  // old code used the global custom dir which is no longer a valid
   $practice_logo = "$OE_SITE_DIR/images/practice_logo.gif";
   if (file_exists($practice_logo)) {
        echo "<img src='$practice_logo' align='left'><br />\n";
     } 
?>
<h2><?php echo $facility['name'] ?></h2>
<?php echo $facility['street'] ?><br>
<?php echo $facility['city'] ?>, <?php echo $facility['state'] ?> <?php echo $facility['postal_code'] ?><br clear='all'>
<?php echo $facility['phone'] ?><br>

<a href="javascript:window.close();"><span class='title'><?php echo $titleres['fname'] . " " . $titleres['lname']; ?></span></a><br>
<span class='text'><?php xl('Generated on','e'); ?>: <?php echo oeFormatShortDate(); ?></span>
<br><br>
<?php
}

// include ALL form's report.php files
$inclookupres = sqlStatement("select distinct formdir from forms where pid = '$pid' AND deleted=0");
while($result = sqlFetchArray($inclookupres)) {
  // include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
  $formdir = $result['formdir'];
  if (substr($formdir,0,3) == 'LBF')
    include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
  else
    include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
}

// For each form field from patient_report.php...
//
foreach ($ar as $key => $val) {
    if ($key == 'pdf') continue;

    // These are the top checkboxes (demographics, allergies, etc.).
    //
    if (stristr($key,"include_")) {

        if ($val == "demographics") {
            
            echo "<hr />";
            echo "<div class='text demographics' id='DEM'>\n";
            print "<h1>".xl('Patient Data').":</h1>";
            // printRecDataOne($patient_data_array, getRecPatientData ($pid), $N);
            $result1 = getPatientData($pid);
            $result2 = getEmployerData($pid);
            echo "   <table>\n";
            display_layout_rows('DEM', $result1, $result2);
            echo "   </table>\n";
            echo "</div>\n";

        } elseif ($val == "history") {

            echo "<hr />";
            echo "<div class='text history' id='HIS'>\n";
            if (acl_check('patients', 'med')) {
                print "<h1>".xl('History Data').":</h1>";
                // printRecDataOne($history_data_array, getRecHistoryData ($pid), $N);
                $result1 = getHistoryData($pid);
                echo "   <table>\n";
                display_layout_rows('HIS', $result1);
                echo "   </table>\n";
            }
            echo "</div>";

        } elseif ($val == "employer") {
               print "<br><span class='bold'>".xl('Employer Data').":</span><br>";
               printRecDataOne($employer_data_array, getRecEmployerData ($pid), $N);
        } elseif ($val == "insurance") {

            echo "<hr />";
            echo "<div class='text insurance'>";
            echo "<h1>".xl('Insurance Data').":</h1>";
            print "<br><span class=bold>".xl('Primary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"primary"), $N);		
            print "<span class=bold>".xl('Secondary Insurance Data').":</span><br>";	
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"secondary"), $N);
            print "<span class=bold>".xl('Tertiary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"tertiary"), $N);
            echo "</div>";

        } elseif ($val == "billing") {

            echo "<hr />";
            echo "<div class='text billing'>";
            print "<h1>".xl('Billing Information').":</h1>";
            if (count($ar['newpatient']) > 0) {
                $billings = array();
                echo "<table>";
                echo "<tr><td width='400' class='bold'>Code</td><td class='bold'>".xl('Fee')."</td></tr>\n";
                $total = 0.00;
                $copays = 0.00;
                foreach ($ar['newpatient'] as $be) {
                    $ta = explode(":",$be);
                    $billing = getPatientBillingEncounter($pid,$ta[1]);
                    $billings[] = $billing;
                    foreach ($billing as $b) {
                        echo "<tr>\n";
                        echo "<td class=text>";
                        echo $b['code_type'] . ":\t" . $b['code'] . "&nbsp;". $b['modifier'] . "&nbsp;&nbsp;&nbsp;" . $b['code_text'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        echo "</td>\n";
                        echo "<td class=text>";
                        echo oeFormatMoney($b['fee']);
                        echo "</td>\n";
                        echo "</tr>\n";
                        $total += $b['fee'];
                        if ($b['code_type'] == "COPAY") {
                            $copays += $b['fee'];
                        }
                    }
                }
                echo "<tr><td>&nbsp;</td></tr>";
                echo "<tr><td class=bold>".xl('Sub-Total')."</td><td class=text>" . oeFormatMoney($total + abs($copays)) . "</td></tr>";
                echo "<tr><td class=bold>".xl('Paid')."</td><td class=text>" . oeFormatMoney(abs($copays)) . "</td></tr>";
                echo "<tr><td class=bold>".xl('Total')."</td><td class=text>" . oeFormatMoney($total) . "</td></tr>";
                echo "</table>";
                echo "<pre>";
                //print_r($billings);
                echo "</pre>";
            } else {
                printPatientBilling($pid);
            }
            echo "</div>\n"; // end of billing DIV
        } elseif ($val == "allergies") {

            print "<span class=bold>Patient Allergies:</span><br>";
            printListData($pid, "allergy", "1");

        } elseif ($val == "medications") {

            print "<span class=bold>Patient Medications:</span><br>";
            printListData($pid, "medication", "1");

        } elseif ($val == "medical_problems") {

            print "<span class=bold>Patient Medical Problems:</span><br>";
            printListData($pid, "medical_problem", "1");
        } elseif ($val == "immunizations") {

            if (acl_check('patients', 'med')) {
                echo "<hr />";
                echo "<div class='text immunizations'>\n";
                print "<h1>".xl('Patient Immunization').":</h1>";
                $sql = "select i1.immunization_id, i1.administered_date, substring(i1.note,1,20) as immunization_note, c.code_text_short ".
                   " from immunizations i1 ".
                   " left join code_types ct on ct.ct_key = 'CVX' ".
                   " left join codes c on c.code_type = ct.ct_id AND i1.cvx_code = c.code ".
                   " where i1.patient_id = '$pid' and i1.added_erroneously = 0 ".
                   " order by administered_date desc";
                $result = sqlStatement($sql);
                while ($row=sqlFetchArray($result)) {
                  // Figure out which name to use (ie. from cvx list or from the custom list)
                  if ($GLOBALS['use_custom_immun_list']) {
                     $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                  }
                  else {
                     if (!empty($row['code_text_short'])) {
                        $vaccine_display = htmlspecialchars( xl($row['code_text_short']), ENT_NOQUOTES);
                     }
                     else {
                        $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                     }
                  }
                  echo $row['administered_date'] . " - " . $vaccine_display;
                  if ($row['immunization_note']) {
                     echo " - " . $row['immunization_note'];
                  }
                  echo "<br>\n";
                }
                echo "</div>\n";
            }

        // communication report
        } elseif ($val == "batchcom") {

            echo "<hr />";
            echo "<div class='text transactions'>\n";
            print "<h1>".xl('Patient Communication sent').":</h1>";
            $sql="SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id='$pid'";
            // echo $sql;
            $result = sqlStatement($sql);
            while ($row=sqlFetchArray($result)) {
                echo $row{'batchcom_data'}.", By: ".$row{'user_name'}."<br>Text:<br> ".$row{'msg_txt'}."<br>\n";
            }
            echo "</div>\n";

        } elseif ($val == "notes") {

            echo "<hr />";
            echo "<div class='text notes'>\n";
            print "<h1>".xl('Patient Notes').":</h1>";
            printPatientNotes($pid);
            echo "</div>";

        } elseif ($val == "transactions") {

            echo "<hr />";
            echo "<div class='text transactions'>\n";
            print "<h1>".xl('Patient Transactions').":</h1>";
            printPatientTransactions($pid);
            echo "</div>";

        }

    } else {

        // Documents is an array of checkboxes whose values are document IDs.
        //
        if ($key == "documents") {

            echo "<hr />";
            echo "<div class='text documents'>";
	    echo "<h1>".xl('Documents')."</h1><br/>";
	    $category_tree=array();
	    $ord=array();
            foreach($val as $valkey => $valvalue)
	    {
  	     $s=sqlQuery("select category_id from categories_to_documents where document_id='".$valvalue."'");
	     $category_tree[]=array('cat'=>category_tree($s['category_id']),'ord'=>6,'doc'=>$valvalue);
	     if(in_array('Sonograms',$category_tree[count($category_tree)-1]['cat']))
	     $category_tree[count($category_tree)-1]['ord']=1;
	     elseif(in_array('Pap Smears',$category_tree[count($category_tree)-1]['cat']))
	     $category_tree[count($category_tree)-1]['ord']=2;
	     elseif(in_array('Mamograms',$category_tree[count($category_tree)-1]['cat']))
	     $category_tree[count($category_tree)-1]['ord']=3;
	     elseif(in_array('Dexa Scans',$category_tree[count($category_tree)-1]['cat']))
	     $category_tree[count($category_tree)-1]['ord']=4;
	     elseif(in_array('Surgeries',$category_tree[count($category_tree)-1]['cat']))
	     $category_tree[count($category_tree)-1]['ord']=5;
	     $ord[]=$category_tree[count($category_tree)-1]['ord'];
	    }
	    array_multisort($ord,SORT_ASC,$category_tree);
            //foreach($val as $valkey => $valvalue)
            foreach($category_tree as $valkey => $valvalue)
	    {
                $document_id = $valvalue['doc'];
                if (!is_numeric($document_id)) continue;
                $d = new Document($document_id);
                $fname = basename($d->get_url());
                $couch_docid = $d->get_couch_docid();
                $couch_revid = $d->get_couch_revid();
		$a=$valvalue['cat'];
		array_unshift($a,$fname);
		$b=array();
		for($i=count($a)-1;$i>=0;$i--)
		$b[]=$a[$i];
                echo "<h1>".implode('->',$b)."</h1>";
                $n = new Note();
                $notes = $n->notes_factory($d->get_id());
                if (!empty($notes)) echo "<table>";
                foreach ($notes as $note) {
                    echo '<tr>';
                    echo '<td>' . xl('Note') . ' #' . $note->get_id() . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>' . xl('Date') . ': ' . oeFormatShortDate($note->get_date()) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>'.$note->get_note().'<br><br></td>';
                    echo '</tr>';
                }
                if (!empty($notes)) echo "</table>";

                $url_file = $d->get_url_filepath();
                if($couch_docid && $couch_revid){
                  $url_file = $d->get_couch_url($pid,$encounter);
                }
                // Collect filename and path
                $from_all = explode("/",$url_file);
                $from_filename = array_pop($from_all);
                $from_pathname_array = array();
                for ($i=0;$i<$d->get_path_depth();$i++) {
                  $from_pathname_array[] = array_pop($from_all);
                }
                $from_pathname_array = array_reverse($from_pathname_array);
                $from_pathname = implode("/",$from_pathname_array);

                if($couch_docid && $couch_revid) {
                  $from_file = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/' . $from_filename;
                  $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
                }
                else {
                  $from_file = $GLOBALS["fileroot"] . "/sites/" . $_SESSION['site_id'] .
                    '/documents/' . $from_pathname . '/' . $from_filename;
                  $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
                }
                //Extract the extension by the mime/type and not the file name extension
                $image_data = getimagesize($from_file);
                $extension = image_type_to_extension($image_data[2]);
                if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
                  if ($PDF_OUTPUT) {
                    // OK to link to the image file because it will be accessed by the
                    // HTML2PDF parser and not the browser.
                    $from_rel = $web_root . substr($from_file, strlen($webserver_root));
                    echo "<img src='$from_rel'";
                    // Flag images with excessive width for possible stylesheet action.
                    $asize = getimagesize($from_file);
                    if ($asize[0] > 750) echo " class='bigimage'";
                    echo " /><br><br>";
                  }
                }
                else {

          // Most clinic documents are expected to be PDFs, and in that happy case
          // we can avoid the lengthy image conversion process.
	  if(!$extension)
	  {
           $extension=explode('.',$from_filename);
	   $extension=$extension[count($extension)-1];
	  }
          if ($PDF_OUTPUT && $extension == "pdf") {
            // HTML to PDF conversion will fail if there are open tags.
            echo "</div></div>\n";
            $content = getContent();
            // $pdf->setDefaultFont('Arial');
            $pdf->writeHTML($content, false);
            $pagecount = $pdf->pdf->setSourceFile($from_file);
            for($i = 0; $i < $pagecount; ++$i){
              $pdf->pdf->AddPage();  
              $itpl = $pdf->pdf->importPage($i + 1, '/MediaBox');
              $pdf->pdf->useTemplate($itpl);
            }
            // Make sure whatever follows is on a new page.
            $pdf->pdf->AddPage();
            // Resume output buffering and the above-closed tags.
            ob_start();
            echo "<div><div class='text documents'>\n";
          }
                } // end if-else
            } // end Documents loop
            echo "</div>";
        }

        // Procedures is an array of checkboxes whose values are procedure order IDs.
        //
        else if ($key == "procedures") {
          if ($auth_med) {
            echo "<hr />";
            echo "<div class='text documents'>";
            foreach($val as $valkey => $poid) {
              echo "<h1>" . xlt('Procedure Order') . ":</h1>";
              echo "<br />\n";
              // Need to move the inline styles from this function to the stylesheet, but until
              // then we do it just for PDFs to avoid breaking anything.
              generate_order_report($poid, false, !$PDF_OUTPUT);
              echo "<br />\n";
            }
            echo "</div>";
          }
        }

        else if (strpos($key, "issue_") === 0) {
            // display patient Issues

            if ($first_issue) {
                $prevIssueType = 'asdf1234!@#$'; // random junk so as to not match anything
                $first_issue = 0;
                echo "<hr />";
                echo "<h1>".xl("Issues")."</h1>";
            }
            preg_match('/^(.*)_(\d+)$/', $key, $res);
            $rowid = $res[2];
            $irow = sqlQuery("SELECT type, title, comments, diagnosis " .
                            "FROM lists WHERE id = '$rowid'");
            $diagnosis = $irow['diagnosis'];
            if ($prevIssueType != $irow['type']) {
                // output a header for each Issue Type we encounter
                $disptype = $ISSUE_TYPES[$irow['type']][0];
                echo "<div class='issue_type'>" . $disptype . ":</div>\n";
                $prevIssueType = $irow['type'];
            }
            echo "<div class='text issue'>";
            echo "<span class='issue_title'>" . $irow['title'] . ":</span>";
            echo "<span class='issue_comments'> " . $irow['comments'] . "</span>\n";
            // Show issue's chief diagnosis and its description:
            if ($diagnosis) {
                echo "<div class='text issue_diag'>";
                echo "<span class='bold'>[".xl('Diagnosis')."]</span><br>";
                $dcodes = explode(";", $diagnosis);
                foreach ($dcodes as $dcode) {
                    echo "<span class='italic'>".$dcode."</span>: ";
                    echo lookup_code_descriptions($dcode)."<br>\n";
                }
                //echo $diagnosis." -- ".lookup_code_descriptions($diagnosis)."\n";
                echo "</div>";
            }

            // Supplemental data for GCAC or Contraception issues.
            if ($irow['type'] == 'ippf_gcac') {
                echo "   <table>\n";
                display_layout_rows('GCA', sqlQuery("SELECT * FROM lists_ippf_gcac WHERE id = '$rowid'"));
                echo "   </table>\n";
            }
            else if ($irow['type'] == 'contraceptive') {
                echo "   <table>\n";
                display_layout_rows('CON', sqlQuery("SELECT * FROM lists_ippf_con WHERE id = '$rowid'"));
                echo "   </table>\n";
            }

            echo "</div>\n"; //end the issue DIV

        } else {
            // we have an "encounter form" form field whose name is like
            // dirname_formid, with a value which is the encounter ID.
            //
            // display encounter forms, encoded as a POST variable
            // in the format: <formdirname_formid>=<encounterID>

            if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                $form_encounter = $val;
                preg_match('/^(.*)_(\d+)$/', $key, $res);
                $form_id = $res[2];
                $formres = getFormNameByFormdirAndFormid($res[1],$form_id);
                $dateres = getEncounterDateByEncounter($form_encounter);
                $formId = getFormIdByFormdirAndFormid($res[1], $form_id);

                if ($res[1] == 'newpatient') {
                    echo "<div class='text encounter'>\n";
                    echo "<h1>" . xl($formres["form_name"]) . "</h1>";
                }
                else {
                    echo "<div class='text encounter_form'>";
                    echo "<h1>" . xl_form_title($formres["form_name"]) . "</h1>";
                }

                // show the encounter's date
                echo "(" . oeFormatSDFT(strtotime($dateres["date"])) . ") ";
                if ($res[1] == 'newpatient') {
                    // display the provider info
                    echo ' '. xl('Provider') . ': ' . text(getProviderName(getProviderIdOfEncounter($form_encounter)));
                }
                echo "<br>\n";
   
                // call the report function for the form
                ?>                
                <div name="search_div" id="search_div_<?php echo attr($form_id)?>_<?php echo attr($res[1])?>" class="report_search_div class_<?php echo attr($res[1]); ?>">
                <?php
                if (substr($res[1],0,3) == 'LBF')
                  call_user_func("lbf_report", $pid, $form_encounter, $N, $form_id, $res[1]);
                else
                  call_user_func($res[1] . "_report", $pid, $form_encounter, $N, $form_id);
                
                $esign = $esignApi->createFormESign( $formId, $res[1], $form_encounter );
                if ( $esign->isLogViewable("report") ) {
                    $esign->renderLog();
                }
                ?>
                
                </div>
                <?php

                if ($res[1] == 'newpatient') {
                    // display billing info
                    $bres = sqlStatement("SELECT b.date, b.code, b.code_text " .
                      "FROM billing AS b, code_types AS ct WHERE " .
                      "b.pid = ? AND " .
                      "b.encounter = ? AND " .
                      "b.activity = 1 AND " .
                      "b.code_type = ct.ct_key AND " .
                      "ct.ct_diag = 0 " .
                      "ORDER BY b.date",
                      array($pid, $form_encounter));
                    while ($brow=sqlFetchArray($bres)) {
                        echo "<span class='bold'>&nbsp;".xl('Procedure').": </span><span class='text'>" .
                            $brow['code'] . " " . $brow['code_text'] . "</span><br>\n";
                    }
                }

                print "</div>";
            
            } // end auth-check for encounter forms

        } // end if('issue_')... else...

    } // end if('include_')... else...

} // end $ar loop

if ($PDF_OUTPUT)
  echo "<br /><br />" . xl('Signature') . ": _______________________________<br />";
?>

</div> <!-- end of report_custom DIV -->

<?php
 if($PDF_OUTPUT)
 {
  $content = getContent();
  // $pdf->setDefaultFont('Arial');
  $pdf->writeHTML($content, false);
  if($PDF_OUTPUT == 1)
  $pdf->Output("../backup/$pid.pdf",'F'); // D = Download, I = Inline
 }
 }

 if(isset($_POST['pdf_type'])&&$_POST['pdf_type']=='batch')
 {
  for($i=$_POST['starting_patient'];$i<=$_POST['ending_patient'];$i++)
  {
   $pid=$_SESSION['pid']=$ar[$i];
   patient_pdf($pid);
  }
 }
 elseif(isset($_POST['pdf_type'])&&$_POST['pdf_type']=='single')
 {
  $pid=$_SESSION['pid']=$ar[$_POST['patient']];
  patient_pdf($pid);
 }
 $rootPath = realpath('../backup');
 $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath),RecursiveIteratorIterator::LEAVES_ONLY);
 $zip = new ZipArchive();
 unlink('backup.zip');
 $zip->open('backup.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
 $i=0;
 foreach ($files as $name => $file)
 {
   if (!$file->isDir())
   {
       $filePath = $file->getRealPath();
       $relativePath = substr($filePath, strlen($rootPath) + 1);
       $zip->addFile($filePath, $relativePath);
       $i++;
   }
 }
 $zip->close();
 if($i>2)
 header('location: backup.zip');
 elseif(is_file("../backup/$pid.pdf"))
 {
  header("Content-type:application/pdf");
  header("Content-Disposition:attachment;filename=$pid.pdf");
  readfile("../backup/$pid.pdf");
 }
}
?>
 <!DOCTYPE html>
 <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDF Export</title>
    <link href="../library/css/bootstrap-3-2-0.min.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="../library/js/jquery-1.9.1.min.js"></script>
    <script>
     $(function(){
      single_or_batch();
     });
     function single_or_batch()
     {
      if($("[name='pdf_type']:checked").val()=='single')
      {
       $('.single').show();
       $('.batch').hide();
      }
      else if($("[name='pdf_type']:checked").val()=='batch')
      {
       $('.batch').show();
       $('.single').hide();
      }
     }
    </script>
  </head>
  <body style='overflow-x:hidden'>
        <div id="page-wrapper">
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            PDF Export
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form role="form" method='POST'>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="pdf_type" value="single" onclick='single_or_batch()'checked>Single
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="pdf_type" value="batch" onclick='single_or_batch()'>Batch
                                            </label>
                                        </div>
					<?php
					 $s=sqlStatement("select lname,fname,pid from patient_data order by lname");
					 $s1='';
					 $i=0;
					 while($row=sqlFetchArray($s))
					 {
					  $s1.="<option value='$i'>".$row['lname'].",".$row['fname']."</option>";
					  $i++;
					 }
					?>
                                        <div class="form-group single">
                                          <label>Patient</label>
                                          <select class="form-control" name='patient'>
					   <?php echo $s1; ?>
					  </select>
                                        </div>
                                        <div class="form-group batch">
                                          <label>Starting Patient</label>
                                          <select name='starting_patient' class="form-control">
					   <?php echo $s1; ?>
					  </select>
                                        </div>
                                        <div class="form-group batch">
                                          <label>Ending Patient</label>
                                          <select name='ending_patient' class="form-control">
					   <?php echo $s1; ?>
					  </select>
                                        </div>
					<input type='hidden' value='demographics' name='include_demographics'>
					<input type='hidden' value='history' name='include_history'>
					<input type='hidden' value='employer' name='include_employer'>
					<input type='hidden' value='insurance' name='include_insurance'>
					<input type='hidden' value='billing' name='include_billing'>
					<input type='hidden' value='allergies' name='include_allergies'>
					<input type='hidden' value='medications' name='include_medications'>
					<input type='hidden' value='medical_problems' name='include_medical_problems'>
					<input type='hidden' value='immunizations' name='include_immunizations'>
					<input type='hidden' value='notes' name='include_notes'>
					<input type='hidden' value='transactions' name='include_transactions'>
					<input type='hidden' value='batchcom' name='include_batchcom'>
					<input type='hidden' value=1 name='pdf'>
                                        <button type="submit" class="btn btn-default">Export</button>
                                    </form>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
  </body>
 </html>
