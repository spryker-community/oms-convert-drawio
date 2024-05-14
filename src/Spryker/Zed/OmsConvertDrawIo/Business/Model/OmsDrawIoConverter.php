<?php

namespace Spryker\Zed\OmsConvertDrawIo\Business\Model;

use Generated\Shared\Transfer\OmsConvertRequestTransfer;

class OmsDrawIoConverter implements OmsDrawIoConverterInterface
{
    protected const STYLE_CONDITION = 'rhombus';
    protected const DEFAULT_PROCESS_NAME = 'process1';
    protected const CONDITION_TRUE = 'YES';
    protected const CONDITION_FALSE = 'NO';
    protected const EVENT_TYPE_ON_ENTER = 'ONENTER';
    protected const EVENT_TYPE_MANUAL = 'MANUAL';

    public function convert(OmsConvertRequestTransfer $convertRequestTransfer): string
    {
        $xml = $this->getXmlFromRequest($convertRequestTransfer);

        if (!$xml) {
            return '';
        }

        $states = [];
        $transitions = [];
        $events = [];
        $conditions = [];
        $transitionPath = [];
        $eventsType = [];

        foreach ($xml->diagram->mxGraphModel->root->mxCell as $item) {
            if ((string)$item['connectable'] === '0') {
                // event
                $events[(string)$item['parent']] = $this->getEventName((string)$item['value']);
                $eventsType[(string)$item['parent']] = $this->getEventType((string)$item['value']);
                continue;
            }
            if ($item['source'] === null && $item['value'] !== null) {
                if (substr((string)$item['style'], 0, strlen(self::STYLE_CONDITION)) === self::STYLE_CONDITION) {
                    // condition
                    $conditions[(string)$item['id']] = $this->getConditionName((string)$item['value']);
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
                    'target' => (string)$item['target'],
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

        foreach (array_unique($events) as $eventId=> $event) {
            if ($event === static::CONDITION_TRUE || $event === static::CONDITION_FALSE) {
                continue;
            }

            $eventsText[] = sprintf(
                '<event name="%s" %scommand=""/>',
                $event,
                match($eventsType[$eventId] ?? '') {
                    static::EVENT_TYPE_ON_ENTER => 'onEnter="true" ',
                    static::EVENT_TYPE_MANUAL => 'manual="true" ',
                    default => '',
                },
            );
        }

        foreach ($transitions as $transitionId => $transition) {
            if (isset($conditions[$transition['source']])) {
                // condition transition is handled from the source state
                continue;
            }

            $defaultEvent = '';
            if (isset($events[$transitionId])) {
                $defaultEvent = sprintf('<event>%s</event>', $events[$transitionId]);
            }

            if (!isset($conditions[$transition['target']])) {
                $target = $states[$transition['target']];

                $transitionsText[] = sprintf(
                    '<transition>
                <source>%s</source>
                <target>%s</target>%s
            </transition>',
                    $states[$transition['source']],
                    $target,
                    $defaultEvent ? sprintf("\n\t\t\t\t%s", $defaultEvent) : '',
                );
                continue;
            }
            $target = $transition['target'];

            $condition = sprintf(' condition="%s"', $conditions[$target]);

            foreach ($transitionPath[$target] as $targetState => $conditionTransitionId) {
                if ($events[$conditionTransitionId] === '' || $events[$conditionTransitionId] === self::CONDITION_FALSE) {
                    $transitionsText[] = sprintf(
                        '<transition>
                <source>%s</source>
                <target>%s</target>%s
            </transition>',
                        $states[$transition['source']],
                        $states[$targetState],
                        $defaultEvent ? sprintf("\n\t\t\t\t%s", $defaultEvent) : '',
                    );
                    continue;
                }

                $event = $defaultEvent;
                if ($events[$conditionTransitionId] !== self::CONDITION_TRUE) {
                    $event = $events[$conditionTransitionId];
                }

                $transitionsText[] = sprintf(
                        '<transition%s>
                <source>%s</source>
                <target>%s</target>%s
            </transition>',
                    $condition,
                    $states[$transition['source']],
                    $states[$targetState],
                    $event ? sprintf("\n\t\t\t\t%s", $event) : '',
                );
            }
        }

        return sprintf(
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
                $convertRequestTransfer->getProcessName() ?? self::DEFAULT_PROCESS_NAME,
                implode("\n\t\t\t\t", $statesText),
                implode("\n\t\t\t", $transitionsText),
                implode("\n\t\t\t", $eventsText),
        );
    }

    protected function getEventName(string $eventString): string
    {
        [$eventString, $rest] = explode('<', $eventString, 2);

        return trim($eventString);
    }

    protected function getEventType(string $eventString): string
    {
        $eventString = strtolower($eventString);

        if (strpos($eventString, 'on enter') || strpos($eventString, 'onEnter')) {
            return self::EVENT_TYPE_ON_ENTER;
        }

        if (strpos($eventString, 'manual')) {
            return self::EVENT_TYPE_MANUAL;
        }

        return '';
    }

    protected function getXmlFromRequest(OmsConvertRequestTransfer $convertRequestTransfer): ?\SimpleXMLElement
    {
        if ($convertRequestTransfer->getOriginalContent()) {
            return simplexml_load_string($convertRequestTransfer->getOriginalContent());
        }

        return null;
    }

    protected function getConditionName(string $rawCondition): string
    {
        return trim($rawCondition, '?');
    }
}
