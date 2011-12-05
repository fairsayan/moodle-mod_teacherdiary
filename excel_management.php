<?php

/**
 * This page is used for general settings and bulk upload of new teacherdiaries
 *
 * @author  Domenico Pontari <fairsayan@gmail.com>
 * @package mod/teacherdiary
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/lib/uploadlib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // teacherdiary instance ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('teacherdiary', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (! $teacherdiary = get_record('teacherdiary', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $teacherdiary = get_record('teacherdiary', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $teacherdiary->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('teacherdiary', $teacherdiary->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

add_to_log($course->id, "teacherdiary", "excel management", "excel_management.php?id=$cm->id", "$teacherdiary->id");

/// Print the page header
$strteacherdiaries = get_string('modulenameplural', 'teacherdiary');
$strteacherdiary  = get_string('modulename', 'teacherdiary');

$navlinks = array();
$navlinks[] = array('name' => $strteacherdiaries, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($teacherdiary->name), 'link' => 'view.php?id=' . $cm->id, 'type' => 'activityinstance');
$navlinks[] = array('name' => format_string(get_string('teacherdiarypagesexceltool', 'teacherdiary')), 'link' => '', 'type' => 'activityaction');

$navigation = build_navigation($navlinks);

print_header_simple(format_string($teacherdiary->name), '', $navigation, '', '', true,
              update_module_button($cm->id, $course->id, $strteacherdiary), navmenu($course, $cm));

/// Print the main part of the page
$context = get_context_instance(CONTEXT_SYSTEM);
if (!has_capability('mod/teacherdiary:useanyexceltool', $context))
  notice(get_string('teacherdiarypagesexceltooldenied', 'teacherdiary'));

$um = new upload_manager('csvfile');
if (! $filedir = make_upload_directory('temp'))
  error("The site administrator needs to fix the file permissions");
if ($um->process_file_uploads($filedir) && !empty($_FILES['FILE_0']['size'])) {
  $tmpfilename = $_FILES['FILE_0']["tmp_name"];
  $handle = fopen($tmpfilename, "r");
  $data = array();
  while (($row = fgetcsv($handle,0, ";")) !== false) {
    if (!isset($rownum)) {
      $rownum = 0;
      $arraykeys = $row;
      continue;
    }
    foreach ($arraykeys as $pos => $key) $data[$rownum]->$key = $row[$pos];
    $rownum++;
  }
  fclose($handle);

  echo "<ol>\n";
  foreach ($data as $row) {
    if (!empty($row->id)) {
      echo "<li>Record already exists with ID = $row->id</li>";
      continue;
    }
    
    $row_teacherdiary = get_record ('teacherdiary', 'course', $row->course, 'name', $row->name);
    if (empty($row_teacherdiary)) {
      echo "<li>Unable to find a teacher diary called <b>$row->name</b> with course id <b>$row->course</b></li>";
      continue;
    }

    $row->teacherdiaryid = $row_teacherdiary->id;
    $row->starttime = mktime($row->starthour, $row->startminute, 0, $row->month, $row->day, $row->year);
    $row->endtime = mktime($row->endhour, $row->endminute, 0, $row->month, $row->day, $row->year);
    $row->timecreated = time();
    $row->timemodified = time();
    $row->id = insert_record('teacherdiary_pages', $row);
    if (!$row->id) {
      echo '<li>' . get_string("unabletoaddteacherdiarypage", 'teacherdiary') . '</li>';
      continue;
    } else echo "<li>teacher diary page with id <b>$row->id</b>: created";
  }
  echo "</ol>\n";
  notice('File parsed', "view.php?a=$a");
}

$formrow = helpbutton("excel_management", 'excel management', 'teacherdiary', true, false, '', true);
$formrow .= upload_print_form_fragment(1, null, array('CSV file'), false, null, 0, 0, true);
$submit = get_string ('submit');
$uploadform = <<<EOD
  <form id="teacherdiary_excel_management_form" enctype="multipart/form-data" method="post" action="excel_management.php?a=$a">
    $formrow
    <button type="submit">$submit</button>
  </form>
EOD;
$download_excel_link = '<a href="download.php?editable=1&a=' . $teacherdiary->id . '">' . get_string('downloadeditableexcelformat', 'teacherdiary') . "</a><br />\n";
print_box($download_excel_link . $uploadform, 'generalbox');

/// Finish the page
print_footer();
?>
