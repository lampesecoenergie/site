<?php

namespace Potato\ImageOptimization\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;

abstract class Image extends Action
{
    const ADMIN_RESOURCE = 'Potato_ImageOptimization::po_image_grid';

    /** @var ImageRepository  */
    protected $imageRepository;

    /**
     * @param Action\Context $context
     * @param ImageRepository $imageRepository
     */
    public function __construct(
        Action\Context $context,
        ImageRepository $imageRepository
    ) {
        parent::__construct($context);
        $this->imageRepository = $imageRepository;

    }
}
