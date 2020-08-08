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



namespace Mirasvit\Feed\Api\Data;


interface ValidationInterface
{
    const TABLE_NAME = 'mst_feed_validation';

    const ID         = 'validation_id';
    const ENTITY_ID  = 'entity_id';
    const LINE_NUM   = 'line_num';
    const FEED_ID    = 'feed_id';
    const ATTRIBUTE  = 'attribute';
    const VALIDATOR  = 'validator';
    const VALUE      = 'value';

    /**
     * @return string
     */
    public function getValidator();
}
