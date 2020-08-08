<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 13/9/19
 * Time: 3:24 PM
 */

namespace Ced\Amazon\Service\Feed;

use Ced\Amazon\Api\Processor\BulkActionProcessorInterface;
use Ced\Amazon\Repository\Feed as FeedRepository;
use Ced\Amazon\Helper\Logger;

class Sync implements BulkActionProcessorInterface
{
    /**
     * @var \Ced\Amazon\Helper\Logger
     */
    public $logger;

    /** @var \Ced\Amazon\Repository\Feed */
    public $feedRepository;

    public function __construct(
        FeedRepository $feedRepository,
        Logger $logger
    ) {
        $this->feedRepository = $feedRepository;
        $this->logger = $logger;
    }

    public function process($ids)
    {
        try {
            $status = false;
            if (!empty($ids) && is_array($ids)) {
                foreach ($ids as $id) {
                    $this->feedRepository->sync($id);
                    $status = true;
                }
            }
        } catch (\Exception $e) {
            $status = false;
            $this->logger->error(
                'Error in bulk feed sync',
                [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }

        return $status;
    }
}