<?php

namespace BoostMyShop\AdminLogger\Model;


class Log extends \Magento\Framework\Model\AbstractModel
{
    protected $_dateTime = null;
    protected $_backendAuthSession;
    protected $_logger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdminLogger\Helper\Logger $logger,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_dateTime = $dateTime;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_logger = $logger;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdminLogger\Model\ResourceModel\Log');
    }

    public function getObjectLabel()
    {
        $label = $this->getal_object_type();
        if ($this->getal_object_id())
            $label .= ' #'.$this->getal_object_id();
        return $label;
    }

    public function beforeSave()
    {
        $msg = $this->getal_action().' - '.$this->getal_object_type().' - '.$this->getal_object_id();
        $this->_logger->log($msg);

        parent::beforeSave();

        if (!$this->getId()) {
            $this->setal_created_at($this->_dateTime->gmtDate());

            if ($this->_backendAuthSession->isLoggedIn())
                $this->setal_user($this->_backendAuthSession->getUser()->getUsername());

            $this->setal_ip($_SERVER['REMOTE_ADDR']);

        }

    }

}