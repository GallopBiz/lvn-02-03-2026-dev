<?php

namespace App\Academic;

class AcademicHelper
{
    /**
     * Map of numbers, kindergarten names, and variations to Roman representations.
     */
    private const ROMAN_MAP = [
        '1' => 'I',
        '01' => 'I',
        '2' => 'II',
        '02' => 'II',
        '3' => 'III',
        '03' => 'III',
        '4' => 'IV',
        '04' => 'IV',
        '5' => 'V',
        '05' => 'V',
        '6' => 'VI',
        '06' => 'VI',
        '7' => 'VII',
        '07' => 'VII',
        '8' => 'VIII',
        '08' => 'VIII',
        '9' => 'IX',
        '09' => 'IX',
        '10' => 'X',
        '11' => 'XI',
        '12' => 'XII',
        'i' => 'I',
        'ii' => 'II',
        'iii' => 'III',
        'iv' => 'IV',
        'v' => 'V',
        'vi' => 'VI',
        'vii' => 'VII',
        'viii' => 'VIII',
        'ix' => 'IX',
        'x' => 'X',
        'xi' => 'XI',
        'xii' => 'XII',
        'kg1' => 'KG-I',
        'kg01' => 'KG-I',
        'kgi' => 'KG-I',
        'kg2' => 'KG-II',
        'kg02' => 'KG-II',
        'kgii' => 'KG-II',
        'nursery' => 'Nursery',
    ];

    /**
     * Convert a class string or number to Roman numeral format for printed documents.
     *
     * @param string|null $className
     * @return string
     */
    public static function toRoman(?string $className): string
    {
        $raw = trim((string) $className);
        if ($raw === '') {
            return '';
        }

        // If it contains separator like " - " or "-" separating class and section
        if (preg_match('/^(.*?)\s*-\s*([a-zA-Z0-9]+)$/', $raw, $splitMatch)) {
            $classPart = self::toRoman($splitMatch[1]);
            return trim($classPart . ' - ' . $splitMatch[2]);
        }

        $cleaned = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $raw));
        if (isset(self::ROMAN_MAP[$cleaned])) {
            return self::ROMAN_MAP[$cleaned];
        }

        // If string starts with "class" or similar prefix e.g. "Class 01", "Class-1"
        if (preg_match('/^(class\s*[-:]?\s*)(0?[1-9]|1[0-2])\b(.*)$/i', $raw, $m)) {
            $num = (string) (int) $m[2];
            return trim($m[1] . (self::ROMAN_MAP[$num] ?? $m[2]) . $m[3]);
        }

        // If whole string is purely 01-12 with suffix like "1st", "2nd", "10th"
        if (preg_match('/^0*(1[0-2]|[1-9])(?:st|nd|rd|th)?$/i', $raw, $m)) {
            $num = (string) (int) $m[1];
            return self::ROMAN_MAP[$num] ?? $raw;
        }

        // Standalone number within string e.g. "01 A"
        if (preg_match('/(^|\b|\D)0*(1[0-2]|[1-9])(\b|\D|$)/', $raw, $m)) {
            $num = (string) (int) $m[2];
            if (isset(self::ROMAN_MAP[$num])) {
                return preg_replace('/(^|\b|\D)0*(1[0-2]|[1-9])(\b|\D|$)/', '${1}' . self::ROMAN_MAP[$num] . '${3}', $raw, 1);
            }
        }

        return $raw;
    }

    /**
     * Format class and optional section into Roman numeral display string for printed documents.
     * e.g. ('01', 'A') -> 'I - A', ('01 - A') -> 'I - A', ('01') -> 'I'
     *
     * @param string|null $className
     * @param string|null $sectionName
     * @return string
     */
    public static function formatClassSection(?string $className, ?string $sectionName = null): string
    {
        $classStr = trim((string) $className);
        $sectionStr = trim((string) $sectionName);

        if ($classStr === '' && $sectionStr === '') {
            return '';
        }

        if ($sectionStr !== '') {
            // If className already contains the section, strip it before converting
            if (preg_match('/^(.*?)\s*-\s*' . preg_quote($sectionStr, '/') . '$/i', $classStr, $m)) {
                $classStr = trim($m[1]);
            }
            $romanClass = self::toRoman($classStr);
            return trim($romanClass . ($sectionStr !== '' ? ' - ' . $sectionStr : ''));
        }

        return self::toRoman($classStr);
    }

    /**
     * Convert a Roman numeral string (e.g. 'XI', 'VI', 'Class XI') back to numeric class string (e.g. '11', '6').
     * Returns null if not a recognized Roman numeral.
     *
     * @param string|null $roman
     * @return string|null
     */
    public static function fromRoman(?string $roman): ?string
    {
        $raw = trim((string) $roman);
        if ($raw === '') {
            return null;
        }

        $clean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $raw));
        $clean = preg_replace('/^class/i', '', $clean);

        $reverseMap = [
            'i' => '1',
            'ii' => '2',
            'iii' => '3',
            'iv' => '4',
            'v' => '5',
            'vi' => '6',
            'vii' => '7',
            'viii' => '8',
            'ix' => '9',
            'x' => '10',
            'xi' => '11',
            'xii' => '12',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10',
            '11' => '11',
            '12' => '12',
        ];

        return $reverseMap[$clean] ?? null;
    }
}

