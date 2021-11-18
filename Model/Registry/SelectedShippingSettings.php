<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Model\Registry;

/**
 * Dedicated registry to hold shipping settings selected in the interactive form for shipment processing.
 */
class SelectedShippingSettings
{
    /**
     * Array of selected settings per order.
     *
     * Example:
     *
     * [
     *     $orderId => [
     *         $inputCodeA => $inputValueA,
     *         $inputCodeB => $inputValueB,
     *     ],
     *     69 => [
     *         'packageDetails.packagingId' => '_1636464161289_289',
     *         'packageDetails.productCode' => 'V01PAK',
     *     ],
     * ]
     *
     * @var string[][]
     */
    private $settings = [];

    /**
     * @return string[]
     */
    public function get(int $orderId): array
    {
        return $this->settings[$orderId] ?? [];
    }

    /**
     * @param int $orderId
     * @param string[] $settings
     */
    public function set(int $orderId, array $settings): void
    {
        $this->settings[$orderId] = $settings;
    }
}
