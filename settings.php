<?php

$bulkinserttool = get_string('bulkinsertteacherdiarytool', 'teacherdiary');
$downloadallteacherdiaries = get_string('downloadallexcel', 'teacherdiary');
$bulkinserttool_link = addslashes_js($CFG->wwwroot . '/mod/teacherdiary/bulk_insert_teacherdiary.php');
$downloadallteacherdiaries_link = addslashes_js($CFG->wwwroot . '/mod/teacherdiary/download_all.php');
$teacherdiary_tools_content = <<<EOD
  <ul>
    <li><a href="$bulkinserttool_link">$bulkinserttool</a></li>
    <li><a href="$downloadallteacherdiaries_link">$downloadallteacherdiaries</a></li>
  </ul>
EOD;

$settings->add(new admin_setting_heading('teacherdiary_tools', get_string('teacherdiary_tools', 'teacherdiary'),$teacherdiary_tools_content));

?>
