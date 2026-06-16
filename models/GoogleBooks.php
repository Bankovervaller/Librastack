<?php

class GoogleBooks
{
    private const OPEN_LIBRARY_SEARCH_URL = 'https://openlibrary.org/search.json';
    private const OPEN_LIBRARY_BOOKS_URL = 'https://openlibrary.org/api/books';

    public static function search($query, $limit = 8, $type = 'all')
    {
        $query = trim(substr((string)$query, 0, 200));
        if (strlen($query) < 2) {
            return [];
        }

        // 1. Check Session Cache
        if (session_status() === PHP_SESSION_ACTIVE) {
            $cacheKey = 'ol_cache_' . md5($query . $type . $limit);
            if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['time'] < 3600)) {
                return $_SESSION[$cacheKey]['data'];
            }
        }

        $compact = preg_replace('/[\s-]+/', '', $query);
        $isIsbn = preg_match('/^(97[89])?\d{9}[\dXx]$/', $compact);

        // 2. High-speed ISBN Direct Lookup
        if ($isIsbn) {
            $books = self::fastIsbnLookup($compact);
            if (!empty($books)) {
                return self::cacheAndReturn($query, $type, $limit, $books);
            }
        }

        // 3. Optimized General Search
        $limit = max(1, min(20, (int)$limit));
        $params = [
            'limit' => $limit,
            'fields' => 'key,title,author_name,isbn,first_publish_year'
        ];

        if ($isIsbn) {
            $params['isbn'] = $compact;
        } elseif ($type === 'title') {
            $params['title'] = $query;
        } elseif ($type === 'author') {
            $params['author'] = $query;
        } else {
            $params['q'] = $query;
        }

        $url = self::OPEN_LIBRARY_SEARCH_URL . '?' . http_build_query($params);
        $response = self::requestJson($url);
        
        $books = [];
        if (isset($response['docs']) && is_array($response['docs'])) {
            foreach ($response['docs'] as $item) {
                $book = self::formatSearchItem($item);
                if ($book !== null) $books[] = $book;
            }
        }

        return self::cacheAndReturn($query, $type, $limit, $books);
    }

    private static function fastIsbnLookup($isbn)
    {
        $url = self::OPEN_LIBRARY_BOOKS_URL . '?' . http_build_query([
            'bibkeys' => 'ISBN:' . $isbn,
            'format' => 'json',
            'jscmd' => 'data'
        ]);

        $response = self::requestJson($url);
        $key = 'ISBN:' . $isbn;

        if (!isset($response[$key])) return [];

        $data = $response[$key];
        $authors = [];
        if (isset($data['authors']) && is_array($data['authors'])) {
            foreach ($data['authors'] as $a) $authors[] = $a['name'];
        }

        return [[
            'id' => $data['url'] ?? $isbn,
            'title' => $data['title'] ?? 'Onbekend',
            'author' => implode(', ', $authors),
            'isbn' => $isbn,
            'publishedDate' => $data['publish_date'] ?? '',
            'thumbnail' => $data['cover']['medium'] ?? '',
            'source' => 'Open Library (Fast)'
        ]];
    }

    private static function cacheAndReturn($query, $type, $limit, $data)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $cacheKey = 'ol_cache_' . md5($query . $type . $limit);
            $_SESSION[$cacheKey] = ['time' => time(), 'data' => $data];
            
            // Cleanup old cache entries (keep session lean)
            if (count($_SESSION) > 50) {
                foreach ($_SESSION as $k => $v) {
                    if (strpos($k, 'ol_cache_') === 0 && (time() - $v['time'] > 3600)) {
                        unset($_SESSION[$k]);
                    }
                }
            }
        }
        return $data;
    }

    private static function requestJson($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 4, // Aggressive timeout
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: LibraStack/1.0'],
        ]);
        $body = curl_exec($ch);
        curl_close($ch);

        if ($body !== false) {
            $decoded = json_decode($body, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private static function formatSearchItem(array $item)
    {
        $title = trim((string)($item['title'] ?? ''));
        if ($title === '') return null;

        $authors = $item['author_name'] ?? [];
        $isbn = '';
        if (isset($item['isbn']) && is_array($item['isbn'])) {
            foreach ($item['isbn'] as $i) {
                if (strlen($i) === 13) { $isbn = $i; break; }
            }
            if ($isbn === '') $isbn = $item['isbn'][0] ?? '';
        }

        return [
            'id' => (string)($item['key'] ?? ''),
            'title' => $title,
            'author' => is_array($authors) ? implode(', ', $authors) : (string)$authors,
            'isbn' => $isbn,
            'publishedDate' => (string)($item['first_publish_year'] ?? ''),
            'thumbnail' => isset($item['key']) ? "https://covers.openlibrary.org/b/olid/" . str_replace('/works/', '', $item['key']) . "-M.jpg" : '',
            'source' => 'Open Library',
        ];
    }
}
