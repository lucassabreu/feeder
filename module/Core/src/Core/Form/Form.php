<?php

namespace Core\Form;

use Exception;
use Zend\Form\Form as ZendForm;

/**
 * Implementation of Zend Form with CRUD controls
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class Form extends ZendForm {

    /**
     * Array with messages of exceptions not associatad with fields
     * @var array
     */
    private $exceptionMessages = array();

    /**
     * Set all the elements on form to read only
     */
    public function readonly() {
        foreach ($this as $element) {
            if ($element->getAttribute('type') == 'select' || $element->getAttribute('type') == 'checkbox'):
                $element->setAttribute('disabled', 'disabled');
            else:
                $element->setAttribute('readonly', 'readonly');
            endif;
        }
    }

    /**
     * Enable the elements
     */
    public function editable() {
        foreach ($this as $element) {
            $element->setAttribute('readonly', null);
        }
    }

    /**
     * Retrieves the exception messages
     * @return array
     */
    public function getExceptionMessages() {
        return $this->exceptionMessages;
    }

    /**
     * Set the form's messages
     * @param \Exception|string $exceptionMessage
     */
    public function setExceptionMessages($exceptionMessages = array()) {

        foreach ($exceptionMessages as $key => $value) {
            if ($value instanceof Exception)
            /* @var $value Exception */
                $exceptionMessages[$key] = $value = $value->getMessage();
        }

        $this->exceptionMessages = $exceptionMessages;
    }

    /**
     * Add a message (<code>string</code> or <code>Exception</code>) to the form
     * @param \Exception|string $exceptionMessage
     */
    public function addExceptionMessage($exceptionMessage) {
        if ($exceptionMessage instanceof Exception)
            $exceptionMessage = $exceptionMessage->getMessage();

        $this->exceptionMessages[] = $exceptionMessage;
    }

    public function getMessages($elementName = null) {

        $messages = parent::getMessages($elementName);

        if ($elementName !== null)
            return $messages;
        else {
            if (count($this->getExceptionMessages()) > 0)
                $messages[$this->getName()] = $this->getExceptionMessages();
            return $messages;
        }
    }

    public function isValid() {
        if (count($this->exceptionMessages) === 0 && parent::isValid())
            return true;
        else
            return false;
    }

}

?>
