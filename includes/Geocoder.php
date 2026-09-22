<?php

class Geocoder {
    public static function forward(string $input): ?array {
        $input = trim($input);
        if ($input === '') return null;

        $coordinates = self::coordinatesFromText($input);
        if ($coordinates) return $coordinates + ['address' => $input, 'provider' => 'coordinates'];

        foreach (self::queryVariants($input) as $query) {
            $url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=ph&addressdetails=1&q=' . rawurlencode($query);
            $rows = self::getJson($url);
            if (is_array($rows) && !empty($rows[0]['lat']) && !empty($rows[0]['lon'])) {
                return [
                    'lat' => (float)$rows[0]['lat'],
                    'lng' => (float)$rows[0]['lon'],
                    'address' => (string)($rows[0]['display_name'] ?? $query),
                    'provider' => 'OpenStreetMap',
                ];
            }

            $photon = self::getJson('https://photon.komoot.io/api/?limit=1&lang=en&q=' . rawurlencode($query));
            $feature = $photon['features'][0] ?? null;
            $coords = $feature['geometry']['coordinates'] ?? null;
            if (is_array($coords) && count($coords) >= 2) {
                $properties = $feature['properties'] ?? [];
                $label = array_filter([
                    $properties['name'] ?? null,
                    $properties['street'] ?? null,
                    $properties['district'] ?? null,
                    $properties['city'] ?? null,
                    $properties['state'] ?? null,
                    $properties['country'] ?? null,
                ]);
                return [
                    'lat' => (float)$coords[1],
                    'lng' => (float)$coords[0],
                    'address' => $label ? implode(', ', array_unique($label)) : $query,
                    'provider' => 'Photon',
                ];
            }
        }
        return null;
    }

    private static function coordinatesFromText(string $text): ?array {
        $decoded = rawurldecode($text);
        $patterns = [
            '/@(-?\d{1,2}\.\d+)\s*,\s*(-?\d{1,3}\.\d+)/',
            '/(?:query|q|destination)=(-?\d{1,2}\.\d+)%?2C\s*(-?\d{1,3}\.\d+)/i',
            '/^\s*(-?\d{1,2}\.\d+)\s*,\s*(-?\d{1,3}\.\d+)\s*$/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $decoded, $match)) {
                $lat = (float)$match[1];
                $lng = (float)$match[2];
                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                    return ['lat' => $lat, 'lng' => $lng];
                }
            }
        }
        return null;
    }

    private static function queryVariants(string $input): array {
        $input = preg_replace('/\s+/', ' ', trim($input));
        $variants = [$input];
        $parts = array_values(array_filter(array_map('trim', explode(',', $input))));
        if (count($parts) >= 3) {
            // Google often prefixes an address with a business, unit, or building.
            // Try progressively broader forms while retaining city/province context.
            $maxTrim = min(3, count($parts) - 2);
            for ($offset = 1; $offset <= $maxTrim; $offset++) {
                $variants[] = implode(', ', array_slice($parts, $offset));
            }
        }
        $withoutStandaloneNumbers = array_values(array_filter($parts, function ($part) {
            return !preg_match('/^\d{1,6}$/', $part);
        }));
        if ($withoutStandaloneNumbers && $withoutStandaloneNumbers !== $parts) {
            $variants[] = implode(', ', $withoutStandaloneNumbers);
            if (count($withoutStandaloneNumbers) >= 4) {
                $variants[] = implode(', ', array_slice($withoutStandaloneNumbers, 1));
            }
        }
        foreach (array_values($variants) as $variant) {
            if (!preg_match('/\bphilippines\b/i', $variant)) $variants[] = $variant . ', Philippines';
        }
        return array_values(array_unique($variants));
    }

    private static function getJson(string $url): ?array {
        $headers = [
            'Accept: application/json',
            'Accept-Language: en',
            'User-Agent: FieldITHub/1.0 (local-support-application)',
        ];
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $body = curl_exec($curl);
            $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if (is_string($body) && $status >= 200 && $status < 300) return json_decode($body, true);
        }
        $context = stream_context_create(['http' => ['timeout' => 10, 'header' => implode("\r\n", $headers)]]);
        $body = @file_get_contents($url, false, $context);
        return is_string($body) ? json_decode($body, true) : null;
    }
}
