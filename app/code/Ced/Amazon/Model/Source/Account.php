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

namespace Ced\Amazon\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Account implements ArrayInterface
{
    /** @var \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory  */
    public $accountCollectionFactory;

    public function __construct(
        \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory
    ) {
        $this->accountCollectionFactory = $accountCollectionFactory;
    }

    /*
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        $accounts = $this->toArray();
        $result = [];

        foreach ($accounts as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $result;
    }

    /*
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {

        $accounts = $this->accountCollectionFactory->create();

        $accountsList = [];
        foreach ($accounts as $account) {
            $accountsList[$account->getId()] = __($account->getName()) . ' | Id:' . $account->getId();
        }

        return $accountsList;
    }
}
