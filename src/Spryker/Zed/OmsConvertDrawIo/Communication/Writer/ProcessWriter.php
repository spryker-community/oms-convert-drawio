<?php

namespace Spryker\Zed\OmsConvertDrawIo\Communication\Writer;

use Spryker\Zed\OmsConvertDrawIo\OmsConvertDrawIoConfig;

class ProcessWriter
{
    public function __construct(protected OmsConvertDrawIoConfig $config)
    {
    }

    public function getFullFilename(string $fileName): string
    {
        return $this->config->getProcessDefinitionLocation() .'/'. $fileName;
    }

    public function write(string $fileName, string $content): void
    {
        file_put_contents(
            $fileName,
            $content,
        );
    }
}
