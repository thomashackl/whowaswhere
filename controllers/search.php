<?php
/**
 * search.php
 *
 * Provides a search form and displays search results.
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

class SearchController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        // Check for AJAX.
        if (Request::isXhr()) {
            $this->set_layout(null);
            header('Content-Type: text/html; charset=windows-1252');
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/search-sidebar.png');
    }

    public function index_action() {
        // Navigation handling.
        Navigation::activateItem('/search/whowaswhere/search');
        if (class_exists('FullUserSearch')) {
            $search = new FullUserSearch('user_id');
        } else {
            $search = new StandardSearch('user_id');
        }
        $this->search = QuickSearch::get('user_id', $search)
            ->render();
        $this->semesters = Semester::getAll();
    }

    /**
     * Get results for a given user and semester.
     */
    public function results_action() {
        // Navigation handling.
        Navigation::activateItem('/search/whowaswhere');
        /*
         * Check if a user_id was given or just pressed enter after entering
         * something in search field.
         */
        if ($user_id = Request::option('user_id')) {
            $query = "SELECT s.`Seminar_id`, s.`VeranstaltungsNummer`, s.`Name`, st.`name` AS type, su.`status`, sd.`name` AS semester
                FROM `seminare` s
                    INNER JOIN `seminar_user` su ON (s.`Seminar_id`=su.`Seminar_id`)
                    INNER JOIN `sem_types` st ON (s.`status`=st.`id`)
                    INNER JOIN `semester_data` sd ON (s.`start_time` BETWEEN sd.`beginn` AND sd.`ende`)
                WHERE su.`user_id`=?
                    AND s.`status` NOT IN (?)";
            $parameters = array($user_id, Config::get()->STUDYGROUPS_ENABLE ? studygroup_sem_types() : array());
            if ($status = Request::quoted('status')) {
                $query .= " AND su.`status` IN (?) ";
                $parameters[] = explode(",", $status);
            }
            if ($start_time = Request::option('start_time')) {
                $query .= " AND s.`start_time`=?";
                $parameters[] = $start_time;
            }
            $query .= "ORDER BY s.`start_time` DESC, s.`VeranstaltungsNummer`, s.`Name`";
            $courses = DBManager::get()->fetchAll($query, $parameters);
            if ($courses) {
                $this->courses = array();
                foreach ($courses as $c) {
                    $this->courses[$c['semester']][] = $c;
                }
                $this->user = User::find($user_id);
            } else {
                $this->message = MessageBox::info(sprintf(dgettext('whowaswhere',
                    'Es wurden keine Veranstaltungen für %s gefunden.'),
                    User::find($user_id)->getFullname()));
            }
            // Add semester selection filter widget.
            $semselect = new SelectWidget(dgettext('whowaswhere', 'Semester einschränken'),
                URLHelper::getLink('plugins.php/whowaswhereplugin/search/results',
                    array('user_id' => $user_id, 'status' => $status)),
                'start_time', 'post');
            $semselect->addElement(new SelectElement(0, dgettext('whowaswhere', 'Alle Semester'),
                $start_time == 0), 'semester-0');
            foreach (Semester::getAll() as $semester) {
                $semselect->addElement(new SelectElement($semester->beginn, $semester->name,
                    $start_time == $semester->beginn), 'semester-'.$semester->beginn);
            }
            $this->sidebar->addWidget($semselect);
            // Add status selection filter widget.
            $statselect = new SelectWidget(dgettext('whowaswhere', 'Status einschränken'),
                URLHelper::getLink('plugins.php/whowaswhereplugin/search/results',
                    array('user_id' => $user_id, 'start_time' => $start_time)),
                'status', 'post');
            $statselect->addElement(new SelectElement('',
                dgettext('whowaswhere', 'Nicht einschränken'),
                $status == ''), 'status-all');
            $statselect->addElement(new SelectElement('user,autor',
                dgettext('whowaswhere', 'Teilnehmer/in'),
                $status == 'user,autor'), 'status-user-autor');
            $statselect->addElement(new SelectElement('tutor,dozent',
                dgettext('whowaswhere', 'Lehrende/r'),
                $status == 'tutor,dozent'), 'status-tutor-dozent');
            $this->sidebar->addWidget($statselect);
        // No real user_id given -> redirect to search form.
        } else {
            $this->redirect(URLHelper::getLink('plugins.php/whowaswhereplugin/search',
                array('user_id_parameter' => Request::quoted('user_id_parameter'))));
        }
    }

    // customized #url_for for plugins
    function url_for($to) {
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

}

