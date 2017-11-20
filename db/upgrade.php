<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade logic.
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2010-2017 Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_bigbluebuttonbn_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2015080605) {
        // Drop field description.
        xmldb_bigbluebuttonbn_drop_field($dbman, 'bigbluebuttonbn', 'description');
        // Change welcome, allow null.
        $fielddefinition = array('type' => XMLDB_TYPE_TEXT, 'precision' => null, 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => null, 'previous' => 'type');
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'welcome',
            $fielddefinition);
        // Change userid definition in bigbluebuttonbn_log.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '10', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => null,
            'previous' => 'bigbluebuttonbnid');
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn_log', 'userid',
            $fielddefinition);
        // No settings to migrate.
        // Update db version tag.
        upgrade_mod_savepoint(true, 2015080605, 'bigbluebuttonbn');
    }
    if ($oldversion < 2016011305) {
        // Define field type to be droped from bigbluebuttonbn.
        xmldb_bigbluebuttonbn_drop_field($dbman, 'bigbluebuttonbn', 'type');
        // Rename table bigbluebuttonbn_log to bigbluebuttonbn_logs.
        xmldb_bigbluebuttonbn_rename_table($dbman, 'bigbluebuttonbn_log', 'bigbluebuttonbn_logs');
        // Rename field event to log in table bigbluebuttonbn_logs.
        xmldb_bigbluebuttonbn_rename_field($dbman, 'bigbluebuttonbn_logs', 'event', 'log');
        // No settings to migrate.
        // Update db version tag.
        upgrade_mod_savepoint(true, 2016011305, 'bigbluebuttonbn');
    }
    if ($oldversion < 2017101000) {
        // Drop field newwindow.
        xmldb_bigbluebuttonbn_drop_field($dbman, 'bigbluebuttonbn', 'newwindow');
        // Add field type.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '2', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => 'id');
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'type',
            $fielddefinition);
        // Add field recordings_html.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_html',
            $fielddefinition);
        // Add field recordings_deleted.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 1, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_deleted',
            $fielddefinition);
        // Add field recordings_imported.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_imported',
            $fielddefinition);
        // Drop field newwindow.
        xmldb_bigbluebuttonbn_drop_field($dbman, 'bigbluebuttonbn', 'tagging');
        // Migrate settings.
        unset_config('bigbluebuttonbn_recordingtagging_default', '');
        unset_config('bigbluebuttonbn_recordingtagging_editable', '');
        $cfgvalue = get_config('', 'bigbluebuttonbn_importrecordings_from_deleted_activities_enabled');
        set_config('bigbluebuttonbn_importrecordings_from_deleted_enabled', $cfgvalue, '');
        unset_config('bigbluebuttonbn_importrecordings_from_deleted_activities_enabled', '');
        $cfgvalue = get_config('', 'bigbluebuttonbn_moderator_default');
        set_config('bigbluebuttonbn_participant_moderator_default', $cfgvalue, '');
        unset_config('bigbluebuttonbn_moderator_default', '');
        // Update db version tag.
        upgrade_mod_savepoint(true, 2017101000, 'bigbluebuttonbn');
    }
    return true;
}

function xmldb_bigbluebuttonbn_add_change_field($dbman, $tablename, $fieldname, $fielddefinition) {
    $table = new xmldb_table($tablename);
    $field = new xmldb_field($fieldname);
    $field->set_attributes($fielddefinition['type'], $fielddefinition['precision'], $fielddefinition['unsigned'],
        $fielddefinition['notnull'], $fielddefinition['sequence'], $fielddefinition['default'],
        $fielddefinition['previous']);
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_type($table, $field, true, true);
        $dbman->change_field_precision($table, $field, true, true);
        $dbman->change_field_notnull($table, $field, true, true);
        $dbman->change_field_default($table, $field, true, true);
        return;
    }
    $dbman->add_field($table, $field, true, true);
}

function xmldb_bigbluebuttonbn_drop_field($dbman, $tablename, $fieldname) {
    $table = new xmldb_table($tablename);
    $field = new xmldb_field($fieldname);
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field, true, true);
    }
}

function xmldb_bigbluebuttonbn_rename_field($dbman, $tablename, $fieldnameold, $fieldnamenew) {
    $table = new xmldb_table($tablename);
    $field = new xmldb_field($fieldnameold);
    if ($dbman->field_exists($table, $field)) {
        $dbman->rename_field($table, $field, $fieldnamenew, true, true);
    }
}

function xmldb_bigbluebuttonbn_rename_table($dbman, $tablenameold, $tablenamenew) {
    $table = new xmldb_table($tablenameold);
    if ($dbman->table_exists($table)) {
        $dbman->rename_table($table, $tablenamenew, true, true);
    }
}
