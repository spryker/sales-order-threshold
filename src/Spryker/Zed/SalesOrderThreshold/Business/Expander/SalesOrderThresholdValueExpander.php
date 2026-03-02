<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\Expander;

use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\MoneyTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SalesOrderThresholdValueTransfer;
use Spryker\Shared\SalesOrderThreshold\SalesOrderThresholdConfig;
use Spryker\Zed\SalesOrderThreshold\Business\Strategy\Resolver\SalesOrderThresholdStrategyResolverInterface;
use Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToGlossaryFacadeInterface;
use Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToLocaleFacadeInterface;
use Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToMoneyFacadeInterface;

class SalesOrderThresholdValueExpander implements SalesOrderThresholdValueExpanderInterface
{
    /**
     * @var string
     */
    protected const GLOSSARY_PARAMETER_THRESHOLD = '{{threshold}}';

    /**
     * @var string
     */
    protected const GLOSSARY_PARAMETER_FEE = '{{fee}}';

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Business\Strategy\Resolver\SalesOrderThresholdStrategyResolverInterface
     */
    protected $salesOrderThresholdStrategyResolver;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToGlossaryFacadeInterface
     */
    protected $glossaryFacade;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToLocaleFacadeInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToMoneyFacadeInterface
     */
    protected $moneyFacade;

    public function __construct(
        SalesOrderThresholdStrategyResolverInterface $salesOrderThresholdStrategyResolver,
        SalesOrderThresholdToGlossaryFacadeInterface $glossaryFacade,
        SalesOrderThresholdToLocaleFacadeInterface $localeFacade,
        SalesOrderThresholdToMoneyFacadeInterface $moneyFacade
    ) {
        $this->salesOrderThresholdStrategyResolver = $salesOrderThresholdStrategyResolver;
        $this->glossaryFacade = $glossaryFacade;
        $this->localeFacade = $localeFacade;
        $this->moneyFacade = $moneyFacade;
    }

    /**
     * @param array<\Generated\Shared\Transfer\SalesOrderThresholdValueTransfer> $salesOrderThresholdValueTransfers
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Generated\Shared\Transfer\SalesOrderThresholdValueTransfer>
     */
    public function expandSalesOrderThresholdValues(array $salesOrderThresholdValueTransfers, QuoteTransfer $quoteTransfer): array
    {
        $applicableSalesOrderThresholdValues = [];
        $localeTransfer = $this->localeFacade->getCurrentLocale();
        $currencyTransfer = $quoteTransfer->getCurrencyOrFail();

        foreach ($salesOrderThresholdValueTransfers as $salesOrderThresholdValueTransfer) {
            $salesOrderThresholdStrategy = $this->salesOrderThresholdStrategyResolver->resolveSalesOrderThresholdStrategy(
                $salesOrderThresholdValueTransfer->getSalesOrderThresholdType()->getKey(),
            );

            if (!$salesOrderThresholdStrategy->isApplicable($salesOrderThresholdValueTransfer)) {
                continue;
            }

            $messageTransfer = $this->createMessageTransfer(
                $salesOrderThresholdValueTransfer,
                $currencyTransfer,
                (string)$salesOrderThresholdStrategy->calculateFee($salesOrderThresholdValueTransfer),
            );

            $message = $this->glossaryFacade->translate(
                $messageTransfer->getValue(),
                $messageTransfer->getParameters(),
                $localeTransfer,
            );

            $applicableSalesOrderThresholdValues[] = $salesOrderThresholdValueTransfer
                ->setDeltaWithSubtotal($this->calculateDeltaWithSubtotal($salesOrderThresholdValueTransfer))
                ->setMessage($message);
        }

        return $applicableSalesOrderThresholdValues;
    }

    protected function createMessageTransfer(
        SalesOrderThresholdValueTransfer $salesOrderThresholdValueTransfer,
        CurrencyTransfer $currencyTransfer,
        string $fee
    ): MessageTransfer {
        $messageParams = [
            static::GLOSSARY_PARAMETER_THRESHOLD => $this->moneyFacade->formatWithSymbol(
                $this->createMoneyTransfer((string)$salesOrderThresholdValueTransfer->getThreshold(), $currencyTransfer),
            ),
        ];

        if ($fee) {
            $messageParams[static::GLOSSARY_PARAMETER_FEE] = $this->moneyFacade->formatWithSymbol(
                $this->createMoneyTransfer($fee, $currencyTransfer),
            );
        }

        return (new MessageTransfer())
            ->setValue($salesOrderThresholdValueTransfer->getMessageGlossaryKey())
            ->setParameters($messageParams);
    }

    protected function calculateDeltaWithSubtotal(SalesOrderThresholdValueTransfer $salesOrderThresholdValueTransfer): int
    {
        $isHardMaximumThreshold = $salesOrderThresholdValueTransfer->getSalesOrderThresholdTypeOrFail()->getKey() === SalesOrderThresholdConfig::THRESHOLD_STRATEGY_KEY_HARD_MAXIMUM;

        $deltaWithSubtotal = $salesOrderThresholdValueTransfer->getThresholdOrFail() - $salesOrderThresholdValueTransfer->getValueOrFail();
        if ($isHardMaximumThreshold) {
            $deltaWithSubtotal = -$deltaWithSubtotal;
        }

        return max($deltaWithSubtotal, 0);
    }

    protected function createMoneyTransfer(
        string $moneyValue,
        CurrencyTransfer $currencyTransfer
    ): MoneyTransfer {
        return (new MoneyTransfer())
            ->setAmount($moneyValue)
            ->setCurrency($currencyTransfer);
    }
}
