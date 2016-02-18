<?php
/**
 * This migration provides a global config entry in order to specify which
 * course categories shall be shown in the list. Default value is "1",
 * meaning only lessons and not community or organisational courses.
 */

class RestrictCategories extends Migration {

    public function up() {
        DBManager::get()->execute("INSERT IGNORE INTO `config` VALUES (
            MD5('WHOWASWHERE_SHOW_COURSE_CATEGORIES'),
            '',
            'WHOWASWHERE_SHOW_COURSE_CATEGORIES',
            '[1]',
            1,
            'array',
            'global',
            'whowaswhere',
            1,
            UNIX_TIMESTAMP(),
            UNIX_TIMESTAMP(),
            'Welche Veranstaltungskategorien sollen in der Liste erscheinen? (Standard: Lehre)',
            '',
            '')");
    }

    public function down() {
        DBManager::get()->execute("DELETE FROM `config` WHERE `field` = 'WHOWASWHERE_SHOW_COURSE_CATEGORIES'");
    }

}
