<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 4/1/18
 * Time: 5:10 PM
 */

namespace Ced\RueDuCommerce\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Colors extends AbstractSource
{

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => 'Black',
                'label' => __('Black')
            ],
            [
                'value' => 'Beige',
                'label' => __('Beige')
            ],
            [
                'value' => 'Blue',
                'label' => __('Blue')
            ],
            [
                'value' => 'Brown',
                'label' => __('Brown')
            ],
            [
                'value' => 'Gold',
                'label' => __('Gold')
            ],
            [
                'value' => 'Green',
                'label' => __('Green')
            ],
            [
                'value' => 'Grey',
                'label' => __('Grey')
            ],
            [
                'value' => 'Multicolor',
                'label' => __('Multicolor')
            ],
            [
                'value' => 'Olive',
                'label' => __('Olive')
            ],
            [
                'value' => 'Orange',
                'label' => __('Orange')
            ],
            [
                'value' => 'Pink',
                'label' => __('Pink')
            ],
            [
                'value' => 'Purple',
                'label' => __('Purple')
            ],
            [
                'value' => 'Red',
                'label' => __('Red')
            ],
            [
                'value' => 'Silver',
                'label' => __('Silver')
            ],
            [
                'value' => 'Turquoise',
                'label' => __('Turquoise')
            ],
            [
                'value' => 'Violet',
                'label' => __('Violet')
            ],
            [
                'value' => 'White',
                'label' => __('White')
            ],
            [
                'value' => 'Clear',
                'label' => __('Clear')
            ],
            [
                'value' => 'Apricot',
                'label' => __('Apricot')
            ],
            [
                'value' => 'Aqua',
                'label' => __('Aqua')
            ],
            [
                'value' => 'Avocado',
                'label' => __('Avocado')
            ],
            [
                'value' => 'Blueberry',
                'label' => __('Blueberry')
            ],
            [
                'value' => 'Blush Pink',
                'label' => __('Blush Pink')
            ],
            [
                'value' => 'Bronze',
                'label' => __('Bronze')
            ],
            [
                'value' => 'Charcoal',
                'label' => __('Charcoal')
            ],
            [
                'value' => 'Cherry',
                'label' => __('Cherry')
            ],
            [
                'value' => 'Chestnut',
                'label' => __('Chestnut')
            ],
            [
                'value' => 'Chili Red',
                'label' => __('Chili Red')
            ],
            [
                'value' => 'Chocolate',
                'label' => __('Chocolate')
            ],
            [
                'value' => 'Cinnamon',
                'label' => __('Cinnamon')
            ],
            [
                'value' => 'Coffee',
                'label' => __('Coffee')
            ],
            [
                'value' => 'Cream',
                'label' => __('Cream')
            ],
            [
                'value' => 'Floral',
                'label' => __('Floral')
            ],
            [
                'value' => 'Galaxy',
                'label' => __('Galaxy')
            ],
            [
                'value' => 'Hotpink',
                'label' => __('Hotpink')
            ],
            [
                'value' => 'Ivory',
                'label' => __('Ivory')
            ],
            [
                'value' => 'Jade',
                'label' => __('Jade')
            ],
            [
                'value' => 'Khaki',
                'label' => __('Khaki')
            ],
            [
                'value' => 'Lavender',
                'label' => __('Lavender')
            ],
            [
                'value' => 'Magenta',
                'label' => __('Magenta')
            ],
            [
                'value' => 'Mahogany',
                'label' => __('Mahogany')
            ],
            [
                'value' => 'Mango',
                'label' => __('Mango')
            ],
            [
                'value' => 'Maroon',
                'label' => __('Maroon')
            ],
            [
                'value' => 'Khaki',
                'label' => __('Khaki')
            ],
            [
                'value' => 'Neon',
                'label' => __('Neon')
            ],
            [
                'value' => 'Tan',
                'label' => __('Tan')
            ],
            [
                'value' => 'Watermelon red',
                'label' => __('Watermelon red')
            ],
            [
                'value' => 'Lake Blue',
                'label' => __('Lake Blue')
            ],
            [
                'value' => 'Lemon Yellow',
                'label' => __('Lemon Yellow')
            ],
            [
                'value' => 'Army Green',
                'label' => __('Army Green')
            ],
            [
                'value' => 'Dark blue',
                'label' => __('Dark blue')
            ],
            [
                'value' => 'Rose',
                'label' => __('Rose')
            ],
            [
                'value' => 'Camel',
                'label' => __('Camel')
            ],
            [
                'value' => 'Burgundy',
                'label' => __('Burgundy')
            ],
            [
                'value' => 'Light blue',
                'label' => __('Light blue')
            ],
            [
                'value' => 'Champagne',
                'label' => __('Champagne')
            ],
            [
                'value' => 'Light green',
                'label' => __('Light green')
            ],
            [
                'value' => 'Dark Brown',
                'label' => __('Dark Brown')
            ],
            [
                'value' => 'Navy Blue',
                'label' => __('Navy Blue')
            ],
            [
                'value' => 'Light Grey',
                'label' => __('Light Grey')
            ],
            [
                'value' => 'Off White',
                'label' => __('Off White')
            ],
            [
                'value' => 'Light yellow',
                'label' => __('Light yellow')
            ],
            [
                'value' => 'Emerald Green',
                'label' => __('Emerald Green')
            ],
            [
                'value' => 'Fluorescent Green',
                'label' => __('Fluorescent Green')
            ],
            [
                'value' => 'Fluorescent Yellow',
                'label' => __('Fluorescent Yellow')
            ],
            [
                'value' => 'Deep green',
                'label' => __('Deep green')
            ],
            [
                'value' => 'Rose Gold',
                'label' => __('Rose Gold')
            ],
            [
                'value' => 'Neutral',
                'label' => __('Neutral')
            ],
            [
                'value' => 'Peach',
                'label' => __('Peach')
            ],
            [
                'value' => 'Fuchsia',
                'label' => __('Fuchsia')
            ],
            [
                'value' => 'Blue Gray',
                'label' => __('Blue Gray')
            ],
            [
                'value' => 'Not Specified',
                'label' => __('Not Specified')
            ],
            [
                'value' => 'Orchid Grey',
                'label' => __('Orchid Grey')
            ]
        ];
    }
}
