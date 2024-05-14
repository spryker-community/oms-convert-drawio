<?php

namespace Spryker\Zed\OmsConvertDrawIo;

use Spryker\Shared\OmsConvertDrawIo\OmsConvertDrawIoConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class OmsConvertDrawIoConfig extends AbstractBundleConfig
{
    /**
     * @uses \Spryker\Zed\Oms\OmsConfig::DEFAULT_PROCESS_LOCATION
     */
    public const DEFAULT_PROCESS_LOCATION = APPLICATION_ROOT_DIR . '/config/Zed/oms';

    /**
     * @api
     *
     * @return string
     */
    public function getProcessDefinitionLocation(): string
    {
        $location = $this->get(OmsConvertDrawIoConstants::PROCESS_LOCATION, static::DEFAULT_PROCESS_LOCATION);

        return is_array($location) ? $location[0] : $location;
    }
}
