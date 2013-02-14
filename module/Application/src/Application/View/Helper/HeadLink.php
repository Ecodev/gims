<?php

namespace Application\View\Helper;

class HeadLink extends \Zend\View\Helper\HeadLink
{

    /**
     * Override parent to inject the last modified time of file.
     * This avoid browser cache and force reloading when the file changed.
     * @param string $method
     * @param array $args
     * @return type
     */
    public function __call($method, $args)
    {
        if (strpos($method, 'Stylesheet')) {
            $fileName = $args[0];
            $fullPath = DOCUMENT_ROOT . $fileName;
            if (is_file($fullPath)) {
                $fileName .= '?' . filemtime($fullPath);
                $args[0] = $fileName;
            }
        }

        return parent::__call($method, $args);
    }

}
