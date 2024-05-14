<?php

namespace Spryker\Zed\OmsConvertDrawIo\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\OmsConvertDrawIo\Business\Model\OmsDrawIoConverter;
use Spryker\Zed\OmsConvertDrawIo\Business\Model\OmsDrawIoConverterInterface;

class OmsConvertDrawIoBusinessFactory extends AbstractBusinessFactory
{
    public function createOmsDrawIoConverter(): OmsDrawIoConverterInterface
    {
        return new OmsDrawIoConverter();
    }
}
