<?php
namespace Potato\ImageOptimization\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Manager\Optimization as OptimizationManager;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Symfony\Component\Console\Helper\ProgressBar;
use Potato\ImageOptimization\Model\Lock;

class Optimize extends Command
{
    const INPUT_KEY_LIMIT = 'limit';
    
    const PROGRESS_FORMAT = '<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%';
    
    /** @var Logger  */
    protected $logger;

    /** @var Config  */
    protected $config;

    /** @var OptimizationManager  */
    protected $optimizationManager;

    /** @var ImageRepository  */
    protected $imageRepository;

    /** @var Lock  */
    protected $lock;

    /** @var ProgressBar */
    protected $progress;

    /**
     * @param Logger $logger
     * @param Config $config
     * @param OptimizationManager $optimizationManager
     * @param ImageRepository $imageRepository
     * @param Lock $lock
     * @param null $name
     */
    public function __construct(
        Logger $logger,
        Config $config,
        OptimizationManager $optimizationManager,
        ImageRepository $imageRepository,
        Lock $lock,
        $name = null
    ) {
        parent::__construct($name);
        $this->logger = $logger;
        $this->imageRepository = $imageRepository;
        $this->optimizationManager = $optimizationManager;
        $this->config = $config;
        $this->lock = $lock;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('po_image_optimization:optimize')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_LIMIT,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Optimize until optimized image count < limit'
                )
            ])
            ->setDescription('Potato Image Optimizer: manually optimize via console');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return $this|int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (true === $this->lock->isLocked(Lock::OPTIMIZATION_LOCK_FILE)) {
            return $this;
        }
        /* optimize via lib */
        $limit = $this->imageRepository
            ->getCollectionByStatusList([StatusSource::STATUS_PENDING, StatusSource::STATUS_OUTDATED])->getSize();
        if ($input->getOption(self::INPUT_KEY_LIMIT) && $input->getOption(self::INPUT_KEY_LIMIT) < $limit) {
            $limit = $input->getOption(self::INPUT_KEY_LIMIT);
        }
        $this->progress = new ProgressBar($output, $limit);
        $this->progress->setFormat(self::PROGRESS_FORMAT);
        $this->progress->setMessage("Optimize images");
        try {
            $this->runOptimization($limit);
        } catch (\Exception $e) {
            $this->lock->removeLock(Lock::OPTIMIZATION_LOCK_FILE);
            $output->writeln($e->getMessage());
            return $this;
        }

        $output->writeln("");
        $this->lock->removeLock(Lock::OPTIMIZATION_LOCK_FILE);
        return $this;
    }

    /**
     * @param int|null $limit
     * @return $this
     * @throws \Exception
     */
    protected function runOptimization($limit)
    {
        $count = 0;
        while ($count < $limit) {
            $imageCollection = $this->imageRepository->getCollectionForOptimization($limit);
            $this->optimizationManager->optimizeImageCollection($imageCollection->getItems());
            $count += count($imageCollection->getItems());
            $this->progress->advance(count($imageCollection->getItems()));
        }
        return $this;
    }
}