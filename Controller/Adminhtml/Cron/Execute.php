<?php

/*
 * CronManager
 *
 * @copyright Copyright © 2025 Dot Sistemas. All rights reserved.
 * @author Eliel de Paula <elieldepaula@gmail.com>
 */

declare(strict_types=1);

namespace DotSistemas\CronManager\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Cron\Model\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Execute extends Action
{

    /** @var ManagerInterface $messageManager */
    protected $messageManager;

    /** @var ConfigInterface $cronConfig */
    private $cronConfig;

    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * Class constructor.
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param ConfigInterface $cronConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        ConfigInterface $cronConfig,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->cronConfig = $cronConfig;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $cronJobId = $this->getRequest()->getParam('id');

        if (!$cronJobId) {
            $this->messageManager->addErrorMessage(__('Cron job ID is required'));
            return $this->_redirect('*/*/');
        }

        try {
            $cronJob = $this->getCronJobConfig($cronJobId);

            if (!$cronJob) {
                throw new LocalizedException(__('Cron job not found: %1', $cronJobId));
            }

            if (!isset($cronJob['instance']) || !isset($cronJob['method'])) {
                throw new LocalizedException(__('Invalid cron job configuration for: %1', $cronJobId));
            }

            $instance = $this->_objectManager->create($cronJob['instance']);
            $method = $cronJob['method'];

            if (!method_exists($instance, $method)) {
                throw new LocalizedException(
                    __('Method %1 does not exist in class %2', $method, $cronJob['instance'])
                );
            }

            $instance->$method();
            $this->messageManager->addSuccessMessage(__('Cron job %1 executed successfully', $cronJobId));

            $this->logger->info(sprintf('Cron job %s executed manually', $cronJobId));

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while executing the cron job'));
            $this->logger->error(sprintf(
                'Error executing cron job %s: %s',
                $cronJobId,
                $e->getMessage()
            ));
        }

        return $this->_redirect('*/*/');
    }

    /**
     * Get cron job configuration
     * @param string $jobId
     * @return array|null
     */
    private function getCronJobConfig(string $jobId): ?array
    {
        $jobs = $this->cronConfig->getJobs();

        foreach ($jobs as $group) {
            if (isset($group[$jobId])) {
                return $group[$jobId];
            }
        }

        return null;
    }

    /**
     * Check if user has access to the resource
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('DotSistemas_CronManager::cron_manager');
    }
}
