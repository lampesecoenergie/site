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



namespace Mirasvit\Feed\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreFactory;

/**
 * Feed Model
 *
 * @method string getName()
 * @method $this setName($value)
 * @method $this setFilename($value)
 * @method $this setCreatedAt($value)
 * @method $this setUpdatedAt($value)
 * @method int getStoreId()
 * @method string getArchivation()
 * @method bool getIsActive()
 *
 * @method bool getFtp()
 * @method string getFtpProtocol()
 * @method string getFtpHost()
 * @method string getFtpUser()
 * @method string getFtpPassword()
 * @method string getFtpPath()
 * @method string getFtpPassiveMode()
 *
 * @method string getGeneratedAt()
 * @method $this setGeneratedAt($value)
 * @method $this setGeneratedCnt($value)
 * @method $this setGeneratedTime($value)
 * @method $this setUploadedAt($value)
 * @method $this setRuleIds($value)
 *
 * @method string getGaSource()
 * @method string getGaMedium()
 * @method string getGaName()
 * @method string getGaTerm()
 * @method string getGaContent()
 *
 * @method bool getReportEnabled()
 *
 * @method string getNotificationEmails()
 */
class Feed extends AbstractTemplate
{
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var UrlInterface
     */
    protected $urlManager;


    /**
     * Feed constructor.
     *
     * @param StoreFactory $storeFactory
     * @param Config       $config
     * @param UrlInterface $urlManager
     * @param Context      $context
     * @param Registry     $registry
     */
    public function __construct(
        StoreFactory $storeFactory,
        Config $config,
        UrlInterface $urlManager,
        Context $context,
        Registry $registry
    ) {
        $this->storeFactory = $storeFactory;
        $this->config = $config;
        $this->urlManager = $urlManager;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Feed\Model\ResourceModel\Feed');
    }

    /**
     * Model of store
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if (!$this->store) {
            $this->store = $this->storeFactory->create()->load($this->getStoreId());
        }

        return $this->store;
    }

    /**
     * Rule IDs
     *
     * @return array
     */
    public function getRuleIds()
    {
        return is_array($this->getData('rule_ids')) ? $this->getData('rule_ids') : [];
    }

    /**
     * Email notification events
     *
     * @return array
     */
    public function getNotificationEvents()
    {
        if (!is_array($this->getData('notification_events'))) {
            $this->setData('notification_events', explode(',', $this->getData('notification_events')));
        }

        return $this->getData('notification_events');
    }

    /**
     * Cron days
     *
     * @return array
     */
    public function getCronDay()
    {
        if (!is_array($this->getData('cron_day'))) {
            $this->setData('cron_day', explode(',', $this->getData('cron_day')));
        }

        return $this->getData('cron_day');
    }

    /**
     * Cron time
     *
     * @return array
     */
    public function getCronTime()
    {
        if (!is_array($this->getData('cron_time'))) {
            $this->setData('cron_time', explode(',', $this->getData('cron_time')));
        }

        return $this->getData('cron_time');
    }

    /**
     * Full url to feed
     *
     * @return string|false
     */
    public function getUrl()
    {
        $url = false;

        $filename = $this->getFilename();

        if ($this->getArchivation()) {
            $filename .= '.' . $this->getArchivation();
        }

        $path = $this->config->getBasePath() . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($path)) {
            $url = $this->getStore()->getBaseUrl(DirectoryList::MEDIA) . 'feed/' . $filename;
        }

        return $url;
    }

    /**
     * Filename with extension
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->getData('filename') . '.' . strtolower($this->getType());
    }

    /**
     * Preview filename with extension
     *
     * @return string
     */
    public function getPreviewFilename()
    {
        return $this->getData('filename') . '.test' . '.' . strtolower($this->getType());
    }

    /**
     * Absolute feed path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->config->getBasePath() . DIRECTORY_SEPARATOR . $this->getFilename();
    }

    /**
     * Preview feed absolute path
     *
     * @return string
     */
    public function getPreviewFilePath()
    {
        return $this->config->getBasePath() . DIRECTORY_SEPARATOR . $this->getPreviewFilename();
    }

    /**
     * Set template data (type, content settings) to feed
     *
     * @param Template $template
     *
     * @return $this
     */
    public function loadFromTemplate(Template $template)
    {
        $this->addData($template->getData());

        return $this;
    }
}
