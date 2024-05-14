<?php

namespace Spryker\Zed\OmsConvertDrawIo\Communication\Console;

use Generated\Shared\Transfer\OmsConvertRequestTransfer;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Spryker\Zed\OmsConvertDrawIo\Business\OmsConvertDrawIoFacade;
use Spryker\Zed\OmsConvertDrawIo\Communication\OmsConvertDrawIoCommunicationFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method OmsConvertDrawIoFacade getFacade()
 * @method OmsConvertDrawIoCommunicationFactory getFactory()
 */
class ConvertConsole extends Console
{
    public function configure(): void
    {
        parent::configure();
        $this->setName('oms:convert:drawio');

        $this->addArgument('file', InputArgument::REQUIRED, 'Filename to convert');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = $input->getArgument('file');

        if (!$fileName || !file_exists($fileName)) {
            $output->writeln('<fg=red>No file to convert. Aborting.</>');

            return static::CODE_ERROR;
        }

        $processName = basename($fileName, '.xml');
        $targetFilename = $this->getFactory()->createProcessWriter()
            ->getFullFilename($processName . '.xml');

        if (file_exists($targetFilename)) {
            $output->writeln('<fg=red>Target file already exists. Aborting.</>');

            return static::CODE_ERROR;
        }

        $processContent = $this->getFacade()->convert(
            (new OmsConvertRequestTransfer())
                ->setOriginalContent(file_get_contents($fileName))
                ->setProcessName($processName)
        );

        $this->getFactory()->createProcessWriter()
            ->write(
                $targetFilename,
                $processContent,
            );

        $output->writeln(sprintf('Process "%s" was written in %s.', $processName, $targetFilename));
        return static::CODE_SUCCESS;
    }
}
