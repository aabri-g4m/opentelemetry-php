<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Resource;

use OpenTelemetry\SDK\Resource\ResourceConstants;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Attribute;
use OpenTelemetry\SDK\Trace\Attributes;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
    public function testEmptyResource(): void
    {
        $resource = ResourceInfo::emptyResource();
        $this->assertEmpty($resource->getAttributes());
    }

    public function testGetAttributes(): void
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');
        $resource = ResourceInfo::create($attributes);

        /** @var Attribute $name */
        $name = $resource->getAttributes()->getAttribute('name');
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        $attributes->setAttribute(ResourceConstants::TELEMETRY_SDK_NAME, 'opentelemetry');
        $attributes->setAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE, 'php');
        $attributes->setAttribute(ResourceConstants::TELEMETRY_SDK_VERSION, 'dev');

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertSame('opentelemetry', $sdkname->getValue());
        $this->assertSame('php', $sdklanguage->getValue());
        $this->assertSame('dev', $sdkversion->getValue());
        $this->assertSame('test', $name->getValue());
    }

    /**
     * @test
     */
    public function testDefaultResource()
    {
        $attributes = new Attributes(
            [
                ResourceConstants::TELEMETRY_SDK_NAME => 'opentelemetry',
                ResourceConstants::TELEMETRY_SDK_LANGUAGE => 'php',
                ResourceConstants::TELEMETRY_SDK_VERSION => 'dev',
            ]
        );
        $resource = ResourceInfo::create(new Attributes());
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());
    }

    /**
     * @test
     */
    public function testMerge()
    {
        $primary = ResourceInfo::create(new Attributes(['name' => 'primary', 'empty' => '']));
        $secondary = ResourceInfo::create(new Attributes(['version' => '1.0.0', 'empty' => 'value']));
        $result = ResourceInfo::merge($primary, $secondary);

        /** @var Attribute $name */
        $name = $result->getAttributes()->getAttribute('name');
        /** @var Attribute $version */
        $version = $result->getAttributes()->getAttribute('version');
        /** @var Attribute $empty */
        $empty = $result->getAttributes()->getAttribute('empty');

        $this->assertCount(6, $result->getAttributes());
        $this->assertEquals('primary', $name->getValue());
        $this->assertEquals('1.0.0', $version->getValue());
        $this->assertEquals('value', $empty->getValue());
    }

    /**
     * @test
     */
    public function testImmutableCreate()
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');
        $attributes->setAttribute('version', '1.0.0');

        $resource = ResourceInfo::create($attributes);

        $attributes->setAttribute('version', '2.0.0');

        /** @var Attribute $version */
        $version = $resource->getAttributes()->getAttribute('version');

        $this->assertEquals('1.0.0', $version->getValue());
    }
}
