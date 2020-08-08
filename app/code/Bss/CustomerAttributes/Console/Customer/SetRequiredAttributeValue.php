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
namespace Bss\CustomerAttributes\Console\Customer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Area;

class SetRequiredAttributeValue extends Command
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerFactory;

    /**
     * @var State
     */
    protected $appState;

    /**
     * SetRequiredAttributeValue constructor.
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     * @param State $appState
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        State $appState
    ) {
        parent::__construct();
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->appState = $appState;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('bss:required-attribute:customer');
        $this->setDescription('Bss Customer Required Attribute');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|int|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
            $attributes = $this->getRequiredAttributeAndValue();
            $customerCollection = $this->customerFactory->create();
            foreach ($attributes as $attribute) {
                foreach ($customerCollection as $customer) {
                    if (!$customer->getData($attribute["code"])) {
                        $customerDataModel = $customer->getDataModel();
                        $customerDataModel->setCustomAttribute($attribute["code"], $attribute["value"]);
                        $customer->updateData($customerDataModel);
                        $this->setCustomer($customer);
                    }
                }
            }
        } catch (LocalizedException $e) {
            $output->writeln('Something went wrong while run command.');
            $output->writeln($e->getMessage());
            return false;
        }

        $output->writeln("Set Default Require Value For Existing Customer Complete !");
    }

    /**
     * Get Required Attributes and Default Value
     *
     * @return array
     */
    protected function getRequiredAttributeAndValue()
    {
        $attributes = [];
        $attributeCollection = $this->helper->getUserDefinedAttributes();
        if ($attributeCollection->getSize() > 0) {
            foreach ($attributeCollection as $attribute) {
                if ($attribute->getIsRequired()) {
                    $attributeCode = $attribute->getAttributeCode();
                    $defaultRequired = $this->helper->getDefaultValueRequired($attribute);
                    $attributes[] = [
                        "code" => $attributeCode,
                        "value" => $defaultRequired
                    ];
                }
            }
        }

        return $attributes;
    }

    /**
     * @param Customer $customer
     * @return mixed
     */
    protected function setCustomer($customer)
    {
        return $customer->save();
    }
}
