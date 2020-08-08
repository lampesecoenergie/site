<?php
namespace Potato\Crawler\Model\Lock;

abstract class LockAbstract
{
    const LOCK_EXPIRE_TIME = 1800;

    abstract protected function _getLockFilePath();

    /**
     * Protection from multi-process via file
     *
     * @return bool
     */
    public function isLocked()
    {
        //check file with pid
        if (file_exists($this->_getLockFilePath()) &&
            //check pid from file exists
            $this->isPidRunning() &&
            //compare time expire
            time() - filemtime($this->_getLockFilePath()) < self::LOCK_EXPIRE_TIME &&
            //compare current pid with pid from file
            $this->getProcessPid() != getmypid()
        ) {
            return true;
        }
        if ($this->isPidRunning()) {
            $this->_stopProcess();
        }
        file_put_contents($this->_getLockFilePath(), getmypid());
        return false;
    }

    /**
     *
     */
    public function removeLock()
    {
        //@unlink($this->_getLockFilePath());
        return $this;
    }

    public function getProcessPid()
    {
        if (file_exists($this->_getLockFilePath())) {
            return @file_get_contents($this->_getLockFilePath());
        }
        return false;
    }

    public function isPidRunning()
    {
        if ($this->_isWin() || !$this->getProcessPid()) {
            //apache will crashed if $threads > 1
            return false;
        }
        if (file_exists( "/proc/" . $this->getProcessPid())){
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function _isWin()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }
        return false;
    }

    public function getLastActivity()
    {
        if (file_exists($this->_getLockFilePath())) {
            return date('Y-m-d H:i:s', @filemtime($this->_getLockFilePath()));
        }
        return ' - ';
    }

    public function updateLockFile()
    {
        file_put_contents($this->_getLockFilePath(), getmypid());
        return $this;
    }

    protected function _stopProcess()
    {
        if ($this->getProcessPid() == getmypid()) {
            return $this;
        }
        $result = $status = array();
        exec('kill -9 ' . $this->getProcessPid(), $result, $status);
        if ($status != 0) {
            if (empty($result)) {
                $result[0] = 'Please wait. Web-server haven\'t access to the process with PID: ' .  $this->getProcessPid();
            }
            throw new \Exception(implode(' ', $result));
        }
        return $this;
    }

    protected function _startProcess()
    {
        $result = $status = array();
        exec(BP . '/shell/potato/warmer.sh', $result, $status);
        if ($status != 0) {
            throw new \Exception(implode(' ', $result));
        }
        return $this;
    }

    public function restart()
    {
        if ($this->isPidRunning()) {
            $this->_stopProcess();
        }
        return $this->_startProcess();
    }
}