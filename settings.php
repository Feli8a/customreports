<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_externalpage(
        'local_customreports',
        get_string('pluginname', 'local_customreports'),
        new moodle_url('/local/customreports/index.php'),
        'local/customreports:view'
    ));
}