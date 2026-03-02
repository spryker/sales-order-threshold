<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\ExpenseCalculator;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SalesOrderThresholdValueTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Shared\SalesOrderThreshold\SalesOrderThresholdConfig;
use Spryker\Zed\SalesOrderThreshold\Business\DataSource\SalesOrderThresholdDataSourceStrategyResolverInterface;
use Spryker\Zed\SalesOrderThreshold\Business\Strategy\Resolver\SalesOrderThresholdStrategyResolverInterface;
use Spryker\Zed\SalesOrderThreshold\Business\TaxRateReader\TaxRateReaderInterface;

class ExpenseCalculator implements ExpenseCalculatorInterface
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_TEMPLATE = 'sales-order-threshold.strategy.%s';

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Business\Strategy\Resolver\SalesOrderThresholdStrategyResolverInterface
     */
    protected $salesOrderThresholdStrategyResolver;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Business\TaxRateReader\TaxRateReaderInterface
     */
    protected $taxRateReader;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Business\DataSource\SalesOrderThresholdDataSourceStrategyResolverInterface
     */
    protected $salesOrderThresholdDataSourceStrategyResolver;

    public function __construct(
        SalesOrderThresholdStrategyResolverInterface $salesOrderThresholdStrategyResolver,
        SalesOrderThresholdDataSourceStrategyResolverInterface $salesOrderThresholdDataSourceStrategyResolver,
        TaxRateReaderInterface $taxRateReader
    ) {
        $this->salesOrderThresholdStrategyResolver = $salesOrderThresholdStrategyResolver;
        $this->salesOrderThresholdDataSourceStrategyResolver = $salesOrderThresholdDataSourceStrategyResolver;
        $this->taxRateReader = $taxRateReader;
    }

    public function addSalesOrderThresholdExpenses(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $salesOrderThresholdValueTransfers = $this->salesOrderThresholdDataSourceStrategyResolver
            ->findApplicableThresholds(
                $this->prepareCalculatedQuoteTransfer($calculableObjectTransfer),
            );

        $salesOrderThresholdValueTransfers = $this->filterSalesOrderThresholdsByThresholdGroup(
            $salesOrderThresholdValueTransfers,
            SalesOrderThresholdConfig::GROUP_SOFT,
        );

        foreach ($salesOrderThresholdValueTransfers as $salesOrderThresholdValueTransfer) {
            $this->addExpense($calculableObjectTransfer, $salesOrderThresholdValueTransfer);
        }
    }

    protected function prepareCalculatedQuoteTransfer(CalculableObjectTransfer $calculableObjectTransfer): QuoteTransfer
    {
        return (new QuoteTransfer())
            ->fromArray($calculableObjectTransfer->getOriginalQuote()->toArray(), true)
            ->setItems(clone $calculableObjectTransfer->getItems())
            ->setTotals(clone $calculableObjectTransfer->getTotals());
    }

    protected function addExpense(CalculableObjectTransfer $calculableObjectTransfer, SalesOrderThresholdValueTransfer $salesOrderThresholdValueTransfer): void
    {
        $this->assertRequiredAttributes($salesOrderThresholdValueTransfer);
        $salesOrderThresholdValueTransferStrategy = $this->salesOrderThresholdStrategyResolver
            ->resolveSalesOrderThresholdStrategy($salesOrderThresholdValueTransfer->getSalesOrderThresholdType()->getKey());

        if (!$salesOrderThresholdValueTransferStrategy->isApplicable($salesOrderThresholdValueTransfer)) {
            return;
        }

        $calculatedFee = $salesOrderThresholdValueTransferStrategy->calculateFee($salesOrderThresholdValueTransfer);

        if (!$calculatedFee) {
            return;
        }

        $this->addSalesOrderThresholdExpense($salesOrderThresholdValueTransfer, $calculableObjectTransfer, $calculatedFee);
    }

    /**
     * @param array<\Generated\Shared\Transfer\SalesOrderThresholdValueTransfer> $salesOrderThresholdValueTransfers
     * @param string $thresholdGroup
     *
     * @return array<\Generated\Shared\Transfer\SalesOrderThresholdValueTransfer>
     */
    protected function filterSalesOrderThresholdsByThresholdGroup(array $salesOrderThresholdValueTransfers, string $thresholdGroup): array
    {
        return array_filter($salesOrderThresholdValueTransfers, function (SalesOrderThresholdValueTransfer $salesOrderThresholdValueTransfers) use ($thresholdGroup) {
            return $salesOrderThresholdValueTransfers->getSalesOrderThresholdType()->getThresholdGroup() === $thresholdGroup;
        });
    }

    protected function assertRequiredAttributes(SalesOrderThresholdValueTransfer $salesOrderThresholdValueTransfer): void
    {
        $salesOrderThresholdValueTransfer
            ->requireSalesOrderThresholdType()
            ->requireValue()
            ->requireThreshold();

        $salesOrderThresholdValueTransfer->getSalesOrderThresholdType()
            ->requireKey();
    }

    protected function addSalesOrderThresholdExpense(
        SalesOrderThresholdValueTransfer $salesOrderThresholdValueTransfer,
        CalculableObjectTransfer $calculableObjectTransfer,
        int $fee
    ): void {
        $calculableObjectTransfer->addExpense(
            $this->createExpenseByPriceMode($salesOrderThresholdValueTransfer, $fee, $calculableObjectTransfer->getPriceMode(), $calculableObjectTransfer->getStore()),
        );
    }

    protected function createExpenseByPriceMode(
        SalesOrderThresholdValueTransfer $salesOrderThresholdValueTransfer,
        int $expensePrice,
        string $priceMode,
        ?StoreTransfer $storeTransfer = null
    ): ExpenseTransfer {
        $expenseTransfer = (new ExpenseTransfer())
            ->setName(sprintf(
                static::GLOSSARY_KEY_TEMPLATE,
                $salesOrderThresholdValueTransfer->getSalesOrderThresholdType()->getKey(),
            ))->setType(SalesOrderThresholdConfig::THRESHOLD_EXPENSE_TYPE)
            ->setUnitPrice($expensePrice)
            ->setSumPrice($expensePrice)
            ->setUnitPriceToPayAggregation($expensePrice)
            ->setSumPriceToPayAggregation($expensePrice)
            ->setTaxRate($this->taxRateReader->getSalesOrderThresholdTaxRate($storeTransfer))
            ->setQuantity(1);

        if ($priceMode === SalesOrderThresholdConfig::PRICE_MODE_NET) {
            $expenseTransfer->setUnitGrossPrice(0);
            $expenseTransfer->setSumGrossPrice(0);
            $expenseTransfer->setUnitNetPrice($expensePrice);
            $expenseTransfer->setSumNetPrice($expensePrice);

            return $expenseTransfer;
        }

        $expenseTransfer->setUnitNetPrice(0);
        $expenseTransfer->setSumNetPrice(0);
        $expenseTransfer->setUnitGrossPrice($expensePrice);
        $expenseTransfer->setSumGrossPrice($expensePrice);

        return $expenseTransfer;
    }
}
