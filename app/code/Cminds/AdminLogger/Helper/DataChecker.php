<?php

namespace Cminds\AdminLogger\Helper;

/**
 * Class DataChecker
 *
 * @package Cminds\AdminLogger\Helper
 */
class DataChecker
{
    /**
     * Check is there are any changes during object saving.
     *
     * @param $eventObject
     *
     * @return array
     */
    public function getDataChanges($eventObject, $oldData, $newData)
    {
        $diff = [];

        $keys = $this->getArrayKeys($oldData);
        // Put all keys with new and old values into the array and check for changes
        foreach ($keys as $key) {
            if ($key !== null) {
                if ($eventObject->dataHasChangedFor($key)) {
                    $newValue = $newData[$key];
                    $oldValue = $oldData[$key];

                    if ($newValue != $oldValue
                        && is_array($newValue) === false
                        && is_object($newValue) === false
                        && is_array($oldValue) === false
                        && is_object($oldValue) === false
                    ) {
                        if (($oldValue == null && $newValue == '0') === false) {
                            $diff['new_value'][$key] = $newValue;
                            $diff['old_value'][$key] = $oldValue;
                        }
                    }
                }
            }
        }

        return $diff;
    }

    /**
     * Check Config data changes.
     *
     * @param $configDataArray
     *
     * @return array
     */
    public function getConfigDataChanges($configDataArray)
    {
        $newData = $configDataArray['new_data'];
        $oldData = $configDataArray['old_data'];

        $diff = [];

        // check new array
        foreach ($newData as $key => $value) {
            if (!isset($oldData[$key])) {
                $oldData[$key] = '';
            }

            if ($value != $oldData[$key]) {
                if ($oldData[$key] == '' || $oldData[$key] == null) {
                    $oldData[$key] = '';
                }
                $diff['old_value'][$key] = $oldData[$key];
                $diff['new_value'][$key] = $value;
            }
        }

        // check old array
        foreach ($oldData as $key => $value) {
            if (!isset($newData[$key])) {
                $newData[$key] = '';
            }

            if ($value != $newData[$key]) {
                if ($newData[$key] == '' || $newData[$key] == null) {
                    $newData[$key] = '';
                }
                if (!isset($diff['old_value'][$key]) && !isset($diff['new_value'][$key])) {
                    $diff['old_value'][$key] = $value;
                    $diff['new_value'][$key] = $newData[$key];
                }
            }
        }

        return $diff;
    }

    /**
     * Get product changes.
     *
     * @param $oldData
     * @param $newData
     *
     * @return array
     */
    public function getProductChanges($oldData, $newData)
    {
        $diff = [];

        $keys = $this->getArrayKeys($newData);

        foreach ($keys as $key) {
            if ($key !== null) {
                $newValue = $key ? $newData[$key] : '';
                $oldValue = $key ? $oldData[$key] : '';

                if ($newValue != $oldValue
                    && is_array($newValue) === false
                    && is_object($newValue) === false
                    && is_array($oldValue) === false
                    && is_object($oldValue) === false
                ) {
                    if (($oldValue == null && $newValue == '0') === false) {
                        $diff['new_value'][$key] = $newValue;
                        $diff['old_value'][$key] = $oldValue;
                    }
                }
            }
        }

        return $diff;
    }

    /**
     * Get the all existing keys in the simple numeric array.
     *
     * @param array $array
     *
     * @return array
     */
    private function getArrayKeys(array $array)
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $keys = array_merge($keys, $this->getArrayKeys($value));
            }
        }

        return $keys;
    }

    /**
     * Get product attributes data changes.
     *
     * @param $eventObject
     * @param $oldData
     * @param $newData
     *
     * @return array
     */
    public function getProductAttributesDataChanges($eventObject, $oldData, $newData)
    {
        $diff = [];

        $keys = $this->getArrayKeys($newData);
        // Put all keys with new and old values into the array and check for changes
        foreach ($keys as $key) {
            if ($key !== null) {
                if ($eventObject->dataHasChangedFor($key)
                    && $key != 'image_label'
                    && $key != 'small_image_label'
                    && $key != 'thumbnail_label'
                    && $key != 'quantity_and_stock_status'
                ) {
                    $newValue = $newData[$key];
                    if (isset($oldData[$key])) {
                        $oldValue = $oldData[$key];
                    } else {
                        $oldValue = '';
                    }

                    if ($newValue != $oldValue
                        && is_array($newValue) === false
                        && is_object($newValue) === false
                        && is_array($oldValue) === false
                        && is_object($oldValue) === false
                    ) {
                        if (($oldValue == null && $newValue == '0') === false) {
                            $diff['new_value'][$key] = $newValue;
                            $diff['old_value'][$key] = $oldValue;
                        }
                    }
                }
            }
        }

        return $diff;
    }
}
