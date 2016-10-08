<?php

namespace Core\Model\DAO;

use Core\Model\Entity\Entity;

/**
 * Class with register based processes
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
interface Registrator {
    public function register(Entity $entity);
}
