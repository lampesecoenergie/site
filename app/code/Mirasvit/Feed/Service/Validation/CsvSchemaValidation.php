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



namespace Mirasvit\Feed\Service\Validation;


use Mirasvit\Feed\Api\Repository\ValidationRepositoryInterface;
use Mirasvit\Feed\Api\Service\SchemaValidationInterface;
use Mirasvit\Feed\Model\Feed;
use Magento\Framework\File\Csv;

class CsvSchemaValidation extends AbstractSchemaValidation implements SchemaValidationInterface
{
    /**
     * CSV key used to store validator names.
     */
    const CSV_ATTRIBUTE = 'validators';

    /**
     * Include CSV header or not.
     *
     * @var bool
     */
    protected $includeHeader = false;

    /**
     * @var Csv
     */
    private $csvParser;

    public function __construct(
        Csv $csvParser,
        ValidationRepositoryInterface $validationRepository
    ) {
        parent::__construct($validationRepository);

        $this->csvParser = $csvParser;
    }

    /**
     * {@inheritDoc}
     */
    public function init(Feed $feed)
    {
        $this->setFeed($feed);
        $this->schemaPath = $feed->getFilePath();

        $this->includeHeader = $feed->getData('csv_include_header');

        // configure CSV parser
        $delimiter = $feed->getData('csv_delimiter') == 'tab' ? "\t" : $feed->getData('csv_delimiter');
        $this->csvParser->setDelimiter($delimiter);
        if ($feed->getData('csv_enclosure')) {
            $this->csvParser->setEnclosure($feed->getData('csv_enclosure'));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validateSchema()
    {
        if ($this->canValidate()) {
            return $this->validateContent($this->csvParser->getData($this->schemaPath));
        } else {
            return [];
        }
    }


    /**
     * {@inheritDoc}
     */
    public function validateContent(array $content)
    {
        $result = [];
        $schema = $this->getFeed()->getCsvSchema();

        foreach ($content as $rowIdx => $row) {
            if ($this->includeHeader && $rowIdx == 0) { // skip header row
                continue;
            }

            foreach ($row as $idx => $attributeValue) {
                if (isset($schema[$idx][self::CSV_ATTRIBUTE])) {
                    $this->lineNum = $rowIdx + 1; // set line number to current index of row

                    $validators = array_map(function($validator) {
                        return array_shift($validator);
                    }, $schema[$idx][self::CSV_ATTRIBUTE]);

                    $validationResult = $this->validateValue(
                        $schema[$idx]['header'],
                        $attributeValue,
                        $this->getValidators($validators)

                    );

                    $result = array_merge($result, $validationResult);

                    if (!empty($validationResult)) {
                        $this->invalidEntities[$rowIdx] = true; // increment QTY of invalid entities
                    }
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function canValidate()
    {
        foreach ($this->getFeed()->getCsvSchema() as $field) {
            if (isset($field[self::CSV_ATTRIBUTE])) {
                return true;
            }
        }

        return false;
    }
}
