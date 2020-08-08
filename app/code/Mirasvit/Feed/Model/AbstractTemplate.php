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


namespace Mirasvit\Feed\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Abstract Template Model
 *
 * @method string getType()
 * @method $this setType($type)
 *
 * @method $this setFormat($format)
 *
 * @method array getCsvSchema()
 * @method $this setCsvSchema(array $schema)
 *
 * @method string getXmlSchema()
 * @method $this setXmlSchema($schema)
 */
abstract class AbstractTemplate extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->extract();

        return parent::_afterLoad();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        $this->serializeFormat();

        return parent::beforeSave();
    }

    /**
     * Serialize csv/xml data to format_serialized
     *
     * @return $this
     */
    protected function serializeFormat()
    {
        if ($this->isCsv()) {
            if ($this->hasData('csv')) {
                $this->setData('format_serialized', serialize($this->getData('csv')));
            }
        } else {
            if ($this->hasData('xml')) {
                $this->setData('format_serialized', serialize($this->getData('xml')));
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isCsv()
    {
        return in_array($this->getType(), ['txt', 'csv']);
    }

    /**
     * Is Xml?
     *
     * @return bool
     */
    public function isXml()
    {
        return !$this->isCsv();
    }

    /**
     * Is object instance of feed?
     *
     * @return bool
     */
    public function isFeed()
    {
        return $this instanceof Feed;
    }

    /**
     * Is object instance of template?
     *
     * @return bool
     */
    public function isTemplate()
    {
        return $this instanceof Template;
    }

    /**
     * Extract csv/xml values from format_serialized
     *
     * @return $this
     */
    protected function extract()
    {
        $data = $this->getData('format_serialized') ? unserialize($this->getData('format_serialized')) : [];

        if ($this->isCsv()) {
            foreach ($data as $key => $value) {
                $this->setData('csv_' . $key, $value);
            }

            if (is_array($this->getCsvSchema())) {
                // sort columns by order
                $orders = [];
                $schema = $this->getCsvSchema();
                foreach ($schema as $key => $row) {
                    $orders[$key] = isset($row['order']) ? $row['order'] : 0;
                }
                array_multisort($orders, SORT_ASC, $schema);
                $this->setData('csv_schema', $schema);
            } else {
                $this->setCsvSchema([]);
            }
        } else {
            foreach ($data as $key => $value) {
                $this->setData('xml_' . $key, $value);
            }
        }

        return $this;
    }

    /**
     * Return liquid template for csv/xml
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function getLiquidTemplate()
    {
        $this->serializeFormat()
            ->extract();

        $liquid = '';

        if ($this->isCsv()) {
            $delimiter = $this->getData('csv_delimiter') == 'tab' ? "\t" : $this->getData('csv_delimiter');
            $enclosure = $this->getData('csv_enclosure');

            if ($this->getData('csv_extra_header')) {
                $liquid .= $this->getData('csv_extra_header') . PHP_EOL;
            }

            if ($this->getData('csv_include_header')) {

                $headers = array_map(function ($column) {
                    $delimiter = $this->getData('csv_delimiter') == 'tab' ? "\t" : $this->getData('csv_delimiter');

                    if ($column['header'] == "XALL") {
                        $all = [];
                        foreach ($this->getAttributes() as $attribute) {
                            if ($attribute->getStoreLabel()) {
                                $all[] = $attribute->getStoreLabel();
                            }
                        }

                        return implode($delimiter, $all);
                    }

                    return $column['header'];
                }, $this->getCsvSchema());

                $liquid .= implode($delimiter, $headers) . PHP_EOL;
            }

            $liquid .= '{% for product in context.products %}' . PHP_EOL;

            $columns = [];
            foreach ($this->getCsvSchema() as $column) {
                $variable = '';

                if ($column['header'] == "XALL") {
                    foreach ($this->getAttributes() as $attribute) {
                        if ($attribute->getStoreLabel()) {
                            $columns[] = '{{ product.' . $attribute->getAttributeCode() . ' }}';
                        }
                    }

                    continue;
                }

                if ($column['type'] == 'pattern') {
                    $variable .= $column['pattern'];
                } elseif (isset($column['attribute']) && $column['attribute']) {
                    $variable .= '{{ product';

                    if ($column['type']) {
                        $variable .= '.parent';
                    }

                    $variable .= '.' . $column['attribute'];

                    $column['modifiers'][] = [
                        'modifier' => 'csv',
                        'args'     => [$delimiter, $enclosure]
                    ];

                    foreach ($column['modifiers'] as $modifier) {
                        if (!$modifier['modifier']) {
                            continue;
                        }

                        $modifier['args'] = isset($modifier['args']) ? $modifier['args'] : [];

                        $variable .= ' | ' . $modifier['modifier'];

                        $args = array_map(function (&$arg) {
                            if (is_string($arg)) {
                                $arg = "'$arg'";
                            }

                            return $arg;
                        }, $modifier['args']);

                        if (count($args)) {
                            $variable .= ': ' . implode(', ', $args);
                        }
                    }

                    $variable .= ' }}';
                }

                $columns[] = $variable;
            }

            $liquid .= implode($delimiter, $columns) . PHP_EOL;

            $liquid .= '{% endfor %}';


        } else {
            $liquid = $this->getXmlSchema();
        }

        return $liquid;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute[]
     */
    protected function getAttributes()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
        $collection = $om->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class);
        $collection->addFieldToFilter("entity_type_id", 4);

        return $collection;
    }
}
