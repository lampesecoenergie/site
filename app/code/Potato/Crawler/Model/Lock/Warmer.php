<?php
namespace Potato\Crawler\Model\Lock;

class Warmer extends LockAbstract
{
    const LOCK_FILE_NAME = 'warmer.lock';

    protected function _getLockFilePath()
    {
        return BP . '/var/' . self::LOCK_FILE_NAME;
    }
}