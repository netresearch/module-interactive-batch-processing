<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Block\Adminhtml\Widget\Form\Renderer;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class TableRow extends Template implements RendererInterface
{
    /**
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @var string
     */
    protected $_template = 'Netresearch_InteractiveBatchProcessing::widget/form/renderer/tablerow.phtml';

    /**
     * @return AbstractElement
     */
    public function getElement(): AbstractElement
    {
        return $this->_element;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    #[\Override]
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
}
