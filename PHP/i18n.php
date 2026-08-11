<?php

if (!defined('BARABEAT_LANGUAGE_COOKIE')) {
    define('BARABEAT_LANGUAGE_COOKIE', 'barabeat_language');
}

function barabeat_supported_languages()
{
    return ['de', 'en', 'fr', 'es', 'pt'];
}

function barabeat_available_languages()
{
    $available = [];
    foreach (barabeat_supported_languages() as $language) {
        if (is_file(__DIR__ . '/../languages/' . $language . '.json')) {
            $available[] = $language;
        }
    }
    return $available;
}

function barabeat_normalize_language($language)
{
    $value = strtolower(trim(str_replace('_', '-', (string) $language)));
    if ($value === '') {
        return null;
    }

    $primary = explode('-', $value, 2)[0];
    return in_array($primary, barabeat_supported_languages(), true) ? $primary : 'en';
}

function barabeat_browser_language()
{
    $header = (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
    if ($header === '') {
        return 'en';
    }

    $candidates = [];
    foreach (explode(',', $header) as $position => $part) {
        $segments = array_map('trim', explode(';', $part));
        $language = barabeat_normalize_language($segments[0] ?? '');
        $quality = 1.0;
        foreach (array_slice($segments, 1) as $parameter) {
            if (stripos($parameter, 'q=') === 0) {
                $quality = (float) substr($parameter, 2);
            }
        }
        if ($language !== null) {
            $candidates[] = ['language' => $language, 'quality' => $quality, 'position' => $position];
        }
    }

    usort($candidates, function ($left, $right) {
        if ($left['quality'] === $right['quality']) {
            return $left['position'] <=> $right['position'];
        }
        return $left['quality'] < $right['quality'] ? 1 : -1;
    });

    return $candidates[0]['language'] ?? 'en';
}

function barabeat_requested_language($explicitLanguage = null)
{
    if ($explicitLanguage !== null && trim((string) $explicitLanguage) !== '') {
        return barabeat_normalize_language($explicitLanguage) ?: 'en';
    }

    $cookieLanguage = $_COOKIE[BARABEAT_LANGUAGE_COOKIE] ?? null;
    if (is_string($cookieLanguage) && trim($cookieLanguage) !== '') {
        return barabeat_normalize_language($cookieLanguage) ?: 'en';
    }

    return barabeat_browser_language();
}

function barabeat_resolve_language($language)
{
    $normalized = barabeat_normalize_language($language) ?: 'en';
    return in_array($normalized, barabeat_available_languages(), true) ? $normalized : 'en';
}

function barabeat_language($explicitLanguage = null)
{
    return barabeat_resolve_language(barabeat_requested_language($explicitLanguage));
}

function barabeat_locale($language = null)
{
    $locales = [
        'de' => 'de-DE',
        'en' => 'en-GB',
        'fr' => 'fr-FR',
        'es' => 'es-ES',
        'pt' => 'pt-PT',
    ];
    $resolved = $language === null ? barabeat_language() : barabeat_normalize_language($language);
    return $locales[$resolved] ?? $locales['en'];
}

function barabeat_i18n_catalog($language)
{
    static $catalogs = [];
    $resolved = barabeat_resolve_language($language);
    if (array_key_exists($resolved, $catalogs)) {
        return $catalogs[$resolved];
    }

    $path = __DIR__ . '/../languages/' . $resolved . '.json';
    $json = @file_get_contents($path);
    $catalog = is_string($json) ? json_decode($json, true) : null;
    if (!is_array($catalog)) {
        error_log('[BaraBeat i18n] Could not load catalog: ' . $resolved);
        $catalog = [];
    }
    $catalogs[$resolved] = $catalog;
    return $catalog;
}

function barabeat_i18n_value(array $catalog, $key)
{
    $value = $catalog;
    foreach (explode('.', (string) $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return null;
        }
        $value = $value[$segment];
    }
    return is_string($value) || is_numeric($value) ? (string) $value : null;
}

function barabeat_i18n_interpolate($text, array $values)
{
    $replacements = [];
    foreach ($values as $name => $value) {
        $replacements['{' . $name . '}'] = (string) $value;
    }
    return strtr((string) $text, $replacements);
}

function barabeat_t($key, $values = [])
{
    $language = barabeat_language();
    $text = barabeat_i18n_value(barabeat_i18n_catalog($language), $key);
    if ($text === null && $language !== 'en') {
        error_log('[BaraBeat i18n] Missing key "' . $key . '" in ' . $language . '; using English.');
        $text = barabeat_i18n_value(barabeat_i18n_catalog('en'), $key);
    }
    if ($text === null) {
        error_log('[BaraBeat i18n] Missing translation key: ' . $key);
        $text = (string) $key;
    }
    return barabeat_i18n_interpolate($text, is_array($values) ? $values : []);
}

function barabeat_i18n_config()
{
    $catalogs = [];
    foreach (barabeat_available_languages() as $language) {
        $catalogs[$language] = barabeat_i18n_catalog($language);
    }

    return [
        'language' => barabeat_language(),
        'requestedLanguage' => barabeat_requested_language(),
        'fallbackLanguage' => 'en',
        'cookieName' => BARABEAT_LANGUAGE_COOKIE,
        'storageKey' => BARABEAT_LANGUAGE_COOKIE,
        'supportedLanguages' => barabeat_supported_languages(),
        'availableLanguages' => barabeat_available_languages(),
        'locales' => [
            'de' => 'de-DE',
            'en' => 'en-GB',
            'fr' => 'fr-FR',
            'es' => 'es-ES',
            'pt' => 'pt-PT',
        ],
        'catalogs' => $catalogs,
    ];
}

function barabeat_i18n_config_json()
{
    $json = json_encode(
        barabeat_i18n_config(),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    );
    return is_string($json) ? $json : '{}';
}

