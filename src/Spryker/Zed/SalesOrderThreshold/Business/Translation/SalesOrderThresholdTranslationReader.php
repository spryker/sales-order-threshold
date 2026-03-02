<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\Translation;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\SalesOrderThresholdLocalizedMessageTransfer;
use Generated\Shared\Transfer\SalesOrderThresholdTransfer;
use Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToGlossaryFacadeInterface;
use Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToStoreFacadeInterface;

/**
 * @deprecated Use {@link \Spryker\Zed\SalesOrderThreshold\Business\Translation\Hydrator\SalesOrderThresholdTranslationHydratorInterface} instead.
 */
class SalesOrderThresholdTranslationReader implements SalesOrderThresholdTranslationReaderInterface
{
    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToGlossaryFacadeInterface
     */
    protected $glossaryFacade;

    /**
     * @var \Spryker\Zed\SalesOrderThreshold\Dependency\Facade\SalesOrderThresholdToStoreFacadeInterface
     */
    protected $storeFacade;

    public function __construct(
        SalesOrderThresholdToGlossaryFacadeInterface $glossaryFacade,
        SalesOrderThresholdToStoreFacadeInterface $storeFacade
    ) {
        $this->glossaryFacade = $glossaryFacade;
        $this->storeFacade = $storeFacade;
    }

    public function hydrateLocalizedMessages(SalesOrderThresholdTransfer $salesOrderThresholdTransfer): SalesOrderThresholdTransfer
    {
        $storeTransfer = $this->storeFacade
            ->getStoreByName($salesOrderThresholdTransfer->getStore()->getName());

        foreach ($storeTransfer->getAvailableLocaleIsoCodes() as $localeIsoCode) {
            $this->initOrUpdateLocalizedMessages(
                $salesOrderThresholdTransfer,
                $localeIsoCode,
            );
        }

        return $salesOrderThresholdTransfer;
    }

    protected function initOrUpdateLocalizedMessages(
        SalesOrderThresholdTransfer $salesOrderThresholdTransfer,
        string $localeIsoCode
    ): SalesOrderThresholdTransfer {
        $translationValue = $this->findTranslationValue(
            $salesOrderThresholdTransfer->getSalesOrderThresholdValue()->getMessageGlossaryKey(),
            $this->createLocaleTransfer($localeIsoCode),
        );

        foreach ($salesOrderThresholdTransfer->getLocalizedMessages() as $salesOrderThresholdLocalizedMessageTransfer) {
            if ($salesOrderThresholdLocalizedMessageTransfer->getLocaleCode() === $localeIsoCode) {
                $salesOrderThresholdLocalizedMessageTransfer->setMessage($translationValue);

                return $salesOrderThresholdTransfer;
            }
        }

        $salesOrderThresholdTransfer->addLocalizedMessage(
            (new SalesOrderThresholdLocalizedMessageTransfer())
                ->setLocaleCode($localeIsoCode)
                ->setMessage($translationValue),
        );

        return $salesOrderThresholdTransfer;
    }

    protected function createLocaleTransfer(string $localeName): LocaleTransfer
    {
        return (new LocaleTransfer())
            ->setLocaleName($localeName);
    }

    protected function findTranslationValue(string $keyName, LocaleTransfer $localeTransfer): ?string
    {
        if (!$this->glossaryFacade->hasTranslation($keyName, $localeTransfer)) {
            return null;
        }

        $translationTransfer = $this->glossaryFacade->getTranslation($keyName, $localeTransfer);

        return $translationTransfer->getValue();
    }
}
