<?php
namespace Potato\Crawler\Model\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Potato\Crawler\Model\Cron\Warmer as CronWarmer;
use Potato\Crawler\Logger\Logger;

class Warmer extends Command
{
    /** @var Logger  */
    protected $logger;
    
    /** @var CronWarmer */
    protected $cronWarmer;

    /**
     * Warmer constructor.
     * @param CronWarmer $cronWarmer
     * @param Logger $logger
     * @param null $name
     */
    public function __construct(
        CronWarmer $cronWarmer,
        Logger $logger,
        $name = null
    ) {
        parent::__construct($name);
        $this->cronWarmer = $cronWarmer;
        $this->logger = $logger;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('po_crawler:warmer')
            ->setDescription('Potato Crawler: run crawler')
        ;
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //process should work without time limit
        ini_set('max_execution_time', -1);

        try {
            $this->cronWarmer->process();
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        return true;
    }
}