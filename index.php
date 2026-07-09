<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_customreports');

$context = context_system::instance();
require_capability('local/customreports:view', $context);

// Parámetros
$courseid = optional_param('courseid', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$export = optional_param('export', '', PARAM_ALPHA);
$status = optional_param('status', '', PARAM_ALPHA);
$category = optional_param('category', 0, PARAM_INT);

// Procesar fechas desde selectores
$datefrom = 0;
$datefrom_day = optional_param('datefrom_day', 0, PARAM_INT);
$datefrom_month = optional_param('datefrom_month', 0, PARAM_INT);
$datefrom_year = optional_param('datefrom_year', 0, PARAM_INT);

if ($datefrom_day && $datefrom_month && $datefrom_year) {
    $datefrom = mktime(0, 0, 0, $datefrom_month, $datefrom_day, $datefrom_year);
}

$dateto = 0;
$dateto_day = optional_param('dateto_day', 0, PARAM_INT);
$dateto_month = optional_param('dateto_month', 0, PARAM_INT);
$dateto_year = optional_param('dateto_year', 0, PARAM_INT);

if ($dateto_day && $dateto_month && $dateto_year) {
    $dateto = mktime(23, 59, 59, $dateto_month, $dateto_day, $dateto_year);
}

$PAGE->set_url(new moodle_url('/local/customreports/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_customreports'));
$PAGE->set_heading(get_string('pluginname', 'local_customreports'));

// Si es exportación, procesar y salir
if ($export && $courseid > 0) {
    require_once(__DIR__ . '/export.php');
    local_customreports_do_export($courseid, $search, $export, $datefrom, $dateto, $status);
    exit;
}

echo $OUTPUT->header();

// CSS mejorado
?>
<style>
.customreports-filters {
    background: #f8f9fa;
    padding: 25px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 25px;
}
.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    align-items: flex-end;
}
.filter-item {
    display: flex;
    flex-direction: column;
    position: relative;
}
.filter-item label {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 0.9em;
}
.filter-item input, .filter-item select {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    min-width: 200px;
}
.filter-item select[name*="day"],
.filter-item select[name*="month"],
.filter-item select[name*="year"] {
    min-width: auto;
    width: auto;
    margin-right: 5px;
}
#courseSearch {
    width: 100%;
    padding: 8px 12px;
    border: 2px solid #007bff;
    border-radius: 4px;
    margin-bottom: 5px;
    font-size: 14px;
}
#courseSearch:focus {
    outline: none;
    border-color: #0056b3;
    box-shadow: 0 0 5px rgba(0,123,255,0.5);
}
#courseSelect {
    max-height: 300px;
}
.filter-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}
.filter-buttons button {
    padding: 10px 25px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}
.btn-search {
    background: #28a745;
    color: white;
}
.btn-search:hover {
    background: #218838;
}
.btn-clear {
    background: #6c757d;
    color: white;
}
.btn-clear:hover {
    background: #5a6268;
}
.btn-export {
    background: #007bff;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
}
.btn-export:hover {
    background: #0056b3;
}
.stats-summary {
    background: white;
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
}
.stat-box {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    min-width: 150px;
}
.stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #007bff;
}
.stat-label {
    color: #6c757d;
    margin-top: 5px;
}
.customreports-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
}
.customreports-table th {
    background: #0f6cbf;
    color: white;
    padding: 12px 8px;
    text-align: left;
    border: 1px solid #0a5291;
    font-size: 0.9em;
    position: sticky;
    top: 0;
    z-index: 10;
}
.customreports-table td {
    padding: 10px 8px;
    border: 1px solid #dee2e6;
    vertical-align: top;
    font-size: 0.85em;
}
.customreports-table tr:nth-child(even) {
    background: #f8f9fa;
}
.customreports-table tr:hover {
    background: #e9ecef;
}
.access-info {
    font-size: 0.85em;
    line-height: 1.4;
}
.access-yes {
    color: #28a745;
    font-weight: bold;
}
.access-no {
    color: #6c757d;
    font-style: italic;
}
.grade-badge {
    background: #ff69b4;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: bold;
    display: inline-block;
    margin-top: 5px;
    font-size: 0.9em;
}
.student-photo {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    margin-right: 8px;
    vertical-align: middle;
}
.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.loading-spinner {
    color: white;
    font-size: 1.5em;
}
.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.export-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}
</style>

<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner"> Cargando datos...</div>
</div>

<div class="customreports-filters">
    <form method="get" action="" id="filterForm">
        <div class="filter-row">
            <div class="filter-item" style="flex: 2;">
                <label>Seleccionar curso: *</label>
                <input type="text" id="courseSearch" placeholder="Buscar curso por nombre...">
                <select name="courseid" id="courseSelect" required>
                    <option value="0">Seleccionar curso...</option>
                    <?php
                    $courses = $DB->get_records('course', array(), 'fullname');
                    
                    foreach ($courses as $cid => $course) {
                        if ($cid == SITEID) continue;
                        $selected = ($cid == $courseid) ? 'selected' : '';
                        echo '<option value="' . $cid . '" ' . $selected . ' data-name="' . s(strtolower($course->fullname)) . '">' 
                             . s($course->fullname) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-item">
                <label>Buscar estudiante:</label>
                <input type="text" name="search" value="<?php echo s($search); ?>" 
                       placeholder="Nombre, apellido o email">
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-item">
                <label>Fecha desde:</label>
                <div>
                    <select name="datefrom_day">
                        <option value="">Día</option>
                        <?php
                        for ($i = 1; $i <= 31; $i++) {
                            $sel = ($datefrom && date('j', $datefrom) == $i) ? 'selected' : '';
                            echo '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
                        }
                        ?>
                    </select>
                    <select name="datefrom_month">
                        <option value="">Mes</option>
                        <?php
                        $months = array(1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio',
                                        7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre');
                        foreach ($months as $num => $name) {
                            $sel = ($datefrom && date('n', $datefrom) == $num) ? 'selected' : '';
                            echo '<option value="' . $num . '" ' . $sel . '>' . $name . '</option>';
                        }
                        ?>
                    </select>
                    <select name="datefrom_year">
                        <option value="">Año</option>
                        <?php
                        $currentyear = date('Y');
                        for ($i = $currentyear; $i >= $currentyear - 5; $i--) {
                            $sel = ($datefrom && date('Y', $datefrom) == $i) ? 'selected' : '';
                            echo '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="filter-item">
                <label>Fecha hasta:</label>
                <div>
                    <select name="dateto_day">
                        <option value="">Día</option>
                        <?php
                        for ($i = 1; $i <= 31; $i++) {
                            $sel = ($dateto && date('j', $dateto) == $i) ? 'selected' : '';
                            echo '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
                        }
                        ?>
                    </select>
                    <select name="dateto_month">
                        <option value="">Mes</option>
                        <?php
                        foreach ($months as $num => $name) {
                            $sel = ($dateto && date('n', $dateto) == $num) ? 'selected' : '';
                            echo '<option value="' . $num . '" ' . $sel . '>' . $name . '</option>';
                        }
                        ?>
                    </select>
                    <select name="dateto_year">
                        <option value="">Año</option>
                        <?php
                        for ($i = $currentyear; $i >= $currentyear - 5; $i--) {
                            $sel = ($dateto && date('Y', $dateto) == $i) ? 'selected' : '';
                            echo '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="filter-item">
                <label>Estado de acceso:</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Con acceso</option>
                    <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Sin acceso</option>
                </select>
            </div>
        </div>
        
        <div class="filter-buttons">
            <button type="submit" class="btn-search"> Buscar</button>
            <button type="button" class="btn-clear" onclick="window.location.href='index.php'"> Limpiar filtros</button>
        </div>
    </form>
</div>

<?php

if ($courseid > 0) {
    
    // Obtener información del curso
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    
    echo '<div class="alert-info">';
    echo '<strong> Curso seleccionado:</strong> ' . format_string($course->fullname);
    echo '</div>';
    
    // Query de estudiantes - TODOS los inscritos
    $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, u.picture, 
                   ue.timecreated as enrolldate
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
        $params['dateto'] = $dateto;
    }
    
    $sql .= " ORDER BY u.lastname, u.firstname";
    
    $students = $DB->get_records_sql($sql, $params);
    
    // Filtrar por estado de acceso si es necesario
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
    
    // Obtener TODAS las actividades del curso
    $sql = "SELECT cm.id, cm.instance, cm.section, m.name as modname,
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
            WHERE cm.course = :cid 
                AND cm.deletioninprogress = 0
            ORDER BY cm.section, cm.id";
    
    $activities = $DB->get_records_sql($sql, array('cid' => $courseid));
    
    // Estadísticas
    $totalstudents = count($students);
    $totalactivities = count($activities);
    
    $activestudents = 0;
    foreach ($students as $student) {
        if ($DB->record_exists('logstore_standard_log', array('userid' => $student->id, 'courseid' => $courseid))) {
            $activestudents++;
        }
    }
    
    // Mostrar estadísticas
    echo '<div class="stats-summary">';
    echo '<div class="stat-box">';
    echo '<div class="stat-number">' . $totalstudents . '</div>';
    echo '<div class="stat-label">Total Estudiantes</div>';
    echo '</div>';
    
    echo '<div class="stat-box">';
    echo '<div class="stat-number">' . $activestudents . '</div>';
    echo '<div class="stat-label">Con Actividad</div>';
    echo '</div>';
    
    echo '<div class="stat-box">';
    echo '<div class="stat-number">' . ($totalstudents - $activestudents) . '</div>';
    echo '<div class="stat-label">Sin Actividad</div>';
    echo '</div>';
    
    echo '<div class="stat-box">';
    echo '<div class="stat-number">' . $totalactivities . '</div>';
    echo '<div class="stat-label">Total Actividades</div>';
    echo '</div>';
    echo '</div>';
    
    if (empty($students)) {
        echo $OUTPUT->notification('No se encontraron estudiantes con los criterios seleccionados.', 'info');
    } else {
        
        // Botones de exportación
        $exportparams = 'courseid='.$courseid.'&search='.urlencode($search).
                       '&datefrom_day='.$datefrom_day.'&datefrom_month='.$datefrom_month.'&datefrom_year='.$datefrom_year.
                       '&dateto_day='.$dateto_day.'&dateto_month='.$dateto_month.'&dateto_year='.$dateto_year.
                       '&status='.$status;
        
        echo '<div class="export-buttons">';
        echo '<a href="?'.$exportparams.'&export=csv" class="btn-export"> Exportar CSV</a>';
        echo '<a href="?'.$exportparams.'&export=excel" class="btn-export"> Exportar Excel</a>';
        echo '</div>';
        
        // Tabla
        echo '<div style="overflow-x: auto;">';
        echo '<table class="customreports-table">';
        
        // Encabezados
        echo '<thead><tr>';
        echo '<th style="min-width: 180px;">Estudiante</th>';
        echo '<th style="min-width: 200px;">Email</th>';
        echo '<th style="min-width: 120px;">Fecha Inscripción</th>';
        
        foreach ($activities as $act) {
            echo '<th style="min-width: 150px;">' . s($act->actname) . '<br><small>(' . $act->modname . ')</small></th>';
        }
        
        echo '</tr></thead><tbody>';
        
        // Datos de cada estudiante
        foreach ($students as $student) {
            echo '<tr>';
            
            // Foto y nombre
            $userpic = $OUTPUT->user_picture($student, array('size' => 35, 'link' => false));
            echo '<td>' . $userpic . ' <strong>' . fullname($student) . '</strong></td>';
            echo '<td>' . s($student->email) . '</td>';
            echo '<td>' . userdate($student->enrolldate, '%d/%m/%Y') . '</td>';
            
            // Por cada actividad
            foreach ($activities as $act) {
                echo '<td>';
                
                // Contar accesos
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
                    $accessparams['dt'] = $dateto;
                }
                
                $accessdata = $DB->get_record_sql($accesssql, $accessparams);
                
                if ($accessdata && $accessdata->cnt > 0) {
                    echo '<div class="access-yes">';
                    echo 'Accedida ' . $accessdata->cnt . '<br>veces</div>';
                    
                    if ($accessdata->lasttime) {
                        echo '<div class="access-info">' . userdate($accessdata->lasttime, '%d de %B de %Y<br>%H:%M') . '</div>';
                    }
                    
                    // Obtener calificación
                    $gradesql = "SELECT gg.finalgrade, gi.grademax
                                 FROM {grade_grades} gg
                                 JOIN {grade_items} gi ON gi.id = gg.itemid
                                 WHERE gg.userid = :uid 
                                   AND gi.iteminstance = :inst 
                                   AND gi.itemmodule = :mod
                                   AND gg.finalgrade IS NOT NULL";
                    
                    $grade = $DB->get_record_sql($gradesql, array(
                        'uid' => $student->id, 
                        'inst' => $act->instance, 
                        'mod' => $act->modname
                    ));
                    
                    if ($grade && $grade->grademax > 0) {
                        $gradevalue = ($grade->finalgrade / $grade->grademax) * 10;
                        echo '<div class="grade-badge">' . number_format($gradevalue, 1) . '</div>';
                    }
                    
                } else {
                    echo '<span class="access-no">Sin acceso</span>';
                }
                
                echo '</td>';
            }
            
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '</div>';
    }
}

echo $OUTPUT->footer();
?>

<script>
// Búsqueda de cursos en tiempo real
document.getElementById('courseSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const select = document.getElementById('courseSelect');
    const options = select.getElementsByTagName('option');
    
    for (let i = 0; i < options.length; i++) {
        const option = options[i];
        if (i === 0) continue;
        
        const courseName = option.getAttribute('data-name');
        if (courseName && courseName.includes(searchTerm)) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    }
    
    const visibleOptions = Array.from(options).filter(opt => opt.style.display !== 'none' && opt.value !== '0');
    if (visibleOptions.length === 1) {
        select.value = visibleOptions[0].value;
    }
});

document.getElementById('courseSelect').addEventListener('change', function() {
    if (this.value !== '0') {
        document.getElementById('courseSearch').value = '';
        const options = this.getElementsByTagName('option');
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = '';
        }
    }
});

document.getElementById('filterForm').addEventListener('submit', function() {
    document.getElementById('loadingOverlay').style.display = 'flex';
});
</script>