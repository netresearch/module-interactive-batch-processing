<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Block\Adminhtml\Container;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * The form container that holds the form widget.
 */
class Confirm extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_mode = 'widget';
        $this->_blockGroup = 'Netresearch_InteractiveBatchProcessing';

        parent::_construct();

        $this->removeButton('reset');
    }

    public function getBackUrl()
    {
        return $this->getUrl('sales/order/index');
    }
}
