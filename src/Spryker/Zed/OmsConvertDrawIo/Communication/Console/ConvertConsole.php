<?php

namespace Spryker\Zed\OmsConvertDrawIo\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertConsole extends Console
{
    const STYLE_CONDITION = 'rhombus';

    public function configure(): void
    {
        parent::configure();
        $this->setName('oms:convert:drawio');

        $this->addArgument('file', InputArgument::REQUIRED, 'File to convert');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = $input->getArgument('file');
        $xml = simplexml_load_file($fileName);
        /** @var \SimpleXMLElement $item */

        $states = [];
        $transitions = [];
        $events = [];
        $conditions = [];
        $transitionPath = [];

        foreach ($xml->diagram->mxGraphModel->root->mxCell as $item) {
            if ((string)$item['connectable'] === '0') {
                // event
                $events[(string)$item['parent']] = (string)$item['value'];
                continue;
            }
            if ($item['source'] === null && $item['value'] !== null) {
                if (substr((string)$item['style'], 0, strlen(self::STYLE_CONDITION)) === self::STYLE_CONDITION) {
                    // condition
                    $conditions[(string)$item['id']] = (string)$item['value'];
                    continue;
                }

                // state
                $states[(string)$item['id']] = (string)$item['value'];
                continue;
            }
            if ($item['source'] !== null && $item['target'] !== null) {
                // transition
                $transitions[(string)$item['id']] = [
                    'source' => (string)$item['source'],
                    'target' => (string)$item['target']
                ];

                $transitionPath[(string)$item['source']][(string)$item['target']] = (string)$item['id'];
            }
        }

        $statesText = [];
        $transitionsText = [];
        $eventsText = [];

        foreach ($states as $state) {
            $statesText[] = sprintf(
                '<state name="%s" />',
                $state
            );
        }

        foreach ($events as $event) {
            $eventsText[] = sprintf(
                '<event name="%s" />',
                $event
            );
        }

        foreach ($transitions as $transitionId => $transition) {
            if (isset($conditions[$transition['source']])) {
                // condition transition is handled from the source state
                continue;
            }

            $event = '';
            if (isset($events[$transitionId])) {
                $event = sprintf('<event>%s</event>', $events[$transitionId]);
            }

            if (!isset($conditions[$transition['target']])) {
                $target = $states[$transition['target']];

                $transitionsText[] = sprintf(
                    '<transition>
                <source>%s</source>
                <target>%s</target>
                %s
            </transition>',
                    $states[$transition['source']],
                    $target,
                    $event,
                );
                continue;
            }
            $target = $transition['target'];

            $condition = sprintf(' condition="%s"', $conditions[$target]);

            foreach ($transitionPath[$target] as $targetState => $conditionTransitionId) {
                if ($events[$conditionTransitionId] === 'YES') {
                    $transitionsText[] = sprintf(
                        '<transition%s>
                <source>%s</source>
                <target>%s</target>
                %s
            </transition>',
                        $condition,
                        $states[$transition['source']],
                        $states[$targetState],
                        $event,
                    );
                } else {
                    $transitionsText[] = sprintf(
                        '<transition>
                <source>%s</source>
                <target>%s</target>
                %s
            </transition>',
                        $states[$transition['source']],
                        $states[$targetState],
                        $event,
                    );
                }
            }
        }

        file_put_contents($fileName . '-process.xml',
            sprintf(
                '<?xml version="1.0"?>
    <statemachine
            xmlns="spryker:oms-01"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="spryker:oms-01 http://static.spryker.com/oms-01.xsd">

        <process name="%s" main="true">
            <states>
                %s
            </states>

            <transitions>
                %s
            </transitions>

            <events>
                %s
            </events>
        </process>
    </statemachine>',
                $fileName,
                implode("\n", $statesText),
                implode("\n", $transitionsText),
                implode("\n", $eventsText),
            )
        );

        return 0;
    }
}
