<?php

namespace Database\Seeders;

use App\Models\ChatbotFaq;
use Illuminate\Database\Seeder;

class ChatbotFaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'keywords' => ['apa itu peset', 'peset', 'portal', 'tentang peset', 'tentang website', 'aplikasi ini', 'peset apa', 'pengertian peset', 'info peset', 'tentang aplikasi', 'website ini'],
                'answer' => 'PESET (Pemanfaatan Aset) adalah portal informasi dan rekomendasi pemanfaatan aset milik Pemerintah Kota Medan. Di sini Anda bisa melihat daftar aset yang tersedia, skema pemanfaatannya, serta rekomendasi AI untuk ide pemanfaatan.',
            ],
            [
                'keywords' => ['aset apa saja', 'aset tersedia', 'daftar aset', 'aset yang bisa', 'tanah kosong', 'gedung kosong', 'lihat aset', 'cari aset', 'list aset', 'katalog aset', 'data aset', 'aset apa', 'aset yang tersedia', 'aset dijual', 'aset disewakan', 'aset apa aja', 'daftar tanah', 'daftar gedung'],
                'answer' => 'Aset yang bisa dimanfaatkan antara lain tanah dan gedung/bangunan milik daerah yang berstatus tersedia (Idle). Anda bisa melihat daftarnya di halaman beranda portal PESET, lengkap dengan lokasi dan statusnya.',
            ],
            [
                'keywords' => ['skema', 'jenis pemanfaatan', 'sewa', 'pinjam pakai', 'kerja sama', 'bangun guna', 'bangun serah', 'ksp', 'pkp', 'bgs', 'bsg', 'bagi hasil', 'skema apa saja', 'jenis skema', 'macam skema', 'skema sewa', 'skema pemanfaatan', 'kerjasama', 'sewa lahan', 'sewa bangunan'],
                'answer' => 'Skema pemanfaatan aset daerah meliputi: SEWA (sewa lahan/bangunan), PKP (Pinjam Pakai), KSP (Kerja Sama Pemanfaatan), BGS (Bangun Guna Serah), dan BSG (Bangun Serah Guna). Masing-masing memiliki ketentuan tersendiri sesuai regulasi.',
            ],
            [
                'keywords' => ['cara mengajukan', 'prosedur', 'pendaftaran', 'langkah', 'cara sewa', 'cara kerja sama', 'pengajuan', 'cara daftar', 'tata cara', 'bagaimana mengajukan', 'langkah langkah', 'cara ikut', 'cara pakai', 'cara sewa aset', 'gimana caranya', 'bagaimana caranya', 'cara menggunakan', 'alur pengajuan'],
                'answer' => 'Pengajuan pemanfaatan diajukan kepada OPD/BPKAD pengelola aset. Siapkan proposal pemanfaatan dan dokumen legalitas, kemudian akan dievaluasi dan ditetapkan skema pemanfaatannya oleh pejabat berwenang.',
            ],
            [
                'keywords' => ['syarat', 'persyaratan', 'bisa ikut', 'berbadan hukum', 'npwp', 'siapa saja', 'kriteria', 'ketentuan', 'syarat sewa', 'syarat ikut', 'persyaratan sewa', 'siapa yang bisa', 'yang bisa daftar', 'syarat daftar'],
                'answer' => 'Pihak yang dapat mengajukan adalah badan usaha berbadan hukum atau perorangan yang memenuhi persyaratan, antara lain memiliki NPWP, tidak memiliki tunggakan pajak, dan tidak sedang bermasalah secara hukum.',
            ],
            [
                'keywords' => ['tarif', 'harga sewa', 'berapa biaya', 'nilai sewa', 'kontribusi', 'bayar berapa', 'njop', 'appraisal', 'harga', 'biaya', 'berapa harga', 'tarif sewa', 'harga aset', 'sewa berapa', 'biayanya', 'tarifnya', 'berapa bayar', 'harga sewa tanah', 'biaya sewa'],
                'answer' => 'Tarif sewa/kontribusi ditentukan berdasarkan nilai NJOP atau hasil appraisal dan peraturan daerah yang berlaku, kemudian disetorkan ke kas daerah. Besarannya dapat berbeda tergantung jenis dan lokasi aset.',
            ],
            [
                'keywords' => ['berapa lama', 'jangka waktu', 'masa sewa', 'durasi', 'lama kontrak', 'tahun', 'masa pemanfaatan', 'berapa tahun', 'jangka waktu sewa', 'lama sewa', 'durasi sewa', 'maksimal berapa', 'sampai berapa lama', 'lama pemanfaatan'],
                'answer' => 'Jangka waktu pemanfaatan berbeda tiap skema, misalnya sewa umumnya maksimal 5 tahun dan Kerja Sama Pemanfaatan (KSP) dapat lebih lama sesuai ketentuan. Rincian disesuaikan perjanjian.',
            ],
            [
                'keywords' => ['lelang', 'tender', 'mekanisme pemilihan', 'pemilihan mitra', 'seleksi', 'cara pemilihan', 'proses lelang', 'ikut lelang', 'lelang aset', 'pemenang', 'pemilihan', 'mekanisme'],
                'answer' => 'Pemilihan mitra dilakukan secara terbuka (tender/seleksi) sesuai ketentuan Permendagri No. 19 Tahun 2016 tentang Pedoman Pengelolaan Barang Milik Daerah.',
            ],
            [
                'keywords' => ['status idle', 'idle', 'tidak terpakai', 'belum dipakai', 'tersedia', 'arti status', 'idle artinya', 'apa itu idle', 'status aset', 'artinya idle', 'tanah idle', 'gedung idle', 'kosong', 'tidak dipakai', 'nganggur'],
                'answer' => 'Status Idle berarti aset tersebut belum/tidak sedang dimanfaatkan sehingga sangat potensial untuk diajukan pemanfaatan melalui skema yang tersedia.',
            ],
            [
                'keywords' => ['rekomendasi ai', 'rekomendasi', 'ai', 'ide usaha', 'kecerdasan buatan', 'rekomendasi aset', 'ide pemanfaatan', 'saran pemanfaatan', 'ide bisnis', 'saran ai', 'hasil ai', 'analisis ai', 'machine learning', 'rekomendasi otomatis'],
                'answer' => 'Rekomendasi AI adalah hasil analisis otomatis yang memberikan ide pemanfaatan aset beserta alasannya, mempertimbangkan fasilitas umum (POI) terdekat seperti kampus, pasar, atau perkantoran.',
            ],
            [
                'keywords' => ['cara melihat rekomendasi', 'lihat rekomendasi', 'rekomendasi ai di mana', 'dimana rekomendasi', 'lihat ai', 'lihat rekomendasi ai', 'menu rekomendasi', 'rekomendasi dimana', 'cara lihat rekomendasi'],
                'answer' => 'Buka detail aset pada halaman beranda, lalu lihat bagian "Rekomendasi AI". Bagian ini menampilkan ide pemanfaatan dan alasan yang sudah dianalisis secara otomatis.',
            ],
            [
                'keywords' => ['hubungi siapa', 'kontak', 'opd', 'petugas', 'nomor telepon', 'kemana saya hubungi', 'nomor', 'telepon', 'email', 'kontak siapa', 'hubungi siapa', 'admin', 'kontak opd', 'nomor hp', 'ke mana hubungi', 'siapa yang bisa dihubungi'],
                'answer' => 'Anda dapat menghubungi OPD terkait pengelola aset tersebut, atau melihat daftar kontak pada bagian Kontak di website. Anda juga bisa bertanya langsung di sini.',
            ],
            [
                'keywords' => ['regulasi', 'dasar hukum', 'peraturan', 'undang undang', 'permendagri', 'pp', 'perda', 'aturan', 'hukum', 'legal', 'dasar aturan', 'regulasi apa', 'peraturan perundangan', 'uu', 'landasan hukum'],
                'answer' => 'Regulasi utama antara lain Permendagri No. 19 Tahun 2016, PP No. 27 Tahun 2014, dan PP No. 28 Tahun 2020 tentang pengelolaan Barang Milik Daerah.',
            ],
            [
                'keywords' => ['lapor', 'pengaduan', 'kendala', 'masalah', 'keluhan', 'komplain', 'error', 'rusak website', 'lapor masalah', 'lapor bug', 'website error', 'gak jalan', 'tidak berfungsi', 'aduan', 'kendala website', 'lapor kendala'],
                'answer' => 'Anda dapat melaporkan kendala atau pengaduan melalui chat ini atau menghubungi petugas. Pertanyaan Anda akan diteruskan kepada petugas terkait.',
            ],
            [
                'keywords' => ['pembayaran', 'bayar kontribusi', 'setor', 'kas daerah', 'transfer', 'bayar sewa', 'bayar', 'cara bayar', 'pembayaran sewa', 'transfer ke mana', 'rekening', 'bayar kapan', 'cara pembayaran', 'metode pembayaran', 'setoran'],
                'answer' => 'Pembayaran kontribusi dilakukan dengan menyetor ke kas daerah sesuai surat ketetapan/nomor rekening yang diterbitkan, dengan bukti setor sebagai arsip.',
            ],
            [
                'keywords' => ['aset rusak', 'aset terpakai', 'status dimanfaatkan', 'bisa dikerjasamakan', 'kondisi rusak', 'rusak', 'aset sudah dipakai', 'aset terpakai', 'aset rusak ringan', 'bisa disewakan', 'kondisi aset', 'aset yang dipakai'],
                'answer' => 'Aset dengan kondisi rusak ringan masih dapat dikerjasamakan, misalnya melalui skema KSP di mana mitra ikut menanggung biaya perbaikan. Aset yang sudah dimanfaatkan umumnya tidak tersedia untuk ditawarkan.',
            ],
            [
                'keywords' => ['dokumen', 'berkas', 'persyaratan dokumen', 'ktp', 'akta', 'sertifikat', 'dokumen apa saja', 'berkas apa saja', 'syarat dokumen', 'dokumen yang dibutuhkan', 'persyaratan berkas', 'surat surat', 'dokumen dibutuhkan', 'berkas yang diperlukan'],
                'answer' => 'Dokumen yang perlu disiapkan antara lain fotokopi KTP dan NPWP, akta pendirian perusahaan (bila badan usaha), serta proposal rencana pemanfaatan aset.',
            ],
            [
                'keywords' => ['proses berapa lama', 'lama proses', 'estimasi waktu', 'kapan selesai', 'berapa hari', 'berapa lama proses', 'estimasi', 'kapan keluar', 'lama pengajuan', 'proses berapa', 'berapa lama', 'waktu proses', 'kapan selesainya'],
                'answer' => 'Lama proses pengajuan tergantung pada kompleksitas aset dan dokumen yang dilengkapi. Petugas akan menginformasikan estimasi waktu pada saat pengajuan dilakukan.',
            ],
        ];

        foreach ($faqs as $f) {
            ChatbotFaq::updateOrCreate(['answer' => $f['answer']], $f);
        }
    }
}
