<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\InteractiveBatchProcessing\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Netresearch\InteractiveBatchProcessing\Model\OrderProvider;

/**
 * @method Http getRequest()
 */
class Interactive extends Action implements HttpPostActionInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var OrderProvider
     */
    private $orderProvider;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter,
        OrderProvider $orderProvider
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->orderProvider = $orderProvider;

        parent::__construct($context);
    }

    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::sales_order');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Bulk Shipment'));

        // todo(nr): filter applicable orders (label status, carrier code)
        // @see \Netresearch\ShippingCore\Model\BulkShipment\BulkShipmentManagement::createShipments
        $orderCollection = $this->filter->getCollection($this->collectionFactory->create());

        // the "Create New Order" UI button gets added by using the UI filter above…
        $this->_view->getLayout()->unsetElement('container-sales_order_grid-add');

        // todo(nr): if order collection is empty, redirect to grid.
        $this->orderProvider->setOrders($orderCollection->getItems());

        return $this->_view->getPage();
    }
}
