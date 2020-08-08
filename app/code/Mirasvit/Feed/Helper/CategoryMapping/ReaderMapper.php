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

use \Mirasvit\Feed\Helper\CategoryMapping\Multiplicity\ReaderMultiplicityInterface;

class ReaderMapper
{
    /**
     * @var array
     */
    protected $multiplicityArray = [];

    /**
     * @var string
     */
    protected $mappingDelimiter = ' > ';

    /**
     * @param string $search
     *
     * @return array
     */
    public function getData($search)
    {
        $data = [];
        $result = [];
        /** @var ReaderMultiplicityInterface $multiplicity */
        foreach ($this->multiplicityArray as $multiplicity) {
            $items = $multiplicity->getItems();
            /** @var ReaderInterface $item */
            foreach ($items as $item) {
                $item->setMappingDelimiter($this->mappingDelimiter);
                $data = array_merge($data, $item->getRows($search));
            }
        }

        foreach ($data as $path => $row) {
            $result[] = [
                'file'  => $row,
                'path'  => $path,
                'label' => $path,
                'id'    => $path,
            ];
        }

        return $result;
    }

    /**
     * @param ReaderMultiplicityInterface $multiplicity
     * @return $this
     */
    public function addMultiplicity($multiplicity)
    {
        $this->multiplicityArray[] = $multiplicity;

        return $this;
    }
}