<?php

namespace Api\Service;

class MetaModel
{

    /**
     * @var array
     */
    protected $metadata = array(
        'dateCreated',
        'dateModified',
        'creator' => array('name'),
        'modifier' => array('name'),
    );

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

}
