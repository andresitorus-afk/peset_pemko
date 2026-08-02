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
        $msg = self::normalize($message);
        $best = null;
        $bestScore = 0;

        foreach ($faqs as $faq) {
            if (!($faq['aktif'] ?? true)) {
                continue;
            }

            $score = 0;
            foreach ($faq['keywords'] as $keyword) {
                if (str_contains($msg, self::normalize((string) $keyword))) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        return $bestScore > 0 ? $best : null;
    }
}
