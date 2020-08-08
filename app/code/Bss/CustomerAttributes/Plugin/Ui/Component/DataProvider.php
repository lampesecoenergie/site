<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Ui\Component;

class DataProvider
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $customerAttribute;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $config;

    /**
     * DataProvider constructor.
     * @param \Magento\Eav\Model\Config $config
     */
    public function __construct(
        \Magento\Eav\Model\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param \Magento\Customer\Ui\Component\DataProvider $subject
     * @param array $proceed
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundGetData($subject, $proceed)
    {
        $data = $proceed();
        foreach ($data['items'] as &$item) {
            $item_keys = array_keys($item);
            foreach ($item_keys as $key) {
                $attributeCodeCustom = $this->config->getAttribute('customer', $key);
                if ($attributeCodeCustom) {
                    if ($attributeCodeCustom->getFrontendInput() == 'file') {
                        $item[$key] = $this->getFileName($item[$key]);
                    }

                }
            }
        }
        return $data;
    }

    /**
     * @param string $filename
     * @return mixed
     */
    protected function getFileName($filename)
    {
        if (strpos($filename, "/") !==false) {
            $nameArr = explode("/", $filename);
            return end($nameArr);
        }
        return $filename;
    }
}
