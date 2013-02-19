<?php

namespace Application\Log\Filter;

/**
 * Block logs if HTTP headers are already sent.
 * This might be the case if xdebug output error/warning/notice before FirePHP
 */
class Headers implements \Zend\Log\Filter\FilterInterface
{

    public function filter(array $event)
    {
        return !headers_sent();
    }

}
