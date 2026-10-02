<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    /**
     * Google Books APIからISBNを用いて書籍情報を取得します。
     *
     * @return array
     */
    public function fetchByIsbn($isbn)
    {
        try {
            $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
                'q' => 'isbn:'.$isbn,
            ]);

            if ($response->failed()) {
                return ['error' => 'Google Books APIとの通信に失敗しました。'];
            }

            $data = $response->json();

            if (empty($data['items'])) {
                return ['error' => '該当する書籍情報が見つかりませんでした。'];
            }

            $firstItem = $data['items'][0] ?? [];
            $volumeInfo = $firstItem['volumeInfo'] ?? [];

            return [
                'title' => $volumeInfo['title'] ?? '',
                'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '',
                'description' => $volumeInfo['description'] ?? '',
                'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
                'published_date' => $volumeInfo['publishedDate'] ?? null,
            ];
        } catch (\Exception $e) {
            return ['error' => 'システムエラーが発生しました。時間を置いて再度お試しください。'];
        }
    }
}
