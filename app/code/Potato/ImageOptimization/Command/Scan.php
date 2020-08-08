<?php
namespace Potato\ImageOptimization\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Store\Model\StoreManagerInterface;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Manager\Scanner as ScannerManager;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Helper\ProgressBar;
use Potato\ImageOptimization\Model\Lock;

class Scan extends Command
{
    const INPUT_KEY_LIMIT = 'limit';
    const INPUT_KEY_SEARCH_DIR = 'dir';
    const INPUT_KEY_START_PATH = 'start_path';
    
    /** @var Logger  */
    protected $logger;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var Config  */
    protected $config;

    /** @var ScannerManager  */
    protected $scanner;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var ProgressBar */
    protected $progress;

    /** @var Lock  */
    protected $lock;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param Config $config
     * @param ScannerManager $scanner
     * @param Filesystem $filesystem
     * @param Lock $lock
     * @param null $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Logger $logger,
        Config $config,
        ScannerManager $scanner,
        Filesystem $filesystem,
        Lock $lock,
        $name = null
    ) {
        parent::__construct($name);
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->scanner = $scanner;
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->lock = $lock;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('po_image_optimization:scan')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_LIMIT,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Scan until found image count < limit'
                ),
                new InputOption(
                    self::INPUT_KEY_SEARCH_DIR,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Scan in this dir'
                ),
                new InputOption(
                    self::INPUT_KEY_START_PATH,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Scan from this path'
                )
            ])
            ->setDescription('Potato Image Optimizer: manually scan via console');

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
        if (true === $this->lock->isLocked(Lock::SCAN_LOCK_FILE)) {
            return $this;
        }
        $limit = null;
        if ($input->getOption(self::INPUT_KEY_LIMIT)) {
            $limit = $input->getOption(self::INPUT_KEY_LIMIT);
        }
        $searchDir = null;
        if ($input->getOption(self::INPUT_KEY_SEARCH_DIR)) {
            $basePath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $searchDir = $basePath . trim($input->getOption(self::INPUT_KEY_SEARCH_DIR), '/');
        }
        $startPath = null;
        if ($input->getOption(self::INPUT_KEY_START_PATH)) {
            $basePath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $startPath = $basePath . trim($input->getOption(self::INPUT_KEY_START_PATH), '/');
        }
        $progressLimit = ($limit) ? $limit : 0;
        $this->progress = new ProgressBar($output, $progressLimit);
        $this->progress->setFormat('<comment>%message%</comment> %current% images');
        $this->progress->setMessage(__('Search images in file system'));
        $this->progress->start();
        $this->scanner->setCallback([$this, 'updateProgress']);
        try {
            $this->runScanFilesystem($searchDir, $startPath, $limit);
        } catch (\Exception $e) {
            $this->lock->removeLock(Lock::SCAN_LOCK_FILE);
            $output->writeln($e->getMessage());
            return $this;
        }
        $output->writeln("");
        $this->progress = new ProgressBar($output, $progressLimit);
        $this->progress->setFormat('<comment>%message%</comment> %current% images');
        $this->progress->setMessage(__('Update images from database'));
        $this->progress->start();
        try {
            $this->runScanDatabase();
        } catch (\Exception $e) {
            $this->lock->removeLock(Lock::SCAN_LOCK_FILE);
            $output->writeln($e->getMessage());
            return $this;
        }

        $output->writeln("");
        $this->lock->removeLock(Lock::SCAN_LOCK_FILE);
        return $this;
    }

    /**
     * @param null|string $searchDir
     * @param null|string $startPath
     * @param null|int $limit
     * @return $this
     * @throws \Exception
     */
    protected function runScanFilesystem($searchDir, $startPath, $limit)
    {
        if ($searchDir) {
            $this->scanner->prepareImagesFromDir($searchDir, $startPath, $limit);
        } else {
            $this->scanner->saveImageGalleryFiles($limit);
        }
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function runScanDatabase()
    {
        $result = true;
        while($result) {
            $result = $this->scanner->updateImagesFromDatabase();
        }
        return $this;
    }

    /**
     * @param int $callbackCount
     */
    public function updateProgress($callbackCount)
    {
        $this->progress->setProgress($callbackCount);
    }
}