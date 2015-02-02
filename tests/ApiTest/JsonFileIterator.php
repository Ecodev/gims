<?php

namespace ApiTest;

use Zend\Json\Json;

class JsonFileIterator extends \GlobIterator
{

    /**
     * Override parent to force pattern for JSON file
     * @param string $path
     */
    public function __construct($path)
    {
        $path = $path . '/*.json';
        parent::__construct($path, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO);
    }

    /**
     * Override pattern to return an array instead of FileInfo.
     * @return array [url parameters, expected json, optional message]
     */
    public function current()
    {
        $file = parent::current();

        @list($params, $message) = explode('#', str_replace('.json', '', $file->getFilename()));

        $fullpath = getcwd() . '/../data/logs/tests/' . $file->getPath() . '/';
        `rm -rf $fullpath`;
        @mkdir($fullpath, 0777, true);

        $json = file_get_contents($file->getPathname());
        $result = [
            $params,
            $json,
            $message,
            $fullpath . $file->getFilename(),
        ];

        return $result;
    }
}
