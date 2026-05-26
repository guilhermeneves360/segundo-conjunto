<?php

class Validator
{
    public static function sanitizeString($value)
    {
        return filter_var(trim($value), FILTER_SANITIZE_STRING);
    }

    public static function sanitizeEmail($value)
    {
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    public static function sanitizeFloat($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function validateRequired(array $data, array $fields)
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing[] = $field;
            }
        }
        return $missing;
    }
}
