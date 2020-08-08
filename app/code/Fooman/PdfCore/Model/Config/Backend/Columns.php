<?php
namespace Fooman\PdfCore\Model\Config\Backend;

use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Columns extends \Magento\Framework\App\Config\Value implements ProcessorInterface
{
    public function beforeSave()
    {
        $values = $this->getValue();
        if ($values) {
            if (!is_array($values)) {
                $values = json_decode($values, true);
            }
            $check = [];
            foreach ($values as $value) {
                if (isset($value['columntype'])) {
                    if (isset($check[$value['columntype']])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Each column type can only appear once.')
                        );
                    } else {
                        $check[$value['columntype']] = true;
                    }
                }
            }
        }
        if (is_array($values)) {
            unset($values['__empty']);
            $this->setValue(json_encode($values));
        }

        parent::beforeSave();
    }

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Magento 2 Core use
    protected function _afterLoad()
    {
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = json_decode($values, true);
            foreach ($values as $key => $value) {
                if (!isset($values[$key]['title'])) {
                    $values[$key]['title'] = null;
                }
                if (!isset($values[$key]['align'])) {
                    $values[$key]['align'] = null;
                }
            }
            $this->setValue(empty($values) ? false : $values);
        }
    }

    public function getOldValue()
    {
        return $this->_config->getValue(
            $this->getPath(),
            $this->getScope() ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->getScopeCode()
        );
    }

    public function processValue($value)
    {
        if (!empty($value) && !is_array($value) && is_string($value)) {
            $values = json_decode($value, true);
            if ($values) {
                foreach ($values as $key => $val) {
                    if (!isset($values[$key]['title'])) {
                        $values[$key]['title'] = null;
                    }
                    if (!isset($values[$key]['align'])) {
                        $values[$key]['align'] = null;
                    }
                }
                return $values;
            }
        }
        return $value;
    }
}
