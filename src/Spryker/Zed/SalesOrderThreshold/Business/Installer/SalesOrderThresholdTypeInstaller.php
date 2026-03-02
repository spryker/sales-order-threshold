<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\Installer;

use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\SalesOrderThreshold\Persistence\SalesOrderThresholdEntityManagerInterface;

class SalesOrderThresholdTypeInstaller implements SalesOrderThresholdTypeInstallerInterface
{
    use TransactionTrait;

    /**
     * @var array<\Spryker\Zed\SalesOrderThresholdExtension\Dependency\Plugin\SalesOrderThresholdStrategyPluginInterface>
     */
    protected $salesOrderThresholdStrategyPlugins;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Persistence\SalesOrderThresholdEntityManagerInterface
     */
    protected $salesOrderThresholdEntityManager;

    /**
     * @param array<\Spryker\Zed\SalesOrderThresholdExtension\Dependency\Plugin\SalesOrderThresholdStrategyPluginInterface> $salesOrderThresholdStrategyPlugins
     * @param \Spryker\Zed\SalesOrderThreshold\Persistence\SalesOrderThresholdEntityManagerInterface $salesOrderThresholdEntityManager
     */
    public function __construct(
        array $salesOrderThresholdStrategyPlugins,
        SalesOrderThresholdEntityManagerInterface $salesOrderThresholdEntityManager
    ) {
        $this->salesOrderThresholdStrategyPlugins = $salesOrderThresholdStrategyPlugins;
        $this->salesOrderThresholdEntityManager = $salesOrderThresholdEntityManager;
    }

    public function install(): void
    {
        $this->getTransactionHandler()->handleTransaction(function () {
            $this->executeInstallTransaction();
        });
    }

    protected function executeInstallTransaction(): void
    {
        foreach ($this->salesOrderThresholdStrategyPlugins as $salesOrderThresholdStrategy) {
            $this->salesOrderThresholdEntityManager->saveSalesOrderThresholdType(
                $salesOrderThresholdStrategy->toTransfer(),
            );
        }
    }
}
