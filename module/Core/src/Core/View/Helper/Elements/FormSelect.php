<?php

namespace Core\View\Helper\Elements;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormSelect as ZendFormSelect;

/**
 * Description of FormSelect
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class FormSelect extends ZendFormSelect {
    
    /**
     * Generate an opening select tag
     *
     * @param  null|array|ElementInterface $attributesOrElement
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function openTag($attributesOrElement = null) {
        if (null === $attributesOrElement) {
            return '<select>';
        }

        if (is_array($attributesOrElement)) {
            $attributes = $this->createAttributesString($attributesOrElement);
            return sprintf('<select %s>', $attributes);
        }

        if (!$attributesOrElement instanceof ElementInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                    '%s expects an array or Zend\Form\ElementInterface instance; received "%s"', __METHOD__, (is_object($attributesOrElement) ? get_class($attributesOrElement) : gettype($attributesOrElement))
            ));
        }

        $element = $attributesOrElement;
        $name = $element->getName();

        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                    '%s requires that the element has an assigned name; none discovered', __METHOD__
            ));
        }

        $attributes = $element->getAttributes();
        $attributes['name'] = $name;
        $attributes['id'] = $this->getId($element);

        $this->validTagAttributes = $this->validSelectAttributes;
        
        return sprintf(
                '<select %s>', $this->createAttributesString($attributes)
        );
    }

    /**
     * Return a closing select tag
     *
     * @return string
     */
    public function closeTag() {
        return '</select>';
    }

}

?>
