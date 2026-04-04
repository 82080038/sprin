<?php
declare(strict_types=1);
/**
 * SPRIN Enhanced Input Validator
 * PHP 8.2+ compatible comprehensive input validation
 */

class InputValidator {
    private array $errors = [];
    private array $rules = [];
    private array $data = [];
    
    public function __construct(array $data = []) {
        $this->data = $data;
    }
    
    public function validate(array $rules, array $data = null): bool {
        $this->errors = [];
        $this->rules = $rules;
        
        if ($data !== null) {
            $this->data = $data;
        }
        
        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            
            foreach ($fieldRules as $rule => $params) {
                if (!$this->validateRule($field, $value, $rule, $params)) {
                    break; // Stop on first error for field
                }
            }
        }
        
        return empty($this->errors);
    }
    
    private function validateRule(string $field, mixed $value, string $rule, mixed $params): bool {
        return match ($rule) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            'min' => $this->validateMin($field, $value, $params),
            'max' => $this->validateMax($field, $value, $params),
            'numeric' => $this->validateNumeric($field, $value),
            'alpha' => $this->validateAlpha($field, $value),
            'alphanumeric' => $this->validateAlphanumeric($field, $value),
            'regex' => $this->validateRegex($field, $value, $params),
            'safe' => $this->validateSafe($field, $value),
            'unique' => $this->validateUnique($field, $value, $params),
            'in' => $this->validateIn($field, $value, $params),
            'date' => $this->validateDate($field, $value),
            'url' => $this->validateUrl($field, $value),
            'phone' => $this->validatePhone($field, $value),
            default => true
        };
    }
    
    private function validateRequired(string $field, mixed $value): bool {
        if ($value === null || $value === '' || $value === []) {
            $this->addError($field, 'Field is required');
            return false;
        }
        return true;
    }
    
    private function validateEmail(string $field, mixed $value): bool {
        if ($value !== null && $value !== '') {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->addError($field, 'Invalid email format');
                return false;
            }
        }
        return true;
    }
    
    private function validateMin(string $field, mixed $value, int $min): bool {
        if ($value !== null && $value !== '') {
            if (is_string($value) && strlen($value) < $min) {
                $this->addError($field, "Minimum length is {$min} characters");
                return false;
            } elseif (is_numeric($value) && $value < $min) {
                $this->addError($field, "Minimum value is {$min}");
                return false;
            }
        }
        return true;
    }
    
    private function validateMax(string $field, mixed $value, int $max): bool {
        if ($value !== null && $value !== '') {
            if (is_string($value) && strlen($value) > $max) {
                $this->addError($field, "Maximum length is {$max} characters");
                return false;
            } elseif (is_numeric($value) && $value > $max) {
                $this->addError($field, "Maximum value is {$max}");
                return false;
            }
        }
        return true;
    }
    
    private function validateNumeric(string $field, mixed $value): bool {
        if ($value !== null && $value !== '') {
            if (!is_numeric($value)) {
                $this->addError($field, 'Field must be numeric');
                return false;
            }
        }
        return true;
    }
    
    private function validateAlpha(string $field, mixed $value): bool {
        if ($value !== null && $value !== '') {
            if (!ctype_alpha($value)) {
                $this->addError($field, 'Field must contain only letters');
                return false;
            }
        }
        return true;
    }
    
    private function validateAlphanumeric(string $field, mixed $value): bool {
        if ($value !== null && $value !== '') {
            if (!ctype_alnum($value)) {
                $this->addError($field, 'Field must contain only letters and numbers');
                return false;
            }
        }
        return true;
    }
    
    private function validateRegex(string $field, mixed $value, string $pattern): bool {
        if ($value !== null && $value !== '') {
            if (!preg_match($pattern, $value)) {
                $this->addError($field, 'Field format is invalid');
                return false;
            }
        }
        return true;
    }
    
    private function validateSafe(string $field, mixed $value): bool {
        if ($value !== null && $value !== '') {
            // Check for dangerous patterns
            $dangerous = ['<script', '</script>', 'javascript:', 'vbscript:', 'onload=', 'onerror='];
            
            foreach ($dangerous as $pattern) {
                if (stripos($value, $pattern) !== false) {
                    $this->addError($field, 'Field contains unsafe content');
                    return false;
                }
            }
        }
        return true;
    }
    
    private function validateUnique(string $field, mixed $value, array $params): bool {
        if ($value !== null && $value !== '') {
            [$table, $column] = $params;
            
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ? AND id != ?");
                $stmt->execute([$value, $this->data['id'] ?? 0]);
                $result = $stmt->fetch();
                
                if ($result['count'] > 0) {
                    $this->addError($field, 'Value must be unique');
                    return false;
                }
            } catch (Exception $e) {
                $this->addError($field, 'Validation failed');
                return false;
            }
        }
        return true;
    }
    
    private function validateIn(string $field, mixed $value, array $allowed): bool {
        if ($value !== null && $value !== '') {
            if (!in_array($value, $allowed, true)) {
                $this->addError($field, 'Invalid value selected');
                return false;
            }
        }
        return true;
    }
    
    private function validateDate(string $field, mixed $value): bool {
        if ($value !== null && $value !== '') {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->addError($field, 'Invalid date format (YYYY-MM-DD)');
                return false;
            }
        }
        return true;
    }
    
    private function validateUrl(string $field, mixed $value): bool {
        if ($value !== null && $value !== '') {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                $this->addError($field, 'Invalid URL format');
                return false;
            }
        }
        return true;
    }
    
    private function validatePhone(string $field, mixed $value): bool {
        if ($value !== null && $value !== '') {
            // Basic phone validation - can be enhanced
            if (!preg_match('/^[\d\s\-\+\(\)]+$/', $value) || strlen($value) < 10) {
                $this->addError($field, 'Invalid phone number format');
                return false;
            }
        }
        return true;
    }
    
    private function addError(string $field, string $message): void {
        $this->errors[$field][] = $message;
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
    
    public function getFirstError(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }
    
    public function hasErrors(): bool {
        return !empty($this->errors);
    }
    
    public function getValidatedData(): array {
        $validated = [];
        
        foreach ($this->data as $field => $value) {
            if (!isset($this->errors[$field])) {
                $validated[$field] = $this->sanitize($value);
            }
        }
        
        return $validated;
    }
    
    private function sanitize(mixed $value): mixed {
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }
    
    // Static helper methods
    public static function validateRequired(array $data, array $required): array {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $errors[$field] = 'Field is required';
            }
        }
        
        return $errors;
    }
    
    public static function sanitizeInput(mixed $input): mixed {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
        
        return $input;
    }
    
    public static function validateNRP(string $nrp): bool {
        // NRP validation for Indonesian police numbers
        return preg_match('/^[A-Z]{2}\d{8}$/', $nrp);
    }
    
    public static function validateName(string $name): bool {
        return preg_match('/^[a-zA-Z\s\.]{2,50}$/', $name);
    }
    
    public static function validateRank(string $rank): bool {
        $validRanks = ['Bripda', 'Brigadir', 'Bripka', 'Aiptu', 'Aipda', 'Ipda', 'Iptu', 'AKP', 'Kompol', 'AKBP', 'Kombes', 'Brigjen', 'Irjen', 'Komjen'];
        return in_array($rank, $validRanks);
    }
}

// Usage examples:
/*
// Basic validation
$validator = new InputValidator($_POST);
$rules = [
    'name' => ['required' => true, 'min' => 2, 'max' => 50, 'safe' => true],
    'email' => ['required' => true, 'email' => true],
    'age' => ['numeric' => true, 'min' => 18, 'max' => 100]
];

if ($validator->validate($rules)) {
    $data = $validator->getValidatedData();
    // Process valid data
} else {
    $errors = $validator->getErrors();
    // Show errors
}

// Static validation
$errors = InputValidator::validateRequired($_POST, ['name', 'email']);
$sanitized = InputValidator::sanitizeInput($_POST);
*/
?>
