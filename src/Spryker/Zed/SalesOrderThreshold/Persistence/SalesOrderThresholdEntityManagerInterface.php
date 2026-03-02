<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Persistence;

use Generated\Shared\Transfer\SalesOrderThresholdTransfer;
use Generated\Shared\Transfer\SalesOrderThresholdTypeTransfer;

interface SalesOrderThresholdEntityManagerInterface
{
    public function saveSalesOrderThresholdType(SalesOrderThresholdTypeTransfer $salesOrderThresholdTypeTransfer): SalesOrderThresholdTypeTransfer;

    public function saveSalesOrderThreshold(
        SalesOrderThresholdTransfer $salesOrderThresholdTransfer
    ): SalesOrderThresholdTransfer;

    public function deleteSalesOrderThreshold(
        SalesOrderThresholdTransfer $salesOrderThresholdTransfer
    ): bool;

    public function saveSalesOrderThresholdTaxSet(int $idTaxSet): void;
}
