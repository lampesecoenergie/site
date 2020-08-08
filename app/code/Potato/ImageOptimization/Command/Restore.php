<?php
namespace Potato\ImageOptimization\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Potato\ImageOptimization\Logger\Logger;
use Magento\Framework\App\CacheInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Manager\Restore as RestoreManager;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Symfony\Component\Console\Helper\ProgressBar;

class Restore extends Command
{
    const INPUT_KEY_FROM_STATUS = 'from_status';

    const INPUT_KEY_TO_STATUS = 'to_status';
    
    const PROGRESS_FORMAT = '<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%';
    
    /** @var Logger  */
    protected $logger;

    /** @var StatusSource  */
    protected $statusSource;

    /** @var CacheInterface  */
    protected $cache;

    /** @var Config  */
    protected $config;

    /** @var ImageRepository  */
    protected $imageRepository;

    /** @var RestoreManager  */
    protected $restoreManager;

    /**
     * @param Logger $logger
     * @param CacheInterface $cache
     * @param Config $config
     * @param ImageRepository $imageRepository
     * @param RestoreManager $restoreManager
     * @param StatusSource $statusSource
     * @param null $name
     */
    public function __construct(
        Logger $logger,
        CacheInterface $cache,
        Config $config,
        ImageRepository $imageRepository,
        RestoreManager $restoreManager,
        StatusSource $statusSource,
        $name = null
    ) {
        parent::__construct($name);
        $this->logger = $logger;
        $this->cache = $cache;
        $this->imageRepository = $imageRepository;
        $this->restoreManager = $restoreManager;
        $this->config = $config;
        $this->statusSource = $statusSource;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('po_image_optimization:restore')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_FROM_STATUS,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Restore all images with this status (Error by default)'
                ),
                new InputOption(
                    self::INPUT_KEY_TO_STATUS,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Set this status for all restored images (Pending by default)'
                ),
            ])
            ->setDescription('Potato Image Optimizer: manually restore images via console');

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
        $availableStatus = $this->statusSource->getOptionArray();
        $fromStatus = StatusSource::STATUS_ERROR;
        if ($input->getOption(self::INPUT_KEY_FROM_STATUS)
            && array_key_exists($input->getOption(self::INPUT_KEY_FROM_STATUS), $availableStatus)) {
            $fromStatus = $input->getOption(self::INPUT_KEY_FROM_STATUS);
        }

        $toStatus = StatusSource::STATUS_PENDING;
        if ($input->getOption(self::INPUT_KEY_TO_STATUS)
            && array_key_exists($input->getOption(self::INPUT_KEY_TO_STATUS), $availableStatus)) {
            $toStatus = $input->getOption(self::INPUT_KEY_TO_STATUS);
        }

        $restoreCollection = $this->imageRepository->getCollectionByStatusList([$fromStatus]);

        $limit = $restoreCollection->getSize();
        $progress = new ProgressBar($output, $limit);
        $progress->setFormat(self::PROGRESS_FORMAT);
        $progress->setMessage("Restore images");
        $processedCount = 0;
        $successCount = 0;
        $errorCount = 0;
        foreach($restoreCollection as $restoreImage) {
            try {
                $this->restoreManager->restoreImage($restoreImage);

                /* change */
                $restoreImage
                    ->setStatus($toStatus)
                    ->save();

                $successCount++;
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage() . __(" File path: %1", $restoreImage->getPath()));
                $errorCount++;
            }
            $processedCount++;
            $progress->setProgress($processedCount);
        }
        $output->writeln("");
        $output->writeln("Restored images: $successCount, Failed restore images: $errorCount");
        $output->writeln("");

        return $this;
    }
}