<?php

/**
 * CronManager
 *
 * @copyright Copyright © 2025 Dot Sistemas. All rights reserved.
 * @author Eliel de Paula <elieldepaula@gmail.com>
 */

declare(strict_types=1);

namespace DotSistemas\CronManager\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{

    /** @var PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /**
     * Class constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('DotSistemas_CronManager::cron_manager');
        $resultPage->addBreadcrumb(__('Cron Manager'), __('Cron Manager'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cron Manager'));

        $resultPage->addHandle('dotsistemas_cronmanager_cron_index');
        return $resultPage;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('DotSistemas_CronManager::cron_manager');
    }
}
