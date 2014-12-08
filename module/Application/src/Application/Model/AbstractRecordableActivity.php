<?php

namespace Application\Model;

/**
 * Base class for objects with recorded activity
 */
abstract class AbstractRecordableActivity extends AbstractModel
{

    /**
     * @see Activity::$data
     * @return  array data
     */
    abstract public function getActivityData();
}
