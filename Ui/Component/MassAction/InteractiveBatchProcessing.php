<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Ui\Component\MassAction;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;
use Netresearch\InteractiveBatchProcessing\Model\Config\ModuleConfig;

class InteractiveBatchProcessing extends Action
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        ModuleConfig $moduleConfig,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->moduleConfig = $moduleConfig;

        parent::__construct($context, $components, $data, $actions);
    }

    #[\Override]
    public function prepare()
    {
        parent::prepare();

        if ($this->moduleConfig->isInteractiveMassActionEnabled()) {
            $config = $this->getConfiguration();
            foreach ($config['actions'] as &$action) {
                if ($action['type'] === 'nrshipping_batch_create_shipments') {
                    $action['url'] = $this->urlBuilder->getUrl('nrshipping/shipment/interactive');
                }
            }
            $this->setData('config', $config);
        }
    }
}
