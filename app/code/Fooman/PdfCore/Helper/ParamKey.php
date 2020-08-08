<?php
namespace Fooman\PdfCore\Helper;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ParamKey extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENCRYPT_SOURCE = 'PDFCUSTOMISER';

    /**
     * @var string
     */
    private $encKey;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @param \Magento\Framework\App\Helper\Context            $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    private function getKey()
    {
        if (null === $this->encKey) {
            $this->encKey = urlencode($this->encryptor->encrypt(self::ENCRYPT_SOURCE));
        }
        return $this->encKey;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getEncodedParams(array $params)
    {
        return sprintf(
            'key="%s" params="%s"',
            $this->getKey(),
            urlencode(json_encode($params))
        );
    }

    /**
     * @return string
     */
    public function getDecodeRegex()
    {
        return sprintf(
            '#<tcpdf [^>]*key="%s" params=["\']([^>]*?)["\']\s*/>#is',
            $this->getKey()
        );
    }
}
