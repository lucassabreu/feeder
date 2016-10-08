<?php

namespace FeederRestful\Model\Entity;

use Core\Model\Entity\Entity;
use Core\Util\DateTime;
use Zend\InputFilter\Factory;

/**
 * Object representation of feed's collection
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * 
 * @property int $id Identification of Entry
 * @property string $link Link to entry
 * @property int $feedId Feed's ID
 * @property string $title Title of Entry
 * @property string $content Content of entry
 * @property DateTime $dateModified Last data that entry was updated
 * @property boolean $readed If the entry has been readed
 * @property string $authors Authors of Entry
 */
class Entry extends Entity {

    protected $id;
    protected $link;
    protected $feedId;
    protected $title;
    protected $content;
    protected $dateModified;
    protected $authors;
    protected $readed;

    public function getInputFilter() {
        if ($this->inputFilter === null) {
            $factory = new Factory();
            $this->inputFilter = $factory->createInputFilter(array(
                'id' => array(
                    'name' => 'id',
                    'required' => false,
                    'allow_empty' => true,
                ),
                'link' => array(
                    'name' => 'link',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StripTags'),
                    ),
                    'validators' => array(
                        array('name' => 'NotEmpty'),
                    ),
                ),
                'feedId' => array(
                    'name' => 'feedId',
                    'required' => false,
                    'allow_empty' => true,
                ),
                'title' => array(
                    'name' => 'title',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StripTags'),
                    ),
                    'validators' => array(
                        array('name' => 'NotEmpty'),
                        array(
                            'name' => 'StringLength',
                            'options' => array(
                                'min' => 0,
                                'max' => 100
                            )
                        ),
                    ),
                ),
                'dateModified' => array(
                    'name' => 'dateModified',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'Core\Filter\DateTime'),
                    ),
                ),
                'content' => array(
                    'name' => 'content',
                    'required' => false,
                    'allow_empty' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),
                    ),
                ),
                'authors' => array(
                    'name' => 'authors',
                    'required' => false,
                    'allow_empty' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StripTags'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'StringLength',
                            'options' => array(
                                'min' => 0,
                                'max' => 100
                            )
                        ),
                    ),
                ),
                'readed' => array(
                    'name' => 'readed',
                    'allow_empty' => true,
                )
            ));
        }

        return $this->inputFilter;
    }

}
