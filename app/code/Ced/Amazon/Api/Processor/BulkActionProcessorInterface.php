<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 12/9/19
 * Time: 4:28 PM
 */

namespace Ced\Amazon\Api\Processor;

interface BulkActionProcessorInterface
{
    /**
     * Process the given ids
     * @param mixed $ids
     * @return boolean
     */
    public function process($ids);
}