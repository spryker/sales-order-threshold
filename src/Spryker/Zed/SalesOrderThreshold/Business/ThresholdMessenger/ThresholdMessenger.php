<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\ThresholdMessenger;

use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\MoneyTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SalesOrderThresholdValueTransfer;
use Spryker\Shared\SalesOrderThreshold\SalesOrderThresholdConfig;
use Spryker\Zed\SalesOrderThreshold\Business\DataSource\SalesOrderThresholdDataSourceStrategyResolverInterface;
use Spryker\Zed\SalesOrderThreshold\Business\Strategy\Resolver\SalesOrderThresholdStrategyResolverInterface;
use Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToMessengerFacadeInterface;
use Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToMoneyFacadeInterface;

class ThresholdMessenger implements ThresholdMessengerInterface
{
    /**
     * @var string
     */
    protected const THRESHOLD_GLOSSARY_PARAMETER = '{{threshold}}';

    /**
     * @var string
     */
    protected const FEE_GLOSSARY_PARAMETER = '{{fee}}';

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToMessengerFacadeInterface
     */
    protected $messengerFacade;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToMoneyFacadeInterface
     */
    protected $moneyFacade;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Business\DataSource\SalesOrderThresholdDataSourceStrategyResolverInterface
     */
    protected $salesOrderThresholdDataSourceStrategyResolver;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Business\Strategy\Resolver\SalesOrderThresholdStrategyResolverInterface
     */
    protected $salesOrderThresholdStrategyResolver;

    public function __construct(
        SalesOrderThresholdToMessengerFacadeInterface $messengerFacade,
        SalesOrderThresholdToMoneyFacadeInterface $moneyFacade,
        SalesOrderThresholdDataSourceStrategyResolverInterface $salesOrderThresholdDataSourceStrategyResolver,
        SalesOrderThresholdStrategyResolverInterface $salesOrderThresholdStrategyResolver
    ) {
        $this->messengerFacade = $messengerFacade;
        $this->moneyFacade = $moneyFacade;
        $this->salesOrderThresholdDataSourceStrategyResolver = $salesOrderThresholdDataSourceStrategyResolver;
        $this->salesOrderThresholdStrategyResolver = $salesOrderThresholdStrategyResolver;
    }

    public function addSalesOrderThresholdMessages(
        QuoteTransfer $quoteTransfer
    ): QuoteTransfer {
        $thresholdMessages = $this->getMessagesForThresholds($quoteTransfer);
        foreach ($thresholdMessages as $thresholdMessage) {
            $this->messengerFacade->addInfoMessage($thresholdMessage);
        }

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Generated\Shared\Transfer\MessageTransfer>
     */
    protected function getMessagesForThresholds(QuoteTransfer $quoteTransfer): array
    {
        $salesOrderThresholdValueTransfers = $this->filterSalesOrderThresholdsByThresholdGroup(
            $this->salesOrderThresholdDataSourceStrategyResolver->findApplicableThresholds($quoteTransfer),
            SalesOrderThresholdConfig::GROUP_SOFT,
        );

        $thresholdMessages = [];
        foreach ($salesOrderThresholdValueTransfers as $salesOrderThresholdValueTransfer) {
            $salesOrderThresholdStrategy = $this->salesOrderThresholdStrategyResolver->resolveSalesOrderThresholdStrategy(
                $salesOrderThresholdValueTransfer->getSalesOrderThresholdType()->getKey(),
            );

            if (!$salesOrderThresholdStrategy->isApplicable($salesOrderThresholdValueTransfer)) {
                continue;
            }

            $key = $salesOrderThresholdValueTransfer->getMessageGlossaryKey();
            $thresholdMessages[$key] = $this->createMessageTransfer(
                $key,
                (string)$salesOrderThresholdValueTransfer->getThreshold(),
                (string)$salesOrderThresholdStrategy->calculateFee($salesOrderThresholdValueTransfer),
                $quoteTransfer->getCurrency(),
            );
        }

        return $thresholdMessages;
    }

    protected function createMessageTransfer(
        string $messageGlossaryKey,
        string $threshold,
        string $fee,
        CurrencyTransfer $currencyTransfer
    ): MessageTransfer {
        $messageParams = [
            static::THRESHOLD_GLOSSARY_PARAMETER => $this->moneyFacade->formatWithSymbol(
                $this->createMoneyTransfer($threshold, $currencyTransfer),
            ),
        ];

        if ($fee) {
            $messageParams[static::FEE_GLOSSARY_PARAMETER] = $this->moneyFacade->formatWithSymbol(
                $this->createMoneyTransfer($fee, $currencyTransfer),
            );
        }

        return (new MessageTransfer())
            ->setValue($messageGlossaryKey)
            ->setParameters($messageParams);
    }

    protected function createMoneyTransfer(
        string $moneyValue,
        CurrencyTransfer $currencyTransfer
    ): MoneyTransfer {
        return (new MoneyTransfer())
            ->setAmount($moneyValue)
            ->setCurrency($currencyTransfer);
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
}
