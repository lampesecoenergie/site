<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Api\Repository;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Feed\Api\Data\ValidationInterface;
use Mirasvit\Feed\Api\Service\SchemaValidationInterface;
use Mirasvit\Feed\Validator\ValidatorInterface;

interface ValidationRepositoryInterface
{
    /**
     * @return ValidationInterface[]|\Mirasvit\Feed\Model\ResourceModel\Validation\Collection
     */
    public function getCollection();

    /**
     * @return ValidationInterface|AbstractModel
     */
    public function create();

    /**
     * @param int $id
     *
     * @return ValidationInterface|false
     */
    public function get($id);

    /**
     * @param ValidationInterface|AbstractModel $model
     * @return ValidationInterface
     */
    public function save(ValidationInterface $model);

    /**
     * @param ValidationInterface|AbstractModel $model
     * @return bool
     */
    public function delete(ValidationInterface $model);

    /**
     * Get available validators.
     *
     * @return ValidatorInterface[]
     */
    public function getValidators();

    /**
     * Get validator by code.
     *
     * @param string $code
     *
     * @return bool|ValidatorInterface
     */
    public function getValidatorByCode($code);

    /**
     * Instantiate and return schema validation service based on given type.
     *
     * @param string $schemaType
     *
     * @return SchemaValidationInterface
     */
    public function getSchemaValidationService($schemaType);
}