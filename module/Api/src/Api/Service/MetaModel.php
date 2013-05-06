<?php

namespace Api\Service;


class MetaModel
{

    /**
     * @var array
     */
    protected $metadata = array('dateCreated', 'dateModified', 'creator', 'modifier');

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }


}
