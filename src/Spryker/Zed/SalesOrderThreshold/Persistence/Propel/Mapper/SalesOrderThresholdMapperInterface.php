<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\SalesOrderThresholdTransfer;
use Generated\Shared\Transfer\SalesOrderThresholdTypeTransfer;
use Orm\Zed\SalesOrderThreshold\Persistence\SpySalesOrderThreshold;
use Orm\Zed\SalesOrderThreshold\Persistence\SpySalesOrderThresholdType;

interface SalesOrderThresholdMapperInterface
{
    public function mapSalesOrderThresholdTypeEntityToTransfer(
        SpySalesOrderThresholdType $spySalesOrderThresholdType,
        SalesOrderThresholdTypeTransfer $salesOrderThresholdTypeTransfer
    ): SalesOrderThresholdTypeTransfer;

    public function mapSalesOrderThresholdEntityToTransfer(
        SpySalesOrderThreshold $salesOrderThresholdEntity,
        SalesOrderThresholdTransfer $salesOrderThresholdTransfer
    ): SalesOrderThresholdTransfer;
}
