<?php

namespace Application\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

class HeadScript extends \Zend\View\Helper\HeadScript implements ServiceLocatorAwareInterface
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    /**
     * Inject the last modified time of file.
     * This avoid browser cache and force reloading when the file changed.
     * @param string $fileName
     * @return string
     */
    protected function addCacheStamp($fileName)
    {
        $fullPath = 'htdocs/' . $fileName;
        if (is_file($fullPath)) {
            $fileName = $this->getView()->serverUrl() . $this->view->basePath($fileName) . '?' . filemtime($fullPath);
        }

        return $fileName;
    }

    protected function includeDirectory($directory, $method, $args)
    {
        foreach (glob($directory . '/*') as $file) {
            if (is_dir($file)) {
                $this->includeDirectory($file, $method, $args);
            } else {
                $args[0] = $this->addCacheStamp(str_replace('htdocs/', '', $file));
                parent::__call($method, $args);
            }
        }
    }

    /**
     * Override parent to support timestamp, compilation and concatenation.
     * Compiled and concatened files must pre-exist (compiled by external tools).
     * @param string $method
     * @param array $args
     * @return HeadScript
     */
    public function __call($method, $args)
    {

        if (strpos($method, 'File')) {
            $fileName = $args[0];

            // If file will be concatened, use concatenation system instead
            if (is_array($fileName)) {
                // If we are in development, actually don't concatenate anything
                if (!$this->getServiceLocator()->getServiceLocator()->get('Config')['compressJS']) {
                    foreach ($fileName[1] as $f) {
                        $this->includeDirectory('htdocs' . $f, $method, $args);
                    }

                    return $this;
                }
                // Otherwise use pre-existing concatenated file
                else {
                    $fileName = $fileName[0];
                }
            }

            $args[0] = $this->addCacheStamp($fileName);
        }

        return parent::__call($method, $args);
    }

}
