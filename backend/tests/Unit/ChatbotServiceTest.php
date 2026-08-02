<?php

namespace Tests\Unit;

use App\Services\ChatbotService;
use Tests\TestCase;

class ChatbotServiceTest extends TestCase
{
    private array $faqs = [
        [
            'id' => 'a',
            'aktif' => true,
            'keywords' => ['cara sewa', 'sewa', 'tarif'],
            'answer' => 'Tarif ditentukan berdasar NJOP.',
        ],
        [
            'id' => 'b',
            'aktif' => true,
            'keywords' => ['status idle', 'idle'],
            'answer' => 'Idle berarti belum dimanfaatkan.',
        ],
        [
            'id' => 'c',
            'aktif' => false,
            'keywords' => ['nonaktif'],
            'answer' => 'Tidak boleh terpilih.',
        ],
    ];

    public function test_normalize_menghilangkan_tanda_baca_dan_huruf_besar(): void
    {
        $this->assertSame('cara sewa aset daerah', ChatbotService::normalize('Cara Sewa Aset Daerah?'));
    }

    public function test_match_menemukan_faq_berdasarkan_keyword(): void
    {
        $result = ChatbotService::match('Bagaimana cara sewa aset daerah?', $this->faqs);
        $this->assertSame('a', $result['id']);
    }

    public function test_match_skor_tertinggi_diutamakan(): void
    {
        $result = ChatbotService::match('apa itu status idle dan cara sewa?', $this->faqs);
        $this->assertSame('a', $result['id']);
    }

    public function test_match_menghiraukan_faq_nonaktif(): void
    {
        $this->assertNull(ChatbotService::match('nonaktif', $this->faqs));
    }

    public function test_match_tanpa_cocok_mengembalikan_null(): void
    {
        $this->assertNull(ChatbotService::match('pertanyaan acak tidak dikenal', $this->faqs));
    }
}
