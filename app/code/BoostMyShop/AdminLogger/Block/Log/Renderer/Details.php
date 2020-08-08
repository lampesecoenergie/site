<?php

namespace BoostMyShop\AdminLogger\Block\Log\Renderer;


class Details extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $json = json_decode($row->getal_details());
        if ($json)
            return $this->jsonDecode($row->getal_details());
        else
            return $row->getal_details();
    }

    protected function jsonDecode($json)
    {
        $html = '<table>';

        $html .= '<tr>';
        $html .= '<th>'.__('Field').'</th>';
        $html .= '<th>'.__('Old value').'</th>';
        $html .= '<th>'.__('New value').'</th>';
        $html .= '</tr>';

        $data = json_decode($json);
        foreach($data as $k => $v)
        {
            $html .= '<tr>';
            $html .= '<td>'.$k.'</td>';
            $html .= '<td>'.$v->from.'</td>';
            $html .= '<td>'.$v->to.'</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }
}