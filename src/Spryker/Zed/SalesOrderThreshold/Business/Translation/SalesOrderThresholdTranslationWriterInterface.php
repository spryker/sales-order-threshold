<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesOrderThreshold\Business\Translation;

use Generated\Shared\Transfer\SalesOrderThresholdTransfer;

interface SalesOrderThresholdTranslationWriterInterface
{
    public function saveLocalizedMessages(SalesOrderThresholdTransfer $salesOrderThresholdTransfer): SalesOrderThresholdTransfer;

    public function deleteLocalizedMessages(SalesOrderThresholdTransfer $salesOrderThresholdTransfer): void;
}
