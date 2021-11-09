<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Model;

use Magento\Sales\Api\Data\OrderInterface;

class OrderProvider
{
    /**
     * @var OrderInterface[]
     */
    private $orders = [];

    /**
     * @return OrderInterface[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @param OrderInterface[] $orders
     */
    public function setOrders(array $orders): void
    {
        $this->orders = $orders;
    }
}
