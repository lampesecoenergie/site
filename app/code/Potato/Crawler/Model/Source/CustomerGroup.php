<?php

namespace Potato\Crawler\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class CustomerGroup
 */
class CustomerGroup implements OptionSourceInterface
{
    const GUEST_VALUE = 'guest';

    /** @var GroupRepositoryInterface  */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * CustomerGroup constructor.
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $items = $this->groupRepository->getList($searchCriteria)->getItems();
        $result = [];
        foreach ($items as $item) {
            $id = $item->getId();
            if ($id == 0) {
                $id = self::GUEST_VALUE;
            }
            $result[] = ['value' => $id, 'label' => $item->getCode()];
        }
        return $result;
    }
}