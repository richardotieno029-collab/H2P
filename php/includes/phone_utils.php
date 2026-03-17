<?php

/**
 * Normalize phone numbers for consistency.
 *
 * - Accepts formats like 0712345678, 0112345678, 254712345678, 254112345678, +254712345678, +254112345678
 * - Returns the normalized 12-digit format (2547xxxxxxx or 2541xxxxxxx) when possible.
 * - Returns the cleaned digits otherwise.
 */
function normalizePhoneForDb(string $phone): string {
    $digits = preg_replace('/\D+/', '', $phone);

    // Handle +254 prefix
    if (strpos($digits, '254') === 0 && strlen($digits) === 12) {
        return $digits;
    }

    // Handle local 0 prefix
    if (strpos($digits, '0') === 0 && (strlen($digits) === 10)) {
        return '254' . substr($digits, 1);
    }

    return $digits;
}

/**
 * Return an array of equivalent phone number variants (normalized and local forms).
 * This helps detect duplicates when users submit alternate formats.
 */
function getPhoneVariants(string $phone): array {
    $normalized = normalizePhoneForDb($phone);

    // If the normalized form is 12 digits starting with 254, return both variants.
    if (strlen($normalized) === 12 && strpos($normalized, '254') === 0) {
        $local = '0' . substr($normalized, 3);
        return array_unique([$normalized, $local]);
    }

    return [$normalized];
}
