<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\TaxRateReader;

use Generated\Shared\Transfer\StoreTransfer;

interface TaxRateReaderInterface
{
    public function getSalesOrderThresholdTaxRate(?StoreTransfer $storeTransfer = null): float;
}
