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



namespace Mirasvit\Feed\Helper\CategoryMapping;

class FileReader implements FileInterface
{
    /**
     * @var int
     */
    protected $limit = 100;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $mappingDelimiter = ' > ';

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $search
     * @return array
     */
    public function getRows($search)
    {
        $result = [];
        $handle = @fopen($this->getFile(), "r");
        if ($handle) {
            $i = 0;
            while (($buffer = fgets($handle, 4096)) !== false) {
                if (($this->getLimit() && $i >= $this->getLimit())) {
                    break;
                }
                if (stripos($buffer, $search) !== false) {
                    $categories = explode($this->getMappingDelimiter(), trim($buffer));
                    $buffer = implode($this->getMappingDelimiter(), $categories);
                    $result[$buffer] = $this->getFileName();
                    $i++;
                }
            }
            fclose($handle);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getMappingDelimiter()
    {
        return $this->mappingDelimiter;
    }

    /**
     * @param string $delimiter
     * @return $this
     */
    public function setMappingDelimiter($delimiter)
    {
        $this->mappingDelimiter = $delimiter;

        return $this;
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        return basename($this->getFile());
    }
}