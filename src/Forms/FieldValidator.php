<?php

/**
 * FieldValidator.php
 *
 * Field validation support for AcroForms.
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
 * Field Validator class
 *
 * Provides JavaScript-based validation rules for PDF form fields.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class FieldValidator
{
    /**
     * Validation type constants
     */
    public const TYPE_REQUIRED = 'required';
    public const TYPE_NUMBER = 'number';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_EMAIL = 'email';
    public const TYPE_PHONE = 'phone';
    public const TYPE_DATE = 'date';
    public const TYPE_REGEX = 'regex';
    public const TYPE_RANGE = 'range';
    public const TYPE_LENGTH = 'length';
    public const TYPE_CUSTOM = 'custom';

    /**
     * Target field name
     */
    protected string $fieldName;

    /**
     * Validation rules
     *
     * @var array<array{type: string, params: array<string, mixed>, message: string}>
     */
    protected array $rules = [];

    /**
     * Constructor
     *
     * @param string $fieldName Target field name
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Create a validator for a field
     *
     * @param string $fieldName Field name
     * @return self
     */
    public static function forField(string $fieldName): self
    {
        return new self($fieldName);
    }

    /**
     * Add required validation
     *
     * @param string $message Error message
     * @return self
     */
    public function required(string $message = 'This field is required.'): self
    {
        $this->rules[] = [
            'type' => self::TYPE_REQUIRED,
            'params' => [],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add number validation
     *
     * @param string $message Error message
     * @return self
     */
    public function number(string $message = 'Please enter a valid number.'): self
    {
        $this->rules[] = [
            'type' => self::TYPE_NUMBER,
            'params' => [],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add integer validation
     *
     * @param string $message Error message
     * @return self
     */
    public function integer(string $message = 'Please enter a whole number.'): self
    {
        $this->rules[] = [
            'type' => self::TYPE_INTEGER,
            'params' => [],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add email validation
     *
     * @param string $message Error message
     * @return self
     */
    public function email(string $message = 'Please enter a valid email address.'): self
    {
        $this->rules[] = [
            'type' => self::TYPE_EMAIL,
            'params' => [],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add phone validation
     *
     * @param string $message Error message
     * @return self
     */
    public function phone(string $message = 'Please enter a valid phone number.'): self
    {
        $this->rules[] = [
            'type' => self::TYPE_PHONE,
            'params' => [],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add date validation
     *
     * @param string $format Date format (for display message)
     * @param string $message Error message
     * @return self
     */
    public function date(string $format = 'YYYY-MM-DD', string $message = ''): self
    {
        $this->rules[] = [
            'type' => self::TYPE_DATE,
            'params' => ['format' => $format],
            'message' => $message ?: "Please enter a valid date ($format).",
        ];
        return $this;
    }

    /**
     * Add regex validation
     *
     * @param string $pattern Regular expression pattern
     * @param string $message Error message
     * @return self
     */
    public function regex(string $pattern, string $message = 'Invalid format.'): self
    {
        $this->rules[] = [
            'type' => self::TYPE_REGEX,
            'params' => ['pattern' => $pattern],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add numeric range validation
     *
     * @param float|null $min Minimum value (null for no minimum)
     * @param float|null $max Maximum value (null for no maximum)
     * @param string $message Error message
     * @return self
     */
    public function range(?float $min = null, ?float $max = null, string $message = ''): self
    {
        if ($message === '') {
            if ($min !== null && $max !== null) {
                $message = "Value must be between $min and $max.";
            } elseif ($min !== null) {
                $message = "Value must be at least $min.";
            } elseif ($max !== null) {
                $message = "Value must be at most $max.";
            } else {
                $message = 'Invalid value.';
            }
        }

        $this->rules[] = [
            'type' => self::TYPE_RANGE,
            'params' => ['min' => $min, 'max' => $max],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add length validation
     *
     * @param int|null $min Minimum length (null for no minimum)
     * @param int|null $max Maximum length (null for no maximum)
     * @param string $message Error message
     * @return self
     */
    public function length(?int $min = null, ?int $max = null, string $message = ''): self
    {
        if ($message === '') {
            if ($min !== null && $max !== null) {
                $message = "Length must be between $min and $max characters.";
            } elseif ($min !== null) {
                $message = "Minimum length is $min characters.";
            } elseif ($max !== null) {
                $message = "Maximum length is $max characters.";
            } else {
                $message = 'Invalid length.';
            }
        }

        $this->rules[] = [
            'type' => self::TYPE_LENGTH,
            'params' => ['min' => $min, 'max' => $max],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add custom validation with JavaScript expression
     *
     * @param string $expression JavaScript expression that returns true if valid
     * @param string $message Error message
     * @return self
     */
    public function custom(string $expression, string $message = 'Invalid value.'): self
    {
        $this->rules[] = [
            'type' => self::TYPE_CUSTOM,
            'params' => ['expression' => $expression],
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Get the field name
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Get all validation rules
     *
     * @return array<array{type: string, params: array<string, mixed>, message: string}>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Generate JavaScript validation code
     *
     * @return string JavaScript code for validation action
     */
    public function toJavaScript(): string
    {
        if (empty($this->rules)) {
            return '';
        }

        $fieldRef = "getField('" . addslashes($this->fieldName) . "')";
        $checks = [];

        foreach ($this->rules as $rule) {
            $check = $this->generateRuleCheck($rule);
            if ($check !== '') {
                $checks[] = $check;
            }
        }

        if (empty($checks)) {
            return '';
        }

        $checksCode = implode("\n", $checks);

        return <<<JS
var f = {$fieldRef};
var v = f.value;
var valid = true;
var msg = '';

{$checksCode}

if (!valid) {
    app.alert(msg, 1);
    event.rc = false;
}
JS;
    }

    /**
     * Generate JavaScript for keystroke validation
     *
     * @return string JavaScript code for keystroke action
     */
    public function toKeystrokeJavaScript(): string
    {
        $js = '';

        foreach ($this->rules as $rule) {
            switch ($rule['type']) {
                case self::TYPE_NUMBER:
                    $js .= <<<JS
if (event.change && !/^[0-9.\\-]*$/.test(event.change)) {
    event.rc = false;
}

JS;
                    break;

                case self::TYPE_INTEGER:
                    $js .= <<<JS
if (event.change && !/^[0-9\\-]*$/.test(event.change)) {
    event.rc = false;
}

JS;
                    break;

                case self::TYPE_LENGTH:
                    $max = $rule['params']['max'] ?? null;
                    if ($max !== null) {
                        $js .= <<<JS
if (AFMergeChange(event).length > {$max}) {
    event.rc = false;
}

JS;
                    }
                    break;
            }
        }

        return $js;
    }

    /**
     * Generate JavaScript check for a single rule
     *
     * @param array{type: string, params: array<string, mixed>, message: string} $rule
     * @return string
     */
    protected function generateRuleCheck(array $rule): string
    {
        $message = addslashes($rule['message']);

        switch ($rule['type']) {
            case self::TYPE_REQUIRED:
                return <<<JS
if (valid && (v === '' || v === null || v === undefined)) {
    valid = false;
    msg = '{$message}';
}
JS;

            case self::TYPE_NUMBER:
                return <<<JS
if (valid && v !== '' && isNaN(parseFloat(v))) {
    valid = false;
    msg = '{$message}';
}
JS;

            case self::TYPE_INTEGER:
                return <<<JS
if (valid && v !== '' && (isNaN(parseInt(v)) || parseInt(v) != parseFloat(v))) {
    valid = false;
    msg = '{$message}';
}
JS;

            case self::TYPE_EMAIL:
                return <<<JS
if (valid && v !== '' && !/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(v)) {
    valid = false;
    msg = '{$message}';
}
JS;

            case self::TYPE_PHONE:
                return <<<JS
if (valid && v !== '' && !/^[\\d\\s\\-\\+\\(\\)]+$/.test(v)) {
    valid = false;
    msg = '{$message}';
}
JS;

            case self::TYPE_DATE:
                return <<<JS
if (valid && v !== '') {
    var d = new Date(v);
    if (isNaN(d.getTime())) {
        valid = false;
        msg = '{$message}';
    }
}
JS;

            case self::TYPE_REGEX:
                $pattern = addslashes($rule['params']['pattern']);
                return <<<JS
if (valid && v !== '' && !/{$pattern}/.test(v)) {
    valid = false;
    msg = '{$message}';
}
JS;

            case self::TYPE_RANGE:
                $min = $rule['params']['min'];
                $max = $rule['params']['max'];
                $checks = [];

                if ($min !== null) {
                    $checks[] = "parseFloat(v) < {$min}";
                }
                if ($max !== null) {
                    $checks[] = "parseFloat(v) > {$max}";
                }

                if (empty($checks)) {
                    return '';
                }

                $condition = implode(' || ', $checks);
                return <<<JS
if (valid && v !== '' && ({$condition})) {
    valid = false;
    msg = '{$message}';
}
JS;

            case self::TYPE_LENGTH:
                $min = $rule['params']['min'];
                $max = $rule['params']['max'];
                $checks = [];

                if ($min !== null) {
                    $checks[] = "v.length < {$min}";
                }
                if ($max !== null) {
                    $checks[] = "v.length > {$max}";
                }

                if (empty($checks)) {
                    return '';
                }

                $condition = implode(' || ', $checks);
                return <<<JS
if (valid && v !== '' && ({$condition})) {
    valid = false;
    msg = '{$message}';
}
JS;

            case self::TYPE_CUSTOM:
                $expression = $rule['params']['expression'];
                return <<<JS
if (valid && v !== '' && !({$expression})) {
    valid = false;
    msg = '{$message}';
}
JS;

            default:
                return '';
        }
    }
}
