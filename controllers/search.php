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
    public function before_filter(&$action, &$args)
    {
        if (!$GLOBALS['perm']->have_perm('root') &&
                !RolePersistence::isAssignedRole($GLOBALS['user']->id, 'Wer hat wo teilgenommen') &&
                !RolePersistence::isAssignedRole($GLOBALS['user']->id, 'Wer hat wo teilgenommen - eingeschränkt')) {
            throw new AccessDeniedException(dgettext('whowaswhere',
                'Sie haben nicht die nötigen Rechte, um auf diese Funktion zuzugreifen!'));
        }

        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        // Check for AJAX.
        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/search-sidebar.png');
    }

    public function index_action()
    {
        // Navigation handling.
        Navigation::activateItem('/search/whowaswhere/search');
        $search = new PermissionSearch(
            'user',
            '',
            'user_id',
            array(
                'permission' => array('user', 'autor', 'tutor', 'dozent'),
                'exclude_user' => array()
            )
        );
        $this->search = QuickSearch::get('user_id', $search)
            ->render();
        $this->semesters = Semester::getAll();
    }

    /**
     * Get results for a given user and semester.
     */
    public function results_action()
    {
        // Navigation handling.
        Navigation::activateItem('/search/whowaswhere');

        /*
         * Check if a user_id was given or just pressed enter after entering
         * something in search field.
         */
        if ($user_id = Request::option('user_id')) {

            $this->categories = array_map(function ($c) {
                return SeminarCategories::get($c)->name;
            }, Config::get()->WHOWASWHERE_SHOW_COURSE_CATEGORIES ?: array(1));

            $this->user = User::find($user_id);

            $start_time = Request::int('start_time', 0);

            if (Request::get('status')) {

                $rawstatus = Request::get('status');
                $status = explode(',', Request::get('status'));

            } else {

                $status = 'user,autor,tutor,dozent';

                if (Request::option('awaiting')) {
                    $status .= ',awaiting';
                }

                if (Request::option('accepted')) {
                    $status .= ',accepted';
                }

                $rawstatus = $status;
                $status = explode(',', $status);

            }

            // Get courses for given user.
            $this->courses = $this->getCourses($user_id, $status, Request::int('start_time', 0));

            if (Config::get()->WHOWASWHERE_MATRICULATION_DATAFIELD_ID) {
                $matriculation = DBManager::get()->fetchOne(
                    "SELECT `content` FROM `datafields_entries` WHERE `datafield_id` = ? AND `range_id` = ?",
                    array(Config::get()->WHOWASWHERE_MATRICULATION_DATAFIELD_ID, $user_id));
                $this->matriculation = $matriculation['content'];
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
            $statselect->addElement(new SelectElement('user,autor,tutor,dozent,awaiting,accepted',
                dgettext('whowaswhere', 'Nicht einschränken'),
                $rawstatus == 'user,autor,tutor,dozent,awaiting,accepted'), 'status-all');
            $statselect->addElement(new SelectElement('user,autor,tutor,dozent',
                dgettext('whowaswhere', 'Nur Veranstaltungsteilnahmen'),
                $rawstatus == 'user,autor,tutor,dozent'), 'status-participant');
            $statselect->addElement(new SelectElement('dozent',
                dgettext('whowaswhere', 'Lehrende/r'),
                $rawstatus == 'dozent'), 'status-dozent');
            $statselect->addElement(new SelectElement('tutor',
                dgettext('whowaswhere', 'Tutor/in'),
                $rawstatus == 'tutor'), 'status-tutor');
            $statselect->addElement(new SelectElement('autor',
                dgettext('whowaswhere', 'Teilnehmer/in'),
                $rawstatus == 'autor'), 'status-autor');
            $statselect->addElement(new SelectElement('user',
                dgettext('whowaswhere', 'Leser/in'),
                $rawstatus == 'user'), 'status-user');
            $statselect->addElement(new SelectElement('awaiting',
                dgettext('whowaswhere', 'Warteliste'),
                $rawstatus == 'awaiting'), 'status-awaiting');
            $statselect->addElement(new SelectElement('accepted',
                dgettext('whowaswhere', 'Vorläufig akzeptiert'),
                $rawstatus == 'accepted'), 'status-accepted');
            $this->sidebar->addWidget($statselect);

            PageLayout::setTitle($this->plugin->getDisplayName() . ' - ' .
                sprintf(dgettext('whowaswhere', 'Suchergebnis für %s'), $this->user->getFullname()));

        // No real user_id given -> redirect to search form.
        } else {

            $this->redirect(URLHelper::getLink('plugins.php/whowaswhereplugin/search',
                array('user_id_parameter' => Request::quoted('user_id_parameter'))));

        }

    }

    public function export_csv_action($user_id, $status, $start_time)
    {
        $courses = $this->getCourses($user_id, explode(',', $status), $start_time);
        $data = array(
            array(_('Semester'), _('Nummer'), _('Titel'), _('Dozent/in'), _('Zeiten'))
        );

        $user = User::find($user_id)->getFullname();

        header("Content-type: text/csv;charset=utf-8");
        header("Content-disposition: attachment; filename=" . $filename . ".vcf");
        header("Pragma: private");
    }

    // customized #url_for for plugins
    public function url_for($to = '')
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

    private function getCourses($user_id, $status = ['user','autor','tutor','dozent','awaiting','accepted'], $start_time = 0)
    {

        // Check if current user can only see results coming from own institutes.
        $onlyOwn = false;
        if (RolePersistence::isAssignedRole($GLOBALS['user']->id, 'Wer hat wo teilgenommen - eingeschränkt')) {
            $onlyOwn = true;
        }

        // Get all allowed course types.
        $categories = Config::get()->WHOWASWHERE_SHOW_COURSE_CATEGORIES ?: array(1);
        $types = array_filter(SemType::getTypes(), function ($t) use ($categories) { return in_array($t['class'], $categories); });

        // Get courses for given user...
        $query = "SELECT s.`Seminar_id`, s.`VeranstaltungsNummer`, s.`Name`, s.`start_time`,
                    st.`name` AS type, su.`status`, sd.`name` AS semester, su.`mkdate`
                FROM `seminare` s " .
                ($onlyOwn ? "    INNER JOIN `seminar_inst` si ON (s.`Seminar_id` = si.`seminar_id`) " : "") .
                "    INNER JOIN `seminar_user` su ON (s.`Seminar_id` = su.`Seminar_id`)
                    INNER JOIN `sem_types` st ON (s.`status` = st.`id`)
                    INNER JOIN `semester_data` sd ON (s.`start_time` BETWEEN sd.`beginn` AND sd.`ende`)
                WHERE su.`user_id` = :user
                    AND s.`status` IN (:include)
                    AND su.`status` IN (:userstatus)";

        $parameters = [
            'user' => $user_id,
            'include' => array_map(function ($t) { return $t['id']; }, $types),
            'userstatus' => array_filter($status, function ($one) {
                return in_array($one, ['user', 'autor', 'tutor', 'dozent']);
            })
        ];

        if ($onlyOwn) {
            $query .= " AND si.`institut_id` IN (:inst)";
            $parameters['inst'] = array_map(function ($i) { return $i['Institut_id']; }, Institute::getMyInstitutes());
        }

        if ($start_time) {
            $query .= " AND s.`start_time`=:start ";
            $parameters['start'] = $start_time;
        }

        if (in_array('awaiting', $status) || in_array('accepted', $status)) {
            $query .= "UNION
                SELECT s.`Seminar_id`, s.`VeranstaltungsNummer`, s.`Name`, s.`start_time`,
                    st.`name` AS type, a.`status`, sd.`name` AS semester, a.`mkdate`
                FROM `seminare` s " .
                ($onlyOwn ? "    INNER JOIN `seminar_inst` si ON (s.`Seminar_id`=si.`seminar_id`) " : "") .
                "    INNER JOIN `admission_seminar_user` a ON (s.`Seminar_id` = a.`seminar_id`)
                    INNER JOIN `sem_types` st ON (s.`status` = st.`id`)
                    INNER JOIN `semester_data` sd ON (s.`start_time` BETWEEN sd.`beginn` AND sd.`ende`)
                WHERE a.`user_id` = :user
                    AND s.`status` IN (:include)
                    AND a.`status` IN (:prelimstatus)";

            if ($onlyOwn) {
                $query .= " AND si.`institut_id` IN (:inst)";
            }

            if ($start_time) {
                $query .= " AND s.`start_time`=:start ";
            }

            $parameters['prelimstatus'] = array_filter($status, function ($one) {
                return in_array($one, ['awaiting', 'accepted']);
            });
        }

        $query .= " ORDER BY `start_time` DESC, `VeranstaltungsNummer`, `Name`";
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

