<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Model\Feed;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Feed\Model\Feed;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Notifier
{
    /**
     * Email builder
     *
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * Constructor
     *
     * @param TransportBuilder  $transportBuilder
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        TimezoneInterface $localeDate,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->localeDate = $localeDate;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Export Success
     *
     * @param Feed $feed
     * @return bool
     */
    public function exportSuccess(Feed $feed)
    {
        if (!$feed || !in_array('export_success', $feed->getNotificationEvents())) {
            return true;
        }

        $vars = [
            'feed' => $feed,
            'url' => $feed->getUrl(),
            'exported_at' => $this->localeDate->formatDateTime(
                new \DateTime($feed->getGeneratedAt()),
                \IntlDateFormatter::MEDIUM,
                true
            ),
            'export_time' => gmdate('H:i:s', $feed->getGeneratedTime()),
            'export_count' => $feed->getGeneratedCnt(),
        ];

        $this->send($feed, 'feed_export_success', $vars);

        return true;
    }

    /**
     * Export Fail
     *
     * @param Feed   $feed
     * @param string $error
     * @return bool
     */
    public function exportFail(Feed $feed, $error)
    {
        if (!$feed || !in_array('export_fail', $feed->getNotificationEvents())) {
            return true;
        }

        $vars = [
            'feed' => $feed,
            'url' => $feed->getUrl(),
            'error' => $error
        ];

        $this->send($feed, 'feed_export_fail', $vars);

        return true;
    }

    /**
     * Delivery Success
     *
     * @param Feed $feed
     * @return bool
     */
    public function deliverySuccess(Feed $feed)
    {
        if (!$feed || !in_array('delivery_success', $feed->getNotificationEvents())) {
            return true;
        }

        $vars = [
            'feed' => $feed,
            'url' => $feed->getUrl(),
            'delivered_at' => $this->localeDate->formatDateTime(
                new \DateTime($feed->getDeliveredAt()),
                \IntlDateFormatter::MEDIUM,
                true
            ),
        ];

        $this->send($feed, 'feed_delivery_success', $vars);

        return true;
    }

    /**
     * Delivery Fail
     *
     * @param Feed   $feed
     * @param string $error
     * @return bool
     */
    public function deliveryFail(Feed $feed, $error)
    {
        if (!$feed || !in_array('delivery_fail', $feed->getNotificationEvents())) {
            return true;
        }

        $vars = [
            'feed' => $feed,
            'url' => $feed->getUrl(),
            'error' => $error
        ];

        $this->send($feed, 'feed_delivery_fail', $vars);

        return true;
    }

    /**
     * Send email by given template and vars
     *
     * @param Feed   $feed
     * @param string $template
     * @param []     $vars
     * @return bool
     */
    protected function send(Feed $feed, $template, $vars)
    {
        $notificationEmails = explode(',', $feed->getNotificationEmails());
        foreach ($notificationEmails as $email) {
            if (!trim($email)) {
                continue;
            }

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions([
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => $feed->getStore()->getId(),
                ])
                ->setTemplateVars($vars)
                ->setFrom([
                    'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE),
                    'name' => $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE),
                ])
                ->addTo($email)
                ->getTransport();
            $transport->sendMessage();
        }

        return true;
    }
}
