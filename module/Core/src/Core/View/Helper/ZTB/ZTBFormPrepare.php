<?php

namespace Core\View\Helper\ZTB;

use Zend\Form\Element\Button;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper to prepare form and form items to use Twitter Bootstrap
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class ZTBFormPrepare extends AbstractHelper {

    /**
     * Alter attribute on Form to use Twitter Bootstrap CSS classes
     * @param Form $form
     * @param string $formLayout (optional)
     * @return Form
     */
    public function __invoke(Form $form, $formLayout = null) {
        if (!is_null($formLayout))
            $form->setAttribute('class', "form-$formLayout");

        foreach ($form as $element):
            $element->setLabelAttributes(array('class' => 'control-label'));

            if (($element instanceof Button)) :
                $element->setAttribute('class', 'btn');
            endif;
        endforeach;

        return $form;
    }

}

?>
