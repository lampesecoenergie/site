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



namespace Mirasvit\Feed\Console\Command;

use Magento\Framework\App\State;
use Mirasvit\Feed\Model\FeedFactory;
use Mirasvit\Feed\Model\Feed\Exporter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends AbstractCommand
{
    const INPUT_FEED_ID = 'id';
    const INPUT_FEED_STEP = 'step';

    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var Exporter
     */
    protected $exporter;

    /**
     * {@inheritdoc}
     * @param FeedFactory $feedFactory
     * @param Exporter $exporter
     * @param State $appState
     */
    public function __construct(
        FeedFactory $feedFactory,
        Exporter $exporter,
        State $appState
    ) {
        $this->feedFactory = $feedFactory;
        $this->exporter = $exporter;

        parent::__construct($appState);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_FEED_ID,
                null,
                InputOption::VALUE_OPTIONAL,
                'Feed ID',
                false
            ),
            new InputOption(
                self::INPUT_FEED_STEP,
                null,
                InputOption::VALUE_OPTIONAL,
                'Feed Export Iteration Step',
                false
            ),
        ];
        $this->setName('mirasvit:feed:export')
            ->setDescription('Export Feed')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $feedId = $input->getOption(self::INPUT_FEED_ID);
        $feedStep = $input->getOption(self::INPUT_FEED_STEP);
        $verbose = $output->getVerbosity() >= 2 ? true : false;

        if ($feedId) {
            $feedsIds = [$feedId];
        } else {
            $feedsIds = $this->feedFactory->create()->getCollection()
                ->addFieldToFilter('is_active', ['eq' => 1])
                ->getAllIds();
        }

        if (!$feedStep) {
            $feedStep = \Mirasvit\Feed\Export\Liquid\Tag\TagFor::DEFAULT_STEP;
        }
        $this->exporter->setProductExportStep($feedStep);

        foreach ($feedsIds as $feedId) {
            /** @var \Mirasvit\Feed\Model\Feed $feed */
            $feed = $this->feedFactory->create()->load($feedId);

            if (!$feed->getId()) {
                $output->writeln('<error>Feed not exists.</error>');

                continue;
            }

            if ($verbose) {
                $output->writeln('<info>' . $feed->getName() . '</info>');
            }

            try {
                $time = microtime(true);
                foreach ($this->exporter->exportCli($feed) as $status => $state) {
                    if ($verbose) {
                        $output->writeln('<info>' . ucfirst($status) . '</info>');

                        if ($output->getVerbosity() === 256) {
                            $output->writeln('  Memory: ' . round(memory_get_usage(true) / 1024 / 1024) . 'mb');
                            $output->writeln('  Iteration time: ' . round(microtime(true) - $time) . 's');
                            $time = microtime(true);
                        }

                        $output->writeln('<comment>' . $state . '</comment>');
                    }
                }

                $output->writeln('<info>Feed successfully generated.</info>');
                $output->writeln('<info>' . $feed->getUrl() . '</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>Error during feed generation.</error>');
                $output->writeln('<error>' . $e->getMessage() . '</error>');

                return;
            }
        }
    }
}
