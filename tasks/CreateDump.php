<?php

require_once(__DIR__ . '/AbstractDatabase.php');

class CreateDump extends AbstractDatabase
{

    protected $dumpFile;

    public function setDumpFile($dumpFile)
    {
        $this->dumpFile = $dumpFile;
    }

    public function main()
    {
        $siteLocal = __DIR__ . '/../';
        self::dumpData($siteLocal, $this->dumpFile);
    }

}
