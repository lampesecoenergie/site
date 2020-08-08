<?php
namespace Fooman\PdfDesign\Model;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PdfDesignFactory
{

    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    private $config;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        \Magento\Framework\Config\DataInterface $config,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $designName
     *
     * @return \Fooman\PdfDesign\Model\Api\DesignInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($designName)
    {
        $className = $this->config->getClassForDesign($designName);
        $design = $this->objectManager->get($className);
        if (!$design instanceof \Fooman\PdfDesign\Model\Api\DesignInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'The Factory ' . $className
                    . ' does not return an instance of \Fooman\PdfDesign\Model\Api\DesignInterface'
                )
            );
        }
        return $design;
    }
}
