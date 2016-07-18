<?php
/**
 * export.php
 *
 * Allows exporting the currently shown own courses as PDF overview.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class ExportController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        // Check for AJAX.
        if (Request::isXhr()) {
            $this->set_layout(null);
            header('Content-Type: text/html; charset=windows-1252');
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
    }

    public function index_action()
    {

        PageLayout::setTitle(dgettext('whowaswhere', 'Meine Veranstaltungen exportieren'));

        $this->courses = $this->getCourses();

    }

    public function do_action()
    {
        $courses = $this->getCourses(Request::getArray('courses'));

        $csv = array();

        $csv[] = array(sprintf(
            dgettext('whowaswhere', 'Veranstaltungs�bersicht f�r %s'),
            $GLOBALS['user']->getFullname()));
        $csv[] = array(sprintf(
            dgettext('whowaswhere', 'Daten vom %s'),
            date('d.m.Y H:i')));

        foreach ($courses as $semester => $data) {
            foreach ($data as $course) {
                $csv[] = array(
                    $semester,
                    $course['VeranstaltungsNummer'],
                    $course['Name'],
                    $course['type']
                );
            }
        }

        $this->response->add_header('Content-Type', 'text/csv');
        $this->response->add_header('Content-Disposition',
            'attachment; filename=veranstaltungen-'.$GLOBALS['user']->username.'.csv');
        $this->render_text(array_to_csv($csv));
    }

    // customized #url_for for plugins
    public function url_for($to)
    {
        $args = func_get_args();

        // find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        // urlencode all but the first argument
        $args = array_map("urlencode", $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->plugin, $params, join("/", $args));
    }

    private function getCourses($course_ids = array())
    {

        // Get all allowed course types.
        $categories = Config::get()->WHOWASWHERE_SHOW_COURSE_CATEGORIES ?: array(1);
        $types = array_filter(SemType::getTypes(), function ($t) use ($categories) { return in_array($t['class'], $categories); });

        // Get my courses...
        $query = "SELECT s.`Seminar_id`, s.`VeranstaltungsNummer`, s.`Name`,
                    st.`name` AS type, su.`status`, sd.`name` AS semester
                FROM `seminare` s
                    INNER JOIN `seminar_user` su ON (s.`Seminar_id`=su.`Seminar_id`)
                    INNER JOIN `sem_types` st ON (s.`status`=st.`id`)
                    INNER JOIN `semester_data` sd ON (s.`start_time` BETWEEN sd.`beginn` AND sd.`ende`)
                WHERE su.`user_id` = ?
                    AND s.`status` NOT IN (?)
                    AND s.`status` IN (?)
                    AND su.`status` IN (?)
                    AND s.`start_time` <= UNIX_TIMESTAMP()";

        $parameters = array(
            $GLOBALS['user']->id,
            Config::get()->STUDYGROUPS_ENABLE ? studygroup_sem_types() : array(),
            array_map(function ($t) { return $t['id']; }, $types),
            array('autor')
        );

        if ($course_ids) {
            $query .= " AND s.`Seminar_id` IN (?)";
            $parameters[] = $course_ids;
        }

        $query .= " ORDER BY s.`start_time` DESC, s.`VeranstaltungsNummer`, s.`Name`";
        $courses = DBManager::get()->fetchAll($query, $parameters);

        // ... and sort them by semester.
        $sorted = array();
        if ($courses) {
            foreach ($courses as $c) {
                $sorted[$c['semester']][] = $c;
            }
        }

        return $sorted;
    }

}
