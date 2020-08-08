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


namespace Mirasvit\Feed\Export;

use Mirasvit\Feed\Export\Step\StepFactory;
use Mirasvit\Feed\Helper\Io;
use Mirasvit\Feed\Model\Config;

class Context
{
    /**
     * @var int
     */
    protected $productExportStep = 0;

    /**
     * @var \Mirasvit\Feed\Model\Feed
     */
    protected $feed;

    /**
     * @var \Mirasvit\Feed\Export\Step\AbstractStep
     */
    protected $rootStep;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var bool
     */
    protected $isTestMode = false;

    /**
     * @var object
     */
    protected $currentObject;

    /**
     * Constructor.
     *
     * @param Io                    $io
     * @param Config                $config
     * @param StepFactory           $stepFactory
     */
    public function __construct(
        Io $io,
        Config $config,
        StepFactory $stepFactory
    ) {
        $this->io = $io;
        $this->config = $config;
        $this->stepFactory = $stepFactory;

        $this->createdAt = microtime(true);
        $this->startedAt = microtime(true);

        $this->lastSaveTime = 0;
    }

    /**
     * @param int $step
     * @return $this
     */
    public function setProductExportStep($step)
    {
        $this->productExportStep = $step;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductExportStep()
    {
        return $this->productExportStep;
    }

    /**
     * Step factory
     *
     * @return Step\StepFactory
     */
    public function getStepFactory()
    {
        return $this->stepFactory;
    }

    /**
     * Set feed model
     *
     * @param \Mirasvit\Feed\Model\Feed $feed
     * @return $this
     */
    public function setFeed($feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Feed model
     *
     * @return \Mirasvit\Feed\Model\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Feed filename
     *
     * @param string $file
     * @return $this
     */
    public function setFilename($file)
    {
        $this->filename = $file;

        return $this;
    }

    /**
     * Feed filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param object $obj
     * @return $this
     */
    public function setCurrentObject($obj)
    {
        $this->currentObject = $obj;

        return $this;
    }

    /**
     * @return object
     */
    public function getCurrentObject()
    {
        return $this->currentObject;
    }

    /**
     * Enable test mode
     *
     * @return $this
     */
    public function enableTestMode()
    {
        $this->isTestMode = true;

        return $this;
    }

    /**
     * Is Test mode?
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->isTestMode;
    }

    /**
     * Root step
     *
     * @return \Mirasvit\Feed\Export\Step\AbstractStep
     */
    public function getRootStep()
    {
        return $this->rootStep;
    }

    /**
     * General method. Run export process.
     *
     * @return $this
     */
    public function execute()
    {
        $this->startedAt = microtime(true);

        $this->getRootStep()->execute();

        return $this;
    }

    /**
     * Is timeout?
     *
     * @return bool
     */
    public function isTimeout()
    {
        if (microtime(true) - $this->lastSaveTime > 0.9) {
            $this->save();
            $this->lastSaveTime = microtime(true);
        }

        $isTimeout = microtime(true) - $this->startedAt > $this->config->getMaxAllowedTime();

        return $isTimeout;
    }

    /**
     * Step to string
     *
     * @return string
     */
    public function toString()
    {
        return $this->rootStep->toString() . PHP_EOL;
    }

    /**
     * Reset step state
     *
     * @return $this
     */
    public function reset()
    {
        $this->rootStep = $this->stepFactory->create('Root');
        $this->createdAt = microtime(true);
        $this->filename = null;
        $this->isTestMode = false;

        $this->save();

        return $this;
    }

    /**
     * Save steps to state file
     *
     * @return $this;
     */
    public function save()
    {
        $data = $this->rootStep->toArray();

        $data['filename'] = $this->filename;
        $data['isTestMode'] = $this->isTestMode;
        $data['createdAt'] = $this->createdAt;

        $string = serialize($data);

        $this->io->write($this->getStateFile(), $string);

        return $this;
    }

    /**
     * Load steps from state file
     *
     * @return $this
     */
    public function load()
    {
        $this->rootStep = $this->stepFactory->create('Root');

        if (file_exists($this->getStateFile()) && ($data = file_get_contents($this->getStateFile()))) {
            $data = unserialize($data);
            $this->filename = $data['filename'];
            $this->isTestMode = $data['isTestMode'];
            $this->createdAt = $data['createdAt'];

            $this->rootStep->fromArray($data);
        }

        return $this;
    }

    /**
     * Full path to state file
     *
     * @return string
     */
    public function getStateFile()
    {
        return $this->config->getTmpPath() . DIRECTORY_SEPARATOR . $this->getFeed()->getId() . '.state';
    }

    /**
     * Getter
     *
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
