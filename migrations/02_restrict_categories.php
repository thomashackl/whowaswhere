<?php
/**
 * This migration provides a global config entry in order to specify which
 * course categories shall be shown in the list. Default value is "1",
 * meaning only lessons and not community or organisational courses.
 */

class RestrictCategories extends Migration {

    public function up() {
        DBManager::get()->execute("INSERT IGNORE INTO `config` VALUES
            (MD5('WHOWASWHERE_SHOW_COURSE_CATEGORIES'), '', 'WHOWASWHERE_SHOW_COURSE_CATEGORIES', '[1]', 1, 'array',
                'global', 'whowaswhere', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                'Welche Veranstaltungskategorien sollen in der Liste erscheinen? (Standard: Lehre)', '', ''),
            (MD5('WHOWASWHERE_MATRICULATION_DATAFIELD_ID'), '', 'WHOWASWHERE_MATRICULATION_DATAFIELD_ID', '', 1,
                'string', 'global', 'whowaswhere', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                'ID des freien Datenfelds für die Matrikelnummer (kann auch leer sein, dann wird nichts angezeigt)',
                '', '')
        ");
    }

    public function down() {
        DBManager::get()->execute("DELETE FROM `config` WHERE `field` IN ('WHOWASWHERE_SHOW_COURSE_CATEGORIES',
            'WHOWASWHERE_MATRICULATION_DATAFIELD_ID')");
    }

}
