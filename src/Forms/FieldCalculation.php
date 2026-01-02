<?php

/**
 * FieldCalculation.php
 *
 * Field calculation support for AcroForms.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Forms;

/**
 * Field Calculation class
 *
 * Provides JavaScript-based calculation formulas for PDF form fields.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class FieldCalculation
{
    /**
     * Calculation type constants
     */
    public const TYPE_SUM = 'sum';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_AVERAGE = 'average';
    public const TYPE_MIN = 'min';
    public const TYPE_MAX = 'max';
    public const TYPE_CUSTOM = 'custom';

    /**
     * Target field name
     */
    protected string $targetField;

    /**
     * Source field names for calculation
     *
     * @var array<string>
     */
    protected array $sourceFields = [];

    /**
     * Calculation type
     */
    protected string $calculationType = self::TYPE_SUM;

    /**
     * Custom calculation expression
     */
    protected string $customExpression = '';

    /**
     * Constructor
     *
     * @param string $targetField Target field name
     */
    public function __construct(string $targetField)
    {
        $this->targetField = $targetField;
    }

    /**
     * Create a SUM calculation
     *
     * @param string $targetField Target field name
     * @param array<string> $sourceFields Source field names to sum
     * @return self
     */
    public static function sum(string $targetField, array $sourceFields): self
    {
        $calc = new self($targetField);
        $calc->sourceFields = $sourceFields;
        $calc->calculationType = self::TYPE_SUM;
        return $calc;
    }

    /**
     * Create a PRODUCT calculation
     *
     * @param string $targetField Target field name
     * @param array<string> $sourceFields Source field names to multiply
     * @return self
     */
    public static function product(string $targetField, array $sourceFields): self
    {
        $calc = new self($targetField);
        $calc->sourceFields = $sourceFields;
        $calc->calculationType = self::TYPE_PRODUCT;
        return $calc;
    }

    /**
     * Create an AVERAGE calculation
     *
     * @param string $targetField Target field name
     * @param array<string> $sourceFields Source field names to average
     * @return self
     */
    public static function average(string $targetField, array $sourceFields): self
    {
        $calc = new self($targetField);
        $calc->sourceFields = $sourceFields;
        $calc->calculationType = self::TYPE_AVERAGE;
        return $calc;
    }

    /**
     * Create a MIN calculation
     *
     * @param string $targetField Target field name
     * @param array<string> $sourceFields Source field names
     * @return self
     */
    public static function min(string $targetField, array $sourceFields): self
    {
        $calc = new self($targetField);
        $calc->sourceFields = $sourceFields;
        $calc->calculationType = self::TYPE_MIN;
        return $calc;
    }

    /**
     * Create a MAX calculation
     *
     * @param string $targetField Target field name
     * @param array<string> $sourceFields Source field names
     * @return self
     */
    public static function max(string $targetField, array $sourceFields): self
    {
        $calc = new self($targetField);
        $calc->sourceFields = $sourceFields;
        $calc->calculationType = self::TYPE_MAX;
        return $calc;
    }

    /**
     * Create a custom calculation with JavaScript expression
     *
     * @param string $targetField Target field name
     * @param string $expression JavaScript expression (use field names as variables)
     * @param array<string> $sourceFields Source field names used in expression
     * @return self
     */
    public static function custom(string $targetField, string $expression, array $sourceFields = []): self
    {
        $calc = new self($targetField);
        $calc->customExpression = $expression;
        $calc->sourceFields = $sourceFields;
        $calc->calculationType = self::TYPE_CUSTOM;
        return $calc;
    }

    /**
     * Get the target field name
     *
     * @return string
     */
    public function getTargetField(): string
    {
        return $this->targetField;
    }

    /**
     * Get source field names
     *
     * @return array<string>
     */
    public function getSourceFields(): array
    {
        return $this->sourceFields;
    }

    /**
     * Get calculation type
     *
     * @return string
     */
    public function getCalculationType(): string
    {
        return $this->calculationType;
    }

    /**
     * Generate JavaScript code for this calculation
     *
     * @return string JavaScript code
     */
    public function toJavaScript(): string
    {
        if ($this->calculationType === self::TYPE_CUSTOM && $this->customExpression !== '') {
            return $this->generateCustomJavaScript();
        }

        return $this->generateBuiltInJavaScript();
    }

    /**
     * Generate JavaScript for built-in calculation types
     *
     * @return string JavaScript code
     */
    protected function generateBuiltInJavaScript(): string
    {
        $fieldRefs = [];
        foreach ($this->sourceFields as $field) {
            $fieldRefs[] = "getField('" . addslashes($field) . "').value";
        }

        $targetRef = "getField('" . addslashes($this->targetField) . "')";

        switch ($this->calculationType) {
            case self::TYPE_SUM:
                $values = implode(', ', $fieldRefs);
                return <<<JS
var values = [{$values}];
var result = 0;
for (var i = 0; i < values.length; i++) {
    var v = parseFloat(values[i]);
    if (!isNaN(v)) result += v;
}
{$targetRef}.value = result;
JS;

            case self::TYPE_PRODUCT:
                $values = implode(', ', $fieldRefs);
                return <<<JS
var values = [{$values}];
var result = 1;
for (var i = 0; i < values.length; i++) {
    var v = parseFloat(values[i]);
    if (!isNaN(v)) result *= v;
}
{$targetRef}.value = result;
JS;

            case self::TYPE_AVERAGE:
                $values = implode(', ', $fieldRefs);
                return <<<JS
var values = [{$values}];
var sum = 0;
var count = 0;
for (var i = 0; i < values.length; i++) {
    var v = parseFloat(values[i]);
    if (!isNaN(v)) { sum += v; count++; }
}
{$targetRef}.value = count > 0 ? sum / count : 0;
JS;

            case self::TYPE_MIN:
                $values = implode(', ', $fieldRefs);
                return <<<JS
var values = [{$values}];
var result = Number.MAX_VALUE;
for (var i = 0; i < values.length; i++) {
    var v = parseFloat(values[i]);
    if (!isNaN(v) && v < result) result = v;
}
{$targetRef}.value = result === Number.MAX_VALUE ? 0 : result;
JS;

            case self::TYPE_MAX:
                $values = implode(', ', $fieldRefs);
                return <<<JS
var values = [{$values}];
var result = Number.MIN_VALUE;
for (var i = 0; i < values.length; i++) {
    var v = parseFloat(values[i]);
    if (!isNaN(v) && v > result) result = v;
}
{$targetRef}.value = result === Number.MIN_VALUE ? 0 : result;
JS;

            default:
                return '';
        }
    }

    /**
     * Generate JavaScript for custom expressions
     *
     * @return string JavaScript code
     */
    protected function generateCustomJavaScript(): string
    {
        $js = '';

        // Create variable references for each source field
        foreach ($this->sourceFields as $field) {
            $varName = $this->fieldNameToVar($field);
            $js .= "var {$varName} = parseFloat(getField('" . addslashes($field) . "').value) || 0;\n";
        }

        // Add the custom expression
        $targetRef = "getField('" . addslashes($this->targetField) . "')";
        $js .= "{$targetRef}.value = {$this->customExpression};\n";

        return $js;
    }

    /**
     * Convert field name to valid JavaScript variable name
     *
     * @param string $fieldName Field name
     * @return string Variable name
     */
    protected function fieldNameToVar(string $fieldName): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $fieldName) ?? $fieldName;
    }
}
