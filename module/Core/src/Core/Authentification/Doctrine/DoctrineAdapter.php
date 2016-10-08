<?php

namespace Core\Authentification\Doctrine;

use DoctrineModule\Authentication\Adapter\ObjectRepository;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;

/**
 * Proxy of <code>ObjectRepository</code> using <code>ValidatableAdapterInterface</code>.
 *
 * @see ObjectRepository
 * @see ValidatableAdapterInterface
 * 
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class DoctrineAdapter implements ValidatableAdapterInterface {

    protected $adapter = null;

    public function __construct(ObjectRepository $adapter) {
        $this->adapter = $adapter;
    }

    public function getDoctrineAdapter() {
        return $this->adapter;
    }

    public function getCredential() {
        return $this->adapter->getCredentialValue();
    }

    public function getIdentity() {
        return $this->adapter->getIdentityValue();
    }

    public function setCredential($credential) {
        $this->adapter->setCredentialValue($credential);
        return $this;
    }

    public function setIdentity($identity) {
        $this->adapter->setIdentityValue($identity);
        return $this;
    }

    public function authenticate() {
        return $this->adapter->authenticate();
    }

}

?>
