<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    /**
     * Google Books APIからISBNを用いて書籍情報を取得します。
     *
     * @param  string  $isbn  13桁のISBNコード
     */
    public function fetchByIsbn(string $isbn): array
    {
        try {
            $params = ['q' => 'isbn:'.$isbn];

            if ($apiKey = config('services.google.books_api_key')) {
                $params['key'] = $apiKey;
            }

            $response = Http::withoutVerifying()->get('https://www.googleapis.com/books/v1/volumes', $params);

            if ($response->failed()) {
                $responseBody = $response->body();

                if ($response->status() === 429 || str_contains($responseBody, 'Quota exceeded') || str_contains($responseBody, 'RESOURCE_EXHAUSTED')) {
                    return [
                        'status' => 200,
                        'data' => ['error' => 'Google Books APIの利用制限に達しました。時間を置いて再度お試しください。'],
                    ];
                }

                if (str_contains($responseBody, 'API key not valid')) {
                    return [
                        'status' => 200,
                        'data' => ['error' => 'Google Books APIの認証に失敗しました。環境設定（APIキー）を確認してください。'],
                    ];
                }

                return [
                    'status' => 200,
                    'data' => ['error' => 'Google Books APIとの通信に失敗しました。時間をおいて手動でお試しください。'],
                ];
            }

            $data = $response->json();

            if (empty($data['items'])) {
                return [
                    'status' => 200,
                    'data' => ['error' => '該当する書籍情報が見つかりませんでした。'],
                ];
            }

            $firstItem = $data['items'] ?? [];
            $volumeInfo = $firstItem['volumeInfo'] ?? [];

            return [
                'status' => 200,
                'data' => [
                    'title' => $volumeInfo['title'] ?? '',
                    'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '',
                    'description' => $volumeInfo['description'] ?? '',
                    'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
                    'published_date' => $volumeInfo['publishedDate'] ?? null,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 200,
                'data' => ['error' => 'システムエラーが発生しました。時間を置いて再度お試しください。'],
            ];
        }
    }
}
