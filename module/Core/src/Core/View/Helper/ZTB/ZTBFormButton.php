<?php

namespace Core\View\Helper\ZTB;

use Zend\Form\ElementInterface;
use Zend\Form\Exception\InvalidArgumentException;
use Zend\Form\View\Helper\FormButton;
use Exception;

/**
 * Improvement of FormButton for use potencial of Twitter Bootstrap
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class ZTBFormButton extends FormButton {

    public function __invoke(ElementInterface $element = null, $buttonContent = null, $extendParams = array()) {
        if (!$element)
            return $this;

        return $this->render($element, $buttonContent, $extendParams);
    }

    public function render(ElementInterface $element, $buttonContent = null, $extendParams = array()) {
        if (count($extendParams) !== 0) {
            $options = $element->getOptions();

            if (isset($extendParams['btn_type']))
                $options['btn_type'] = $extendParams['btn_type'];

            if (isset($extendParams['icon']))
                $options['icon'] = $extendParams['icon'];

            $element->setOptions($options);
        }

        return parent::render($element, $buttonContent);
    }

    public function openTag($attributesOrElement = null) {

        $return = "";
        $icon = null;

        if (null === $attributesOrElement) {
            $attributesOrElement = array();
        }

        if (is_array($attributesOrElement)) {
            if (isset($attributesOrElement['class'])) {
                $class = $attributesOrElement['class'];
            } else {
                $class = "";
            }

            $class = "$class ";

            if (isset($attributesOrElement['btn_type'])) {
                $class = "btn-{$attributesOrElement['btn_type']} $class";
                unset($attributesOrElement['btn_type']);
            }

            if (!(strpos($class, 'btn ') > 0))
                $class = "btn $class";

            $attributesOrElement['class'] = trim($class);

            $return = parent::openTag($attributesOrElement);

            if (isset($attributesOrElement['icon'])) {
                $icon = $attributesOrElement['icon'];
                unset($attributesOrElement['icon']);
            }
        } else {
            if (!$attributesOrElement instanceof ElementInterface) {
                throw new InvalidArgumentException(sprintf(
                        '%s expects an array or Zend\Form\ElementInterface instance; received "%s"', __METHOD__, (is_object($attributesOrElement) ? get_class($attributesOrElement) : gettype($attributesOrElement))
                ));
            }

            $element = $attributesOrElement;
            /* @var $element ElementInterface */

            if ($element->getAttribute('class') === null)
                $class = '';
            else
                $class = "{$element->getAttribute('class')} ";

            $options = $element->getOptions();

            if (isset($options['btn_type'])) {
                $class = "btn-{$options['btn_type']} $class";
                unset($options['btn_type']);
            }

            if (!(strpos($class, "btn ") > 0))
                $class = "btn $class";

            $element->setAttribute('class', trim($class));

            $return = parent::openTag($element);

            if (isset($options['icon'])) {
                $icon = $options['icon'];
                unset($options['icon']);
            }

            $element->setOptions($options);
        }

        if ($icon !== null) {
            $icons = preg_split('/ /', $icon);
            $icon = "icon";
            foreach ($icons as $ic)
                $icon .= " icon-$ic";

            $return = $return . sprintf('<i class="%s"></i> ', $icon);
        }

        return $return;
    }

}

?>
