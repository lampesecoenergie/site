<?php

namespace Potato\Crawler\Plugin\Result;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Potato\Crawler\Api\PopularityRepositoryInterface;
use Magento\Framework\Controller\ResultInterface;

class AddPopularity
{
    /** @var StoreManagerInterface  */
    protected $storeManager;
    
    /** @var PopularityRepositoryInterface  */
    protected $popularityRepository;

    /** @var HttpRequest  */
    protected $request;

    /**
     * AddPopularity constructor.
     * @param StoreManagerInterface $storeManager
     * @param PopularityRepositoryInterface $popularityRepository
     * @param HttpRequest $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PopularityRepositoryInterface $popularityRepository,
        HttpRequest $request
    ) {
        $this->storeManager = $storeManager;
        $this->popularityRepository = $popularityRepository;
        $this->request = $request;
    }
}