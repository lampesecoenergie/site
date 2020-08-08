<?php
namespace Fooman\PdfDesign\Model;

use Magento\Framework\Exception\NotFoundException;

/**
 * pick template file based on chosen pdf design
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TemplateFileDecider
{
    private $defaultDesign;

    public function __construct(
        DefaultDesign $defaultDesign
    ) {
        $this->defaultDesign = $defaultDesign;
    }

    public function pick(Api\DesignInterface $design, $templateFor)
    {
        $designTemplates = $design->getTemplateFiles() + $this->defaultDesign->getTemplateFiles();
        if (!isset($designTemplates[$templateFor])) {
            throw new NotFoundException(__('No template set for %1', $templateFor));
        }
        return $designTemplates[$templateFor];
    }
}
