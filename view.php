<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../lib/csvlib.class.php');



$systemcontext = context_system::instance();

$userid = required_param('userid', PARAM_INT);
$templateid = required_param('templateid', PARAM_INT);
$planid = required_param('planid', PARAM_INT);

if ($userid == $USER->id || has_capability('local/extractcompetencies:useplugin', $systemcontext)) {

    $usercompetencies[0][0] = mb_convert_encoding(get_string('shortname', 'tool_lp'), 'Windows-1252', 'UTF-8');
    $usercompetencies[0][1] = mb_convert_encoding(get_string('rating', 'tool_lp'), 'Windows-1252', 'UTF-8');
    $usercompetencies[0][2] = mb_convert_encoding(get_string('proficient', 'tool_lp'), 'Windows-1252', 'UTF-8');
    $usercompetencies[0][3] = mb_convert_encoding(get_string('status', 'tool_lp'), 'Windows-1252', 'UTF-8');

    $i = 1;

    if ($templateid != 0) {

        $listcompetencies = $DB->get_records('competency_templatecomp', array('templateid' => $templateid));
    } else {
        $listcompetencies = $DB->get_records('competency_plancomp', array('planid' => $planid));
    }

    foreach ($listcompetencies as $competency) {

        $competencyrecord = $DB->get_record('competency', array('id' => $competency->competencyid));
        if (!$competencyrecord) {
            continue;
        }

        $usercompetencies[$i][0] = mb_convert_encoding($competencyrecord->shortname, 'Windows-1252', 'UTF-8');

        $framework = $DB->get_record('competency_framework', array('id' => $competencyrecord->competencyframeworkid));

        if (isset($competencyrecord->scaleid)) {

            $scale = $DB->get_record('scale', array('id' => $competencyrecord->scaleid));
        } else {

            $scale = $DB->get_record('scale', array('id' => $framework->scaleid));
        }

        $scalename = explode(',', $scale->scale);

        $competencyuser = $DB->get_record('competency_usercomp', array('userid' => $userid, 'competencyid' => $competency->competencyid));

        if (!$competencyuser) {

            $usercompetencies[$i][1] = "";
            $usercompetencies[$i][2] = mb_convert_encoding(get_string('no', 'local_extractcompetencies'), 'Windows-1252', 'UTF-8');
            $usercompetencies[$i][3] = "-";
        } else {
            if (isset($competencyuser->grade)) {

                $indexgrade = $competencyuser->grade - 1;

                $usercompetencies[$i][1] = mb_convert_encoding($scalename[$indexgrade], 'Windows-1252', 'UTF-8');
            } else {
                $usercompetencies[$i][1] = "";
            }

            if ($competencyuser->proficiency == 0) {
                $usercompetencies[$i][2] = mb_convert_encoding(get_string('no', 'local_extractcompetencies'), 'Windows-1252', 'UTF-8');
            } else {
                $usercompetencies[$i][2] = mb_convert_encoding(get_string('yes', 'local_extractcompetencies'), 'Windows-1252', 'UTF-8');
            }

            if ($competencyuser->status == 0) {
                $usercompetencies[$i][3] = "-";
            } else {
                $usercompetencies[$i][3] = mb_convert_encoding(get_string('pendingreview', 'local_extractcompetencies'), 'Windows-1252', 'UTF-8');
            }
        }

        $i++;
    }

    $filename = get_string('learningplancompetencies', 'tool_lp');

    csv_export_writer::download_array($filename, $usercompetencies, 'semicolon');
}
