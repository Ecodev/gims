<?php

require_once(__DIR__ . '/AbstractDatabase.php');

class LoadDump extends AbstractDatabase
{

    protected $dumpFile;

    public function setDumpFile($dumpFile)
    {
        $this->dumpFile = $dumpFile;
    }

    public function main()
    {
        $siteLocal = __DIR__ . '/../';
        self::loadDump($siteLocal, $this->dumpFile);
    }

}
