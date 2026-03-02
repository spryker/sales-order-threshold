<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\ExpenseSaver;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Spryker\Shared\SalesOrderThreshold\SalesOrderThresholdConfig;
use Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToSalesFacadeInterface;

class ExpenseSaver implements ExpenseSaverInterface
{
    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToSalesFacadeInterface
     */
    protected $salesFacade;

    public function __construct(SalesOrderThresholdToSalesFacadeInterface $salesFacade)
    {
        $this->salesFacade = $salesFacade;
    }

    public function saveSalesOrderSalesOrderThresholdExpense(
        QuoteTransfer $quoteTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): SaveOrderTransfer {
        foreach ($quoteTransfer->getExpenses() as $expenseTransfer) {
            if ($expenseTransfer->getType() !== SalesOrderThresholdConfig::THRESHOLD_EXPENSE_TYPE) {
                continue;
            }

            $saveOrderTransfer = $this->addExpenseToOrder($expenseTransfer, $saveOrderTransfer);
        }

        return $saveOrderTransfer;
    }

    protected function addExpenseToOrder(
        ExpenseTransfer $expenseTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): SaveOrderTransfer {
        $expenseTransfer->setFkSalesOrder($saveOrderTransfer->getIdSalesOrder());
        $expenseTransfer = $this->salesFacade->createSalesExpense($expenseTransfer);
        $saveOrderTransfer->addOrderExpense($expenseTransfer);

        return $saveOrderTransfer;
    }
}
