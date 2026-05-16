<?php

namespace Tests\Unit;

use Modules\Property\Support\PropertyMetricsFromUnitTypes;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PropertyMetricsFromUnitTypesTest extends TestCase
{
    #[Test]
    public function it_uses_minimum_price_and_extreme_areas_across_unit_types(): void
    {
        $metrics = PropertyMetricsFromUnitTypes::fromRows([
            ['price' => 500000, 'min_area' => 80, 'max_area' => 120],
            ['price' => 350000, 'min_area' => 55, 'max_area' => 95],
            ['price' => 420000, 'min_area' => 60, 'max_area' => 150],
        ]);

        $this->assertSame(350000.0, $metrics['price']);
        $this->assertSame(55.0, $metrics['min_area']);
        $this->assertSame(150.0, $metrics['max_area']);
    }

    #[Test]
    public function it_returns_defaults_when_no_unit_types_have_metrics(): void
    {
        $metrics = PropertyMetricsFromUnitTypes::fromRows([
            ['name' => 'Studio'],
        ]);

        $this->assertSame(0.0, $metrics['price']);
        $this->assertNull($metrics['min_area']);
        $this->assertNull($metrics['max_area']);
    }

    #[Test]
    public function it_ignores_empty_metric_values(): void
    {
        $metrics = PropertyMetricsFromUnitTypes::fromRows([
            ['price' => '', 'min_area' => null, 'max_area' => 90],
            ['price' => 200000, 'min_area' => 45, 'max_area' => ''],
        ]);

        $this->assertSame(200000.0, $metrics['price']);
        $this->assertSame(45.0, $metrics['min_area']);
        $this->assertSame(90.0, $metrics['max_area']);
    }
}
