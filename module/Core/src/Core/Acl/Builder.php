<?php

namespace Core\Acl;

use Core\Service\Service;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Permissions\Acl\Role\GenericRole as Role;

/**
 * Class retrieves a filled ACL. 
 * @author Lucas dos Santos abreu <lucas.s.abreu@gmail.com>
 */
class Builder extends Service {

    /**
     * Constroi a ACL
     * @return Acl 
     */
    public function build() {
        $config = $this->getService('Config');
        $acl = new Acl();
        foreach ($config['acl']['roles'] as $role => $parent) {
            $acl->addRole(new Role(strtolower($role)), $parent === null ? null : strtolower($parent));
        }
        foreach ($config['acl']['resources'] as $r) {
            $acl->addResource(new Resource($r));
        }
        foreach ($config['acl']['privilege'] as $role => $privilege) {
            if (isset($privilege['allow'])) {
                foreach ($privilege['allow'] as $p) {
                    $acl->allow($role, $p);
                }
            }
            if (isset($privilege['deny'])) {
                foreach ($privilege['deny'] as $p) {
                    $acl->deny($role, $p);
                }
            }
        }

        return $acl;
    }

}