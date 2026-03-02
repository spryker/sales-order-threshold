<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Persistence;

use Orm\Zed\SalesOrderThreshold\Persistence\SpySalesOrderThresholdQuery;
use Orm\Zed\SalesOrderThreshold\Persistence\SpySalesOrderThresholdTaxSetQuery;
use Orm\Zed\SalesOrderThreshold\Persistence\SpySalesOrderThresholdTypeQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\SalesOrderThreshold\Persistence\Propel\Mapper\SalesOrderThresholdMapper;
use Spryker\Zed\SalesOrderThreshold\Persistence\Propel\Mapper\SalesOrderThresholdMapperInterface;

/**
 * @method \Spryker\Zed\SalesOrderThreshold\SalesOrderThresholdConfig getConfig()
 * @method \Spryker\Zed\SalesOrderThreshold\Persistence\SalesOrderThresholdRepositoryInterface getRepository()
 * @method \Spryker\Zed\SalesOrderThreshold\Persistence\SalesOrderThresholdEntityManagerInterface getEntityManager()
 */
class SalesOrderThresholdPersistenceFactory extends AbstractPersistenceFactory
{
    public function createSalesOrderThresholdTypeQuery(): SpySalesOrderThresholdTypeQuery
    {
        return SpySalesOrderThresholdTypeQuery::create();
    }

    public function createSalesOrderThresholdQuery(): SpySalesOrderThresholdQuery
    {
        return SpySalesOrderThresholdQuery::create();
    }

    public function createSalesOrderThresholdMapper(): SalesOrderThresholdMapperInterface
    {
        return new SalesOrderThresholdMapper();
    }

    public function createSalesOrderThresholdTaxSetPropelQuery(): SpySalesOrderThresholdTaxSetQuery
    {
        return SpySalesOrderThresholdTaxSetQuery::create();
    }
}
