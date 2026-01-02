<?php

/**
 * ConditionalVisibility.php
 *
 * Conditional visibility support for AcroForms.
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
 * Conditional Visibility class
 *
 * Provides JavaScript-based conditional show/hide logic for PDF form fields.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class ConditionalVisibility
{
    /**
     * Comparison operators
     */
    public const OP_EQUALS = 'eq';
    public const OP_NOT_EQUALS = 'ne';
    public const OP_GREATER_THAN = 'gt';
    public const OP_LESS_THAN = 'lt';
    public const OP_GREATER_OR_EQUAL = 'ge';
    public const OP_LESS_OR_EQUAL = 'le';
    public const OP_CONTAINS = 'contains';
    public const OP_NOT_CONTAINS = 'not_contains';
    public const OP_STARTS_WITH = 'starts_with';
    public const OP_ENDS_WITH = 'ends_with';
    public const OP_IS_EMPTY = 'empty';
    public const OP_IS_NOT_EMPTY = 'not_empty';
    public const OP_CHECKED = 'checked';
    public const OP_UNCHECKED = 'unchecked';

    /**
     * Visibility states
     */
    public const VISIBLE = 'visible';
    public const HIDDEN = 'hidden';
    public const NO_PRINT = 'noPrint';

    /**
     * Target fields to control
     *
     * @var array<string>
     */
    protected array $targetFields = [];

    /**
     * Conditions for visibility
     *
     * @var array<array{field: string, operator: string, value: mixed, logic: string}>
     */
    protected array $conditions = [];

    /**
     * Visibility state when conditions are met
     */
    protected string $showState = self::VISIBLE;

    /**
     * Visibility state when conditions are not met
     */
    protected string $hideState = self::HIDDEN;

    /**
     * Constructor
     *
     * @param string|array<string> $targetFields Target field name(s)
     */
    public function __construct(string|array $targetFields = [])
    {
        if (is_string($targetFields)) {
            $this->targetFields = [$targetFields];
        } else {
            $this->targetFields = $targetFields;
        }
    }

    /**
     * Create visibility rule for field(s)
     *
     * @param string|array<string> $targetFields Target field name(s)
     * @return self
     */
    public static function forFields(string|array $targetFields): self
    {
        return new self($targetFields);
    }

    /**
     * Add a target field
     *
     * @param string $fieldName Field name
     * @return self
     */
    public function addTarget(string $fieldName): self
    {
        $this->targetFields[] = $fieldName;
        return $this;
    }

    /**
     * Show when condition is met
     *
     * @param string $field Source field name
     * @param string $operator Comparison operator
     * @param mixed $value Comparison value
     * @return self
     */
    public function showWhen(string $field, string $operator, mixed $value = null): self
    {
        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'logic' => 'AND',
        ];
        return $this;
    }

    /**
     * Add OR condition
     *
     * @param string $field Source field name
     * @param string $operator Comparison operator
     * @param mixed $value Comparison value
     * @return self
     */
    public function orWhen(string $field, string $operator, mixed $value = null): self
    {
        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'logic' => 'OR',
        ];
        return $this;
    }

    /**
     * Add AND condition
     *
     * @param string $field Source field name
     * @param string $operator Comparison operator
     * @param mixed $value Comparison value
     * @return self
     */
    public function andWhen(string $field, string $operator, mixed $value = null): self
    {
        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'logic' => 'AND',
        ];
        return $this;
    }

    /**
     * Show when equals value
     *
     * @param string $field Source field name
     * @param mixed $value Expected value
     * @return self
     */
    public function showWhenEquals(string $field, mixed $value): self
    {
        return $this->showWhen($field, self::OP_EQUALS, $value);
    }

    /**
     * Show when not equals value
     *
     * @param string $field Source field name
     * @param mixed $value Value to not equal
     * @return self
     */
    public function showWhenNotEquals(string $field, mixed $value): self
    {
        return $this->showWhen($field, self::OP_NOT_EQUALS, $value);
    }

    /**
     * Show when field is not empty
     *
     * @param string $field Source field name
     * @return self
     */
    public function showWhenNotEmpty(string $field): self
    {
        return $this->showWhen($field, self::OP_IS_NOT_EMPTY);
    }

    /**
     * Show when field is empty
     *
     * @param string $field Source field name
     * @return self
     */
    public function showWhenEmpty(string $field): self
    {
        return $this->showWhen($field, self::OP_IS_EMPTY);
    }

    /**
     * Show when checkbox/radio is checked
     *
     * @param string $field Source field name
     * @return self
     */
    public function showWhenChecked(string $field): self
    {
        return $this->showWhen($field, self::OP_CHECKED);
    }

    /**
     * Show when checkbox/radio is unchecked
     *
     * @param string $field Source field name
     * @return self
     */
    public function showWhenUnchecked(string $field): self
    {
        return $this->showWhen($field, self::OP_UNCHECKED);
    }

    /**
     * Show when value is greater than
     *
     * @param string $field Source field name
     * @param float $value Comparison value
     * @return self
     */
    public function showWhenGreaterThan(string $field, float $value): self
    {
        return $this->showWhen($field, self::OP_GREATER_THAN, $value);
    }

    /**
     * Show when value is less than
     *
     * @param string $field Source field name
     * @param float $value Comparison value
     * @return self
     */
    public function showWhenLessThan(string $field, float $value): self
    {
        return $this->showWhen($field, self::OP_LESS_THAN, $value);
    }

    /**
     * Set visibility to hide instead of show
     *
     * @return self
     */
    public function hideInsteadOfShow(): self
    {
        $this->showState = self::HIDDEN;
        $this->hideState = self::VISIBLE;
        return $this;
    }

    /**
     * Set hidden state to no-print (visible but doesn't print)
     *
     * @return self
     */
    public function useNoPrintForHidden(): self
    {
        if ($this->hideState === self::HIDDEN) {
            $this->hideState = self::NO_PRINT;
        }
        return $this;
    }

    /**
     * Get target field names
     *
     * @return array<string>
     */
    public function getTargetFields(): array
    {
        return $this->targetFields;
    }

    /**
     * Get source field names used in conditions
     *
     * @return array<string>
     */
    public function getSourceFields(): array
    {
        $fields = [];
        foreach ($this->conditions as $condition) {
            $fields[] = $condition['field'];
        }
        return array_unique($fields);
    }

    /**
     * Generate JavaScript code for visibility control
     *
     * @return string JavaScript code
     */
    public function toJavaScript(): string
    {
        if (empty($this->targetFields) || empty($this->conditions)) {
            return '';
        }

        $conditionsJs = $this->generateConditionCode();
        $targetCode = $this->generateTargetCode();

        return <<<JS
(function() {
    var conditionMet = {$conditionsJs};

    {$targetCode}
})();
JS;
    }

    /**
     * Generate JavaScript for field change action
     *
     * @return string JavaScript code
     */
    public function toChangeJavaScript(): string
    {
        return $this->toJavaScript();
    }

    /**
     * Generate condition evaluation code
     *
     * @return string JavaScript expression
     */
    protected function generateConditionCode(): string
    {
        if (empty($this->conditions)) {
            return 'true';
        }

        $parts = [];
        $isFirst = true;

        foreach ($this->conditions as $condition) {
            $check = $this->generateSingleCondition($condition);

            if ($isFirst) {
                $parts[] = $check;
                $isFirst = false;
            } else {
                $logic = $condition['logic'] === 'OR' ? '||' : '&&';
                $parts[] = "{$logic} {$check}";
            }
        }

        return '(' . implode(' ', $parts) . ')';
    }

    /**
     * Generate code for a single condition
     *
     * @param array{field: string, operator: string, value: mixed, logic: string} $condition
     * @return string JavaScript expression
     */
    protected function generateSingleCondition(array $condition): string
    {
        $fieldRef = "getField('" . addslashes($condition['field']) . "').value";
        $value = $condition['value'];

        return match ($condition['operator']) {
            self::OP_EQUALS => is_string($value)
                ? "({$fieldRef} === '" . addslashes($value) . "')"
                : "({$fieldRef} == {$value})",

            self::OP_NOT_EQUALS => is_string($value)
                ? "({$fieldRef} !== '" . addslashes($value) . "')"
                : "({$fieldRef} != {$value})",

            self::OP_GREATER_THAN => "(parseFloat({$fieldRef}) > {$value})",

            self::OP_LESS_THAN => "(parseFloat({$fieldRef}) < {$value})",

            self::OP_GREATER_OR_EQUAL => "(parseFloat({$fieldRef}) >= {$value})",

            self::OP_LESS_OR_EQUAL => "(parseFloat({$fieldRef}) <= {$value})",

            self::OP_CONTAINS => "({$fieldRef}.indexOf('" . addslashes((string) $value) . "') !== -1)",

            self::OP_NOT_CONTAINS => "({$fieldRef}.indexOf('" . addslashes((string) $value) . "') === -1)",

            self::OP_STARTS_WITH => "({$fieldRef}.indexOf('" . addslashes((string) $value) . "') === 0)",

            self::OP_ENDS_WITH => "({$fieldRef}.slice(-" . strlen((string) $value) . ") === '" . addslashes((string) $value) . "')",

            self::OP_IS_EMPTY => "({$fieldRef} === '' || {$fieldRef} === null || {$fieldRef} === undefined)",

            self::OP_IS_NOT_EMPTY => "({$fieldRef} !== '' && {$fieldRef} !== null && {$fieldRef} !== undefined)",

            self::OP_CHECKED => "(getField('" . addslashes($condition['field']) . "').isBoxChecked(0))",

            self::OP_UNCHECKED => "(!getField('" . addslashes($condition['field']) . "').isBoxChecked(0))",

            default => 'true',
        };
    }

    /**
     * Generate target field visibility code
     *
     * @return string JavaScript code
     */
    protected function generateTargetCode(): string
    {
        $showDisplay = $this->getDisplayValue($this->showState);
        $hideDisplay = $this->getDisplayValue($this->hideState);

        $lines = [];
        foreach ($this->targetFields as $field) {
            $fieldRef = "getField('" . addslashes($field) . "')";
            $lines[] = "{$fieldRef}.display = conditionMet ? {$showDisplay} : {$hideDisplay};";
        }

        return implode("\n    ", $lines);
    }

    /**
     * Get display constant value
     *
     * @param string $state Visibility state
     * @return string JavaScript display constant
     */
    protected function getDisplayValue(string $state): string
    {
        return match ($state) {
            self::VISIBLE => 'display.visible',
            self::HIDDEN => 'display.hidden',
            self::NO_PRINT => 'display.noPrint',
            default => 'display.visible',
        };
    }
}
