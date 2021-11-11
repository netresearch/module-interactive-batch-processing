<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ModuleConfig
{
    // phpcs:disable Generic.Files.LineLength.TooLong
    public const CONFIG_PATH_INTERACTIVE_MASSACTION_ENABLED = 'shipping/batch_processing/shipping_label/interactive_massaction_enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isInteractiveMassActionEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_INTERACTIVE_MASSACTION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
