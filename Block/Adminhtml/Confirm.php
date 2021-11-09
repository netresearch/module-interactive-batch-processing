<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Block\Adminhtml;

use Magento\Backend\Block\Widget\Form\Container;

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
        $this->_mode = 'confirm';
        $this->_blockGroup = 'Netresearch_InteractiveBatchProcessing';

        parent::_construct();
    }

    public function getBackUrl()
    {
        return $this->getUrl('sales/order/index');
    }
}
