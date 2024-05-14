<?php

namespace Spryker\Zed\OmsConvertDrawIo\Business;

use Generated\Shared\Transfer\OmsConvertRequestTransfer;

interface OmsConvertDrawIoFacadeInterface
{
    public function convert(OmsConvertRequestTransfer $convertRequestTransfer): string;
}
