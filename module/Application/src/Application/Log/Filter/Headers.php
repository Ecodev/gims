<?php

namespace Application\Log\Filter;

/**
 * Block logs if HTTP headers are already sent.
 * This might be the case if xdebug output error/warning/notice before FirePHP
 */
class Headers implements \Zend\Log\Filter\FilterInterface
{

    private $count = 0;

    /**
     * Prevent logging to be more than 250k, becaus Chrome does not support it.
     * The technique used here is very simplistic and assume each log entry size is roughly 1k.
     * This may still fail, so we use a quite low threshold.
     * See: http://stackoverflow.com/questions/3326210/can-http-headers-be-too-big-for-browsers
     * @param array $event
     * @return boolean
     */
    public function filter(array $event)
    {
        return $this->count++ < 150 && !headers_sent();
    }
}
