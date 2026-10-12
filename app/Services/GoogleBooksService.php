<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksService
{
    /**
     * Google Books APIからISBNを用いて書籍情報を実際に取得します。
     *
     * @param  string  $isbn  ISBNコード
     * @return array 書籍情報のフラットな配列、またはエラー配列
     */
    public function fetchByIsbn(string $isbn): array
    {
        try {
            if (empty($isbn)) {
                return [
                    'error' => '書籍が見つかりませんでした。',
                ];
            }

            $baseUrl = 'https://www.googleapis.com/books/v1/volumes';
            $params = [
                'q' => 'isbn:'.$isbn,
            ];

            $apiKey = config('services.google.books_api_key');
            if (! empty($apiKey)) {
                $params['key'] = $apiKey;
            }

            $response = Http::withoutVerifying()
                ->retry(3, 1000)
                ->get($baseUrl, $params);

            if ($response->failed()) {
                Log::error('Google Books API通信エラーが発生しました。');

                return [
                    'error' => 'API通信エラーが発生しました。',
                ];
            }

            $data = $response->json();

            if (isset($data['error'])) {
                return [
                    'error' => 'API通信エラーが発生しました。',
                ];
            }

            if (empty($data['items']) || (isset($data['totalItems']) && $data['totalItems'] === 0)) {
                return [
                    'error' => '書籍が見つかりませんでした。',
                ];
            }

            $volumeInfo = [];
            if (isset($data['items'][0]['volumeInfo'])) {
                $volumeInfo = $data['items'][0]['volumeInfo'];
            } elseif (isset($data['items']['volumeInfo'])) {
                $volumeInfo = $data['items']['volumeInfo'];
            }

            $rawDate = $volumeInfo['publishedDate'] ?? '';
            $publishedDate = '';

            if (! empty($rawDate)) {
                if (preg_match('/^\d{4}$/', $rawDate)) {
                    $publishedDate = $rawDate.'-01-01';
                } elseif (preg_match('/^\d{4}-\d{2}$/', $rawDate)) {
                    $publishedDate = $rawDate.'-01';
                } else {
                    $publishedDate = substr($rawDate, 0, 10);
                }
            }

            return [
                'title' => $volumeInfo['title'] ?? '',
                'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '',
                'description' => $volumeInfo['description'] ?? '',
                'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
                'published_date' => $publishedDate,
            ];
        } catch (\Exception $e) {
            Log::error('Google Books API例外発生: '.$e->getMessage());

            return [
                'error' => 'API通信エラーが発生しました。',
            ];
        }
    }
}
