<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Model\ShippingSettings\TypeProcessor\ShippingOptions;

use Magento\Sales\Api\Data\ShipmentInterface;
use Netresearch\ShippingCore\Api\Config\ParcelProcessingConfigInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\InputInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOptionInterface;
use Netresearch\ShippingCore\Api\ShippingSettings\TypeProcessor\ShippingOptionsProcessorInterface;
use Netresearch\ShippingCore\Model\ShippingBox\Package;
use Netresearch\ShippingCore\Model\ShippingSettings\ShippingOption\Codes;

/**
 * Update dimensions and weight inputs according to selected packaging preset.
 */
class UpdatePackageDimensionsProcessor implements ShippingOptionsProcessorInterface
{
    /**
     * @var ParcelProcessingConfigInterface
     */
    private $parcelConfig;

    public function __construct(ParcelProcessingConfigInterface $parcelConfig)
    {
        $this->parcelConfig = $parcelConfig;
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

    private function updateInputs(
        ShippingOptionInterface $shippingOption,
        Package $selectedPackage,
        ?Package $defaultPackage
    ): void {
        $packagingWeightInput = $this->getOptionInput($shippingOption, Codes::PACKAGE_INPUT_PACKAGING_WEIGHT);
        if ($packagingWeightInput instanceof InputInterface) {
            $packagingWeightInput->setDefaultValue((string) $selectedPackage->getWeight());
        }

        $totalWeightInput = $this->getOptionInput($shippingOption, Codes::PACKAGE_INPUT_WEIGHT);
        if ($totalWeightInput instanceof InputInterface) {
            $totalWeight = $totalWeightInput->getDefaultValue();
            $defaultPackageWeight = $defaultPackage ? $defaultPackage->getWeight() : 0;
            $defaultPackageWeight = $totalWeight - $defaultPackageWeight + $selectedPackage->getWeight();
            $totalWeightInput->setDefaultValue((string) $defaultPackageWeight);
        }

        $lengthInput = $this->getOptionInput($shippingOption, Codes::PACKAGE_INPUT_LENGTH);
        if ($lengthInput instanceof InputInterface) {
            $lengthInput->setDefaultValue((string) $selectedPackage->getLength());
        }

        $widthInput = $this->getOptionInput($shippingOption, Codes::PACKAGE_INPUT_WIDTH);
        if ($widthInput instanceof InputInterface) {
            $widthInput->setDefaultValue((string) $selectedPackage->getWidth());
        }

        $heightInput = $this->getOptionInput($shippingOption, Codes::PACKAGE_INPUT_HEIGHT);
        if ($heightInput instanceof InputInterface) {
            $heightInput->setDefaultValue((string) $selectedPackage->getHeight());
        }
    }

    /**
     * Set options and values to inputs on package level.
     *
     * @param ShippingOptionInterface $shippingOption
     * @param ShipmentInterface $shipment
     */
    private function processInputs(ShippingOptionInterface $shippingOption, ShipmentInterface $shipment): void
    {
        if ($shippingOption->getCode() !== Codes::PACKAGE_OPTION_DETAILS) {
            return;
        }

        $configuredPackageInput = $this->getOptionInput($shippingOption, Codes::PACKAGE_INPUT_PACKAGING_ID);
        if (!$configuredPackageInput instanceof InputInterface) {
            return;
        }

        $defaultPackage = $this->parcelConfig->getDefaultPackage($shipment->getStoreId());
        $selectedPackageId = $configuredPackageInput->getDefaultValue();
        if (($defaultPackage instanceof Package) && ($defaultPackage->getId() === $selectedPackageId)) {
            // nothing to update.
            return;
        }

        $packages = $this->parcelConfig->getPackages($shipment->getStoreId());
        foreach ($packages as $package) {
            if ($package->getId() === $selectedPackageId) {
                $this->updateInputs($shippingOption, $package, $defaultPackage);
            }
        }
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
    public function process(
        string $carrierCode,
        array $shippingOptions,
        int $storeId,
        string $countryCode,
        string $postalCode,
        ShipmentInterface $shipment = null
    ): array {
        if (!$shipment) {
            // checkout scope, nothing to modify.
            return $shippingOptions;
        }

        foreach ($shippingOptions as $shippingOption) {
            $this->processInputs($shippingOption, $shipment);
        }

        return $shippingOptions;
    }
}
