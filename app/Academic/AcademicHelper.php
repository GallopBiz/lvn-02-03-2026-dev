<?php

namespace App\Academic;

use App\Models\Classes;
use App\Models\Classname;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    private static ?array $classNameCache = null;

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

    /**
     * Clear cached class names.
     */
    public static function clearClassNameCache(): void
    {
        self::$classNameCache = null;
    }

    /**
     * Normalize a class string or number to a canonical comparable key (e.g. '01', '1', 'Class 1' -> '1').
     *
     * @param string|null $value
     * @return string
     */
    public static function normalizeClassIdentifier(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        // Strip section if present (e.g. '01 - Kautilya' -> '01')
        if (preg_match('/^(.*?)\s*-\s*[a-zA-Z0-9]+$/', $raw, $m)) {
            $raw = trim($m[1]);
        }

        $clean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $raw));
        $clean = preg_replace('/^(class|grade|std|standard)/i', '', $clean);
        $clean = trim($clean);

        // Try from Roman
        $fromRom = self::fromRoman($clean);
        if ($fromRom !== null) {
            return (string) ((int) $fromRom);
        }

        // Numeric leading zero strip (e.g. '01' -> '1')
        if (is_numeric($clean)) {
            return (string) ((int) $clean);
        }

        // Kindergarten normalization
        if (in_array($clean, ['kg1', 'kg01', 'kgi', 'kg-1', 'kg-i'], true)) {
            return 'kg1';
        }
        if (in_array($clean, ['kg2', 'kg02', 'kgii', 'kg-2', 'kg-ii'], true)) {
            return 'kg2';
        }
        if ($clean === 'nursery') {
            return 'nursery';
        }

        return $clean;
    }

    /**
     * Check whether Special Subject Formatting is applicable for a subject for a given class.
     *
     * Rules:
     * 1. If do_not_print_in_main_scholastic_area is false/0, returns false.
     * 2. If no classes are selected in special_formatting_classes, returns false (appears in main table for all classes).
     * 3. If classes are selected, returns true ONLY if the given class matches one of the selected classes.
     *
     * @param mixed $subject
     * @param int|string|null $classId
     * @param string|null $className
     * @return bool
     */
    public static function isSpecialFormattingForClass(mixed $subject, int|string|null $classId = null, ?string $className = null): bool
    {
        if (empty($subject)) {
            return false;
        }

        $doNotPrint = false;
        $classesConfig = null;

        if (is_object($subject)) {
            $doNotPrint = !empty($subject->do_not_print_in_main_scholastic_area);
            $classesConfig = $subject->special_formatting_classes ?? null;
        } elseif (is_array($subject)) {
            $doNotPrint = !empty($subject['do_not_print_in_main_scholastic_area']);
            $classesConfig = $subject['special_formatting_classes'] ?? null;
        }

        if (!$doNotPrint) {
            return false;
        }

        // Decode configured classes
        $selectedClasses = [];
        if (is_array($classesConfig)) {
            $selectedClasses = $classesConfig;
        } elseif (is_string($classesConfig) && trim($classesConfig) !== '') {
            $decoded = json_decode($classesConfig, true);
            if (is_array($decoded)) {
                $selectedClasses = $decoded;
            } else {
                $selectedClasses = array_filter(array_map('trim', explode(',', $classesConfig)));
            }
        }

        // If Special Subject Formatting is checked and no specific classes are restricted,
        // it applies to all classes of this subject.
        if (empty($selectedClasses)) {
            return true;
        }

        // Resolve target class name if not provided
        if (empty($className) && !empty($classId)) {
            try {
                $foundClass = Classes::find($classId) ?: Classname::find($classId);
                if ($foundClass) {
                    $className = $foundClass->class_name;
                }
            } catch (\Throwable $e) {
                // Ignore DB lookup error
            }
        }

        // Load class_name master for ID-to-name lookup
        if (self::$classNameCache === null) {
            try {
                if (Schema::connection('dynamic')->hasTable('class_name')) {
                    self::$classNameCache = DB::connection('dynamic')
                        ->table('class_name')
                        ->where('is_delete', 0)
                        ->pluck('class_name', 'id')
                        ->all();
                } else {
                    self::$classNameCache = [];
                }
            } catch (\Throwable $e) {
                self::$classNameCache = [];
            }
        }

        $targetNormalizedName = self::normalizeClassIdentifier($className);
        $targetIdStr = $classId !== null ? (string) $classId : null;

        foreach ($selectedClasses as $sel) {
            $selStr = trim((string) $sel);
            if ($selStr === '') {
                continue;
            }

            // If selStr matches class_name ID in cache (where form options come from)
            if (isset(self::$classNameCache[$selStr])) {
                if ($targetIdStr !== null && $selStr === $targetIdStr) {
                    return true;
                }
                $selName = self::$classNameCache[$selStr];
                if ($targetNormalizedName !== '' && $targetNormalizedName === self::normalizeClassIdentifier($selName)) {
                    return true;
                }
            } else {
                // Non-cached or direct class name string (e.g. "01", "KG1", "Nursery")
                if ($targetIdStr !== null && $selStr === $targetIdStr) {
                    return true;
                }
                $normalizedSel = self::normalizeClassIdentifier($selStr);
                if ($targetNormalizedName !== '' && $targetNormalizedName === $normalizedSel) {
                    return true;
                }
            }
        }

        return false;
    }
}
