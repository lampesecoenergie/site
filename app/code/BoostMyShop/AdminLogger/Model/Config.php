<?php

namespace BoostMyShop\AdminLogger\Model;

class Config
{
    /**
     * Core store config
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig){
        $this->_scopeConfig = $scopeConfig;
    }

    public function getSetting($path, $storeId = 0)
    {
        return $this->_scopeConfig->getValue('adminlogger/'.$path, 'store', $storeId);
    }

    public function isEnabled()
    {
        return ($this->getSetting('general/enable') && (php_sapi_name() !== 'cli'));
    }

    public function logLogins()
    {
        return ($this->isEnabled() && $this->getSetting('general/log_login'));
    }

    public function logVisited()
    {
        return ($this->isEnabled() && $this->getSetting('general/log_visited'));
    }

    public function getObjectIdParams()
    {
        return explode(',', $this->getSetting('advanced/object_id_params'));
    }

    public function getPruneDays()
    {
        return $this->getSetting('general/prune_delay');
    }

    public function getFieldsToExclude()
    {
        return explode(',', $this->getSetting('advanced/fields_to_exclude'));
    }

    public function getRoutesToExclude()
    {
        return explode(',', $this->getSetting('advanced/routes_to_exclude'));
    }

    public function getClassesToExclude()
    {
        return explode(',', $this->getSetting('advanced/classes_to_exclude'));
    }

    public function fieldIsExcluded($field)
    {
        foreach($this->getFieldsToExclude() as $item)
        {
            if (preg_match('/'.$item.'/i', $field))
                return true;
        }
        return false;
    }

    public function classIsExcluded($class)
    {
        if (preg_match('/AdminLogger/i', $class))
            return true;

        foreach($this->getClassesToExclude() as $item)
        {
            if (preg_match('/'.$item.'/i', $class))
                return true;
        }

        return false;
    }


}