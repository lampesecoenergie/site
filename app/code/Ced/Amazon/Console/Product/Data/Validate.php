<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Console\Product\Data;

class Validate extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:product:data:validate';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('Validate Magento product data with listed Amazon data.');
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);
        $rows = 2;
        /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
        $progress = $this->om->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            ['output' => $output, 'rows' => $rows]
        );
        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->start();

        try {
            $counter = 0;
            $invalid = 0;

            /** @var \Ced\Amazon\Repository\Account $accountRepository */
            $accountRepository = $this->om->create(\Ced\Amazon\Repository\Account::class);
            /** @var \Magento\Framework\Api\FilterFactory $profile */
            $filter = $this->om->create(\Magento\Framework\Api\FilterFactory::class);
            /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $profile */
            $search = $this->om->create(\Magento\Framework\Api\Search\SearchCriteriaBuilderFactory::class);
            /** @var \Ced\Amazon\Repository\Profile $profile */
            $profile = $this->om->create(\Ced\Amazon\Repository\Profile::class);
            /** @var \Amazon\Sdk\Api\Product\ProductListFactory $productListFactory */
            $productListFactory = $this->om->create(\Amazon\Sdk\Api\Product\ProductListFactory::class);

            /** @var \Magento\Framework\Api\Filter $statusFilter */
            $statusFilter = $filter->create();
            $statusFilter->setField(\Ced\Amazon\Model\Profile::COLUMN_STATUS)
                ->setConditionType('eq')
                ->setValue(\Ced\Amazon\Model\Source\Profile\Status::ENABLED);

            /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
            $criteria = $search->create();
            $criteria->addFilter($statusFilter);
            /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $profiles */
            $profiles = $profile->getList($criteria->create());

            if ($profiles->getTotalCount() > 0) {
                /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
                $accounts = $profiles->getAccounts();

                /** @var array $stores */
                $stores = $profiles->getProfileByStoreIdWise();

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                foreach ($accounts->getItems() as $accountId => $account) {
                    foreach ($stores as $storeId => $profiles) {
                        $envelope = null;
                        /** @var \Ced\Amazon\Api\Data\ProfileInterface $model */
                        foreach ($profiles as $profileId => $model) {
                            $brandAttribute = $this->getAttribute($model, 'DescriptionData_Brand');
                            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
                            $products = $profile->getAssociatedProducts($profileId, $storeId)
                                ->addAttributeToSelect('*');
                            /** @var \Magento\Catalog\Model\Product $product */
                            foreach ($products as $product) {
                                $productList = $productListFactory->create(
                                    [
                                        'config' => $accountRepository->getById($model->getAccountId())->getConfig($model->getMarketplaceIds()),
                                        'logger' => null
                                    ]
                                );

                                $productList->setIdType('SellerSKU');
                                $productList->setProductIds($product->getSku());
                                $productList->fetchProductList();
                                $result = $productList->getProduct();
                                $data = [];
                                if ($result != false && !isset($result['Error'])) {
                                    /** @var \Amazon\Sdk\Api\Product $p */
                                    foreach ($result as $i => $p) {
                                        $data = $p->getData();
                                        if (isset($data['AttributeSets'][0]['Brand'])) {
                                            $brand = $data['AttributeSets'][0]['Brand'];

                                            /** @var \Magento\Eav\Model\Attribute $magentoAttribute */
                                            $magentoAttribute = $product->getResource()
                                                ->getAttribute($brandAttribute);
                                            if ($magentoAttribute && ($magentoAttribute->usesSource() ||
                                                    $magentoAttribute->getData('frontend_input') == 'select')
                                            ) {
                                                $value =
                                                    $magentoAttribute->getSource()->getOptionText(
                                                        $product->getData($brandAttribute)
                                                    );
                                                if (is_object($value)) {
                                                    $value = $value->getText();
                                                }
                                            } else {
                                                $value = $product->getData($brandAttribute);
                                            }

                                            if (!$this->match($brand, $value)) {
                                                $invalid++;
                                                $product->setStoreId(0)->setData('is_amazon_brand_valid', '0');
                                                $product->setData('asin', '');
                                                $product->getResource()->saveAttribute($product, 'is_amazon_brand_valid');
                                                $product->getResource()->saveAttribute($product, 'asin');

                                                $output->writeln("");
                                                $output->writeln("Id: ". $product->getId());
                                                $output->writeln("Magento: ". $value);
                                                $output->writeln("Amazon: ". (string)$brand);
                                            } else {
                                                $product->setStoreId(0)->setData('is_amazon_brand_valid', '1');
                                                $product->getResource()->saveAttribute($product, 'is_amazon_brand_valid');
                                            }

                                            $progress->advance();
                                            $counter++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $progress->finish();

        $output->writeln("{$counter} products validated. {$invalid} are invalid.");
    }

    private function match($value1, $value2)
    {
        $value1 = preg_replace("/[^A-Za-z0-9 ]/", '', $value1);
        $value2 = preg_replace("/[^A-Za-z0-9 ]/", '', $value2);

        $status = false;
        if (strtoupper((string)$value1) == strtoupper($value2) ||
            strpos(
                strtoupper(preg_replace('/\s+/', '', $value1)),
                strtoupper(preg_replace('/\s+/', '', $value2))
            ) !== false ||
            strpos(
                strtoupper(preg_replace('/\s+/', '', $value2)),
                strtoupper(preg_replace('/\s+/', '', $value1))
            ) !== false) {
            $status = true;
        }

        return $status;
    }

    /**
     * @param \Ced\Amazon\Model\Profile $profile
     * @param string $name
     * @return string|null
     */
    private function getAttribute($profile, $name)
    {
        $code = null;
        $attributes = $profile->getProfileAttributes();
        foreach ($attributes as $id => $attribute) {
            if ($id == $name && isset($attribute['magento_attribute_code']) &&
                !empty($attribute['magento_attribute_code'])) {
                $code = $attribute['magento_attribute_code'];
                break;
            }
        }

        return $code;
    }
}
