<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\ExpenseRemover;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Spryker\Shared\SalesOrderThreshold\SalesOrderThresholdConfig;

class ExpenseRemover implements ExpenseRemoverInterface
{
    public function removeSalesOrderThresholdExpenses(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $calculableObjectTransfer->getExpenses()->exchangeArray(
            array_filter(
                $calculableObjectTransfer->getExpenses()->getArrayCopy(),
                function (ExpenseTransfer $expenseTransfer) {
                    return $expenseTransfer->getType() !== SalesOrderThresholdConfig::THRESHOLD_EXPENSE_TYPE;
                },
            ),
        );
    }
}
