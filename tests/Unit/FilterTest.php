<?php

namespace PrivateBin\Test\Unit;

use PHPUnit\Framework\TestCase;
use PrivateBin\Filter;
use PrivateBin\I18n;

class FilterTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('PUBLIC_PATH')) {
            define('PUBLIC_PATH', dirname(__DIR__, 2));
        }
        I18n::loadTranslations();
    }

    /**
     * @dataProvider validTimeProvider
     */
    public function testFormatHumanReadableTimeWithValidInputs($input, $expected)
    {
        $result = Filter::formatHumanReadableTime($input);
        $this->assertIsString($result);
        $this->assertStringContainsString($expected, $result);
    }

    public function validTimeProvider(): array
    {
        return [
            'une seconde' => ['1 second', 'second'],
            'plusieurs secondes' => ['5 seconds', 'second'],
            'une minute' => ['1 minute', 'minute'],
            'plusieurs minutes' => ['30 minutes', 'minute'],
            'une heure' => ['1 hour', 'hour'],
            'plusieurs heures' => ['24 hours', 'hour'],
            'un jour' => ['1 day', 'day'],
            'plusieurs jours' => ['7 days', 'day'],
            'un mois' => ['1 month', 'month'],
            'plusieurs mois' => ['6 months', 'month'],
            'un an' => ['1 year', 'year'],
            'plusieurs années' => ['10 years', 'year'],
        ];
    }

    public function testFormatHumanReadableTimeWithAbbreviations()
    {
        $result = Filter::formatHumanReadableTime('5 min');
        $this->assertIsString($result);
        $this->assertStringContainsString('minute', $result);

        $result = Filter::formatHumanReadableTime('30 sec');
        $this->assertIsString($result);
        $this->assertStringContainsString('second', $result);
    }

    public function testFormatHumanReadableTimeThrowsExceptionForInvalidInput()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Error parsing time format");
        
        Filter::formatHumanReadableTime('invalid input');
    }

    public function testFormatHumanReadableTimeWithVariousSpacing()
    {
        $result1 = Filter::formatHumanReadableTime('5 minutes');
        $result2 = Filter::formatHumanReadableTime('5minutes');
        $result3 = Filter::formatHumanReadableTime('5  minutes');
        
        $this->assertIsString($result1);
        $this->assertIsString($result2);
        $this->assertIsString($result3);
    }
}
