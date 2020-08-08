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


namespace Mirasvit\Feed\Export\Step;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Feed\Api\Data\ValidationInterface;
use Mirasvit\Feed\Api\Repository\ValidationRepositoryInterface;
use Mirasvit\Feed\Api\Service\SchemaValidationInterface;
use Mirasvit\Feed\Export\Context;
use Mirasvit\Feed\Helper\Io;
use Mirasvit\Feed\Model\Feed;

class Validation extends AbstractStep
{
    const STEP = 'validation';
    const INVALID_ENTITY_COUNT = 'invalid_entity_count';

    /**
     * @var Io
     */
    protected $io;
    /**
     * @var ValidationRepositoryInterface
     */
    private $validationRepository;

    /**
     * @var array
     */
    private $invalidEntityQty = 0;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ValidationRepositoryInterface $validationRepository,
        ResourceConnection $resource,
        Io $io,
        Context $context
    ) {
        $this->validationRepository = $validationRepository;
        $this->resource = $resource;
        $this->io = $io;

        parent::__construct($context);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeExecute()
    {
        $connection = $this->resource->getConnection();
        $connection->delete($this->resource->getTableName(ValidationInterface::TABLE_NAME),
            [ValidationInterface::FEED_ID . ' = ?' => $this->context->getFeed()->getId()]
        );

        return parent::beforeExecute();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->context->isTestMode()) {
            return parent::execute();
        }

        if ($this->isReady()) {
            $this->beforeExecute();
        }

        $feed = $this->context->getFeed();

        $schemaValidationService = $this->validationRepository->getSchemaValidationService(
            $feed->isXml() ? SchemaValidationInterface::XML : SchemaValidationInterface::CSV
        )->init($feed);

        $result = $schemaValidationService->validateSchema();
        $this->invalidEntityQty = $schemaValidationService->getInvalidEntityQty();

        if ($result) {
            $connection = $this->resource->getConnection();
            $connection->insertMultiple($this->resource->getTableName(ValidationInterface::TABLE_NAME), $result);
        }

        $this->index++;

        if ($this->isCompleted()) {
            $this->afterExecute();
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function afterExecute()
    {
        $this->setData(self::INVALID_ENTITY_COUNT, $this->invalidEntityQty);

        return $this;
    }
}
