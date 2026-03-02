<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Dependency\Facade;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\SalesExpenseCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\SalesExpenseCollectionResponseTransfer;

interface SalesOrderThresholdToSalesFacadeInterface
{
    public function createSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer;

    public function deleteSalesExpenseCollection(
        SalesExpenseCollectionDeleteCriteriaTransfer $salesExpenseCollectionDeleteCriteriaTransfer
    ): SalesExpenseCollectionResponseTransfer;
}
