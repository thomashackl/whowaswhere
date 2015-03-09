<?php
/**
 * WhoWasWherePlugin.class.php
 *
 * Plugin for searching courses a given user has participated in.
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

require 'bootstrap.php';

class WhoWasWherePlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        // Localization
        bindtextdomain('whowaswhere', realpath(dirname(__FILE__).'/locale'));
        $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, array(), 'search'));
        $navigation->addSubNavigation('search', new Navigation(dgettext('whowaswhere', 'Suche'), PluginEngine::getURL($this, array(), 'search')));
        Navigation::addItem('/search/whowaswhere', $navigation);
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName() {
        return dgettext('whowaswhere', 'Wer hat wo teilgenommen?');
    }

    public function perform($unconsumed_path) {
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'search'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    public static function onEnable($plugin_id) {
        parent::onEnable($plugin_id);
        $role = DBManager::get()->fetchFirst("SELECT `roleid` FROM `roles` WHERE `rolename`='Wer hat wo teilgenommen'");
        if ($role) {
            RolePersistence::assignPluginRoles($plugin_id, array($role, 1));
        }
    }

    public static function onDisable($plugin_id) {
        if ($roles = RolePersistence::getAssignedPluginRoles($plugin_id)) {
            RolePersistence::deleteAssignedPluginRoles($plugin_id, array_map(function($r) { return $r->roleid; }, $roles));
        }
        parent::onDisable($plugin_id);
    }

}
