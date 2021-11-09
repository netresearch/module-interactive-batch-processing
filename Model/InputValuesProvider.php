<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Model;

// fixme(nr): find a better name
class InputValuesProvider
{
    /**
     * @var string[][]
     */
    private $inputValues = [];

    /**
     * @return string[]
     */
    public function getInputValues(int $orderId): array
    {
        return $this->inputValues[$orderId] ?? [];
    }

    /**
     * @param int $orderId
     * @param string[] $inputValues
     */
    public function setInputValues(int $orderId, array $inputValues): void
    {
        $this->inputValues[$orderId] = $inputValues;
    }
}
