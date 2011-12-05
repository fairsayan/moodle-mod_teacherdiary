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

require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
$title = get_string ('bulkinsertteacherdiarytool', 'teacherdiary');

$navlinks[] = array('name' => $title, 'link' => '', 'type' => 'activityaction');

$navigation = build_navigation($navlinks);

print_header_simple($title, $title, $navigation);
if (!has_capability('mod/teacherdiary:bulkinsertteacherdiary', $context))
  notice(get_string('bulkinsertteacherdiarydenied', 'teacherdiary'));
  
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

  echo "<ul>\n";
  foreach ($data as $teacherdiary) {
    $course = get_record("course", "id", $teacherdiary->course);
    $module = get_record("modules", "name", 'teacherdiary');
    $user = get_record("user", "username", $teacherdiary->teacher);
    $teacherdiary->module = $module->id;
    $teacherdiary->userid = $user->id;
    if (! $cw = get_course_section ($course->id, $teacherdiary->section)) {
      echo "wrong section number: diary not created</li>\n";
      continue;
    }
        
    $teacherdiary->visible = $cw->visible;

    echo '<li>Creating <i><b>' . $teacherdiary->name . "</b></i> teacher diary in <i>$course->fullname</i> course... ";
    if (!course_allowed_module($course, $module->id)) {
      echo "this module has been disabled for this particular course: diary not created</li>\n";
      continue;
    }

    if (get_record ('teacherdiary', 'course', $teacherdiary->course, 'name', $teacherdiary->name)) {
      echo "already exists another teacher diary with the same name: diary not created</li>\n";
      continue;
    }

    $add_instance_result = teacherdiary_add_instance($teacherdiary);
    $teacherdiary->instance = $add_instance_result;
    if (!$add_instance_result) {
      echo "could not add a new instance of teacher diary</li>\n";
      continue;
    }
    if (is_string($add_instance_result)) {
      echo "$add_instance_result</li>\n";
      continue;
    }
    // course_modules and course_sections each contain a reference
    // to each other, so we have to update one of them twice.

    if (! $teacherdiary->coursemodule = add_course_module($teacherdiary) ) {
      echo "could not add a new course module</li>\n";
      continue;
    }
    if (! $sectionid = add_mod_to_section($teacherdiary) ) {
      echo "could not add the new course module to that section</li>\n";
      continue;
    }

    if (! set_field("course_modules", "section", $sectionid, "id", $teacherdiary->coursemodule)) {
      echo "could not update the course module with the correct section</li>\n";
      continue;
    }

    // make sure visibility is set correctly (in particular in calendar)
    set_coursemodule_visible($teacherdiary->coursemodule, $teacherdiary->visible);

    echo "OK</li>\n";
    $cm = null;
  }
  echo "</ul>\n";
  notice('File parsed', 'bulk_insert_teacherdiary.php');
}

$formrow = helpbutton("bulk_insert_teacherdiary", 'bulk insert', 'teacherdiary', true, false, '', true);
$formrow .= upload_print_form_fragment(1, null, array('CSV file'), false, null, 0, 0, true);
$submit = get_string ('submit');
$uploadform = <<<EOD
  <form id="teacherdiary_bulk_insert_form" enctype="multipart/form-data" method="post" action="bulk_insert_teacherdiary.php">
    $formrow
    <button type="submit">$submit</button>
  </form>
EOD;
echo $uploadform;

/// Finish the page
print_footer();
?>
