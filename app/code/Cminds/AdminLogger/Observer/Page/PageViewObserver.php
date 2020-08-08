<?php

namespace Cminds\AdminLogger\Observer\Page;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Backend\Model\Auth\Session\Proxy as AdminSession;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;

/**
 * Class PageViewObserver
 *
 * @package Cminds\AdminLogger\Observer\Page
 */
class PageViewObserver implements ObserverInterface
{
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var AdminSession
     */
    private $adminSession;

    /**
     * @var bool
     */
    private static $triggered = false;

    /**
     * PageViewObserver constructor.
     *
     * @param ModuleConfig $moduleConfig
     * @param Manager      $manager
     * @param UrlInterface $url
     * @param AdminSession $adminSession
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        Manager $manager,
        UrlInterface $url,
        AdminSession $adminSession
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->manager = $manager;
        $this->url = $url;
        $this->adminSession = $adminSession;
    }

    /**
     * Observer execute method.
     *
     * @param Observer $observer
     *
     * @return PageViewObserver
     */
    public function execute(Observer $observer)
    {
        if ($this->adminSession->getUser() === null) {
            return $this;
        }

        if ($this->moduleConfig->isActive() === false) {
            return $this;
        }

        if ($this->moduleConfig->isPageViewLoggingEnabled() === false) {
            return $this;
        }

        if (self::$triggered === true) {
            return $this;
        }

        // trim the key from original uri if exists
        $originalRequestUri = $this->url->getCurrentUrl();
        if (strpos($originalRequestUri,'/key/') !== false) {
            $keyPosition = strpos($originalRequestUri,'/key/');
            $firstPart = substr($originalRequestUri,0, $keyPosition);
            $requestUri = $firstPart;
            if (strpos($originalRequestUri,'/id/') !== false) {
                $idPosition = strpos($originalRequestUri,'/id/');
                if ($idPosition > $keyPosition) {
                    $secondPart = substr($originalRequestUri,$idPosition);
                    $requestUri = $requestUri . $secondPart;
                }
            }
        } else {
            $requestUri = $originalRequestUri;
        }

        $this->manager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'entity_type' => 'page_view',
                'reference_value' => $requestUri,
                'action_type' => ModuleConfig::ACTION_PAGE_VIEW,
            ]
        );

        self::$triggered = true;
    }
}
