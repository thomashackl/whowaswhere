<?php

/**
 * Creates a role called "Wer hat wo teilgenommen - eingeschränkt" which can be assigned to
 * users and allows accessing the plugin functions, but only for courses at own institutes.
 */

class ViewOnlyOwnInstitutes extends Migration {

    public function up() {
        if (!DBManager::get()->fetchOne("SELECT `roleid` FROM `roles` WHERE `rolename`='Wer hat wo teilgenommen - eingeschränkt'")) {
            $role = new Role();
            $role->setRolename('Wer hat wo teilgenommen - eingeschränkt');
            RolePersistence::saveRole($role);
        }
    }

    public function down() {
        if ($roleid = DBManager::get()->fetchOne("SELECT `roleid` FROM `roles` WHERE `rolename`='Wer hat wo teilgenommen - eingeschränkt'")) {
            $role = new Role();
            $role->setRoleid($roleid);
            RolePersistence::deleteRole($role);
        }
    }

}
