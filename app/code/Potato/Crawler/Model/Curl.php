<?php

namespace Potato\Crawler\Model;

class Curl extends \Magento\Framework\HTTP\Adapter\Curl
{
    /**
     * Get default options
     *
     * @return array
     */
    private function getDefaultConfig()
    {
        $config = [];
        foreach (array_keys($this->_config) as $param) {
            if (array_key_exists($param, $this->_allowedParams)) {
                $config[$this->_allowedParams[$param]] = $this->_config[$param];
            }
        }
        return $config;
    }

    /**
     * curl_multi_* requests support
     *
     * @param array $urls
     * @param array $options
     * @return array
     */
    public function multiRequest($urls, $options = [])
    {
        $handles = [];
        $result = [];

        $multihandle = curl_multi_init();

        // add default parameters
        foreach ($this->getDefaultConfig() as $defaultOption => $defaultValue) {
            if (!isset($options[$defaultOption])) {
                $options[$defaultOption] = $defaultValue;
            }
        }

        foreach ($urls as $key => $url) {
            $result[$key]['url'] = $url;
            $handles[$key] = curl_init();
            curl_setopt($handles[$key], CURLOPT_URL, $url);
            curl_setopt($handles[$key], CURLOPT_HEADER, 1);
            curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, 1);
            if (!empty($options)) {
                curl_setopt_array($handles[$key], $options);
            }
            curl_multi_add_handle($multihandle, $handles[$key]);
        }
        $process = null;
        do {
            curl_multi_exec($multihandle, $process);
            usleep(100);
        } while ($process > 0);

        foreach ($handles as $key => $handle) {
            $result[$key]['content'] = curl_multi_getcontent($handle);
            curl_multi_remove_handle($multihandle, $handle);
        }
        curl_multi_close($multihandle);
        return $result;
    }
}