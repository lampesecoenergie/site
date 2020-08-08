<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ExpeditorInet\Helper;

use LaPoste\ExpeditorInet\Model\Config\Source\EndOfLineCharacter;
use LaPoste\ExpeditorInet\Model\Config\Source\FieldDelimiter;
use LaPoste\ExpeditorInet\Model\Config\Source\FieldEnclosure;
use LaPoste\ExpeditorInet\Model\Config\Source\FileExtension;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Config helper.
 *
 * @author Smile (http://www.smile.fr)
 */
class Config extends AbstractHelper
{
    /**
     * Config paths.
     */
    const PATH_EXPORT_FILE_EXTENSION           = 'expeditorinet/export/file_extension';
    const PATH_EXPORT_FILE_CHARSET             = 'expeditorinet/export/file_charset';
    const PATH_EXPORT_EOL_CHARACTER            = 'expeditorinet/export/endofline_character';
    const PATH_EXPORT_FIELD_DELIMITER          = 'expeditorinet/export/field_delimiter';
    const PATH_EXPORT_FIELD_ENCLOSURE          = 'expeditorinet/export/field_enclosure';
    const PATH_EXPORT_SIGNATURE_REQUIRED       = 'expeditorinet/export/signature_required';
    const PATH_EXPORT_COMPANY_NAME             = 'expeditorinet/export/company_name';
    const PATH_IMPORT_FIELD_DELIMITER          = 'expeditorinet/import/field_delimiter';
    const PATH_IMPORT_FIELD_ENCLOSURE          = 'expeditorinet/import/field_enclosure';
    const PATH_IMPORT_SEND_EMAIL               = 'expeditorinet/import/send_email';
    const PATH_IMPORT_INCLUDE_COMMENT_IN_EMAIL = 'expeditorinet/import/include_comment_in_email';
    const PATH_IMPORT_DEFAULT_TRACKING_TITLE   = 'expeditorinet/import/default_tracking_title';
    const PATH_IMPORT_SHIPMENT_COMMENT         = 'expeditorinet/import/shipment_comment';
    const PATH_IMPORT_CARRIER_CODE             = 'expeditorinet/import/carrier_code';
    const PATH_PICKUP_POINT_CODES              = 'expeditorinet/pickup_point_codes';
    /**#@-*/

    /**
     * Get export file extension.
     *
     * @return string
     */
    public function getExportFileExtension()
    {
        $code = $this->scopeConfig->getValue(self::PATH_EXPORT_FILE_EXTENSION);

        return $this->getFileExtensionByCode($code);
    }

    /**
     * Get export file charset.
     *
     * @return string
     */
    public function getExportFileCharset()
    {
        return $this->scopeConfig->getValue(self::PATH_EXPORT_FILE_CHARSET);
    }

    /**
     * Get export field delimiter.
     *
     * @return string
     */
    public function getExportFieldDelimiter()
    {
        $code = $this->scopeConfig->getValue(self::PATH_EXPORT_FIELD_DELIMITER);

        return $this->getFieldDelimiterByCode($code);
    }

    /**
     * Get export field enclosure.
     *
     * @return string
     */
    public function getExportFieldEnclosure()
    {
        $code = $this->scopeConfig->getValue(self::PATH_EXPORT_FIELD_ENCLOSURE);

        return $this->getFieldEnclosureByCode($code);
    }

    /**
     * Get export EOL character.
     *
     * @return string
     */
    public function getExportEolCharacter()
    {
        $code = $this->scopeConfig->getValue(self::PATH_EXPORT_EOL_CHARACTER);

        return $this->getEndOfLineCharacterByCode($code);
    }

    /**
     * Get whether a signature is required for home delivery.
     *
     * @return string
     */
    public function getExportSignatureRequired()
    {
        return $this->scopeConfig->getValue(self::PATH_EXPORT_SIGNATURE_REQUIRED);
    }

    /**
     * Get export company name.
     *
     * @return string
     */
    public function getExportCompanyName()
    {
        return $this->scopeConfig->getValue(self::PATH_EXPORT_COMPANY_NAME);
    }

    /**
     * Get import field delimiter.
     *
     * @return string
     */
    public function getImportFieldDelimiter()
    {
        $code = $this->scopeConfig->getValue(self::PATH_IMPORT_FIELD_DELIMITER);

        return $this->getFieldDelimiterByCode($code);
    }

    /**
     * Get import field enclosure.
     *
     * @return string
     */
    public function getImportFieldEnclosure()
    {
        $code = $this->scopeConfig->getValue(self::PATH_IMPORT_FIELD_ENCLOSURE);

        return $this->getFieldEnclosureByCode($code);
    }

    /**
     * Get import email.
     *
     * @return string
     */
    public function getImportSendEmail()
    {
        return $this->scopeConfig->getValue(self::PATH_IMPORT_SEND_EMAIL);
    }

    /**
     * Get import default tracking title.
     *
     * @return string
     */
    public function getImportIncludeCommentInEmail()
    {
        return $this->scopeConfig->getValue(self::PATH_IMPORT_INCLUDE_COMMENT_IN_EMAIL);
    }

    /**
     * Get import default tracking title.
     *
     * @return string
     */
    public function getImportDefaultTrackingTitle()
    {
        return $this->scopeConfig->getValue(self::PATH_IMPORT_DEFAULT_TRACKING_TITLE);
    }

    /**
     * Get import shipment comment.
     *
     * @return string
     */
    public function getImportShipmentComment()
    {
        return $this->scopeConfig->getValue(self::PATH_IMPORT_SHIPMENT_COMMENT);
    }

    /**
     * Get import carrier code.
     *
     * @return string
     */
    public function getImportCarrierCode()
    {
        return $this->scopeConfig->getValue(self::PATH_IMPORT_CARRIER_CODE);
    }

    /**
     * Get pickup point codes.
     *
     * @return array
     */
    public function getPickupPointCodes()
    {
        $codes = $this->scopeConfig->getValue(self::PATH_PICKUP_POINT_CODES);

        return is_array($codes) ? array_keys($codes) : [];
    }

    /**
     * Get file extension by option value.
     *
     * @param string $code
     * @return string
     */
    protected function getFileExtensionByCode($code)
    {
        if ($code === FileExtension::CSV) {
            return '.csv';
        } elseif ($code === FileExtension::TXT) {
            return '.txt';
        } else {
            return '';
        }
    }

    /**
     * Get field delimiter character by option value.
     *
     * @param string $code
     * @return string
     */
    protected function getFieldDelimiterByCode($code)
    {
        if ($code === FieldDelimiter::COMMA) {
            return ',';
        } elseif ($code === FieldDelimiter::SEMICOLUMN) {
            return ';';
        } else {
            return '';
        }
    }

    /**
     * Get field enclosure character by option value.
     *
     * @param string $code
     * @return string
     */
    protected function getFieldEnclosureByCode($code)
    {
        if ($code === FieldEnclosure::SIMPLE_QUOTES) {
            return "'";
        } elseif ($code === FieldEnclosure::DOUBLE_QUOTES) {
            return '"';
        } else {
            return '';
        }
    }

    /**
     * Get end of line character by option value.
     *
     * @param string $code
     * @return string
     */
    public function getEndOfLineCharacterByCode($code)
    {
        if ($code === EndOfLineCharacter::LF) {
            return "\n";
        } elseif ($code === EndOfLineCharacter::CR) {
            return "\r";
        } elseif ($code === EndOfLineCharacter::CRLF) {
            return "\r\n";
        } else {
            return '';
        }
    }
}
