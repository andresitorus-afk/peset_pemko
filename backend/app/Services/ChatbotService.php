<?php

namespace App\Services;

class ChatbotService
{
    public static function normalize(string $s): string
    {
        return trim(mb_strtolower((string) preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s)));
    }

    public static function match(string $message, array $faqs): ?array
    {
        $words = preg_split('/\s+/', self::normalize($message)) ?: [];
        $best = null;
        $bestScore = 0;

        foreach ($faqs as $faq) {
            if (!($faq['aktif'] ?? true)) {
                continue;
            }

            $score = 0;
            foreach ($faq['keywords'] as $keyword) {
                $score += self::keywordScore($words, self::normalize((string) $keyword));
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    /**
     * @param  string[]  $words  Kata-kata pada pesan user (sudah dinormalisasi).
     */
    private static function keywordScore(array $words, string $keyword): int
    {
        if ($keyword === '') {
            return 0;
        }

        $score = 0;
        foreach (explode(' ', $keyword) as $kw) {
            if ($kw === '') {
                continue;
            }

            foreach ($words as $word) {
                if ($word === $kw) {
                    $score++;
                    break;
                }

                // ponytail: levenshtein ambang berlapis — kata pendek toleransi 1 huruf,
                // kata panjang toleransi 2. Kata <=2 huruf ("ai") hanya cocok pas.
                if (strlen($kw) >= 3
                    && $word !== ''
                    && levenshtein($word, $kw) <= (strlen($kw) <= 4 ? 1 : 2)) {
                    $score++;
                    break;
                }
            }
        }

        return $score;
    }
}
