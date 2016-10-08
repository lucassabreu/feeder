<?php

namespace Core\Filter;

use Core\Util\DateTime as CoreDateTime;
use DateTime as SplDateTime;
use Zend\Filter\AbstractFilter;
use Zend\Filter\FilterInterface;

/**
 * Filter convertor for DateTime types
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class DateTime extends AbstractFilter {

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * @see    FilterInterface::filter()
     * @param  mixed $value
     * @return float
     */
    public function filter($value) {

        if ($value == null)
            return null;

        if ($value instanceof CoreDateTime)
            return $value;

        if ($value instanceof SplDateTime)
            return CoreDateTime::convert($value);

        if (is_object($value))
            return new CoreDateTime($value->__toString());

        return new CoreDateTime($value);
    }

}
