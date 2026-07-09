<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->libdir.'/excellib.class.php');

function local_customreports_do_export($courseid, $search, $format, $datefrom = 0, $dateto = 0, $status = '') {
    global $DB;
    
    // Procesar fechas desde parámetros GET si vienen separadas
    if (!$datefrom) {
        $datefrom_day = optional_param('datefrom_day', 0, PARAM_INT);
        $datefrom_month = optional_param('datefrom_month', 0, PARAM_INT);
        $datefrom_year = optional_param('datefrom_year', 0, PARAM_INT);
        
        if ($datefrom_day && $datefrom_month && $datefrom_year) {
            $datefrom = mktime(0, 0, 0, $datefrom_month, $datefrom_day, $datefrom_year);
        }
    }
    
    if (!$dateto) {
        $dateto_day = optional_param('dateto_day', 0, PARAM_INT);
        $dateto_month = optional_param('dateto_month', 0, PARAM_INT);
        $dateto_year = optional_param('dateto_year', 0, PARAM_INT);
        
        if ($dateto_day && $dateto_month && $dateto_year) {
            $dateto = mktime(23, 59, 59, $dateto_month, $dateto_day, $dateto_year);
        }
    }
    
    // Obtener curso
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    
    // Obtener estudiantes
    $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, ue.timecreated as enrolldate
            FROM {user} u
            JOIN {user_enrolments} ue ON u.id = ue.userid
            JOIN {enrol} e ON e.id = ue.enrolid
            WHERE u.deleted = 0 
                AND e.courseid = :courseid";
    
    $params = array('courseid' => $courseid);
    
    if (!empty($search)) {
        $sql .= " AND (u.firstname LIKE :s1 OR u.lastname LIKE :s2 OR u.email LIKE :s3)";
        $params['s1'] = "%{$search}%";
        $params['s2'] = "%{$search}%";
        $params['s3'] = "%{$search}%";
    }
    
    if ($datefrom > 0) {
        $sql .= " AND ue.timecreated >= :datefrom";
        $params['datefrom'] = $datefrom;
    }
    
    if ($dateto > 0) {
        $sql .= " AND ue.timecreated <= :dateto";
        $params['dateto'] = strtotime('+1 day', $dateto) - 1;
    }
    
    $sql .= " ORDER BY u.lastname, u.firstname";
    $students = $DB->get_records_sql($sql, $params);
    
    // Filtrar por estado
    if ($status) {
        $filtered = array();
        foreach ($students as $student) {
            $hasaccess = $DB->record_exists('logstore_standard_log', array(
                'userid' => $student->id,
                'courseid' => $courseid
            ));
            
            if (($status == 'active' && $hasaccess) || ($status == 'inactive' && !$hasaccess)) {
                $filtered[$student->id] = $student;
            }
        }
        $students = $filtered;
    }
    
    // Obtener actividades
    $sql = "SELECT cm.id, cm.instance, m.name as modname,
                   COALESCE(
                       (SELECT name FROM {quiz} WHERE id = cm.instance AND m.name = 'quiz'),
                       (SELECT name FROM {assign} WHERE id = cm.instance AND m.name = 'assign'),
                       (SELECT name FROM {resource} WHERE id = cm.instance AND m.name = 'resource'),
                       (SELECT name FROM {page} WHERE id = cm.instance AND m.name = 'page'),
                       (SELECT name FROM {url} WHERE id = cm.instance AND m.name = 'url'),
                       (SELECT name FROM {forum} WHERE id = cm.instance AND m.name = 'forum'),
                       (SELECT name FROM {book} WHERE id = cm.instance AND m.name = 'book'),
                       (SELECT name FROM {folder} WHERE id = cm.instance AND m.name = 'folder'),
                       'Actividad'
                   ) as actname
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            WHERE cm.course = :cid AND cm.deletioninprogress = 0
            ORDER BY cm.section, cm.id";
    
    $activities = $DB->get_records_sql($sql, array('cid' => $courseid));
    
    $filename = 'informe_' . clean_filename($course->shortname) . '_' . date('Y-m-d_H-i');
    
    if ($format == 'csv') {
        $csv = new csv_export_writer();
        $csv->set_filename($filename);
        
        // Información del curso
        $csv->add_data(array('INFORME DE CURSO'));
        $csv->add_data(array('Curso', $course->fullname));
        $csv->add_data(array('Fecha generacion', userdate(time(), '%d/%m/%Y %H:%M')));
        $csv->add_data(array('Total estudiantes', count($students)));
        $csv->add_data(array('Total actividades', count($activities)));
        $csv->add_data(array(''));
        
        // Encabezados
        $headers = array('Estudiante', 'Email', 'Fecha Inscripcion');
        foreach ($activities as $act) {
            $headers[] = $act->actname . ' (Accesos)';
            $headers[] = $act->actname . ' (Ultima fecha)';
            $headers[] = $act->actname . ' (Nota)';
        }
        $csv->add_data($headers);
        
        // Datos
        foreach ($students as $student) {
            $row = array(
                fullname($student), 
                $student->email,
                userdate($student->enrolldate, '%d/%m/%Y')
            );
            
            foreach ($activities as $act) {
                $accesssql = "SELECT COUNT(*) as cnt, MAX(timecreated) as lasttime
                              FROM {logstore_standard_log}
                              WHERE userid = :uid 
                                  AND contextlevel = 70 
                                  AND contextinstanceid = :cmid";
                
                $accessparams = array('uid' => $student->id, 'cmid' => $act->id);
                
                if ($datefrom > 0) {
                    $accesssql .= " AND timecreated >= :df";
                    $accessparams['df'] = $datefrom;
                }
                
                if ($dateto > 0) {
                    $accesssql .= " AND timecreated <= :dt";
                    $accessparams['dt'] = strtotime('+1 day', $dateto) - 1;
                }
                
                $accessdata = $DB->get_record_sql($accesssql, $accessparams);
                
                if ($accessdata && $accessdata->cnt > 0) {
                    $row[] = $accessdata->cnt;
                    $row[] = userdate($accessdata->lasttime, '%d/%m/%Y %H:%M');
                    
                    // Calificación
                    $grade = $DB->get_record_sql(
                        "SELECT gg.finalgrade, gi.grademax
                         FROM {grade_grades} gg
                         JOIN {grade_items} gi ON gi.id = gg.itemid
                         WHERE gg.userid = :uid 
                           AND gi.iteminstance = :inst 
                           AND gi.itemmodule = :mod
                           AND gg.finalgrade IS NOT NULL",
                        array('uid' => $student->id, 'inst' => $act->instance, 'mod' => $act->modname)
                    );
                    
                    if ($grade && $grade->grademax > 0) {
                        $row[] = number_format(($grade->finalgrade / $grade->grademax) * 10, 2);
                    } else {
                        $row[] = '';
                    }
                } else {
                    $row[] = '0';
                    $row[] = 'Sin acceso';
                    $row[] = '';
                }
            }
            
            $csv->add_data($row);
        }
        
        $csv->download_file();
        
    } else if ($format == 'excel') {
        $workbook = new MoodleExcelWorkbook($filename);
        $sheet = $workbook->add_worksheet('Informe');
        
        // Formatos
        $titlefmt = $workbook->add_format(array('bold' => 1, 'size' => 14));
        $headfmt = $workbook->add_format(array('bold' => 1, 'bg_color' => 'blue', 'color' => 'white'));
        $accessfmt = $workbook->add_format(array('bg_color' => 'lime'));
        $noaccessfmt = $workbook->add_format(array('bg_color' => 'gray', 'color' => 'white'));
        
        $row = 0;
        
        // Información del curso
        $sheet->write($row++, 0, 'INFORME DE CURSO', $titlefmt);
        $sheet->write($row, 0, 'Curso:');
        $sheet->write($row++, 1, $course->fullname);
        $sheet->write($row, 0, 'Fecha generacion:');
        $sheet->write($row++, 1, userdate(time(), '%d/%m/%Y %H:%M'));
        $sheet->write($row, 0, 'Total estudiantes:');
        $sheet->write($row++, 1, count($students));
        $sheet->write($row, 0, 'Total actividades:');
        $sheet->write($row++, 1, count($activities));
        $row++;
        
        // Encabezados
        $col = 0;
        $sheet->write($row, $col++, 'Estudiante', $headfmt);
        $sheet->write($row, $col++, 'Email', $headfmt);
        $sheet->write($row, $col++, 'Fecha Inscripcion', $headfmt);
        
        foreach ($activities as $act) {
            $sheet->write($row, $col++, $act->actname . ' (Accesos)', $headfmt);
            $sheet->write($row, $col++, $act->actname . ' (Ultima fecha)', $headfmt);
            $sheet->write($row, $col++, $act->actname . ' (Nota)', $headfmt);
        }
        $row++;
        
        // Datos
        foreach ($students as $student) {
            $col = 0;
            $sheet->write($row, $col++, fullname($student));
            $sheet->write($row, $col++, $student->email);
            $sheet->write($row, $col++, userdate($student->enrolldate, '%d/%m/%Y'));
            
            foreach ($activities as $act) {
                $accesssql = "SELECT COUNT(*) as cnt, MAX(timecreated) as lasttime
                              FROM {logstore_standard_log}
                              WHERE userid = :uid 
                                  AND contextlevel = 70 
                                  AND contextinstanceid = :cmid";
                
                $accessparams = array('uid' => $student->id, 'cmid' => $act->id);
                
                if ($datefrom > 0) {
                    $accesssql .= " AND timecreated >= :df";
                    $accessparams['df'] = $datefrom;
                }
                
                if ($dateto > 0) {
                    $accesssql .= " AND timecreated <= :dt";
                    $accessparams['dt'] = strtotime('+1 day', $dateto) - 1;
                }
                
                $accessdata = $DB->get_record_sql($accesssql, $accessparams);
                
                if ($accessdata && $accessdata->cnt > 0) {
                    $sheet->write($row, $col++, $accessdata->cnt, $accessfmt);
                    $sheet->write($row, $col++, userdate($accessdata->lasttime, '%d/%m/%Y %H:%M'), $accessfmt);
                    
                    // Calificación
                    $grade = $DB->get_record_sql(
                        "SELECT gg.finalgrade, gi.grademax
                         FROM {grade_grades} gg
                         JOIN {grade_items} gi ON gi.id = gg.itemid
                         WHERE gg.userid = :uid 
                           AND gi.iteminstance = :inst 
                           AND gi.itemmodule = :mod
                           AND gg.finalgrade IS NOT NULL",
                        array('uid' => $student->id, 'inst' => $act->instance, 'mod' => $act->modname)
                    );
                    
                    if ($grade && $grade->grademax > 0) {
                        $sheet->write($row, $col++, number_format(($grade->finalgrade / $grade->grademax) * 10, 2), $accessfmt);
                    } else {
                        $sheet->write($row, $col++, '', $accessfmt);
                    }
                } else {
                    $sheet->write($row, $col++, '0', $noaccessfmt);
                    $sheet->write($row, $col++, 'Sin acceso', $noaccessfmt);
                    $sheet->write($row, $col++, '', $noaccessfmt);
                }
            }
            
            $row++;
        }
        
        $workbook->close();
    }
}