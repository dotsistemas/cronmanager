<?php

/*
 * CronManager
 *
 * @copyright Copyright © 2025 Dot Sistemas. All rights reserved.
 * @author Eliel de Paula <elieldepaula@gmail.com>
 */

declare(strict_types=1);

namespace DotSistemas\CronManager\Model;

use Exception;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResultFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Cron\Model\ConfigInterface;
use Magento\Framework\DataObject;

class CronDataProvider extends DataProvider
{

    /** @var Collection $collection */
    protected Collection $collection;
    /** @var ConfigInterface $cronConfig */
    protected ConfigInterface $cronConfig;
    /** @var CollectionFactory $collectionFactory */
    protected CollectionFactory $collectionFactory;

    /**
     * Class constructor.
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param ConfigInterface $cronConfig
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        ConfigInterface $cronConfig,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->cronConfig = $cronConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $items = $this->getCollection();
        $data = [];
        foreach ($items as $item) {
            $data[$item->getId()] = $item->getData();
        }
        return [
            'totalRecords' => count($data),
            'items' => array_values($data)
        ];
    }

    /**
     * Get cron collection.
     * @return Collection
     * @throws Exception
     */
    protected function getCollection()
    {
        $this->collection = $this->collectionFactory->create();
        $cronJobs = $this->cronConfig->getJobs();
        foreach ($cronJobs as $groupName => $group) {
            foreach ($group as $jobName => $jobConfig) {
                $schedule = '';
                if (isset($jobConfig['schedule'])) {
                    $schedule = $jobConfig['schedule'];
                }
                $item = new DataObject([
                    'id' => $jobName,
                    'name' => $jobName,
                    'group' => $groupName,
                    'schedule' => $schedule,
                    'instance' => isset($jobConfig['instance']) ? $jobConfig['instance'] : '',
                    'method' => isset($jobConfig['method']) ? $jobConfig['method'] : '',
                    'status' => 'Active'
                ]);
                $this->collection->addItem($item);
            }
        }
        return $this->collection;
    }
}
