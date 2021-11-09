<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Ui\Component\MassAction;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

class InteractiveBatchProcessing extends Action
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $components, $data, $actions);
    }

    public function prepare()
    {
        parent::prepare();

        //todo(nr): add configuration setting whether or not to enable interactive mass action
        $isInteractiveModeEnabled = true;
        if ($isInteractiveModeEnabled) {
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
