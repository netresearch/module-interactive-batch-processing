<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Block\Adminhtml\Confirm;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Netresearch\InteractiveBatchProcessing\Model\OrderProvider;
use Netresearch\ShippingCore\Api\BulkShipment\Interactive\InputsProviderInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\OptionInterface;
use Netresearch\ShippingCore\Model\ShippingSettings\ShippingOption\Codes;

class Form extends Generic
{
    /**
     * @var OrderProvider
     */
    private $orderProvider;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * @var InputsProviderInterface
     */
    private $inputsProvider;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        OrderProvider $orderProvider,
        AddressRenderer $addressRenderer,
        InputsProviderInterface $inputsProvider,
        array $data = []
    ) {
        $this->orderProvider = $orderProvider;
        $this->addressRenderer = $addressRenderer;
        $this->inputsProvider = $inputsProvider;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareForm(): self
    {
        $orders = $this->orderProvider->getOrders();
        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'method' => 'post']]);

        foreach ($orders as $orderId => $order) {
            if ($order->getIsVirtual()) {
                continue;
            }

            $shippingAddress = $order->getShippingAddress();
            $form->addField(
                "order-$orderId-address",
                'note',
                [
                    'name' => "receiver[$orderId][address]",
                    'label' => __('Destination Address'),
                    'text' => $this->addressRenderer->format($shippingAddress, 'html'),
                ]
            );

            // todo(nr): add item renderer: collect shippable items, display summary (name, sku, weight?)
            $skus = array_map(
                function (OrderItemInterface $orderItem) {
                    return sprintf('%s (%s)', $orderItem->getName(), $orderItem->getSku());
                },
                $order->getItems()
            );
            $form->addField(
                "order-$orderId-items",
                'note',
                [
                    'name' => "order[$orderId][items]",
                    'label' => __('Items to Ship'),
                    'text' => implode(', ', $skus),
                ]
            );

            $optionCode = Codes::PACKAGE_OPTION_DETAILS;

            $input = $this->inputsProvider->getInput($order, $optionCode, Codes::PACKAGE_INPUT_PRODUCT_CODE);
            $productOptions = array_combine(
                array_map(function (OptionInterface $option) { return $option->getValue(); }, $input->getOptions()),
                array_map(function (OptionInterface $option) { return $option->getLabel(); }, $input->getOptions())
            );
            $form->addField(
                sprintf('order-%d-%s', $orderId, $input->getCode()),
                'select',
                [
                    'name' => "inputs[{$order->getEntityId()}][$optionCode.{$input->getCode()}]",
                    'label' => $input->getLabel(),
                    'options' => $productOptions,
                    'value' => $input->getDefaultValue(),
                ]
            );

            $input = $this->inputsProvider->getInput($order, $optionCode, Codes::PACKAGE_INPUT_PACKAGING_ID);
            if ($input) {
                $packagingOptions = array_combine(
                    array_map(function (OptionInterface $option) { return $option->getValue(); }, $input->getOptions()),
                    array_map(function (OptionInterface $option) { return $option->getLabel(); }, $input->getOptions())
                );
                $form->addField(
                    sprintf('order-%d-%s', $orderId, $input->getCode()),
                    'select',
                    [
                        'name' => "inputs[{$order->getEntityId()}][$optionCode.{$input->getCode()}]",
                        'label' => $input->getLabel(),
                        'options' => $packagingOptions,
                        'value' => $input->getDefaultValue(),
                    ]
                );
            }
        }

        $form->setAction($this->getUrl('nrshipping/shipment/submit'));
        $form->setUseContainer(true);
        $this->setForm($form);

        // todo(nr): render a table row for each order. use another form template, or override \Magento\Framework\Data\Form::toHtml
        return parent::_prepareForm();
    }
}
