<?php
namespace Ves\Themesettings\Model\System\Config\Source\Css\Font;

class Subset implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'cyrillic',	'label' => ('Cyrillic')],
			['value' => 'cyrillic-ext','label' => ('Cyrillic Extended')],
			['value' => 'greek','label' => ('Greek')],
			['value' => 'greek-ext',	'label' => ('Greek Extended')],
			['value' => 'khmer','label' => ('Khmer')],
			['value' => 'latin','label' => ('Latin')],
			['value' => 'latin-ext',	'label' => ('Latin Extended')],
			['value' => 'vietnamese',	'label' => ('Vietnamese')],
		];
	}
}