<?php

namespace Spryker\Zed\OmsConvertDrawIo\Business;

use Generated\Shared\Transfer\OmsConvertRequestTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method OmsConvertDrawIoBusinessFactory getFactory()
 */
class OmsConvertDrawIoFacade extends AbstractFacade implements OmsConvertDrawIoFacadeInterface
{
    public function convert(OmsConvertRequestTransfer $convertRequestTransfer): string
    {
        return $this->getFactory()
            ->createOmsDrawIoConverter()
            ->convert($convertRequestTransfer);
    }
}
