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

    public function test_match_tahan_typo_satu_huruf(): void
    {
        $result = ChatbotService::match('Bagaimana cara sswa aset?', $this->faqs);
        $this->assertSame('a', $result['id']);
    }

    public function test_match_tahan_typo_kata_istilah(): void
    {
        $result = ChatbotService::match('apa arti idla', $this->faqs);
        $this->assertSame('b', $result['id']);
    }

    public function test_match_tahan_typo_huruf_doang(): void
    {
        $result = ChatbotService::match('cek ttarf sewa dong', $this->faqs);
        $this->assertSame('a', $result['id']);
    }

    public function test_match_keyword_tunggal_tidak_pas(): void
    {
        $result = ChatbotService::match('au', $this->faqs);
        $this->assertNull($result);
    }
}
