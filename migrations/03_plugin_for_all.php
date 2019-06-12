<?php

/**
 * Allows general plugin access for everyone by removing the role assignment
 * to the plugin itself. Access permission is now handled in the corresponding
 * controller.
 */

class PluginForAll extends Migration {

    public function up() {
        $pid = DBManager::get()->fetchOne(
            "SELECT `pluginid` FROM `plugins` WHERE `pluginclassname`='WhoWasWherePlugin'");
        $rid = DBManager::get()->fetchOne(
            "SELECT `roleid` FROM `roles` WHERE `rolename` = 'Wer hat wo teilgenommen'");
        if ($roles = RolePersistence::getAssignedPluginRoles($pid['pluginid'])) {
            RolePersistence::deleteAssignedPluginRoles($pid['pluginid'], array($rid['roleid']));
        }
    }

    public function down() {
        $pid = DBManager::get()->fetchOne(
            "SELECT `pluginid` FROM `plugins` WHERE `pluginclassname`='WhoWasWherePlugin'");
        $rid = DBManager::get()->fetchOne(
            "SELECT `roleid` FROM `roles` WHERE `rolename` = 'Wer hat wo teilgenommen'");
        DBManager::get()->execute("INSERT IGNORE INTO `roles_plugins` VALUES (:rid, :pid)",
            array('rid' => $rid['roleid'], 'pid' => $pid['pluginid']));
    }

}
