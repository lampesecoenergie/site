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

use Mirasvit\Feed\Export\Context;
use Mirasvit\Feed\Helper\Io;
use Mirasvit\Feed\Model\Config;

class Finalization extends AbstractStep
{
    /**
     * @var Io
     */
    protected $io;

    /**
     * @var Config
     */
    protected $config;

    /**
     * {@inheritdoc}
     * @param Config  $config
     * @param Io      $io
     * @param Context $context
     */
    public function __construct(
        Config $config,
        Io $io,
        Context $context
    ) {
        $this->io = $io;
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * Copy temp feed file to regular place
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isReady()) {
            $this->beforeExecute();
        }

        $feed = $this->context->getFeed();
        $tmpPath = $this->config->getTmpPath() . DIRECTORY_SEPARATOR . $feed->getId() . '.dat';
        $targetPath = $this->config->getBasePath() . DIRECTORY_SEPARATOR . $this->context->getFilename();

        $this->io->copy($tmpPath, $targetPath);

        $this->index++;
    }
}
