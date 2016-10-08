<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Filter\FilterInterface;

/**
 * Description of Float
 *
 *  @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class Float extends AbstractFilter {

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * @see    FilterInterface::filter()
     * @param  mixed $value
     * @return float
     */
    public function filter($value) {
        $value = floatval($value);

        if ($value === 0)
            $value = '0.00';

        return $value;
    }

}
