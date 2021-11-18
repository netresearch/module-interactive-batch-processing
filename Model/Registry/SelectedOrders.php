<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Model\Registry;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Dedicated registry to hold orders selected in the orders grid for display in the interactive form.
 */
class SelectedOrders
{
    /**
     * @var OrderInterface[]
     */
    private $orders = [];

    /**
     * @return OrderInterface[]
     */
    public function get(): array
    {
        return $this->orders;
    }

    /**
     * @param OrderInterface[] $orders
     */
    public function set(array $orders): void
    {
        $this->orders = $orders;
    }
}
