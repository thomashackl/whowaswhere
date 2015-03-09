<?php
class InitRole extends Migration {

    public function up() {
        if (!DBManager::get()->fetchOne("SELECT `roleid` FROM `roles` WHERE `rolename`='Wer hat wo teilgenommen'")) {
            $role = new Role();
            $role->setRolename('Wer hat wo teilgenommen');
            RolePersistence::saveRole($role);
        }
    }

    public function down() {
        $pid = DBManager::get()->fetchFirst("SELECT `pluginid` FROM `plugins` WHERE `pluginclassname`='WhoWasWherePlugin'");
        if ($roles = RolePersistence::getAssignedPluginRoles($pid[0])) {
            RolePersistence::deleteAssignedPluginRoles($pid[0], $roles);
        }
        $role = DBManager::get()->execute("DELETE FROM `roles` WHERE `rolename`='Wer hat wo teilgenommen'");
    }

}