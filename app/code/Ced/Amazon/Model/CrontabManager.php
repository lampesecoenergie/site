<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\Framework\ShellInterface;

class CrontabManager extends \Magento\Framework\Crontab\CrontabManager
{
    const AMAZON_TASKS_BLOCK_START = '#~ AMAZON START';
    const AMAZON_TASKS_BLOCK_END = '#~ AMAZON END';

    /**
     * @var \Magento\Framework\App\Shell
     */
    private $shell;

    /**
     * @var Filesystem
     */
    private $filesystem;

    private $content;

    /**
     * @param ShellInterface $shell
     * @param Filesystem $filesystem
     */
    public function __construct(
        ShellInterface $shell,
        Filesystem $filesystem
    ) {
        parent::__construct($shell, $filesystem);
        $this->shell = $shell;
        $this->filesystem = $filesystem;
    }

    /**
     * @return string
     */
    private function getTasksBlockStart()
    {
        $tasksBlockStart = self::AMAZON_TASKS_BLOCK_START;
        if (defined('BP')) {
            $tasksBlockStart .= ' ' . sha1(BP);
        }
        return $tasksBlockStart;
    }

    /**
     * @return string
     */
    private function getTasksBlockEnd()
    {
        $tasksBlockEnd = self::AMAZON_TASKS_BLOCK_END;
        if (defined('BP')) {
            $tasksBlockEnd .= ' ' . sha1(BP);
        }
        return $tasksBlockEnd;
    }

    /**
     * Generate Magento Tasks Section
     *
     * @param string $content
     * @param array $tasks
     * @return string
     */
    private function generateSection($content, $tasks = [])
    {
        if ($tasks) {
            // Add EOL symbol to previous line if not exist.
            if (substr($content, -strlen(PHP_EOL)) !== PHP_EOL) {
                $content .= PHP_EOL;
            }

            $content .= $this->getTasksBlockStart() . PHP_EOL;
            foreach ($tasks as $task) {
                // Task updated by removing the PHP Binary from Between.
                $content .= $task['expression'] . ' ' . $task['command'] . PHP_EOL;
            }
            $content .= $this->getTasksBlockEnd() . PHP_EOL;
        }

        return $content;
    }

    /**
     * Clean Magento Tasks Section in crontab content
     *
     * @param string $content
     * @return string
     */
    private function cleanMagentoSection($content)
    {
        $content = preg_replace(
            '!' . preg_quote($this->getTasksBlockStart()) . '.*?'
            . preg_quote($this->getTasksBlockEnd() . PHP_EOL) . '!s',
            '',
            $content
        );

        return $content;
    }

    /**
     * Get crontab content without Magento Tasks Section
     *
     * In case of some exceptions the empty content is returned
     *
     * @return string
     */
    private function getCrontabContent()
    {
        try {
            $content = (string)$this->shell->execute('crontab -l');
        } catch (LocalizedException $e) {
            return '';
        }

        return $content;
    }

    /**
     * Save crontab
     *
     * @param string $content
     * @return void
     * @throws LocalizedException
     */
    private function save($content)
    {
        $this->content = $content;
        $content = str_replace(['%', '"', '$'], ['%%', '\"','\$'], $content);
        try {
            $this->shell->execute('echo "' . $content . '" | crontab -');
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                new Phrase('Error during saving of crontab: %1', [$e->getPrevious()->getMessage()]),
                $e
            );
        }
    }

    /**
     * Check that OS is supported: Function from Magento
     *
     * If OS is not supported then no possibility to work with crontab
     *
     * @return void
     * @throws LocalizedException
     */
    private function checkSupportedOs()
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            throw new LocalizedException(
                new Phrase('Your operating system is not supported to work with this command.')
            );
        }
    }

    /**
     * Updated: Function from Magento
     * {@inheritdoc}
     */
    public function saveTasks(array $tasks)
    {
        if (!$tasks) {
            throw new LocalizedException(new Phrase('List of tasks is empty'));
        }

        $this->checkSupportedOs();
        $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        $logDir = $this->filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath();

        foreach ($tasks as $key => $task) {
            if (empty($task['expression'])) {
                $tasks[$key]['expression'] = '* * * * *';
            }

            if (empty($task['command'])) {
                throw new LocalizedException(new Phrase('Command should not be empty'));
            }

            $tasks[$key]['command'] = str_replace(
                ['{magentoRoot}', '{magentoLog}', '{phpPath}'],
                [$baseDir, $logDir, PHP_BINARY],
                $task['command']
            );
        }

        $content = $this->getCrontabContent();
        $content = $this->cleanMagentoSection($content);
        $content = $this->generateSection($content, $tasks);
        $this->save($content);
    }

    /**
     * @inheritdoc
     */
    public function removeTasks()
    {
        $this->checkSupportedOs();
        $content = $this->getCrontabContent();
        $content = $this->cleanMagentoSection($content);
        $this->save($content);
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function getTasks()
    {
        $this->checkSupportedOs();
        $content = $this->getCrontabContent();
        $pattern = '!(' . $this->getTasksBlockStart() . ')(.*?)(' . $this->getTasksBlockEnd() . ')!s';

        if (preg_match($pattern, $content, $matches)) {
            $tasks = trim($matches[2], PHP_EOL);
            $tasks = explode(PHP_EOL, $tasks);
            return $tasks;
        }

        return [];
    }
}
