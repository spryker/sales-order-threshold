<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SalesOrderThreshold\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use Generated\Shared\DataBuilder\SalesOrderThresholdBuilder;
use Generated\Shared\DataBuilder\SalesOrderThresholdValueBuilder;
use Generated\Shared\Transfer\SalesOrderThresholdTransfer;
use Generated\Shared\Transfer\SalesOrderThresholdTypeTransfer;
use Orm\Zed\SalesOrderThreshold\Persistence\Map\SpySalesOrderThresholdTableMap;
use Orm\Zed\SalesOrderThreshold\Persistence\Map\SpySalesOrderThresholdTypeTableMap;
use Orm\Zed\SalesOrderThreshold\Persistence\SpySalesOrderThresholdQuery;
use Orm\Zed\SalesOrderThreshold\Persistence\SpySalesOrderThresholdTypeQuery;
use ReflectionClass;
use Spryker\Zed\SalesOrderThreshold\Business\SalesOrderThreshold\Reader\SalesOrderThresholdReader;
use Spryker\Zed\SalesOrderThreshold\Business\SalesOrderThresholdFacadeInterface;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class SalesOrderThresholdHelper extends Module
{
    use LocatorHelperTrait;
    use DataCleanupHelperTrait;

    /**
     * @var string
     */
    protected const ERROR_MESSAGE_FOUND = 'Found at least one entry in the database table but database table `%s` was expected to be empty.';

    /**
     * @var string
     */
    protected const ERROR_MESSAGE_EXPECTED = 'Expected at least %d entries in the database table `%s` and found %d entries.';

    public function _after(TestInterface $test): void
    {
        $this->resetSalesOrderThresholdTransfersCache();
    }

    public function truncateSalesOrderThresholds(): void
    {
        $this->getSalesOrderThresholdQuery()
            ->deleteAll();
    }

    protected function resetSalesOrderThresholdTransfersCache(): void
    {
        $reflection = new ReflectionClass(SalesOrderThresholdReader::class);
        $property = $reflection->getProperty('salesOrderThresholdTransfersCache');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    public function assertSalesOrderThresholdTableIsEmtpy(): void
    {
        $this->assertFalse($this->getSalesOrderThresholdQuery()->exists(), sprintf(static::ERROR_MESSAGE_FOUND, SpySalesOrderThresholdTableMap::TABLE_NAME));
    }

    public function assertSalesOrderThresholdTypeTableHasRecords(int $recordsNum): void
    {
        $entriesFound = $this->getSalesOrderThresholdTypeQuery()->count();
        $this->assertSame($entriesFound, $recordsNum, sprintf(static::ERROR_MESSAGE_EXPECTED, $recordsNum, SpySalesOrderThresholdTypeTableMap::TABLE_NAME, $entriesFound));
    }

    public function haveSalesOrderThresholdType(SalesOrderThresholdTypeTransfer $salesOrderThresholdTypeTransfer): SalesOrderThresholdTypeTransfer
    {
        $salesOrderThresholdTypeTransfer = $this->getFacade()->saveSalesOrderThresholdType($salesOrderThresholdTypeTransfer);

        $this->getDataCleanupHelper()->_addCleanup(function () use ($salesOrderThresholdTypeTransfer): void {
            $this->getSalesOrderThresholdTypeQuery()
                ->filterByKey($salesOrderThresholdTypeTransfer->getKey())
                ->filterByThresholdGroup($salesOrderThresholdTypeTransfer->getThresholdGroup())
                ->delete();
        });

        return $salesOrderThresholdTypeTransfer;
    }

    public function haveSalesOrderThreshold(array $seed = []): SalesOrderThresholdTransfer
    {
        $seed = $seed + [
                SalesOrderThresholdTransfer::STORE => $seed[SalesOrderThresholdTransfer::STORE] ?? $this->getLocator()->store()->facade()->getCurrentStore(),
            ];

        $salesOrderThresholdTransfer = $this->buildSalesOrderThresholdTransfer($seed);
        $salesOrderThresholdTransfer = $this->getFacade()->saveSalesOrderThreshold($salesOrderThresholdTransfer);

        $this->getDataCleanupHelper()->_addCleanup(function () use ($salesOrderThresholdTransfer): void {
            $this->getFacade()->deleteSalesOrderThreshold($salesOrderThresholdTransfer);
        });

        return $salesOrderThresholdTransfer;
    }

    protected function buildSalesOrderThresholdTransfer(array $seed): SalesOrderThresholdTransfer
    {
        $salesOrderThresholdValueBuilder = (new SalesOrderThresholdValueBuilder($seed))
            ->withSalesOrderThresholdType($seed);

        return (new SalesOrderThresholdBuilder($seed))
            ->withAnotherSalesOrderThresholdValue($salesOrderThresholdValueBuilder)
            ->build();
    }

    protected function getSalesOrderThresholdQuery(): SpySalesOrderThresholdQuery
    {
        return SpySalesOrderThresholdQuery::create();
    }

    protected function getSalesOrderThresholdTypeQuery(): SpySalesOrderThresholdTypeQuery
    {
        return SpySalesOrderThresholdTypeQuery::create();
    }

    protected function getFacade(): SalesOrderThresholdFacadeInterface
    {
        return $this->getLocator()->salesOrderThreshold()->facade();
    }
}
