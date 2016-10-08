<?php

namespace Core\Service\Exception;

use Core\Model\DAO\Exception\DAOException;

/**
 * Exception for a user/business error
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class BusinessException extends DAOException {

    public $message;
    public $help;

    public function __construct($message, $help = null, $code = null, $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->message = $message;
        $this->help = $help;
    }

    public function getHelp() {
        return $this->help;
    }

}
