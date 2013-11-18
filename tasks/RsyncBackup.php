<?php

require_once(__DIR__ . '/AbstractDatabase.php');

class RsyncBackup extends AbstractDatabase
{

    protected $backupDir;
    protected $siteLocal;

    public function setBackupDir($backupDir)
    {

        $this->siteLocal = __DIR__ . '/../';
        $this->backupDir = realpath($this->siteLocal . $backupDir);
    }

    public function main()
    {
        $config = include $this->siteLocal . 'config/autoload/local.php';
        $rsyncpass = trim(`echo ~`) . "/.rsyncpass";
        file_put_contents($rsyncpass, $config['rsync']['password']);
        chmod($rsyncpass, 0600);
        $cmd = "rsync -av --password-file={$rsyncpass} {$this->backupDir}/ {$config['rsync']['username']}@{$config['rsync']['host']}::{$config['rsync']['module']}/{$config['domain']}";
        exec($cmd . ' 2>&1', $output, $status);
        if ($status != 0)
        {
            // TODO: send an email to the sysadmin
            echo "Error: " . PHP_EOL . implode(PHP_EOL, $output) . PHP_EOL;
        }
    }

}
