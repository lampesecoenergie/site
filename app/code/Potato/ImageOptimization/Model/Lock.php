<?php
namespace Potato\ImageOptimization\Model;

class Lock
{
    const LOCK_TIMEOUT = 3600;

    const LOCK_DIR = '/app/code/Potato/ImageOptimization/lock/';

    const SCAN_LOCK_FILE = 'scan.lock';
    const OPTIMIZATION_LOCK_FILE = 'optimization.lock';

    /**
     * @param string $lockPath
     * @return bool
     */
    public function isLocked($lockPath)
    {
        $filePath = $this->getLockFilePath($lockPath);
        if (!file_exists($filePath) || time() - filemtime($filePath) > self::LOCK_TIMEOUT
            || 0 === (int)@file_get_contents($filePath)) {
            $this->updateLock($lockPath);
            return false;
        }
        if (getmypid() != @file_get_contents($filePath)) {
            return true;
        }
        $this->updateLock($lockPath);
        return true;
    }

    /**
     * @param string $lockPath
     * @return bool
     */
    public function isCanRunProcess($lockPath)
    {
        $filePath = $this->getLockFilePath($lockPath);
        if (getmypid() != @file_get_contents($filePath)) {
            return false;
        }
        $this->updateLock($lockPath);
        return true;
    }

    /**
     * @param string $lockPath
     * @param int $pid
     * @return $this
     */
    public function updateLock($lockPath, $pid = null)
    {
        if (null === $pid) {
            $pid = getmypid();
        }
        $filePath = $this->getLockFilePath($lockPath);
        file_put_contents($filePath, $pid);
        return $this;
    }

    /**
     * @param string $lockPath
     * @return bool|int
     */
    public function getLockFileTime($lockPath)
    {
        $filePath = $this->getLockFilePath($lockPath);
        if (!file_exists($filePath)) {
            return false;
        }
        return filemtime($filePath);
    }

    public function removeLock($lockPath)
    {
        return $this->updateLock($lockPath, 0);
    }

    /**
     * @param string $lockPath
     * @return string
     */
    private function getLockFilePath($lockPath)
    {
        if (!file_exists(BP . self::LOCK_DIR)) {
            //create lock dir
            mkdir(BP . self::LOCK_DIR, 0775);
        }
        return BP . self::LOCK_DIR . $lockPath;
    }
}
