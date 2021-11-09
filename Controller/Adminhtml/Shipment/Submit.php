<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Netresearch\InteractiveBatchProcessing\Model\InputValuesProvider;

/**
 * @method Http getRequest()
 */
class Submit extends Action implements HttpPostActionInterface
{
    /**
     * @var InputValuesProvider
     */
    private $inputValuesProvider;

    public function __construct(Context $context, InputValuesProvider $inputValuesProvider)
    {
        $this->inputValuesProvider = $inputValuesProvider;

        parent::__construct($context);
    }

    public function execute()
    {
        $inputValues = $this->getRequest()->getParam('inputs', []);
        foreach ($inputValues as $orderId => $orderInputValues) {
            $this->inputValuesProvider->setInputValues((int) $orderId, $orderInputValues);
        }

        $this->_forward(
            'autocreate',
            'shipment',
            'nrshipping',
            [
                'selected' => array_keys($inputValues),
                'namespace' => 'sales_order_grid',
            ]
        );
    }
}
