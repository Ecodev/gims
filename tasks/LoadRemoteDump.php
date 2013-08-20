<?php

require_once(__DIR__ . '/AbstractDatabase.php');

class LoadRemoteDump extends AbstractDatabase
{

    protected $remote;

    public function setRemote($remote)
    {
        $this->remote = $remote;
    }

    public function main()
    {
        self::loadRemoteDump($this->remote);
    }

}
