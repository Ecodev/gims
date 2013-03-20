<?php

namespace Application\Model;

interface PropertiesUpdatableInterface
{

    /**
     * Update property of $this
     *
     * @param array $data
     * @return PropertiesUpdatableInterface
     */
    public function updateProperties($data);
}