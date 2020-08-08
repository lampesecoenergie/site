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


namespace Mirasvit\Feed\Export\Resolver;

class Pool
{
    /**
     * List of registered resolvers
     *
     * @var []
     */
    protected $resolvers;

    /**
     * Constructor
     *
     * @param [] $resolvers
     */
    public function __construct(
        $resolvers = []
    ) {
        $this->resolvers = $resolvers;
    }

    /**
     * Return resolver for object, based on object class and type (for products)
     *
     * @param object $object
     *
     * @return AbstractResolver|false
     */
    public function findResolver($object)
    {
        foreach ($this->resolvers as $resolver) {
            if ($object instanceof $resolver['for']) {
                if (!isset($resolver['type_id'])
                    || $object->getData('type_id') == $resolver['type_id']) {
                    return $resolver['resolver'];
                }
            }
        }

        return false;
    }

    /**
     * List of registered resolvers
     *
     * @return AbstractResolver[]
     */
    public function getResolvers()
    {
        $list = [];

        foreach ($this->resolvers as $resolver) {
            $list[] = $resolver['resolver'];
        }

        return $list;
    }
}
