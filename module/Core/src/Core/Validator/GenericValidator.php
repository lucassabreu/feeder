<?php

namespace Core\Validator;

use Closure;
use Core\Validator\GenericValidator;
use InvalidArgumentException;
use Zend\Validator\AbstractValidator;

/**
 * Callback Validor expanded.
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class GenericValidator extends AbstractValidator {

    /**
     * Anonnimous function with the validation
     * @var Closure
     */
    public $validatorFunction = null;

    public function isValid($value) {
        if ($this->validatorFunction == null || !($this->validatorFunction instanceof Closure))
            throw new InvalidArgumentException("The property validatorFunction must be a Closure");

        return $this->validatorFunction->__invoke($value, $this);
    }

    /**
     * @param  string $messageKey
     * @param  string $value      OPTIONAL
     * @return void
     */
    public function error($messageKey, $value = null) {
        if ($messageKey === null) {
            $keys = array_keys($this->abstractOptions['messageTemplates']);
            $messageKey = current($keys);
        }

        if ($value === null) {
            $value = $this->value;
        }

        $this->abstractOptions['messageTemplates'][$messageKey] = $messageKey;     
        $this->abstractOptions['messages'][$messageKey] = $this->createMessage($messageKey, $value);
    }

    /**
     * Retrives the validator function
     * @return Closure
     */
    public function getValidatorFunction() {
        return $this->validatorFunction;
    }

    /**
     * Sets the validator function
     * @param Closure $validatorFunction
     * @return GenericValidator
     */
    public function setValidatorFunction($validatorFunction) {
        $this->validatorFunction = $validatorFunction;
        return $this;
    }

}

?>
