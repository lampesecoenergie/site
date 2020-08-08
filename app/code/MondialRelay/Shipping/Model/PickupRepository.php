<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model;

use MondialRelay\Shipping\Api\PickupRepositoryInterface;
use MondialRelay\Shipping\Api\Data\PickupSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Exception;

/**
 * Class PickupRepository
 */
class PickupRepository implements PickupRepositoryInterface
{
    /**
     * @var PickupFactory $pickupFactory
     */
    protected $pickupFactory;

    /**
     * @var PickupSearchResultsInterfaceFactory $searchResultsFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CartRepositoryInterface $quoteRepository
     */
    protected $quoteRepository;

    /**
     * PickupRepository constructor.
     *
     * @param PickupFactory $pickupFactory
     * @param PickupSearchResultsInterfaceFactory $searchResultsFactory
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        PickupFactory $pickupFactory,
        PickupSearchResultsInterfaceFactory $searchResultsFactory,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->pickupFactory        = $pickupFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->quoteRepository      = $quoteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get($pickupId, $countryId)
    {
        $pickup = $this->pickupFactory->create();
        $pickup->load($pickupId, $countryId);

        if (!$pickup->hasData()) {
            throw new Exception(__('Unable to load pickup now, please select another shipping method'));
        }

        return $pickup;
    }

    /**
     * {@inheritdoc}
     */
    public function current($cartId)
    {
        $quote = $this->quoteRepository->getActive($cartId);

        $pickup = $this->pickupFactory->create();
        $pickup->current($quote->getId());

        return $pickup;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $pickup = $this->pickupFactory->create();

        $required = ['postcode', 'country', 'code'];

        $data = [];

        foreach ($searchCriteria->getFilterGroups() as $group) {
            foreach ($group->getFilters() as $filter) {
                $data[$filter->getField()] = $filter->getValue();
            }
        }

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new Exception(__('%1 field is required', $field));
            }
        }

        $list = $pickup->getList($data['postcode'], $data['country'], $data['code']);

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($list->getItems());
        $searchResult->setTotalCount($list->getSize());

        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function save($cartId, $pickupId, $countryId, $code)
    {
        $quote = $this->quoteRepository->getActive($cartId);

        $pickup = $this->pickupFactory->create();

        return $pickup->save($quote->getId(), $pickupId, $countryId, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function reset($cartId)
    {
        $quote = $this->quoteRepository->getActive($cartId);

        $pickup = $this->pickupFactory->create();

        return $pickup->reset($quote->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function shippingData($orderId)
    {
        $pickup = $this->pickupFactory->create();

        return $pickup->shippingData($orderId);
    }
}
