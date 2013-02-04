<?php
namespace Album\Form;

use Zend\Form\Form;

class AlbumForm extends Form
{
    public function __construct($name = null)
    {
        $this->setAttribute('data-ng-submit', 'submit()');
        
        // we want to ignore the name passed
        parent::__construct('album');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
                'data-ng-model' => 'album.id',
            ),
        ));
        $this->add(array(
            'name' => 'artist',
            'attributes' => array(
                'type'  => 'text',
                'data-ng-model' => 'album.artist',
                'required' => true,
            ),
            'options' => array(
                'label' => 'Artist',
            ),
        ));
        $this->add(array(
            'name' => 'title',
            'attributes' => array(
                'type'  => 'text',
                'data-ng-model' => 'album.title',
                'required' => true,
            ),
            'options' => array(
                'label' => 'Title',
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }
}
