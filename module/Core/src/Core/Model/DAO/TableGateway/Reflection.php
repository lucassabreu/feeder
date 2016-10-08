<?php

namespace Core\Model\DAO\TableGateway;

use Core\Model\DAO\Registrator;
use Zend\Stdlib\Hydrator\Reflection as ZendReflection;

/**
 * Extension of Zend Framework class <code>Reflection</code> for TableGateway controls.
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class Reflection extends ZendReflection {

    protected $registrator = null;

    public function __construct(Registrator $registrator) {
        parent::__construct();

        $this->registrator = $registrator;
    }

    public function hydrate(array $data, $object) {
        return $this->registrator->register(parent::hydrate($data, $object));
    }

}
