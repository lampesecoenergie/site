<?php
namespace Potato\Crawler\Model\Lock;

class Queue extends LockAbstract
{
    const LOCK_FILE_NAME = 'queue.lock';

    protected function _getLockFilePath()
    {
        return BP . '/var/' . self::LOCK_FILE_NAME;
    }
}