<?php

require_once(__DIR__ . '/AbstractDatabase.php');

class PurgeBackup extends AbstractDatabase
{

    protected $numberOfVersions;
    protected $backupDir;

    public function setNumberOfVersions($numberOfVersions)
    {
        $this->numberOfVersions = $numberOfVersions;
    }

    public function setBackupDir($backupDir)
    {
        $siteLocal = __DIR__ . '/../';
        $this->backupDir = realpath($siteLocal . $backupDir);
    }

    public function main()
    {
        $handle = opendir($this->backupDir);
        $files = array();
        while (($file = readdir($handle)) !== false)
        {
            $filePath = $this->backupDir . '/' . $file;
            if ($file == '.' || $file == '..')
            {
                continue;
            } elseif (is_file($filePath))
            {
                $files[] = $filePath;
            }
        }
        sort($files, SORT_REGULAR);
        while (count($files) > $this->numberOfVersions)
        {
            unlink(array_shift($files));
        }
    }

}
