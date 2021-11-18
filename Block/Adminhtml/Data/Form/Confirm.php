<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Block\Adminhtml\Data\Form;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Profiler;

class Confirm extends Form
{
    private function getTableHtml(): string
    {
        $tHead = '';

        $first = $this->getElements()->offsetGet(0);
        if ($first instanceof Fieldset) {
            $tHead .= '<thead><tr>';
            foreach ($first->getChildren() as $child) {
                $tHead .= sprintf(
                    '<th class="%s" scope="col"><span>%s</span></th>',
                    $child->getData('class'),
                    $child->getData('label')
                );
            }
            $tHead .= '</tr></thead>';
        }

        $tBody = '<tbody>';
        foreach ($this->getElements() as $element) {
            $tBody .= $element->toHtml();
        }
        $tBody.= '</tbody>';

        return '<table class="data-table admin__table-primary">' . $tHead . $tBody . '</table>';
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function toHtml()
    {
        Profiler::start('form/toHtml');
        $html = '';
        $useContainer = $this->getUseContainer();
        if ($useContainer) {
            $html .= '<form ' . $this->serialize($this->getHtmlAttributes()) . '>';
            $html .= '<div>';
            if (strtolower($this->getData('method')) == 'post') {
                $html .= '<input name="form_key" type="hidden" value="' . $this->formKey->getFormKey() . '" />';
            }
            $html .= '</div>';
        }

        $html .= $this->getTableHtml();

        if ($useContainer) {
            $html .= '</form>';
        }
        Profiler::stop('form/toHtml');
        return $html;
    }
}
