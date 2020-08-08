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



namespace Mirasvit\Feed\Api\Service;


use Mirasvit\Feed\Model\Feed;

interface SchemaValidationInterface
{
    const CSV = 'csv';
    const XML = 'xml';

    /**
     * Return validation result.
     *
     * @return array
     */
    public function validateSchema();

    /**
     * Initialize schema validation service.
     *
     * @param Feed $feed
     *
     * @return $this
     */
    public function init(Feed $feed);

    /**
     * Get number of invalid entities in the schema.
     *
     * @return int
     */
    public function getInvalidEntityQty();
}
