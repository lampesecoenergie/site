<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model;

use Ced\Integrator\Api\Data\FeedInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Feed
 * @package Ced\Amazon\Model
 */
class Feed extends AbstractModel implements \Ced\Amazon\Api\Data\FeedInterface
{
    const NAME = 'ced_amazon_feed';
    const COLUMN_ID = 'id';
    const COLUMN_FEED_ID = 'feed_id';
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_TYPE = 'type';
    const COLUMN_STATUS = 'status';
    const COLUMN_FEED_FILE = 'feed_file';
    const COLUMN_RESPONSE_FILE = 'response_file';
    const COLUMN_SPECIFICS = 'specifics';
    const COLUMN_CREATED_DATE = 'feed_created_date';
    const COLUMN_EXECUTED_DATE = 'feed_executed_date';

    const SPECIFICS_INDEX_IDS = 'ids';
    const SPECIFICS_SHIPMENT_SYNC = 'shipment_sync';

    const COLUMN_PRODUCT_IDS = 'product_ids'; // TODO: remove

    public $parser;

    public function __construct(
        \Magento\Framework\Xml\ParserFactory $parser,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->parser = $parser;
    }

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Feed::class);
    }

    public function getAccountId()
    {
        return $this->getData(self::COLUMN_ACCOUNT_ID);
    }

    /**
     * Get Feed Id
     * @return string
     */
    public function getFeedId()
    {
        return $this->getData(self::COLUMN_FEED_ID);
    }

    /**
     * Get Response File Path
     * @return string
     */
    public function getResponseFile()
    {
        return $this->getData(self::COLUMN_RESPONSE_FILE);
    }

    /**
     * Get Feed Response xml as Processed Array
     * @return mixed
     */
    public function getResponse()
    {
        $response = [];
        $file = $this->getResponseFile();
        if (!empty($file) && file_exists($file)) {
            // TODO: IMPROVE: improve parser with XMLReader for bulk file support.

            /** @var \Magento\Framework\Xml\Parser $parser */
            $parser = $this->parser->create();
            $parser->load($file);
            try {
                $response = $parser->xmlToArray();
            } catch (\Exception $e) {
                $response = [];
            }
        }

        return $response;
    }

    /**
     * Get Feed File Path
     * @return string
     */
    public function getFeedFile()
    {
        return $this->getData(self::COLUMN_FEED_FILE);
    }

    /**
     * Get Type
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::COLUMN_TYPE);
    }

    /**
     * Get Status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::COLUMN_STATUS);
    }

    /**
     * Set Status
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::COLUMN_STATUS, $status);
    }

    /**
     * Get Specifics
     * @return string
     */
    public function getSpecifics()
    {
        return $this->getData(self::COLUMN_SPECIFICS);
    }

    /**
     * Set Specifics
     * @param string $specifics
     * @return $this
     */
    public function setSpecifics($specifics)
    {
        return $this->setData(self::COLUMN_SPECIFICS, $specifics);
    }

    /**
     * Set feed account
     * @param int $accountId
     * @return $this
     */
    public function setAccountId($accountId)
    {
        return $this->setData(self::COLUMN_ACCOUNT_ID, $accountId);
    }

    /**
     * Set feed Id
     * @param string $feedId
     * @return $this
     */
    public function setFeedId($feedId)
    {
        return $this->setData(self::COLUMN_FEED_ID, $feedId);
    }

    /**
     * Set Response File Path
     * @param string $path
     * @return $this
     */
    public function setResponseFile($path)
    {
        return $this->setData(self::COLUMN_RESPONSE_FILE, $path);
    }

    /**
     * Set Feed File Path
     * @param string $path
     * @return $this
     */
    public function setFeedFile($path)
    {
        return $this->setData(self::COLUMN_FEED_FILE, $path);
    }

    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::COLUMN_TYPE, $type);
    }

    /**
     * Get Executed Date
     * @return string
     */
    public function getExecutedDate()
    {
        return $this->getData(self::COLUMN_EXECUTED_DATE);
    }

    /**
     * Get Created Date
     * @return string
     */
    public function getCreatedDate()
    {
        return $this->getData(self::COLUMN_CREATED_DATE);
    }
}
