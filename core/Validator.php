<?php

declare(strict_types=1);

namespace Core;

/**
 * Input Validator
 * 
 * Rule-based validation with custom rules, error messages, and localization support.
 */
class Validator
{
    /** @var array<string, mixed> */
    private array $data;
    
    /** @var array<string, string|array> */
    private array $rules;
    
    /** @var array<string, array<string>> */
    private array $errors = [];
    
    /** @var array<string, string> */
    private array $customMessages = [];
    
    /** @var array<string, string> */
    private array $attributeNames = [];
    
    /** @var array<string, callable> */
    private static array $customRules = [];
    
    /** @var array<string, string> Default error messages */
    private array $defaultMessages = [
        'required' => 'The :attribute field is required.',
        'email' => 'The :attribute must be a valid email address.',
        'min' => 'The :attribute must be at least :min characters.',
        'max' => 'The :attribute must not exceed :max characters.',
        'between' => 'The :attribute must be between :min and :max.',
        'numeric' => 'The :attribute must be a number.',
        'integer' => 'The :attribute must be an integer.',
        'string' => 'The :attribute must be a string.',
        'array' => 'The :attribute must be an array.',
        'boolean' => 'The :attribute must be true or false.',
        'confirmed' => 'The :attribute confirmation does not match.',
        'unique' => 'The :attribute has already been taken.',
        'exists' => 'The selected :attribute is invalid.',
        'in' => 'The selected :attribute is invalid.',
        'not_in' => 'The selected :attribute is invalid.',
        'regex' => 'The :attribute format is invalid.',
        'date' => 'The :attribute is not a valid date.',
        'before' => 'The :attribute must be before :date.',
        'after' => 'The :attribute must be after :date.',
        'url' => 'The :attribute must be a valid URL.',
        'alpha' => 'The :attribute may only contain letters.',
        'alpha_num' => 'The :attribute may only contain letters and numbers.',
        'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
        'digits' => 'The :attribute must be :digits digits.',
        'digits_between' => 'The :attribute must be between :min and :max digits.',
        'file' => 'The :attribute must be a file.',
        'image' => 'The :attribute must be an image.',
        'mimes' => 'The :attribute must be a file of type: :values.',
        'size' => 'The :attribute must be :size kilobytes.',
        'max_size' => 'The :attribute must not be greater than :max kilobytes.',
        'phone' => 'The :attribute must be a valid phone number.',
        'password' => 'The :attribute must be at least 8 characters with one uppercase, one lowercase, and one number.',
        'same' => 'The :attribute and :other must match.',
        'different' => 'The :attribute and :other must be different.',
        'nullable' => '',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string|array> $rules
     * @param array<string, string> $messages
     */
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
    }

    /**
     * Validate the data
     * 
     * @return array<string, array<string>>
     */
    public function validate(): array
    {
        $this->errors = [];
        
        foreach ($this->rules as $attribute => $rules) {
            $this->validateAttribute($attribute, $rules);
        }
        
        return $this->errors;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->validate());
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Get validation errors
     * 
     * @return array<string, array<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get validated data (only fields that have rules)
     * 
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        $validated = [];
        
        foreach (array_keys($this->rules) as $attribute) {
            if (array_key_exists($attribute, $this->data)) {
                $validated[$attribute] = $this->data[$attribute];
            }
        }
        
        return $validated;
    }

    /**
     * Set custom attribute names
     * 
     * @param array<string, string> $attributes
     */
    public function setAttributeNames(array $attributes): self
    {
        $this->attributeNames = $attributes;
        return $this;
    }

    /**
     * Validate a single attribute
     * 
     * @param string|array<string> $rules
     */
    private function validateAttribute(string $attribute, string|array $rules): void
    {
        $rules = is_string($rules) ? explode('|', $rules) : $rules;
        $value = $this->getValue($attribute);
        
        // Check if field is nullable
        if (in_array('nullable', $rules, true)) {
            if ($value === null || $value === '') {
                return;
            }
        }
        
        foreach ($rules as $rule) {
            if ($rule === 'nullable') {
                continue;
            }
            
            $this->applyRule($attribute, $value, $rule);
        }
    }

    /**
     * Get value from data (supports dot notation)
     */
    private function getValue(string $attribute): mixed
    {
        if (array_key_exists($attribute, $this->data)) {
            return $this->data[$attribute];
        }
        
        // Support dot notation
        $keys = explode('.', $attribute);
        $value = $this->data;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        
        return $value;
    }

    /**
     * Apply a validation rule
     */
    private function applyRule(string $attribute, mixed $value, string $rule): void
    {
        // Parse rule and parameters
        $parameters = [];
        
        if (str_contains($rule, ':')) {
            [$rule, $paramString] = explode(':', $rule, 2);
            $parameters = explode(',', $paramString);
        }
        
        // Check for custom rule
        if (isset(self::$customRules[$rule])) {
            if (!self::$customRules[$rule]($value, $parameters, $attribute, $this->data)) {
                $this->addError($attribute, $rule, $parameters);
            }
            return;
        }
        
        // Built-in rules
        $method = 'validate' . str_replace('_', '', ucwords($rule, '_'));
        
        if (method_exists($this, $method)) {
            if (!$this->$method($value, $parameters, $attribute)) {
                $this->addError($attribute, $rule, $parameters);
            }
        }
    }

    /**
     * Add an error
     * 
     * @param array<string> $parameters
     */
    private function addError(string $attribute, string $rule, array $parameters = []): void
    {
        $message = $this->getMessage($attribute, $rule);
        
        // Replace placeholders
        $message = str_replace(':attribute', $this->getAttributeName($attribute), $message);
        
        if (isset($parameters[0])) {
            $message = str_replace(':min', $parameters[0], $message);
            $message = str_replace(':max', $parameters[0], $message);
            $message = str_replace(':size', $parameters[0], $message);
            $message = str_replace(':digits', $parameters[0], $message);
            $message = str_replace(':date', $parameters[0], $message);
            $message = str_replace(':values', implode(', ', $parameters), $message);
            $message = str_replace(':other', $parameters[0], $message);
        }
        
        if (isset($parameters[1])) {
            $message = str_replace(':max', $parameters[1], $message);
        }
        
        $this->errors[$attribute][] = $message;
    }

    /**
     * Get error message for a rule
     */
    private function getMessage(string $attribute, string $rule): string
    {
        // Check for custom message
        $customKey = "{$attribute}.{$rule}";
        
        if (isset($this->customMessages[$customKey])) {
            return $this->customMessages[$customKey];
        }
        
        if (isset($this->customMessages[$rule])) {
            return $this->customMessages[$rule];
        }
        
        return $this->defaultMessages[$rule] ?? "The :attribute field is invalid.";
    }

    /**
     * Get attribute display name
     */
    private function getAttributeName(string $attribute): string
    {
        if (isset($this->attributeNames[$attribute])) {
            return $this->attributeNames[$attribute];
        }
        
        return str_replace('_', ' ', $attribute);
    }

    // ===== Validation Rules =====

    protected function validateRequired(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        
        if (is_string($value) && trim($value) === '') {
            return false;
        }
        
        if (is_array($value) && empty($value)) {
            return false;
        }
        
        return true;
    }

    protected function validateEmail(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateMin(mixed $value, array $parameters): bool
    {
        $min = (int) ($parameters[0] ?? 0);
        
        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }
        
        if (is_numeric($value)) {
            return $value >= $min;
        }
        
        if (is_array($value)) {
            return count($value) >= $min;
        }
        
        return false;
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateMax(mixed $value, array $parameters): bool
    {
        $max = (int) ($parameters[0] ?? 0);
        
        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }
        
        if (is_numeric($value)) {
            return $value <= $max;
        }
        
        if (is_array($value)) {
            return count($value) <= $max;
        }
        
        return false;
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateBetween(mixed $value, array $parameters): bool
    {
        $min = (int) ($parameters[0] ?? 0);
        $max = (int) ($parameters[1] ?? 0);
        
        if (is_string($value)) {
            $length = mb_strlen($value);
            return $length >= $min && $length <= $max;
        }
        
        if (is_numeric($value)) {
            return $value >= $min && $value <= $max;
        }
        
        return false;
    }

    protected function validateNumeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    protected function validateInteger(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateString(mixed $value): bool
    {
        return is_string($value);
    }

    protected function validateArray(mixed $value): bool
    {
        return is_array($value);
    }

    protected function validateBoolean(mixed $value): bool
    {
        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];
        return in_array($value, $acceptable, true);
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateConfirmed(mixed $value, array $parameters, string $attribute): bool
    {
        $confirmationKey = $attribute . '_confirmation';
        return isset($this->data[$confirmationKey]) && $value === $this->data[$confirmationKey];
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateIn(mixed $value, array $parameters): bool
    {
        return in_array($value, $parameters, true);
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateNotIn(mixed $value, array $parameters): bool
    {
        return !in_array($value, $parameters, true);
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateRegex(mixed $value, array $parameters): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        return preg_match($parameters[0], $value) === 1;
    }

    protected function validateDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        return strtotime($value) !== false;
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateBefore(mixed $value, array $parameters): bool
    {
        if (!$this->validateDate($value)) {
            return false;
        }
        
        return strtotime($value) < strtotime($parameters[0]);
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateAfter(mixed $value, array $parameters): bool
    {
        if (!$this->validateDate($value)) {
            return false;
        }
        
        return strtotime($value) > strtotime($parameters[0]);
    }

    protected function validateUrl(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateAlpha(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value) === 1;
    }

    protected function validateAlphaNum(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[\pL\pM\pN]+$/u', $value) === 1;
    }

    protected function validateAlphaDash(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[\pL\pM\pN_-]+$/u', $value) === 1;
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateDigits(mixed $value, array $parameters): bool
    {
        return is_string($value) && strlen($value) === (int) $parameters[0] && ctype_digit($value);
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateDigitsBetween(mixed $value, array $parameters): bool
    {
        if (!is_string($value) || !ctype_digit($value)) {
            return false;
        }
        
        $length = strlen($value);
        return $length >= (int) $parameters[0] && $length <= (int) $parameters[1];
    }

    protected function validatePhone(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        // Nepal phone number validation
        return preg_match('/^(\+977)?[0-9]{10}$/', preg_replace('/[\s-]/', '', $value)) === 1;
    }

    protected function validatePassword(mixed $value): bool
    {
        if (!is_string($value) || strlen($value) < 8) {
            return false;
        }
        
        // At least one uppercase, one lowercase, one number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $value) === 1;
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateSame(mixed $value, array $parameters, string $attribute): bool
    {
        $other = $parameters[0] ?? '';
        return isset($this->data[$other]) && $value === $this->data[$other];
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateDifferent(mixed $value, array $parameters, string $attribute): bool
    {
        $other = $parameters[0] ?? '';
        return !isset($this->data[$other]) || $value !== $this->data[$other];
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateUnique(mixed $value, array $parameters, string $attribute): bool
    {
        // Format: unique:table,column,except_id
        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? $attribute;
        $exceptId = $parameters[2] ?? null;
        
        if (empty($table)) {
            return true;
        }
        
        $app = Application::getInstance();
        
        if ($app === null) {
            return true;
        }
        
        $query = $app->db()->table($table)->where($column, $value);
        
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
        
        return !$query->exists();
    }

    /**
     * @param array<string> $parameters
     */
    protected function validateExists(mixed $value, array $parameters, string $attribute): bool
    {
        // Format: exists:table,column
        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? $attribute;
        
        if (empty($table)) {
            return true;
        }
        
        $app = Application::getInstance();
        
        if ($app === null) {
            return true;
        }
        
        return $app->db()->table($table)->where($column, $value)->exists();
    }

    /**
     * Register a custom validation rule
     */
    public static function extend(string $rule, callable $callback): void
    {
        self::$customRules[$rule] = $callback;
    }

    /**
     * Quick validation helper
     * 
     * @param array<string, mixed> $data
     * @param array<string, string|array> $rules
     * @param array<string, string> $messages
     * @return array<string, array<string>>
     */
    public static function make(array $data, array $rules, array $messages = []): array
    {
        $validator = new self($data, $rules, $messages);
        return $validator->validate();
    }
}
