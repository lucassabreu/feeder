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
 * @property int $id Identification of Feed
 * @property string $feedLink Link to Feed's source
 * @property string $title Title of Feed
 * @property string $link Link to website
 * @property DateTime $dateModified Last data that feed was updated
 * @property DateTime $lastUpdate Time of last update
 * @property DateTime $nextExpectedUpdate Next date that feed must be readed
 * @property string $description Short description about this Feed
 * @property string $thumbnail Link to a image where the thumbnail is hospeded
 * @property string $type Inter control about Feed types
 */
class Feed extends Entity {

    protected $id;
    protected $feedLink;
    protected $title;
    protected $description;
    protected $dateModified;
    protected $lastUpdate;
    protected $nextExpectedUpdate;
    protected $thumbnail;
    protected $link;
    protected $type;

    public function getInputFilter() {
        if ($this->inputFilter === null) {
            $factory = new Factory();
            $this->inputFilter = $factory->createInputFilter(array(
                'id' => array(
                    'name' => 'id',
                    'required' => false,
                    'allow_empty' => true,
                ),
                'feedLink' => array(
                    'name' => 'feedLink',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StripTags'),
                    ),
                    'validators' => array(
                        array('name' => 'NotEmpty'),
                    ),
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
                                'min' => 3,
                                'max' => 100
                            )
                        ),
                    ),
                ),
                'description' => array(
                    'name' => 'description',
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
                                'max' => 300
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
                'nextExpectedUpdate' => array(
                    'name' => 'nextExpectedUpdate',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'Core\Filter\DateTime'),
                    ),
                ),
                'lastUpdate' => array(
                    'name' => 'lastUpdate',
                    'required' => false,
                    'allow_empty' => true,
                    'filters' => array(
                        array('name' => 'Core\Filter\DateTime'),
                    ),
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
                'thumbnail' => array(
                    'name' => 'thumbnail',
                    'required' => false,
                    'allow_empty' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StripTags'),
                    ),
                ),
                'type' => array(
                    'name' => 'nextExpectedUpdate',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'NotEmpty'),
                        array(
                            'name' => 'InArray',
                            'options' => array(
                                'haystack' => array(
                                    'YOUTUBE',
                                    'DEFAULT',
                                ),
                            ),
                        ),
                    ),
                ),
            ));
        }

        return $this->inputFilter;
    }

}
