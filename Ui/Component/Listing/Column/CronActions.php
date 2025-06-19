<?php

/*
 * CronManager
 *
 * @copyright Copyright © 2025 Dot Sistemas. All rights reserved.
 * @author Eliel de Paula <elieldepaula@gmail.com>
 */

declare(strict_types=1);

namespace DotSistemas\CronManager\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CronActions extends Column
{

    /** @var UrlInterface $urlBuilder */
    protected UrlInterface $urlBuilder;

    /**
     * Class constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = [
                    'execute' => [
                        'href' => $this->urlBuilder->getUrl(
                            'dotsistemas_cronmanager/cron/execute',
                            ['id' => $item['id']]
                        ),
                        'label' => __('Execute'),
                        'hidden' => false
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
