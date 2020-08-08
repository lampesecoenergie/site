<?php
namespace Fooman\PdfDesign\Model;

/**
 * Provide design class
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DesignProvider
{

    /**
     * @var DesignDecider
     */
    private $designDecider;

    /**
     * @var PdfDesignFactory
     */
    private $designFactory;

    public function __construct(
        DesignDecider $designDecider,
        PdfDesignFactory $pdfDesignFactory
    ) {
        $this->designDecider = $designDecider;
        $this->designFactory = $pdfDesignFactory;
    }

    /**
     * @param int   $storeId
     * @param array $templateVars
     *
     * @return Api\DesignInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDesign($storeId, array $templateVars = [])
    {
        $designName = $this->designDecider->pick($storeId, $templateVars);
        $design = $this->designFactory->get($designName);
        $design->setStoreId($storeId);
        return $design;
    }
}
