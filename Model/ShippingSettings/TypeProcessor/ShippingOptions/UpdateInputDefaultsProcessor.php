<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Model\ShippingSettings\TypeProcessor\ShippingOptions;

use Magento\Sales\Api\Data\ShipmentInterface;
use Netresearch\InteractiveBatchProcessing\Model\Registry\SelectedShippingSettings;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\InputInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\OptionInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOptionInterface;
use Netresearch\ShippingCore\Api\ShippingSettings\TypeProcessor\ShippingOptionsProcessorInterface;

class UpdateInputDefaultsProcessor implements ShippingOptionsProcessorInterface
{
    /**
     * @var SelectedShippingSettings
     */
    private $settings;

    public function __construct(SelectedShippingSettings $settings)
    {
        $this->settings = $settings;
    }

    private function getOptionInput(ShippingOptionInterface $serviceOption, string $inputCode): ?InputInterface
    {
        foreach ($serviceOption->getInputs() as $input) {
            if ($input->getCode() === $inputCode) {
                return $input;
            }
        }

        return null;
    }

    /**
     * Override default values by user input from interactive bulk process.
     *
     * @param string $carrierCode
     * @param ShippingOptionInterface[] $shippingOptions
     * @param int $storeId
     * @param string $countryCode
     * @param string $postalCode
     * @param ShipmentInterface|null $shipment
     *
     * @return ShippingOptionInterface[]
     */
    #[\Override]
    public function process(
        string $carrierCode,
        array $shippingOptions,
        int $storeId,
        string $countryCode,
        string $postalCode,
        ?ShipmentInterface $shipment = null
    ): array {
        if (!$shipment) {
            // checkout scope, nothing to modify.
            return $shippingOptions;
        }

        $order = $shipment->getOrder();
        $inputValues = $this->settings->get((int) $order->getId());
        if (empty($inputValues)) {
            // no selection made by user for this particular order, proceed.
            return $shippingOptions;
        }

        foreach ($inputValues as $compoundCode => $inputValue) {
            list($optionCode, $inputCode) = explode('.', $compoundCode);
            $shippingOption = $shippingOptions[$optionCode] ?? false;
            if (!$shippingOption instanceof ShippingOptionInterface) {
                // option not available, proceed.
                continue;
            }

            $input = $this->getOptionInput($shippingOption, $inputCode);
            if (!$input instanceof InputInterface) {
                // input not available, proceed.
                continue;
            }

            // check if selected value is available in the input's options.
            $inputOptions = $input->getOptions();
            if (empty($inputOptions)) {
                $allowedValues = [$inputValue];
            } else {
                $allowedValues = array_map(
                    function (OptionInterface $inputOption) {
                        return $inputOption->getValue();
                    },
                    $inputOptions
                );
            }

            if (in_array($inputValue, $allowedValues, true)) {
                $input->setDefaultValue($inputValue);
            }
        }

        return $shippingOptions;
    }
}
