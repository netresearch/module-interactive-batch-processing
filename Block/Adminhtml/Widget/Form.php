<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Netresearch\InteractiveBatchProcessing\Block\Adminhtml\Data\Form\Confirm;
use Netresearch\InteractiveBatchProcessing\Block\Adminhtml\Widget\Form\Renderer\TableRow;
use Netresearch\InteractiveBatchProcessing\Model\Registry\SelectedOrders;
use Netresearch\ShippingCore\Api\BulkShipment\Interactive\InputsProviderInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\OptionInterface;
use Netresearch\ShippingCore\Model\ShippingSettings\ShippingOption\Codes;

/**
 * The form widget that builds the edit form
 */
class Form extends Generic
{
    /**
     * @var SelectedOrders
     */
    private $selectedOrders;

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
        SelectedOrders $selectedOrders,
        AddressRenderer $addressRenderer,
        InputsProviderInterface $inputsProvider,
        array $data = []
    ) {
        $this->selectedOrders = $selectedOrders;
        $this->addressRenderer = $addressRenderer;
        $this->inputsProvider = $inputsProvider;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    private function addAddressCell(Fieldset $fieldset, OrderInterface $order): void
    {
        $shippingAddress = $order->getShippingAddress();
        $fieldset->addField(
            "order-{$order->getEntityId()}-address",
            'note',
            [
                'name' => "receiver[{$order->getEntityId()}][address]",
                'label' => __('Destination Address'),
                'text' => $this->addressRenderer->format($shippingAddress, 'html'),
                'class' => 'address',
            ]
        );
    }

    private function addItemsCell(Fieldset $fieldset, OrderInterface $order): void
    {
        $listItems = array_map(
            function (string $itemInfo) {
                return "<li>$itemInfo</li>";
            },
            array_reduce(
                $order->getItems(),
                function (array $itemsInfo, OrderItemInterface $orderItem) {
                    $hasParent = $orderItem->getParentItemId() || $orderItem->getParentItem();

                    if (!$hasParent && $orderItem->isShipSeparately()) {
                        // the separate items' container – will not be shipped
                        return $itemsInfo;
                    }

                    if ($hasParent && !$orderItem->isShipSeparately()) {
                        // the bundle's simple items – all shipped together with the parent
                        return $itemsInfo;
                    }

                    $itemsInfo[] = sprintf('%s (%s)', $orderItem->getName(), $orderItem->getSku());
                    return $itemsInfo;
                },
                []
            )
        );

        $fieldset->addField(
            "order-{$order->getEntityId()}-items",
            'note',
            [
                'name' => "order[{$order->getEntityId()}][items]",
                'label' => __('Order Items'),
                'text' => '<ul>' . implode('', $listItems) . '</ul>',
                'class' => 'items',
            ]
        );
    }

    private function addSelectCell(
        Fieldset $fieldset,
        OrderInterface $order,
        string $optionCode,
        string $inputCode
    ): void {
        $input = $this->inputsProvider->getInput($order, $optionCode, $inputCode);
        if ($input) {
            $packagingOptions = array_combine(
                array_map(function (OptionInterface $option) { return $option->getValue(); }, $input->getOptions()),
                array_map(function (OptionInterface $option) { return $option->getLabel(); }, $input->getOptions())
            );
            $fieldset->addField(
                "order-{$order->getEntityId()}-{$input->getCode()}",
                'select',
                [
                    'name' => "inputs[{$order->getEntityId()}][$optionCode.{$input->getCode()}]",
                    'label' => $input->getLabel(),
                    'options' => $packagingOptions,
                    'value' => $input->getDefaultValue(),
                    'class' => $input->getCode(),
                ]
            );
        }
    }

    /**
     * @throws LocalizedException
     */
    #[\Override]
    protected function _prepareForm(): self
    {
        $orders = $this->selectedOrders->get();
        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'method' => 'post']]);

        foreach ($orders as $order) {
            $fieldset = $form->addFieldset("order-{$order->getEntityId()}", []);

            $this->addAddressCell($fieldset, $order);
            $this->addItemsCell($fieldset, $order);
            $this->addSelectCell($fieldset, $order, Codes::PACKAGE_OPTION_DETAILS, Codes::PACKAGE_INPUT_PACKAGING_ID);
            $this->addSelectCell($fieldset, $order, Codes::PACKAGE_OPTION_DETAILS, Codes::PACKAGE_INPUT_PRODUCT_CODE);
        }

        $form->setAction($this->getUrl('nrshipping/shipment/submit'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @throws LocalizedException
     */
    #[\Override]
    protected function _prepareLayout(): self
    {
        parent::_prepareLayout();

        $renderer = $this->getLayout()->createBlock(TableRow::class, $this->getNameInLayout() . '_tablerow');
        Confirm::setFieldsetRenderer($renderer);

        return $this;
    }
}
