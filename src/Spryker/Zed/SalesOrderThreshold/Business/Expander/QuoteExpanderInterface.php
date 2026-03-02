<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\Expander;

use Generated\Shared\Transfer\QuoteTransfer;

interface QuoteExpanderInterface
{
    public function expandQuoteWithSalesOrderThresholdValues(QuoteTransfer $quoteTransfer): QuoteTransfer;
}
