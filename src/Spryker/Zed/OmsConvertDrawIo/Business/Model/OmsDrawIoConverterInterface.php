<?php

namespace Spryker\Zed\OmsConvertDrawIo\Business\Model;

use Generated\Shared\Transfer\OmsConvertRequestTransfer;

interface OmsDrawIoConverterInterface
{
    public function convert(OmsConvertRequestTransfer $convertRequestTransfer): string;
}
