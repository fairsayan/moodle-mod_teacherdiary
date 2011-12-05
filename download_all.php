<?php

/**
 * This page is used for general settings and bulk upload of new teacherdiaries
 *
 * @author  Domenico Pontari <fairsayan@gmail.com>
 * @package mod/teacherdiary
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib_page_diary.php');
require_once($CFG->dirroot . '/lib/filelib.php');

require_login();
$context = get_context_instance(CONTEXT_SYSTEM);

$a  = optional_param('a', 0, PARAM_INT);  // teacherdiary instance ID for future use to break the number of teacherdiaries to process with one HTTP request
$only_report  = optional_param('only_report', false, PARAM_BOOL);  // download only the summary report of all diaries

if (!has_capability('mod/teacherdiary:downloadallteacherdiaries', $context)) {
  $title = get_string ('downloadallexcel', 'teacherdiary');
  $navlinks[] = array('name' => $title, 'link' => '', 'type' => 'activityaction');
  $navigation = build_navigation($navlinks);
  print_header_simple($title, $title, $navigation);
  notice(get_string('bulkinsertteacherdiarydenied', 'teacherdiary'));
}

if (empty($a)) {
  $sourcefiles = array();
  $teacherdiaries = get_records ('teacherdiary', '', '', '', 'id');
  teacherdiary_create_excel_report();
  $sourcefiles[0] = $CFG->dataroot."/teacherdiary/report.csv";
  if ($only_report) {
    send_temp_file ($sourcefiles[0], "report.csv");
    die;
  }

  if (!make_upload_directory('teacherdiary'))
    error("The site administrator needs to fix the file permissions");
  $destinationfile = $CFG->dataroot."/teacherdiary/teacherdiaries.zip";
  foreach ($teacherdiaries as $teacherdiary) {
    $sourcefiles[] = $CFG->dataroot."/teacherdiary/teacherdiary$teacherdiary->id.csv";
    teacherdiary_create_excel ($teacherdiary->id);
  }
  zip_files($sourcefiles, $destinationfile);
  send_temp_file ($destinationfile, "teacherdiaries.zip");
}

?>