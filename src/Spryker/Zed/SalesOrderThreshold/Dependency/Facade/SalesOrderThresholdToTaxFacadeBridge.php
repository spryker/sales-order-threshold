<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Dependency\Facade;

class SalesOrderThresholdToTaxFacadeBridge implements SalesOrderThresholdToTaxFacadeInterface
{
    /**
     * @var \Spryker\Zed\Tax\Business\TaxFacadeInterface
     */
    protected $taxFacade;

    /**
     * @param \Spryker\Zed\Tax\Business\TaxFacadeInterface $taxFacade
     */
    public function __construct($taxFacade)
    {
        $this->taxFacade = $taxFacade;
    }

    public function getDefaultTaxRate(): float
    {
        return $this->taxFacade->getDefaultTaxRate();
    }

    public function getDefaultTaxCountryIso2Code(): string
    {
        return $this->taxFacade->getDefaultTaxCountryIso2Code();
    }
}
