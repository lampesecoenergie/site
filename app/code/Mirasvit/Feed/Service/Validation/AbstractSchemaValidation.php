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


use Mirasvit\Feed\Api\Data\ValidationInterface;
use Mirasvit\Feed\Api\Repository\ValidationRepositoryInterface;
use Mirasvit\Feed\Model\Feed;
use Mirasvit\Feed\Validator\ValidatorInterface;

abstract class AbstractSchemaValidation
{
    /**
     * @var ValidationRepositoryInterface
     */
    private $validationRepository;

    /**
     * @var int
     */
    protected $lineNum = 0;

    /**
     * @var array
     */
    protected $invalidEntities = [];

    /**
     * @var array
     */
    protected $productIndex = [];

    /**
     * @var null|Feed
     */
    protected $feed = null;

    /**
     * Path to schema file.
     *
     * @var string|null
     */
    protected $schemaPath = null;

    public function __construct(ValidationRepositoryInterface $validationRepository)
    {
        $this->validationRepository = $validationRepository;
    }

    /**
     * Determine whether a feed can be validated or not.
     *
     * @return bool
     */
    abstract protected function canValidate();

    /**
     * Validate given value over passed validators.
     *
     * @param string               $attribute
     * @param string               $value
     * @param ValidatorInterface[] $validators
     *
     * @return array
     */
    final protected function validateValue($attribute, $value, array $validators = [])
    {
        $result = [];

        if ($this->getFeed()) {
            foreach ($validators as $validator) {
                if (!$validator->isValid($value)) {
                    $result[] = [
                        ValidationInterface::LINE_NUM  => $this->lineNum,
                        ValidationInterface::FEED_ID   => $this->getFeed()->getId(),
                        ValidationInterface::ATTRIBUTE => $attribute,
                        ValidationInterface::VALIDATOR => $validator->getCode(),
                        ValidationInterface::VALUE     => $value
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Instantiate validators by their codes.
     *
     * @param array $validators
     *
     * @return ValidatorInterface[]
     */
    final protected function getValidators(array $validators)
    {
        $validatorInstances = [];
        foreach ($validators as $code) {
            if ($this->validationRepository->getValidatorByCode($code)) {
                $validatorInstances[] = $this->validationRepository->getValidatorByCode($code);
            }
        }

        return $validatorInstances;
    }

    /**
     * @return int
     */
    public function getInvalidEntityQty()
    {
        return count($this->invalidEntities);
    }

    /**
     * @return null|Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * @param Feed $feed
     */
    public function setFeed(Feed $feed)
    {
        $this->feed = $feed;
    }
}
