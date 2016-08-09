<?php
$show = $GLOBALS['show_history_widget'];
$pid = $_SESSION['pid'];
// History lookup
if ( (acl_check('patients', 'med')) && $show ) {
    $his_result = getHistoryData($pid);
// History expand collapse widget
    $widgetTitle = xl("History");
    $widgetLabel = "history";
    $widgetButtonLabel = xl("Edit");
    $widgetButtonLink = $GLOBALS['webroot']."/interface/patient_file/history/history.php";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "summary_item small";
    $thisauth = acl_check('patients', 'demo');
    $widgetAuth = ($thisauth == "write");
    $fixedWidth = false;
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
      $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
      $widgetAuth, $fixedWidth);
?>
     <?php 
     if (!is_array($his_result)) {
            // Data entry has not happened to this type, so show 'Nothing Recorded"
            echo "  &nbsp;&nbsp;" . htmlspecialchars( xl('Nothing Recorded'), ENT_NOQUOTES);
          } else {
            display_layout_tabs_data('HIS', $his_result, $his_result2);
          } ?>
        </div> <!-- required for expand_collapse_widget -->
       </td>
      </tr>
<?php
} ?> 
      <tr>
          <td>
<!-- End of Histoyr widget -->