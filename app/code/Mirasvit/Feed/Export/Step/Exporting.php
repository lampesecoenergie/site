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
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Feed\Export\Context;
use Mirasvit\Feed\Export\Liquid\Tag\TagFor;
use Mirasvit\Feed\Export\Resolver\GeneralResolver;
use Mirasvit\Feed\Helper\Io;
use Mirasvit\Feed\Model\Config;
use Mirasvit\Feed\Export\Liquid\Context as LiquidContext;
use Mirasvit\Feed\Export\Liquid\Template as LiquidTemplate;

class Exporting extends AbstractStep
{
    const STEP = 'exporting';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var GeneralResolver
     */
    protected $resolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Io
     */
    protected $io;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var LiquidTemplate
     */
    protected $liquidTemplate;


    public function __construct(
        ResourceConnection $resource,
        Io $io,
        Config $config,
        GeneralResolver $resolver,
        ObjectManagerInterface $objectManager,
        Context $context
    ) {
        $this->resource = $resource;
        $this->resolver = $resolver;
        $this->config = $config;
        $this->io = $io;
        $this->objectManager = $objectManager;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        parent::beforeExecute();

        $this->length = $this->resolver->getProducts()->getSize();
        $this->index = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isReady()) {
            $this->beforeExecute();
        }

        $index = $this->index;

        $template = $this->context->getFeed()->getLiquidTemplate();

        $liquidState = [];
        if (isset($this->data['liquid'])) {
            $liquidState = $this->data['liquid'];
        }

        $liquidTemplate = new LiquidTemplate();
        $liquidTemplate->parse($template)
            ->fromArray($liquidState);

        $this->liquidTemplate = $liquidTemplate;

        $liquidContext = new LiquidContext($this->resolver, []);

        $liquidContext->addFilters($this->objectManager->get('\Mirasvit\Feed\Export\Filter\Pool')->getScopes());

        $liquidContext->setTimeoutCallback([$this->context, 'isTimeout'])
            ->setIterationCallback([$this, 'onIndexUpdate']);

        $liquidContext->setProductExportStep($this->context->getProductExportStep());

        $result = $liquidTemplate->execute($liquidContext);

        $filePath = $this->config->getTmpPath() . DIRECTORY_SEPARATOR . $this->context->getFeed()->getId() . '.dat';

        // remove duplicate break lines and trailing newline
        $result = preg_replace("/[\r\n]+/", "\n", rtrim($result));

        $this->io->write($filePath, $result, 'a');

        $this->data['liquid'] = $liquidTemplate->toArray();

        if ($this->index == $index) {
            #index was not changed
            $this->index = $this->length;
        }

        if ($this->isCompleted()) {
            $this->afterExecute();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterExecute()
    {
        $this->setData('count', $this->getLength());

        return parent::afterExecute();
    }

    /**
     * {@inheritDoc}
     */
    public function getLength()
    {
        return $this->length - $this->getSubtractLength();
    }

    /**
     * Callback method for liquid template processor
     *
     * @param array $iteration
     * @return void
     */
    public function onIndexUpdate($iteration)
    {
        $this->index = $iteration['index'];
        $this->length = $iteration['length'];
    }

    /**
     * Get number of non countable tags in the template.
     * Count only length of items in the TagFor.
     *
     * @return int
     */
    private function getSubtractLength()
    {
        $subtractNum = 0;
        $liquidTemplate = $this->liquidTemplate;

        if (!$liquidTemplate) {
            $liquidTemplate = new LiquidTemplate();
            $liquidTemplate->parse($this->context->getFeed()->getLiquidTemplate());
        }

        foreach ($liquidTemplate->getRoot()->getNodeList() as $tag) {
            if (!$tag instanceof TagFor) {
                $subtractNum++;
            }
        }

        return $subtractNum;
    }
}
