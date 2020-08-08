<?php
namespace Potato\Crawler\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class Currency
 */
class Currency implements OptionSourceInterface
{
    /** @var ScopeDefiner  */
    protected $scopeDefiner;

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var ResolverInterface  */
    protected $localeResolver;

    protected $request;

    /**
     * Currency constructor.
     * @param ScopeDefiner $scopeDefiner
     * @param ScopeConfigInterface $scopeConfig
     * @param ResolverInterface $localeResolver
     * @param RequestInterface $request
     */
    public function __construct(
        ScopeDefiner $scopeDefiner,
        ScopeConfigInterface $scopeConfig,
        ResolverInterface $localeResolver,
        RequestInterface $request
    ) {
        $this->scopeDefiner = $scopeDefiner;
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $currencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'];
        $allowedCodes = explode(',', $this->getCodes());

        foreach ($currencies as $code => $data) {
            if (!in_array($code, $allowedCodes)) {
                continue;
            }
            $name = $currencies[$code][1] ?: $code;
            $options[] = [
                'label' => $name,
                'value' => $code,
            ];
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getCodes()
    {
        $path = \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW;
        $scope = $this->scopeDefiner->getScope();
        return (string)$this->scopeConfig->getValue(
            $path,
            $scope,
            $this->request->getParam($scope, null)
        );
    }
}