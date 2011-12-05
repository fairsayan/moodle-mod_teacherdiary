<?php

require_once($CFG->dirroot . '/lib/weblib.php');

/**
 * Library of functions and constants for module teacherdiary (pages of diaries)
 *
 * @author  Domenico Pontari <fairsayan@gmail.com>
 * @version $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $
 * @package mod/teacherdiary
 */
function teacherdiary_get_page_diary_list ($teacherdiary, $editing = true) {
    $result = "\n<table id=\"diary_pages_table\" cellpadding=\"5\" rules=\"rows\" frame=\"below\">\n\t<col width=\"50\" />\n";
    $pages = get_records('teacherdiary_pages', 'teacherdiaryid', $teacherdiary->id, 'starttime DESC');
    if (empty($pages)) return notify(get_string('nopages', 'teacherdiary'), 'notifyproblem', 'center', true);
    foreach ($pages as $page) {
        $result .= teacherdiary_get_page_diary_table_titles ($page, $editing);
        break; // use just the first record
    }
    foreach ($pages as $page) $result .= teacherdiary_get_page_diary_table_row ($page, $editing);
    $result .= "</table>\n";
    return $result;
}

function teacherdiary_get_page_diary_table_titles ($page_diary, $editing = true) {
    $result = "\t<tr>\n";
    if ($editing) $result .= "\t\t<th></th>\n"; // editing cell title: blank
    foreach ($page_diary as $name => $data) {
        if (in_array($name, array('id', 'teacherdiaryid', 'timecreated', 'timemodified'))) continue;
        if ($name == 'starttime') $result .= "\t\t<th>" . get_string("date") . "</th>\n";
        $result .= "\t\t<th>" . get_string($name, 'teacherdiary') . "</th>\n";
    }
    $result .= "\t</tr>\n";
    return $result;
}

function teacherdiary_get_declaredminutes ($page_diaryid) {
  $minutes_declared = 0;
  $records = get_records ('teacherdiary_pages', 'teacherdiaryid', $page_diaryid);
  if (empty($records)) return 0;
  foreach ($records as $record) {
    $minutes_declared += ($record->endtime - $record->starttime)/60;
  }
  return $minutes_declared;
}

function teacherdiary_minutes_to_string ($minutes) {
  $result = '';
  $hours = floor($minutes / 60);
  $minutes = $minutes % 60;
  if ($hours) $result .= sprintf ("%d %s", $hours, get_string ('hours'));
  if ($hours && $minutes) $result .= ', ';
  if ($minutes) $result .= sprintf ("%d %s", $minutes, get_string ('minutes'));

  return $result;
}

function teacherdiary_get_page_diary_summary ($teacherdiary) {
  $teachername = get_string('diaryowner', 'teacherdiary');
  $plannedhours = get_string('plannedhours', 'teacherdiary');
  $declaredhours = get_string('declaredhours', 'teacherdiary');
  $user = get_record ('user', 'id', $teacherdiary->userid);

  $fullname = fullname ($user);
  $plannedtime = teacherdiary_minutes_to_string($teacherdiary->plannedhours * 60);
  $declaredtime = teacherdiary_minutes_to_string(teacherdiary_get_declaredminutes($teacherdiary->id));

  $result = <<<EOD
<table cellpadding="2" class="teacherdiary_summary">
  <tr>
    <th>$teachername:</th>
    <td>$fullname</td>
  </tr>
  <tr>
    <th>$plannedhours:</th>
    <td>$plannedtime</td>
  </tr>
  <tr>
    <th>$declaredhours:</th>
    <td>$declaredtime</td>
  </tr>
</table>
EOD;

  return $result;
}

function teacherdiary_get_tools_rendered ($tools) {
  $html = "<div class=\"teacherdiary_tools\">\n";
  $html .= '<b>' . get_string('actions', 'lesson') . '</b><br /><ul>';
  $first_tool = true;
  foreach ($tools as $tool) {
//    if (!$first_tool) $html .= "<br />"; else $first_tool = false;
    $html .= "<li>$tool</li>\n";
  }
  $html .= "</ul></div>\n";
  
  return $html;
}

function teacherdiary_get_page_diary_formatted_row ($page_diary) {
  $result = array();
  foreach ($page_diary as $name => $data) {
    if (in_array($name, array('id', 'teacherdiaryid', 'teacherdiaryid', 'timecreated', 'timemodified'))) continue;
    switch ($name) {
      case 'starttime':
        $date = userdate($data);
        $date = substr($date, 0, strlen($date) - 7); // pull out seconds
        $result['date'] = $date;
        $getdate = getdate($data);
        $time =  sprintf("%02d:%02d", $getdate['hours'], $getdate['minutes']);
        $result['starttime'] = $time;
        break;
      case 'endtime':
        $getdate = getdate($data);
        $time =  sprintf("%02d:%02d", $getdate['hours'], $getdate['minutes']);
        $result['endtime'] = $time;
        break;
      default:
        $result[$name] = $data;
    }
  }
  return $result;
}

function teacherdiary_get_page_diary_table_row ($page_diary, $editing = true) {
    global $CFG;
    $result = "\t<tr>\n";
    if ($editing) {
        $result .= "\t\t<td>";
        $result .= '<a href="edit.php?pageid=' . $page_diary->id . '"><img class="editing_img" src="' . $CFG->pixpath . '/t/edit.gif" /></a>';
        $result .= '<a href="delete.php?pageid=' . $page_diary->id . '"><img class="editing_img" src="' . $CFG->pixpath . '/t/delete.gif" /></a>';
        $result .= "</td>\n";
    }
    foreach ($page_diary as $name => $data) {
        if (in_array($name, array('id', 'teacherdiaryid', 'teacherdiaryid', 'timecreated', 'timemodified'))) continue;
        switch ($name) {
            case 'starttime':
                $date = userdate($data);
                $date = substr($date, 0, strlen($date) - 7); // pull out time (hh:mm:ss)
                $result .= "\t\t<td>$date</td>\n";
                $getdate = getdate($data);
                $time =  sprintf("%02d:%02d", $getdate['hours'], $getdate['minutes']);
                $result .= "\t\t<td>$time</td>\n";
                break;
            case 'endtime':
                $getdate = getdate($data);
                $time =  sprintf("%02d:%02d", $getdate['hours'], $getdate['minutes']);
                $result .= "\t\t<td>$time</td>\n";
                break;
            default:
                $result .= "\t\t<td>$data</td>\n";
        }
    }
    $result .= "\t</tr>\n";
    return $result;
}

function teacherdiary_get_excel_summary_fieldnames ($teacherdiaryid) {
  $static_fields = array();
  $static_fields[] = get_string ('teacherdiary', 'teacherdiary');
  $static_fields[] = 'ID' . get_string ('course');
  $static_fields[] = get_string ('course');
  $static_fields[] = get_string('diaryowner', 'teacherdiary');
  $static_fields[] = get_string ('lastupdate', 'teacherdiary');
  $static_fields[] = get_string ('lastpagedate', 'teacherdiary');
  $static_fields[] = get_string ('declaredhours', 'teacherdiary');
  $static_fields[] = get_string ('plannedhours', 'teacherdiary');
  foreach ($static_fields as $key => $static_field)
    $static_fields[$key] = '"'.str_replace('"', '\"', utf8_decode($static_field)).'"';
  return $static_fields;
}

function teacherdiary_get_excel_summary_values ($teacherdiaryid) {
  $teacherdiary = get_record ('teacherdiary', 'id', $teacherdiaryid);
  $course = get_record ('course', 'id', $teacherdiary->course);
  $static_values = array();
  $static_values[] = $teacherdiary->name;
  $static_values[] = $teacherdiary->course;
  $static_values[] = $course->fullname;
  $static_values[] = fullname(get_record('user', 'id', $teacherdiary->userid));
  $date = userdate(get_field_select ('teacherdiary_pages', 'timemodified', "teacherdiaryid = $teacherdiaryid ORDER BY timemodified DESC"));
  $static_values[] = substr($date, 0, strlen($date) - 7); // pull out time (hh:mm:ss)
  $date = userdate(get_field_select ('teacherdiary_pages', 'endtime', "teacherdiaryid = $teacherdiaryid ORDER BY endtime DESC"));
  $static_values[] = substr($date, 0, strlen($date) - 7); // pull out time (hh:mm:ss)
  $static_values[] = teacherdiary_minutes_to_string(teacherdiary_get_declaredminutes($teacherdiary->id));
  $static_values[] = teacherdiary_minutes_to_string($teacherdiary->plannedhours * 60);
  foreach ($static_values as $key => $static_value)
    $static_values[$key] = '"'.str_replace('"', '\"', utf8_decode($static_value)).'"';
  return $static_values;
}

function teacherdiary_create_excel_report () {
  global $CFG;
  $first = true;
  if(!($fp = @fopen($CFG->dataroot."/teacherdiary/report.csv", 'w'))) {
    error('put_records_csv failed to create summary file');
  }
  $teacherdiaries = get_records ('teacherdiary');
  foreach ($teacherdiaries as $teacherdiary) {
    if ($first) {
      $static_fields = teacherdiary_get_excel_summary_fieldnames($teacherdiary->id);
      fwrite($fp, implode(';', $static_fields)."\r\n");
      $first = false;
    }
    $static_values = teacherdiary_get_excel_summary_values($teacherdiary->id);
    fwrite($fp, implode(';', $static_values)."\r\n");
  }
  fclose($fp);
}

/**
 *  @return bool
 */
function teacherdiary_create_excel ($teacherdiaryid, $editable = false) {
  global $CFG, $db;

  if(!($fp = @fopen($CFG->dataroot."/teacherdiary/teacherdiary$teacherdiaryid.csv", 'w'))) {
    error('put_records_csv failed to create file');
  }

  // fields from 'teacherdiary' table
  if (!$editable) {
    $static_fields = teacherdiary_get_excel_summary_fieldnames($teacherdiaryid);
    fwrite($fp, implode(';', $static_fields)."\r\n");
    $static_values = teacherdiary_get_excel_summary_values($teacherdiaryid);
    fwrite($fp, implode(';', $static_values)."\r\n");
    fwrite($fp, "\r\n");
  }
  
  // fields from 'teacherdiary_pages' table
  $records = get_records('teacherdiary_pages', 'teacherdiaryid', $teacherdiaryid, 'starttime DESC');
  if (empty($records)) {
    fclose($fp);
    return true;
  }

  if ($editable) {
    $teacherdiary = get_record ('teacherdiary', 'id', $teacherdiaryid);

    $fields = array_keys((array)reset($records));
    $tmp_fields = array();
    foreach ($fields as $field) {
      switch ($field) {
        case 'teacherdiaryid':
          $tmp_fields[] = 'id';
          $tmp_fields[] = 'name';
          $tmp_fields[] = 'course';
        break;
        case 'starttime':
          $tmp_fields[] = 'day';
          $tmp_fields[] = 'month';
          $tmp_fields[] = 'year';
          $tmp_fields[] = 'starthour';
          $tmp_fields[] = 'startminute';
        break;
        case 'endtime':
          $tmp_fields[] = 'endhour';
          $tmp_fields[] = 'endminute';
        break;
        case 'timecreated':
        case 'timemodified':
        case 'id':
        break;
        default:
          $tmp_fields[] = $field;
      }
    }
    $fields = $tmp_fields;
    foreach ($fields as $pos => $field) $fields[$pos] = '"'.str_replace('"', '\"', utf8_decode($field)).'"';
    fwrite($fp, implode(';', $fields)."\r\n");

    foreach ($records as $pos => $record) {
      $row = array();
      $row[] = $pos;
      $row[] = $teacherdiary->name;
      $row[] = $teacherdiary->course;
      $row[] = $record->teachername;
      $startdate = getdate($record->starttime);
      $row[] = $startdate["mday"];
      $row[] = $startdate["mon"];
      $row[] = $startdate["year"];
      $row[] = $startdate["hours"];
      $row[] = $startdate["minutes"];
      $enddate = getdate($record->endtime);
      $row[] = $enddate["hours"];
      $row[] = $enddate["minutes"];
      $row[] = $record->summary;
      foreach ($row as $pos => $value)
        $row[$pos] = '"'.str_replace('"', '\"', utf8_decode($value)).'"';
      fwrite($fp, implode(';', $row)."\r\n");
    }
  } else {
    $first_record = reset($records);
    $first_record = teacherdiary_get_page_diary_formatted_row ($first_record);
    $fields = array_keys($first_record);
    foreach ($fields as $pos => $field) {
      if ($field == 'date') $translated_fields[$pos] = get_string ($field);
        else $translated_fields[$pos] = get_string ($field, 'teacherdiary');
      $translated_fields[$pos] = '"'.str_replace('"', '\"', utf8_decode($translated_fields[$pos])).'"';
    }
    fwrite($fp, implode(';', $translated_fields));
    fwrite($fp, "\r\n");

    foreach ($records as $pos => $record) {
      $row = teacherdiary_get_page_diary_formatted_row ($record);
      foreach ($row as $pos => $value) {
        if ($pos == 'summary') $value = html_to_text($value);
        $row[$pos] = '"'.str_replace('"', '\"', utf8_decode($value)).'"';
      }
      fwrite($fp, implode(';', $row)."\r\n");
    }
  }
  fclose($fp);
  return true;
}

?>
