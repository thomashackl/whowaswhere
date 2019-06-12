<?php
/**
 * This migration provides a global config entry in order to specify which
 * course categories shall be shown in the list. Default value is "1",
 * meaning only lessons and not community or organisational courses.
 */

class RestrictCategories extends Migration {

    public function up() {
        Config::get()->create('WHOWASWHERE_SHOW_COURSE_CATEGORIES', [
            'value' => json_encode([1]),
            'type' => 'array',
            'range' => 'global',
            'section' => 'whowaswhere',
            'description' => 'Welche Veranstaltungskategorien sollen in der Liste erscheinen? (Standard: Lehre)'
        ]);
        Config::get()->create('WHOWASWHERE_MATRICULATION_DATAFIELD_ID', [
            'value' => '',
            'type' => 'string',
            'range' => 'global',
            'section' => 'whowaswhere',
            'description' => 'ID des freien Datenfelds für die Matrikelnummer (kann auch leer sein, dann wird nichts angezeigt)'
        ]);
    }

    public function down() {
        Config::get()->delete('WHOWASWHERE_SHOW_COURSE_CATEGORIES');
        Config::get()->delete('WHOWASWHERE_MATRICULATION_DATAFIELD_ID');
    }

}
