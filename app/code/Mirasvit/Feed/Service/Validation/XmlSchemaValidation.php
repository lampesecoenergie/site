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
use Magento\Framework\Xml\Parser as XmlParser;

class XmlSchemaValidation extends AbstractSchemaValidation implements SchemaValidationInterface
{
    /**
     * XML key used to store value.
     */
    const XML_VALUE = '_value';

    /**
     * XML key used to store attribute.
     */
    const XML_ATTRIBUTE = '_attribute';

    /**
     * XML key used to store validator names.
     */
    const VALIDATION = 'validation';

    /**
     * @var int
     */
    private $addNum = 0;

    /**
     * @var XmlParser
     */
    private $xmlParser;

    public function __construct(
        XmlParser $xmlParser,
        ValidationRepositoryInterface $validationRepository
    ) {
        parent::__construct($validationRepository);

        $this->xmlParser = $xmlParser;
    }

    /**
     * {@inheritDoc}
     */
    public function init(Feed $feed)
    {
        $this->setFeed($feed);
        $this->schemaPath = $feed->getFilePath();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validateSchema()
    {
        if ($this->canValidate()) {
            $result = $this->validateContent($this->xmlParser->load($this->schemaPath)->xmlToArray());
            $this->lineNum += $this->addNum;
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function validateContent(array $element = [], $key = null)
    {
        $result = [];

        if ($key && !in_array($key, [self::XML_VALUE, self::XML_ATTRIBUTE, self::VALIDATION])) {
            $this->lineNum++;
            if (!isset($element[self::XML_ATTRIBUTE][self::VALIDATION])
                && !is_numeric($key) && !is_numeric(key($element))
            ) {
                $this->addNum++; // additionally count number of wrapping closing tags
            }
        }

        if (isset($element[self::XML_ATTRIBUTE][self::VALIDATION])
            && isset($element[self::XML_VALUE]) && is_scalar($element[self::XML_VALUE])
        ) {
            $validators = $this->getValidators(explode(',', $element[self::XML_ATTRIBUTE][self::VALIDATION]));
            $result = $this->validateValue($key, $element[self::XML_VALUE], $validators);
        }

        foreach ($element as $id => $childElement) {
            if (is_array($childElement)) {
                if (is_numeric($id)) {
                    $this->productIndex[] = $id; // save current entity index
                    $this->lineNum++; // increase line number for closing (item) tag, its key always int
                }

                // validate
                $validationResult = $this->validateContent($childElement, $id);
                $result = array_merge_recursive($result, $validationResult);

                // save invalid entity index, only invalid are not empty
                array_map(function($validation) {
                    $this->invalidEntities[end($this->productIndex)] = true;
                }, $validationResult);
            } elseif (!in_array($id, [self::XML_VALUE, self::XML_ATTRIBUTE, self::VALIDATION])) {
                if ($key === self::XML_ATTRIBUTE && $id != self::VALIDATION) // skip simple attributes
                    continue;

                $this->lineNum++; // increase line number for simple tags (not-validatable tags)
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function canValidate()
    {
        if (strpos($this->getFeed()->getXmlSchema(), self::VALIDATION) !== false) {
            return true;
        }

        return false;
    }
}
