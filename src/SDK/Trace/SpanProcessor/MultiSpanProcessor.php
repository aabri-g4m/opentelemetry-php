<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

/**
 * Class SpanMultiProcessor is a SpanProcessor that forwards all events to an
 * array of SpanProcessors.
 */
final class MultiSpanProcessor implements SpanProcessorInterface
{
    /** @var list<SpanProcessorInterface> */
    private array $processors = [];

    private bool $running = true;

    public function __construct(SpanProcessorInterface ...$spanProcessors)
    {
        foreach ($spanProcessors as $processor) {
            $this->addSpanProcessor($processor);
        }
    }

    public function addSpanProcessor(SpanProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    /** @return list<SpanProcessorInterface> */
    public function getSpanProcessors(): array
    {
        return $this->processors;
    }

    /** @inheritDoc */
    public function onStart(ReadWriteSpanInterface $span, ?Context $parentContext = null): void
    {
        foreach ($this->processors as $processor) {
            $processor->onStart($span, $parentContext);
        }
    }

    /** @inheritDoc */
    public function onEnd(ReadableSpanInterface $span): void
    {
        foreach ($this->processors as $processor) {
            $processor->onEnd($span);
        }
    }

    /** @inheritDoc */
    public function shutdown(): bool
    {
        if (!$this->running) {
            return true;
        }

        $result = true;

        foreach ($this->processors as $processor) {
            $result = $result && $processor->shutdown();
        }

        return $result;
    }

    /** @inheritDoc */
    public function forceFlush(): bool
    {
        $result = true;

        foreach ($this->processors as $processor) {
            $result = $result && $processor->forceFlush();
        }

        return $result;
    }
}
