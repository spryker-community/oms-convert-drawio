<?php

namespace Spryker\Zed\OmsConvertDrawIo\Communication;

use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\OmsConvertDrawIo\Communication\Writer\ProcessWriter;

/**
 * @method \Spryker\Zed\OmsConvertDrawIo\OmsConvertDrawIoConfig getConfig()
 */
class OmsConvertDrawIoCommunicationFactory extends AbstractCommunicationFactory
{
    public function createProcessWriter(): ProcessWriter
    {
        return new ProcessWriter($this->getConfig());
    }
}
