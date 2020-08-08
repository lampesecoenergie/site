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

namespace Ced\Amazon\Helper;

/**
 * Directory separator shorthand
 */

use Ced\Amazon\Api\AccountRepositoryInterface;
use Ced\Amazon\Api\ProfileRepositoryInterface;
use Magento\Framework\DataObjectFactory;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Class Product
 * @package Ced\Amazon\Helper
 */
class Product implements \Ced\Integrator\Helper\ProductInterface
{
    const WYSIWYG_TYPE = [
        'description',
        'short_description'
    ];

    const ATTRIBUTE_CODE_PROFILE_ID = 'amazon_profile_id';
    const ATTRIBUTE_CODE_ASIN = 'asin';
    const ATTRIBUTE_CODE_PRODUCT_STATUS = 'amazon_product_status';
    const ATTRIBUTE_CODE_VALIDATION_ERRORS = 'amazon_validation_errors';
    const ATTRIBUTE_CODE_FEED_ERRORS = 'amazon_feed_errors';

    const PRODUCT_ERROR_VALID = 'valid';

    const PRODUCT_TYPE_PARENT = 'parent';
    const PRODUCT_TYPE_CHILD = 'child';

    const BULLET_POINT_1 = 'DescriptionData_BulletPoint1';
    const BULLET_POINT_2 = 'DescriptionData_BulletPoint2';
    const BULLET_POINT_3 = 'DescriptionData_BulletPoint3';
    const BULLET_POINT_4 = 'DescriptionData_BulletPoint4';
    const BULLET_POINT_5 = 'DescriptionData_BulletPoint5';

    const BULLET_POINTS = [
        self::BULLET_POINT_1,
        self::BULLET_POINT_2,
        self::BULLET_POINT_3,
        self::BULLET_POINT_4,
        self::BULLET_POINT_5,
    ];

    const SEARCH_TERMS_1 = 'DescriptionData_SearchTerms1';
    const SEARCH_TERMS_2 = 'DescriptionData_SearchTerms2';
    const SEARCH_TERMS_3 = 'DescriptionData_SearchTerms3';
    const SEARCH_TERMS_4 = 'DescriptionData_SearchTerms4';
    const SEARCH_TERMS_5 = 'DescriptionData_SearchTerms5';

    const SEARCH_TERMS = [
        self::SEARCH_TERMS_1,
        self::SEARCH_TERMS_2,
        self::SEARCH_TERMS_3,
        self::SEARCH_TERMS_4,
        self::SEARCH_TERMS_5,
    ];

    /** @var \Magento\Framework\Api\SearchCriteriaInterface */
    public $search;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Zend_Filter_StripTags
     */
    public $stripTags;

    /** @var AccountRepositoryInterface */
    public $account;

    /**
     * @var ProfileRepositoryInterface
     */
    public $profile;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory
     */
    public $productConfigurable;

    /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable */
    public $productConfigurableResource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $products;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    public $productResource;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    public $storeManager;

    /**
     * @var \Ced\Amazon\Model\Profileproducts
     */
    public $profileProducts;

    /**
     * @var $config
     */
    public $config;

    /**
     * @var \Amazon\Sdk\EnvelopeFactory
     */
    public $envelope;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * File Manager
     * @var $fileIo
     */
    public $fileIo;

    /**
     * Date/Time
     * @var $dateTime
     */
    public $dateTime;

    /** @var \Ced\Amazon\Api\FeedRepositoryInterface */
    public $feed;

    /**
     * @var \Ced\Amazon\Helper\Logger
     */
    public $logger;

    /**
     * @var \Ced\Amazon\Model\FeedsFactory
     */
    public $amazonFeed;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Feeds\CollectionFactory
     */
    public $amazonFeeds;

    /**
     * @var \Ced\Amazon\Api\QueueRepositoryInterface
     */
    public $queue;

    /** @var \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory */
    public $queueDataFactory;

    /**
     * @var \Amazon\Sdk\Api\Product\ProductListFactory
     */
    public $productList;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $urlBuilder;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    public $serializer;

    /** @var DataObjectFactory */
    public $data;

    /**
     * @var \Amazon\Sdk\Envelope
     */
    public $relationships = null;

    /** @var \Amazon\Sdk\Product\RelationshipFactory */
    public $relationship;

    /**
     * Product Ids
     * @var array
     */
    public $ids = [];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList, //remove
        \Magento\Framework\Filesystem\Io\File $fileIo, //remove
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Framework\DataObjectFactory $dataObjectFactory, //remove
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Api\SearchCriteriaInterface $search,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Zend_Filter_StripTags $stripTags,

        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        \Ced\Amazon\Api\ProfileRepositoryInterface $profile,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory,

        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configurableFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableResource,
        \Ced\Amazon\Model\Profileproducts $profileProducts, //remove

        \Ced\Amazon\Model\FeedsFactory $feedsFactory, //remove
        \Ced\Amazon\Model\ResourceModel\Feeds\CollectionFactory $feedsCollectionFactory, //remove

        \Ced\Amazon\Service\Config $config,
        \Ced\Amazon\Helper\Logger $logger,

        \Amazon\Sdk\Api\Product\ProductListFactory $productList,
        \Amazon\Sdk\EnvelopeFactory $envelope,
        \Amazon\Sdk\Product\RelationshipFactory $relationship
    ) {
        $this->objectManager = $objectManager;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->dateTime = $dateTime;
        $this->search = $search;
        $this->storeManager = $storeManager;
        $this->stripTags = $stripTags;

        $this->account = $account;
        $this->profile = $profile;
        $this->feed = $feed;
        $this->queue = $queue;
        $this->queueDataFactory = $queueDataFactory;

        $this->product = $product;
        $this->products = $productCollectionFactory;
        $this->productConfigurable = $configurableFactory;
        $this->productConfigurableResource = $configurableResource;
        $this->productResource = $productResource;
        $this->urlBuilder = $url;
        $this->serializer = $serializer;
        $this->data = $dataObjectFactory;

        $this->amazonFeed = $feedsFactory;
        $this->amazonFeeds = $feedsCollectionFactory;
        $this->profileProducts = $profileProducts;
        $this->config = $config;
        $this->logger = $logger;

        $this->envelope = $envelope;
        $this->productList = $productList;
        $this->relationship = $relationship;
    }

    /**
     * Update/upload products on Amazon
     * @param array $ids
     * @param bool $throttle
     * @param string $operationType
     * @return boolean
     * @throws \Exception
     */
    public function update(array $ids = [], $throttle = true, $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE)
    {
        $status = false;
        if (isset($ids) && !empty($ids)) {
            $profileIds = $this->profile->getProfileIdsByProductIds($ids);
            if (!empty($profileIds)) {
                /** @var \Magento\Framework\Api\SearchCriteriaInterface $search */
                $search = $this->search->setData(
                    'filter_groups',
                    [
                        [
                            'filters' => [
                                [
                                    'field' => \Ced\Amazon\Model\Profile::COLUMN_ID,
                                    'value' => $profileIds,
                                    'condition_type' => 'in'
                                ],
                                [
                                    'field' => \Ced\Amazon\Model\Profile::COLUMN_STATUS,
                                    'value' => \Ced\Amazon\Model\Source\Profile\Status::ENABLED,
                                    'condition_type' => 'eq'
                                ]
                            ]
                        ]
                    ]
                );

                /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $profiles */
                $profiles = $this->profile->getList($search);

                /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
                $accounts = $profiles->getAccounts();

                /** @var array $stores */
                $stores = $profiles->getProfileByStoreIdWise();

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                foreach ($accounts->getItems() as $accountId => $account) {
                    foreach ($stores as $storeId => $profiles) {
                        $envelope = null;
                        /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
                        foreach ($profiles as $profileId => $profile) {
                            $productIds = $this->profile->getAssociatedProductIds($profileId, $storeId, $ids);
                            $specifics = [
                                'ids' => $productIds,
                                'account_id' => $accountId,
                                'marketplace' => $profile->getMarketplace(),
                                'profile_id' => $profileId,
                                'store_id' => $storeId,
                                'type' => \Amazon\Sdk\Api\Feed::PRODUCT,
                            ];

                            if (!empty($productIds)) {
                                if ($throttle == true) {
                                    // queue
                                    /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
                                    $queueData = $this->queueDataFactory->create();
                                    $queueData->setAccountId($accountId);
                                    $queueData->setMarketplace($profile->getMarketplace());
                                    $queueData->setSpecifics($specifics);
                                    $queueData->setOperationType($operationType);
                                    $queueData->setType(\Amazon\Sdk\Api\Feed::PRODUCT);
                                    $status = $this->queue->push($queueData);
                                } else {
                                    //TODO: add all data to uniqueid in session & process via multiple ajax requests.

                                    // prepare & send: divide in chunks and process in multiple requests

                                    if ($operationType == \Amazon\Sdk\Base::OPERATION_TYPE_DELETE) {
                                        $envelope = $this->prepareDelete($specifics, $envelope);
                                    } else {
                                        $envelope = $this->prepare($specifics, $envelope, $operationType);
                                    }

                                    $status = $this->feed->send($envelope, $specifics);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $status;
    }

    /**
     * Product Prepare Delete for Amazon
     * @param array $specifics
     * @param array|null $envelope
     * @return array
     * @throws \Exception
     */
    public function prepareDelete(array $specifics = [], $envelope = null)
    {
        if (isset($specifics) && !empty($specifics)) {
            $amazonList = [
                "1107",
                "1116",
                "1234",
                "1235",
                "124",
                "1242",
                "1244",
                "1245",
                "1246",
                "1247",
                "1249",
                "1250",
                "1251",
                "1252",
                "1253",
                "1254",
                "1255",
                "1256",
                "1257",
                "1258",
                "1259",
                "1260",
                "1261",
                "1262",
                "1263",
                "1264",
                "1266",
                "1267",
                "1268",
                "1269",
                "1270",
                "1271",
                "1272",
                "1273",
                "1274",
                "1275",
                "1277",
                "1279",
                "1280",
                "1281",
                "1282",
                "1283",
                "1284",
                "1285",
                "1286",
                "1287",
                "1288",
                "1289",
                "1290",
                "1291",
                "1292",
                "1293",
                "1294",
                "1295",
                "1296",
                "1297",
                "1298",
                "1299",
                "1300",
                "1301",
                "1302",
                "1303",
                "1305",
                "1306",
                "1307",
                "1308",
                "1310",
                "1311",
                "1312",
                "1315",
                "1318",
                "1320",
                "1321",
                "1322",
                "1323",
                "1324",
                "1325",
                "1326",
                "1327",
                "1328",
                "1329",
                "1330",
                "1331",
                "1396",
                "1398",
                "1399",
                "1401",
                "1402",
                "1407",
                "1408",
                "1409",
                "1411",
                "1412",
                "1413",
                "1414",
                "1415",
                "1416",
                "1417",
                "1418",
                "1419",
                "1420",
                "1422",
                "1424",
                "1425",
                "1427",
                "144",
                "146",
                "1512",
                "1648_01",
                "1649_01",
                "1723",
                "1728",
                "1730",
                "1731",
                "1732",
                "1734",
                "1735",
                "1736",
                "1737",
                "1738",
                "1739",
                "175",
                "1764",
                "193_02",
                "193_03",
                "193_04",
                "193_05",
                "193_06",
                "193_08",
                "193_10",
                "193_12",
                "193_20",
                "193_25",
                "193_35",
                "193_40",
                "193_45",
                "193_50",
                "20",
                "22150K",
                "222",
                "223",
                "224",
                "22440",
                "22460K",
                "22462K",
                "226",
                "22661K",
                "227",
                "22700K",
                "22731K",
                "22732",
                "228",
                "230409",
                "23041K",
                "230423",
                "230430",
                "23410K",
                "23410_10",
                "23410_20",
                "23411K",
                "23411K_10",
                "23411K_20",
                "235",
                "2387",
                "2402",
                "2403",
                "2404",
                "243",
                "259",
                "264",
                "2653",
                "266",
                "2698",
                "2701",
                "2702",
                "2710",
                "2711",
                "2712",
                "2751",
                "2767",
                "2769",
                "2777",
                "278",
                "2850",
                "2851",
                "2852",
                "2853",
                "2854",
                "2855",
                "2856",
                "2857",
                "2858",
                "2860",
                "2861",
                "2862",
                "2863",
                "2864",
                "2865",
                "2867",
                "2868",
                "2869",
                "2870",
                "2871",
                "2872",
                "2873",
                "2874",
                "2875",
                "2876",
                "2877",
                "2878",
                "2879",
                "2880",
                "2881",
                "2882",
                "2900",
                "2901",
                "2902",
                "2903",
                "2974",
                "2975",
                "2976",
                "2977",
                "2978",
                "2979",
                "2980",
                "2981",
                "2983",
                "2985",
                "2986",
                "2988",
                "2989",
                "2990",
                "2991",
                "3002",
                "3003",
                "3004",
                "3005",
                "3006",
                "3007",
                "3008",
                "3009",
                "3010",
                "3011",
                "3012",
                "3013",
                "3014",
                "3015",
                "3016",
                "3017",
                "3018",
                "3019",
                "3020",
                "3021",
                "3022",
                "3023",
                "3024",
                "3025",
                "3026",
                "3027",
                "3028",
                "3029",
                "3030",
                "3031",
                "3032",
                "3033",
                "3051",
                "3083",
                "3096",
                "3097",
                "3098",
                "3105",
                "3116",
                "3117",
                "3118",
                "3119",
                "3126",
                "3132",
                "3138",
                "3142",
                "3168",
                "3234",
                "3235",
                "3237",
                "3238",
                "3239",
                "3244",
                "3245",
                "3253",
                "3254",
                "3255",
                "3256",
                "3257",
                "3304",
                "3305",
                "3358",
                "3359",
                "3360",
                "3361",
                "3375",
                "3376",
                "3377",
                "3378",
                "3379",
                "3380",
                "3381",
                "3382",
                "3383",
                "3384",
                "3385",
                "3386",
                "3387",
                "3388",
                "3389",
                "3390",
                "3391",
                "3392",
                "3393",
                "3394",
                "3395",
                "3396",
                "3397",
                "3398",
                "3399",
                "3400",
                "3401",
                "3403",
                "3404",
                "3405",
                "3406",
                "3407",
                "3408",
                "3409",
                "404_01",
                "404_02",
                "404_03",
                "404_04",
                "404_05",
                "404_06",
                "404_08",
                "404_10",
                "404_12",
                "404_15",
                "404_20",
                "404_25",
                "404_30",
                "404_35",
                "404_40",
                "404_45",
                "404_50",
                "501",
                "6001B24SMD3000K_12",
                "6001BFIX",
                "6001BLANC24SMD3000K_03",
                "6001BLANC24SMD3000K_05",
                "6001BLANC24SMD3000K_06",
                "6001BLANC24SMD3000K_08",
                "6001BLANC24SMD3000K_10",
                "6001BLANC24SMD3000K_12",
                "6001BLANC24SMD3000K_15",
                "6001BLANC24SMD3000K_20",
                "6001BLANC24SMD3000K_25",
                "6001BLANC24SMD3000K_40",
                "6001BLANC24SMD3000K_50",
                "6001BLANC24SMD6000K_03",
                "6001BLANC24SMD6000K_05",
                "6001BLANC24SMD6000K_06",
                "6001BLANC24SMD6000K_08",
                "6001BLANC24SMD6000K_10",
                "6001BLANC24SMD6000K_12",
                "6001BLANC24SMD6000K_15",
                "6001BLANC24SMD6000K_20",
                "6001BLANC24SMD6000K_25",
                "6001BLANC24SMD6000K_30",
                "6001BLANC24SMD6000K_40",
                "6001BLANC24SMD6000K_50",
                "6001SATIN24SMD3000K_01",
                "6001SATIN24SMD3000K_03",
                "6001SATIN24SMD3000K_05",
                "6001SATIN24SMD3000K_06",
                "6001SATIN24SMD3000K_08",
                "6001SATIN24SMD3000K_10",
                "6001SATIN24SMD3000K_12",
                "6001SATIN24SMD3000K_15",
                "6001SATIN24SMD3000K_20",
                "6001SATIN24SMD3000K_30",
                "6001SATIN24SMD3000K_40",
                "6001SATIN24SMD3000K_50",
                "6001SATIN24SMD6000K_01",
                "6001SATIN24SMD6000K_03",
                "6001SATIN24SMD6000K_05",
                "6001SATIN24SMD6000K_06",
                "6001SATIN24SMD6000K_08",
                "6001SATIN24SMD6000K_10",
                "6001SATIN24SMD6000K_12",
                "6001SATIN24SMD6000K_15",
                "6001SATIN24SMD6000K_20",
                "6001SATIN24SMD6000K_25",
                "6001SATIN24SMD6000K_30",
                "6001SATIN24SMD6000K_50",
                "6001SN40W3000K_01",
                "6001SN5WCOB3000_03",
                "6001SN5WCOB3000_05",
                "6001SN5WCOB3000_06",
                "6001SN5WCOB3000_10",
                "6001SN5WCOB3000_12",
                "6001SN5WCOB3000_15",
                "6001SN5WCOB3000_20",
                "6001SN5WCOB3000_25",
                "6001SN5WCOB3000_30",
                "6001SN5WCOB3000_40",
                "6001SN5WCOB3000_50",
                "6001SN5WCOB6000_03",
                "6001SN5WCOB6000_05",
                "6001SN5WCOB6000_06",
                "6001SN5WCOB6000_08",
                "6001SN5WCOB6000_10",
                "6001SN5WCOB6000_12",
                "6001SN5WCOB6000_15",
                "6001SN5WCOB6000_20",
                "6001SN5WCOB6000_25",
                "6001SN5WCOB6000_30",
                "6001SN5WCOB6000_40",
                "6001SN5WCOB6000_50",
                "6001SNFIX",
                "6001WH5WCOB3000_03",
                "6001WH5WCOB3000_05",
                "6001WH5WCOB3000_06",
                "6001WH5WCOB3000_08",
                "6001WH5WCOB3000_10",
                "6001WH5WCOB3000_12",
                "6001WH5WCOB3000_15",
                "6001WH5WCOB3000_20",
                "6001WH5WCOB3000_25",
                "6001WH5WCOB3000_30",
                "6001WH5WCOB3000_40",
                "6001WH5WCOB3000_50",
                "6001WH5WCOB6000_03",
                "6001WH5WCOB6000_05",
                "6001WH5WCOB6000_06",
                "6001WH5WCOB6000_08",
                "6001WH5WCOB6000_10",
                "6001WH5WCOB6000_12",
                "6001WH5WCOB6000_15",
                "6001WH5WCOB6000_20",
                "6001WH5WCOB6000_25",
                "6001WH5WCOB6000_30",
                "6001WH5WCOB6000_40",
                "6001WH5WCOB6000_50",
                "6002BLANC24SMD3000K_01",
                "6002BLANC24SMD3000K_03",
                "6002BLANC24SMD3000K_04",
                "6002BLANC24SMD3000K_05",
                "6002BLANC24SMD3000K_06",
                "6002BLANC24SMD3000K_08",
                "6002BLANC24SMD3000K_10",
                "6002BLANC24SMD3000K_15",
                "6002BLANC24SMD3000K_20",
                "6002BLANC24SMD3000K_25",
                "6002BLANC24SMD3000K_30",
                "6002BLANC24SMD3000K_40",
                "6002BLANC24SMD3000K_50",
                "6002BLANC24SMD3000Kx12",
                "6002BLANC24SMD6000K_01",
                "6002BLANC24SMD6000K_03",
                "6002BLANC24SMD6000K_04",
                "6002BLANC24SMD6000K_05",
                "6002BLANC24SMD6000K_06",
                "6002BLANC24SMD6000K_08",
                "6002BLANC24SMD6000K_10",
                "6002BLANC24SMD6000K_12",
                "6002BLANC24SMD6000K_15",
                "6002BLANC24SMD6000K_20",
                "6002BLANC24SMD6000K_25",
                "6002BLANC24SMD6000K_30",
                "6002BLANC24SMD6000K_40",
                "6002BLANC24SMD6000K_50",
                "6002SATIN24SMD3000K_01",
                "6002SATIN24SMD3000K_03",
                "6002SATIN24SMD3000K_04",
                "6002SATIN24SMD3000K_05",
                "6002SATIN24SMD3000K_06",
                "6002SATIN24SMD3000K_08",
                "6002SATIN24SMD3000K_10",
                "6002SATIN24SMD3000K_12",
                "6002SATIN24SMD3000K_15",
                "6002SATIN24SMD3000K_25",
                "6002SATIN24SMD3000K_30",
                "6002SATIN24SMD3000K_40",
                "6002SATIN24SMD3000K_50",
                "6002SATIN24SMD6000K_01",
                "6002SATIN24SMD6000K_03",
                "6002SATIN24SMD6000K_04",
                "6002SATIN24SMD6000K_05",
                "6002SATIN24SMD6000K_06",
                "6002SATIN24SMD6000K_10",
                "6002SATIN24SMD6000K_15",
                "6002SATIN24SMD6000K_20",
                "6002SATIN24SMD6000K_25",
                "6002SATIN24SMD6000K_30",
                "6002SATIN24SMD6000K_40",
                "6002SATIN24SMD6000K_50",
                "6002SN5WCOB3000_03",
                "6002SN5WCOB3000_05",
                "6002SN5WCOB3000_06",
                "6002SN5WCOB3000_08",
                "6002SN5WCOB3000_10",
                "6002SN5WCOB3000_12",
                "6002SN5WCOB3000_15",
                "6002SN5WCOB3000_20",
                "6002SN5WCOB3000_25",
                "6002SN5WCOB3000_30",
                "6002SN5WCOB3000_40",
                "6002SN5WCOB3000_50",
                "6002SN5WCOB6000_03",
                "6002SN5WCOB6000_05",
                "6002SN5WCOB6000_06",
                "6002SN5WCOB6000_08",
                "6002SN5WCOB6000_10",
                "6002SN5WCOB6000_12",
                "6002SN5WCOB6000_15",
                "6002SN5WCOB6000_20",
                "6002SN5WCOB6000_25",
                "6002SN5WCOB6000_30",
                "6002SN5WCOB6000_40",
                "6002SN5WCOB6000_50",
                "6002SNFIX_01",
                "6002WH5WCOB3000_03",
                "6002WH5WCOB3000_05",
                "6002WH5WCOB3000_06",
                "6002WH5WCOB3000_08",
                "6002WH5WCOB3000_10",
                "6002WH5WCOB3000_12",
                "6002WH5WCOB3000_15",
                "6002WH5WCOB3000_20",
                "6002WH5WCOB3000_30",
                "6002WH5WCOB3000_40",
                "6002WH5WCOB3000_50",
                "6002WH5WCOB6000_03",
                "6002WH5WCOB6000_05",
                "6002WH5WCOB6000_06",
                "6002WH5WCOB6000_08",
                "6002WH5WCOB6000_10",
                "6002WH5WCOB6000_12",
                "6002WH5WCOB6000_15",
                "6002WH5WCOB6000_20",
                "6002WH5WCOB6000_25",
                "6002WH5WCOB6000_30",
                "6002WH5WCOB6000_40",
                "6002WH5WCOB6000_50",
                "6002WHFIX_01",
                "6003SN230V",
                "6003SNFIX_01",
                "6003WH230V",
                "6013FIXSN_01",
                "6020SN230V",
                "6020SN24SMD3000K_01",
                "6020SN24SMD3000_03",
                "6020SN24SMD3000_05",
                "6020SN24SMD3000_06",
                "6020SN24SMD3000_08",
                "6020SN24SMD3000_10",
                "6020SN24SMD3000_12",
                "6020SN24SMD3000_20",
                "6020SN24SMD3000_30",
                "6020SN24SMD3000_40",
                "6020SN24SMD3000_50",
                "6020SN24SMD6000_03",
                "6020SN24SMD6000_05",
                "6020SN24SMD6000_06",
                "6020SN24SMD6000_10",
                "6020SN24SMD6000_12",
                "6020SN24SMD6000_15",
                "6020SN24SMD6000_20",
                "6020SN24SMD6000_25",
                "6020SN24SMD6000_30",
                "6020SN24SMD6000_40",
                "6020SN24SMD6000_50",
                "6020SN5WCOB3000_03",
                "6020SN5WCOB3000_05",
                "6020SN5WCOB3000_06",
                "6020SN5WCOB3000_08",
                "6020SN5WCOB3000_10",
                "6020SN5WCOB3000_12",
                "6020SN5WCOB3000_15",
                "6020SN5WCOB3000_25",
                "6020SN5WCOB3000_30",
                "6020SN5WCOB3000_40",
                "6020SN5WCOB3000_50",
                "6020SN5WCOB6000_03",
                "6020SN5WCOB6000_05",
                "6020SN5WCOB6000_06",
                "6020SN5WCOB6000_08",
                "6020SN5WCOB6000_10",
                "6020SN5WCOB6000_12",
                "6020SN5WCOB6000_15",
                "6020SN5WCOB6000_20",
                "6020SN5WCOB6000_25",
                "6020SN5WCOB6000_30",
                "6020SN5WCOB6000_40",
                "6020SN5WCOB6000_50",
                "6020WH24SMD3000_03",
                "6020WH24SMD3000_05",
                "6020WH24SMD3000_06",
                "6020WH24SMD3000_08",
                "6020WH24SMD3000_10",
                "6020WH24SMD3000_12",
                "6020WH24SMD3000_15",
                "6020WH24SMD3000_20",
                "6020WH24SMD3000_25",
                "6020WH24SMD3000_30",
                "6020WH24SMD3000_40",
                "6020WH24SMD3000_50",
                "6020WH24SMD6000_03",
                "6020WH24SMD6000_05",
                "6020WH24SMD6000_06",
                "6020WH24SMD6000_08",
                "6020WH24SMD6000_10",
                "6020WH24SMD6000_12",
                "6020WH24SMD6000_15",
                "6020WH24SMD6000_20",
                "6020WH24SMD6000_25",
                "6020WH24SMD6000_30",
                "6020WH24SMD6000_40",
                "6020WH24SMD6000_50",
                "6020WH5WCOB3000_03",
                "6020WH5WCOB3000_05",
                "6020WH5WCOB3000_06",
                "6020WH5WCOB3000_08",
                "6020WH5WCOB3000_10",
                "6020WH5WCOB3000_12",
                "6020WH5WCOB3000_15",
                "6020WH5WCOB3000_20",
                "6020WH5WCOB3000_25",
                "6020WH5WCOB3000_30",
                "6020WH5WCOB3000_40",
                "6020WH5WCOB3000_50",
                "6020WHFIX_01",
                "61",
                "6169CH230",
                "6169FIXCH_01",
                "6169FIXSN_01",
                "6169FIXWH_01",
                "6169SN12",
                "6169SN230",
                "68",
                "72369",
                "80",
                "8195WBC_01",
                "8195WBC_05",
                "8195WBC_06",
                "8195WBC_10",
                "8195WBC_15",
                "8195WBC_20",
                "8305WBC_01",
                "8305WBC_05",
                "8305WBC_06",
                "8305WBC_10",
                "8305WBC_15",
                "8305WBC_20",
                "A60_FIL_4W_10",
                "A60_FIL_8W_01",
                "BBC802BC_06",
                "BBC802BC_10",
                "BBC802BC_15",
                "BBC802_05",
                "BBC802_1",
                "BBC826BC_06",
                "BBC826BC_10",
                "BBC826BC_15",
                "BBC826_01",
                "DAL_2PK",
                "DAL_4PK",
                "DAL_6PK",
                "E1448smd_1",
                "E2724SMDBC_10",
                "E2724SMDBC_5",
                "E2724SMD_01",
                "E2748SMDBC360",
                "E2748SMDBC360_10",
                "E2748SMDBC360_5",
                "E27_12_W_4000K_10",
                "E27_12_W_6000K_10",
                "E27_7W_3000K_10",
                "E27_7W_4000K_01",
                "FIL_4W_10",
                "FLAM4260B22",
                "FLAM4WFIL_01",
                "FLAMLED5W_10",
                "FOUR",
                "FOUR25W",
                "G918_10",
                "G925C",
                "G94260",
                "G94260_10",
                "GU104WHPB_01",
                "GU105WPL3000_01",
                "GU1060_1",
                "GU1060_10",
                "GU1060_20",
                "GU1060_5",
                "GU10CON-S",
                "GU10CON-S_05",
                "GU10CON-S_10",
                "GU10CON-S_20",
                "GU10CON-S_50",
                "HOTTE40",
                "J118230300",
                "J118230_10",
                "J118230_5",
                "J78120_10",
                "J78120_5",
                "J78_01",
                "K22050",
                "K4391",
                "K4392",
                "L6001SN230",
                "L6001WH230",
                "LANT_6W_01",
                "MR1110",
                "MR1110_10",
                "MR1112SMDBC",
                "MR1112SMDBC_10",
                "MR111420",
                "MR112535",
                "MR112535_10",
                "MR161420",
                "MR161420_10",
                "MR161420_20",
                "MR161420_5",
                "MR161420_50",
                "MR162535",
                "MR162535_10",
                "MR162535_20",
                "MR162535_5",
                "PROJ10DET",
                "PlafS_18W4500K_01",
                "PlafS_18W4500K_03",
                "PlafS_18W4500K_05",
                "PlafS_18W4500K_10",
                "PlafS_18W6000K_01",
                "PlafS_18W6000K_10",
                "PlafS_3W4500K_01",
                "PlafS_3W4500K_03",
                "PlafS_3W4500K_05",
                "PlafS_3W4500K_10",
                "PlafS_3W6000K_03",
                "PlafS_3W6000K_05",
                "PlafS_3W6000K_10",
                "PlafS_7W4500K_01",
                "PlafS_7W4500K_03",
                "PlafS_7W4500K_05",
                "PlafS_7W4500K_10",
                "PlafS_7W6000K_01",
                "PlafS_7W6000K_03",
                "PlafS_7W6000K_05",
                "S4674",
                "SPOT7W38SN_10",
                "SPOT7W38SN_20",
                "SPOT7W38WH_01",
                "SPOT7W38_01",
                "SPOT7WSN38_05",
                "SPOT_5W_B_01",
                "SPOT_5W_B_02",
                "SPOT_5W_B_03",
                "SPOT_5W_B_04",
                "SPOT_5W_SN_01",
                "SPOT_5W_SN_02",
                "SPOT_5W_SN_03",
                "SPOT_5W_SN_04",
                "SPOT_5W_SN_05",
                "SPOT_5W_SN_06",
                "SPOT_5W_SN_08",
                "SPOT_5W_SN_12",
                "SPOT_5W_SN_15",
                "SPOT_5W_SN_25",
                "SPOT_5W_SN_30",
                "SPOT_5W_SN_35",
                "SPOT_5W_SN_40",
                "SPOT_5W_SN_45",
                "SPOT_5W_SN_50",
                "SPOT_5W_WH_05",
                "SPOT_5W_WH_06",
                "SPOT_5W_WH_08",
                "SPOT_5W_WH_12",
                "SPOT_5W_WH_15",
                "SPOT_5W_WH_25",
                "SPOT_5W_WH_30",
                "SPOT_5W_WH_35",
                "SPOT_5W_WH_40",
                "SPOT_5W_WH_45",
                "SPOT_5W_WH_50",
                "TRANS200H_03",
                "TRANS30LED",
                "TRANS60HALO",
                "TRANSH200W",
                "VT-E27_10W_4500_03",
                "VT-E27_10W_4500_06",
                "VT-E27_10W_4500_10",
                "VT-E27_7W_6000_06",
                "con_ruban",
                "douille_complete_GU5.3",
                "e1448smd_10",
                "e1448smd_5",
                "1243",
                "1248",
                "1265",
                "1276",
                "1410",
                "1537",
                "1539",
                "1548",
                "1555",
                "1556",
                "1557",
                "1608",
                "22051",
                "22441",
                "22820K",
                "22821K",
                "2755",
                "3225",
                "6001SATIN24SMD6000K_40",
                "6013FIXWH_01",
                "6020SN5WCOB3000_20",
                "6020SNFIX_01",
                "6020WH5WCOB6000_03",
                "6020WH5WCOB6000_05",
                "6020WH5WCOB6000_06",
                "6020WH5WCOB6000_08",
                "6020WH5WCOB6000_10",
                "6020WH5WCOB6000_12",
                "6020WH5WCOB6000_15",
                "6020WH5WCOB6000_20",
                "6020WH5WCOB6000_25",
                "6020WH5WCOB6000_30",
                "6020WH5WCOB6000_40",
                "6020WH5WCOB6000_50",
                "6169B12V",
                "6169B230",
                "B22_7W_3000K_01",
                "B22_7W_4000K_01",
                "B22_9W_4000K_01",
                "B22_9W_6000K_01",
                "DAL_40_01",
                "DAL_40_05",
                "E1424SMDBC",
                "E27_7W_4000K_10",
                "E27_9W_3000K_01",
                "G4_BF_3W_01",
                "G94260_5",
                "GU105WPL6000_01",
                "K7170",
                "MR1112SMDBC_5",
                "MR1124SMDBC",
                "PlafS_12W4500K_01",
                "PlafS_12W4500K_03",
                "PlafS_7W6000K_10",
                "S5148",
                "VT-B22_12W_4500_20",
            ];
            /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
            $profile = $this->profile->getById($specifics['profile_id']);

            $ids = $specifics['ids'];
            /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
            $account = $this->account->getById($specifics['account_id']);

            if (!isset($envelope)) {
                /** @var \Amazon\Sdk\Envelope $envelope */
                $envelope = $this->envelope->create(
                    [
                        'merchantIdentifier' => $account->getConfig($profile->getMarketplaceIds())->getSellerId(),
                        'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_PRODUCT
                    ]
                );
            }

            $storeId = $profile->getStore()->getId();

            /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $products */
            $products = $this->products->create()
                ->setStoreId($storeId)
                ->addAttributeToSelect(['sku', 'entity_id', 'type_id'])
                ->addAttributeToFilter('entity_id', ['in' => $ids]);
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($products as $product) {
                // case 1 : for configurable products
                if ($product->getTypeId() == 'configurable') {
                    $parentId = $product->getId();
                    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType */
                    $productType = $product->getTypeInstance();

                    /** @codingStandardsIgnoreStart */
                    $childIds = $productType->getChildrenIds($parentId);
                    /** @codingStandardsIgnoreEnd */
                    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $products */
                    $childs = $this->products->create()
                        ->setStoreId($storeId)
                        ->addAttributeToSelect(['sku', 'entity_id', 'type_id'])
                        ->addAttributeToFilter('entity_id', ['in' => $childIds[0]]);

                    foreach ($childs as $child) {
                        $mpProduct = $this->objectManager->create(
                            \Amazon\Sdk\Product\Category\DefaultCategory::class,
                            [
                                'subCategory' => 'DefaultCategory'
                            ]
                        );
                        $mpProduct->setId($child->getId());
                        $mpProduct->setOperationType(\Amazon\Sdk\Base::OPERATION_TYPE_DELETE);
                        $mpProduct->SKU = $child->getId();
                        if (in_array($child->getSku(), $amazonList)) {
                            $envelope->addProduct($mpProduct);
                        }
                    }
                } elseif ($product->getTypeId() == 'simple') {
                    // case 2 : for simple products
                    $mpProduct = $this->objectManager->create(
                        \Amazon\Sdk\Product\Category\DefaultCategory::class,
                        [
                            'subCategory' => 'DefaultCategory'
                        ]
                    );
                    $mpProduct->setId($product->getId());
                    $mpProduct->setOperationType(\Amazon\Sdk\Base::OPERATION_TYPE_DELETE);
                    $mpProduct->SKU = $product->getId();
                    if (in_array($product->getSku(), $amazonList)) {
                        $envelope->addProduct($mpProduct);
                    }
                }
            }
        }
        return $envelope;
    }

    /**
     * TODO: Option mappings, Update product status for Amazon
     * Product Prepare for Amazon
     * @param array $specifics
     * @param array|null $envelope
     * @param string $operationType
     * @return \Amazon\Sdk\Envelope
     * @throws \Exception
     */
    public function prepare(array $specifics = [], $envelope = null, $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE)
    {
        if (isset($specifics) && !empty($specifics)) {
            /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
            $profile = $this->profile->getById($specifics['profile_id']);

            $this->ids = $specifics['ids'];
            /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
            $account = $this->account->getById($specifics['account_id']);
            $this->initRelationships($account);

            if (!isset($envelope)) {
                /** @var \Amazon\Sdk\Envelope $envelope */
                $envelope = $this->envelope->create(
                    [
                        'merchantIdentifier' => $account->getConfig($profile->getMarketplaceIds())->getSellerId(),
                        'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_PRODUCT
                    ]
                );
            }

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
            $products = $this->products->create();
            $products->setStoreId($specifics['store_id']);
            $products->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', ['in' => $specifics['ids']]);

            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($products as $product) {
                $sku = $product->getSku();
                $errors = [
                    $sku => [
                        'sku' => $sku,
                        'id' => $product->getId(),
                        'profile_id' => $profile->getId(),
                        'account_id' => $profile->getAccountId(),
                        'store_id' => $profile->getStoreId(),
                        'url' => $this->urlBuilder
                            ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                        'errors' => self::PRODUCT_ERROR_VALID
                    ]
                ];

                // case 1 : for configurable products
                if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE &&
                    $profile->getId()) {
                    $relation = [];
                    $parentId = $product->getId();
                    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType */
                    $productType = $product->getTypeInstance();

                    $variantAttributes = [];
                    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
                    foreach ($productType->getConfigurableAttributes($product) as $attribute) {
                        $eavAttribute = $attribute->getProductAttribute();
                        $eavAttribute->setStoreId($product->getStoreId());
                        $variantAttributes[$eavAttribute->getAttributeCode()] = $eavAttribute->getAttributeCode();
                    }

                    /** @codingStandardsIgnoreStart */
                    $childIds = $productType->getChildrenIds($parentId);
                    /** @codingStandardsIgnoreEnd */

                    if (isset($childIds[0])) {
                        $valid = false;
                        /** @var \Amazon\Sdk\Product\CategoryInterface $parent */
                        $parent = $this->create($profile, $product, self::PRODUCT_TYPE_PARENT);

                        // setting a parentSku as the configurable sku.
                        $parentSku = $product->getSku();

                        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
                        $childs = $this->products->create()
                            ->setStoreId($profile->getStoreId())
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id', ['in' => $childIds[0]]);
                        /** @var \Magento\Catalog\Model\Product $child */
                        foreach ($childs as $child) {
                            // TODO: add childs to skip list if processed from parent.
                            $error = [
                                'sku' => $child->getSku(),
                                'id' => $child->getId(),
                                'profile_id' => $profile->getId(),
                                'account_id' => $profile->getAccountId(),
                                'store_id' => $profile->getStoreId(),
                                'url' => $this->urlBuilder
                                    ->getUrl('catalog/product/edit', ['id' => $child->getId()]),
                                'errors' => self::PRODUCT_ERROR_VALID
                            ];

                            /** @var \Amazon\Sdk\Product\CategoryInterface $mpProduct */
                            $mpProduct = $this->create($profile, $child, self::PRODUCT_TYPE_CHILD, $variantAttributes, $parent);
                            $mpProduct->setOperationType($operationType);
                            if ($mpProduct->isValid()) {
                                $valid = true;
                                $envelope->addProduct($mpProduct);
                                $relation[$child->getSku()] =
                                    \Amazon\Sdk\Product\Relationship::RELATION_TYPE_VARIATION;
                            } else {
                                $error['errors'] = [$mpProduct->getError()];
                            }

                            // adding child error to parent errors if exists
                            if (isset($error['errors']) && $error['errors'] != self::PRODUCT_ERROR_VALID) {
                                $errors[$child->getSku()] = $error;
                            }

                            // saving child errors only.
                            $child->setData(
                                self::ATTRIBUTE_CODE_VALIDATION_ERRORS,
                                $this->serializer->serialize([$child->getSku() => $error])
                            );

                            if (!empty($relation)) {
                                $relationship = $this->relationship->create();
                                $relationship->setId($product->getId());
                                $relationship->setData($parentSku, $relation);
                                // Adding a relationship to envelope.
                                $this->relationships->addRelationship($relationship);
                            }
                        }

                        /** @codingStandardsIgnoreStart */
                        $this->save($childs);
                        /** @codingStandardsIgnoreEnd */

                        if ($valid) {
                            if (isset($mpProduct) && $mpProduct->isValid() &&
                                $key = $mpProduct->getVariationThemeAttribute()) {
                                $parent->$key = $mpProduct->get($key);
                            }

                            $envelope->addProduct($parent);
                            $relation[$product->getSku()] = \Amazon\Sdk\Product\Relationship::RELATION_TYPE_VARIATION;
                        }
                    }
                } elseif ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE &&
                    $profile->getId()) {
                    // case 2 : for simple products

                    $type = null;
                    $parentIds = $this->productConfigurableResource->getParentIdsByChild($product->getId());
                    if (!empty($parentIds)) {
                        $type = self::PRODUCT_TYPE_CHILD;
                    }

                    /** @var \Amazon\Sdk\Product\CategoryInterface $mpProduct */
                    $mpProduct = $this->create($profile, $product, $type);
                    $mpProduct->setOperationType($operationType);
                    if ($mpProduct->isValid()) {
                        $envelope->addProduct($mpProduct);
                    } else {
                        $errors[$sku]['errors'] = [$mpProduct->getError()];
                    }
                }

                // saving errors in simple product and configurable parent product.
                $product->setData(
                    self::ATTRIBUTE_CODE_VALIDATION_ERRORS,
                    $this->serializer->serialize($errors)
                );
            }

            $this->save($products);
        }

        return $envelope;
    }

    /**
     * Save attribute in a collection
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $products
     */
    private function save($products)
    {
        $storeId = null;
        if ($this->storeManager->hasSingleStore()) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            try {
                if (isset($storeId)) {
                    $product->setStoreId($storeId);
                }
                // Overriding as "amazon_validation_errors" is Global attribute
                $product->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
                $this->productResource->saveAttribute($product, self::ATTRIBUTE_CODE_VALIDATION_ERRORS);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    /**
     * TODO: use it
     * Intialize relationship object
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     */
    private function initRelationships(\Ced\Amazon\Api\Data\AccountInterface $account)
    {
        $this->relationships = $this->envelope->create(
            [
                'merchantIdentifier' => $account->getConfig()->getSellerId(),
                'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_RELATIONSHIP
            ]
        );
    }

    /**
     * Create Product Data
     * @param \Ced\Amazon\Api\Data\ProfileInterface $profile
     * @param \Magento\Catalog\Model\Product $product
     * @param string $type
     * @param array $variant
     * @param \Amazon\Sdk\Product\CategoryInterface|null $parent
     * @return \Amazon\Sdk\Product\CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function create($profile, $product, $type = null, $variant = [], $parent = null)
    {
        $theme = [];
        $asin = null;
        $variationList = $variant;

        /**
         * @var \Amazon\Sdk\Product\CategoryInterface $mpProduct
         * @codingStandardsIgnoreStart
         */
        $mpProduct = $this->objectManager->create(
            '\Amazon\Sdk\Product\Category\\' . $profile->getProfileCategory(),
            [
                'subCategory' => $profile->getProfileSubCategory()
            ]
        );
        /** @codingStandardsIgnoreEnd */

        $mpProduct->setId($profile->getId() . $product->getId());
        $mpProduct->setBarcodeExemption($profile->getBarcodeExemption());

        $attributes = $profile->getProfileAttributes();
        foreach ($attributes as $id => $attribute) {
            $value = null;
            $code = null;
            if (isset($attribute['magento_attribute_code']) &&
                $attribute['magento_attribute_code'] !== "default_value") {
                $code = $attribute['magento_attribute_code'];
                /** @var \Magento\Eav\Model\Attribute $magentoAttribute */
                $magentoAttribute = $product->getResource()
                    ->getAttribute($code);
                if ($magentoAttribute && ($magentoAttribute->usesSource() ||
                        $magentoAttribute->getData('frontend_input') == 'select')
                ) {
                    $source = $magentoAttribute->getSource();
                    // For Magento 2.2.5 and below,the storeId is not set on loading product.
                    //$attr = $source->getAttribute();
                    //$attr->setStoreId($product->getStoreId());
                    //$source->setAttribute($attr);
                    $value = $source->getOptionText(
                        $product->getData($code)
                    );

                    if (is_object($value)) {
                        $value = $value->getText();
                    }
                } else {
                    $value = $product->getData($code);
                }
            }

            // Filtering html
            if (!empty($code) && in_array($code, self::WYSIWYG_TYPE)) {
                // <strong> replace to <b>
                $value = preg_replace("/<strong(.*?)>(.*?)<\/strong>/", "<b>$2</b>", $value);

                // Filtering other tags except 'b', 'br', 'p'
                $this->stripTags->setTagsAllowed(['b', 'br', 'p']);
                $this->stripTags->setAttributesAllowed([]);
                $value = $this->stripTags->filter($value);
            }

            // Adding units for dimensions. TODO: get from config
            $a = $mpProduct->getAttribute($id);
            if (isset($a['attribute']) && !empty($code)) {
                $subAttribute = $a['attribute'];
                if (in_array($code, ['ts_dimensions_length', 'ts_dimensions_width', 'ts_dimensions_height' ])) {
                    $mpProduct->$subAttribute = "inches";
                } elseif (in_array($code, ['packed_height', 'packed_width', 'packed_height', 'packed_depth' ])) {
                    $mpProduct->$subAttribute = "MM";
                } elseif (in_array($code, ['weight'])) {
                    $mpProduct->$subAttribute = "LB";
                }
            }

            // Using parent product values in case of configurable product. (skipping variation attributes)
            if (isset($parent) && empty($value) && !isset($variationList[$code])) {
                $value = $parent->get($id);
            }

            // Setting default value
            if ((isset($attribute['default_value']) && empty($value) && !empty($attribute['default_value'])) ||
                (isset($attribute['magento_attribute_code']) && $attribute['magento_attribute_code'] == 'default')) {
                $value = $attribute['default_value'];
            }

            // Merging bullets
            if (in_array($id, self::BULLET_POINTS) && !empty(trim((string)$value))) {
                $previous = $mpProduct->get('DescriptionData_BulletPoint');
                $value = isset($previous) ? $previous . "||" . trim((string)$value) : trim((string)$value);
                $mpProduct->DescriptionData_BulletPoint = $value;
            }

            // Merging search terms
            if (in_array($id, self::SEARCH_TERMS) && !empty(trim((string)$value))) {
                $previous = $mpProduct->get('DescriptionData_SearchTerms');
                $value = isset($previous) ? $previous . "||" . trim((string)$value) : trim((string)$value);
                $mpProduct->DescriptionData_SearchTerms = $value;
            }

            if (is_array($value)) {
                $value = implode(",", $value);
            }

            $mpProduct->$id = trim((string)$value);

            // Deleting variantion required attribute if its value is satisfied.
            if (isset($code, $value, $variationList[$code])) {
                $theme[$code][] = $attribute['name'];
                unset($variant[$code]);
            }

            if ($id == "StandardProductID_Value_ASIN") {
                $asin = $value;
            }
        }

        // Adding ASIN
        $barcodeIndex = "StandardProductID_Value";
        $typeIndex = "StandardProductID_Type";
        if (empty($mpProduct->get($barcodeIndex)) && !empty($asin)) {
            $mpProduct->$barcodeIndex = $asin;
        }

        // Sanitizing for Variation
        if ($type == self::PRODUCT_TYPE_PARENT) {
            $index = $mpProduct->getParentageAttribute();
            if (isset($index)) {
                $mpProduct->$index = self::PRODUCT_TYPE_PARENT;
            }
            // Removing barcode for parents
            unset($mpProduct->$barcodeIndex);
            unset($mpProduct->$typeIndex);
        } elseif ($type == self::PRODUCT_TYPE_CHILD) {
            $index = $mpProduct->getParentageAttribute();
            if (isset($index)) {
                $mpProduct->$index = self::PRODUCT_TYPE_CHILD;
            }
        }

        // Adding Variation Theme
        if (!empty($variant)) {
            $attributes = implode('|', $variant);
            $mpProduct->setError(
                "Variation_Attribute_Value",
                "{$attributes} attributes are not mapped in profile or have a invalid value.",
                1
            );
        } elseif (!empty($theme)) {
            // Extract and Set Variation Theme Basis of Variation Attributes Mapped.
            $this->extract($mpProduct, $theme);
        }

        return $mpProduct;
    }

    private function convertToWords($value)
    {
        $result = $value;
        if (!empty($value)) {
            $tmp = explode(" ", $value);
            $tmp = $tmp[0];
            if (!empty($tmp)) {
                $tmp = explode(".", $tmp);
                // "numeric_8_point_5"
                $result = "numeric_" . $tmp[0];
                if (isset($tmp[1])) {
                    $result .= "_point_" . $tmp[1];
                }
            }
        }
        return $result;
    }

    /**
     * Extract variation theme
     * @param \Amazon\Sdk\Product\ProductInterface $mpProduct
     * @param array $themeList,
     * [
     *      'magento_color' => ['Color', 'ColorMap'],
     *      'magento_size'  => ['Size']
     * ]
     */
    public function extract($mpProduct, $themeList)
    {
        // Setting Variation theme
        /** @var string $key */
        $key = $mpProduct->getVariationThemeAttribute();
        if (!empty($key) && empty($mpProduct->get($key))) {
            $themeIdentified = false;
            /** @var array $variationThemeAttribute */
            $variationThemeAttribute = $mpProduct->getAttribute($key);
            /** @var array $variationThemes */
            $variationThemes = isset($variationThemeAttribute['restriction']['optionValues']) ?
                $variationThemeAttribute['restriction']['optionValues'] : [];
            $themeList = $this->combinations(array_values($themeList));
            $theme = "";
            foreach ($themeList as $theme) {
                if (!is_array($theme)) {
                    // For Single Variation Attribute
                    $theme = [$theme];
                }

                $theme = array_unique($theme);
                $count = count($theme);
                $theme = implode('|', $theme);

                foreach ($variationThemes as $variationTheme) {
                    if (preg_match_all("({$theme})", $variationTheme) === $count) {
                        $mpProduct->$key = $variationTheme;
                        $themeIdentified = true;
                        break;
                    }
                }
            }

            if (!$themeIdentified) {
                $mpProduct->setError(
                    "Variation_Theme_Value",
                    "{$theme} unable to identify the variation theme.
                         Kindly specify the theme explicitly in profile attribute mapping.",
                    1
                );
            }
        }
    }

    /**
     * Generate cartesian product of an array
     * @param $arrays
     * @param int $i
     * @return array
     */
    private function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return [];
        }

        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = $this->combinations($arrays, $i + 1);

        $result = [];

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge([$v], $t) :
                    [$v, $t];
            }
        }

        return $result;
    }

    /**
     * TODO: use it.
     * @return \Amazon\Sdk\Envelope
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * NOTE: SKU should not - (dash)
     * Get Product from Amazon.
     * @param null $id
     * @param null $sku
     * @param string $idType
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($id = null, $sku = null, $idType = 'SellerSKU')
    {
        $result = [];
        /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $profileList */
        $profileList = $this->profile->getByProductId($id);

        $i = 0;
        /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
        foreach ($profileList->getItems() as $profile) {
            $productList = $this->productList->create(
                [
                    'config' => $this->account->getById($profile->getAccountId())
                        ->getConfig($profile->getMarketplaceIds()),
                    'logger' => $this->logger
                ]
            );

            foreach ($profile->getMarketplaceIds() as $marketplaceId) {
                $result[$i] = [
                    'profile_id' => $profile->getId(),
                    'profile_name' => $profile->getName(),
                    'store_id' => $profile->getStoreId(),
                    'account_id' => $profile->getAccountId(),
                    'product' => [
                        "Product data not available for SKU: {$sku}."
                    ]
                ];

                $productList->setIdType($idType);
                $productList->setProductIds($sku);
                //$productList->setIdType('ASIN');
                //$productList->setProductIds('B0736YGJGZ');
                $productList->setMarketplaceIds($marketplaceId);
                $productList->fetchProductList();
                $products = $productList->getProduct();
                if ($products != false && !isset($products['Error'])) {
                    /** @var \Amazon\Sdk\Api\Product $product */
                    foreach ($products as $product) {
                        $result[$i]['product'] = $product->getData();
                    }
                } elseif (isset($products['Error'])) {
                    $result[$i]['error'] = $products['Error'];
                }

                $i++;
            }
        }

        return $result;
    }
}
