# Live Chat Real-time + Auto-Chatbot Design

> Tanggal: 2026-08-03
> Status: Approved oleh user (Bagian 1 & 2)
> Fitur: Chat real-time visitor ↔ petugas/admin via WebSocket (Laravel Reverb), auto-chatbot template FAQ 24 jam.

## Tujuan

Memberikan saluran tanya-jawab langsung antara pengunjung website publik PESET dan petugas/admin/super admin, dengan auto-bot berbasis template FAQ yang menjawab otomatis tanpa menunggu admin aktif. Notifikasi real-time ke admin (badge sidebar + toast + browser notification).

## Keputusan Kunci (hasil brainstorming)

- **Transport**: WebSocket **Laravel Reverb** (dipilih user). Backend `php artisan serve` single-threaded tetap aman karena Reverb proses terpisah.
- **Chatbot**: **Template saja** (dipilih user). Tak cocok → balasan standar "Mohon maaf, pertanyaan Anda akan diteruskan ke petugas. Mohon tunggu balasan, terima kasih." + `needs_attention=true`. Tanpa fallback Gemini (gratis & deterministik).
- **Identitas visitor**: **Anonim** (dipilih user). Nama tampil "Pengunjung".
- **Responder**: Semua role autentik (Super Admin, Admin, Petugas) bisa menjawab chat & mengelola FAQ.
- **Posisi ikon**: Tombol headset melayang `bottom-6 right-6` (global via `app.vue`), bukan di navbar — lebih terlihat & tidak memadati navbar.

## Arsitektur

- Backend: package `laravel/reverb` + `config/broadcasting.php` (driver `reverb`) + `config/reverb.php`.
- Event `ChatMessageSent` → channel privat `chat.{sessionId}` (kedua sisi subscribe).
- Event `ChatUnreadUpdated` → channel staff `private-chat.staff` (badge/notifikasi).
- docker-compose: service `reverb` (image backend, command `php artisan reverb:start --host=0.0.0.0 --port=8080`, port `8080:8080`).
- Env: `BROADCAST_CONNECTION=reverb`, `REVERB_APP_ID/KEY/SECRET`, `REVERB_HOST=localhost`, `REVERB_PORT=8080`, `REVERB_SERVER_HOST=reverb` (broadcast keluar backend→reverb via network docker; browser ke `ws://localhost:8080`).
- Frontend: `laravel-echo` + `pusher-js`, plugin Nuxt `plugins/live-chat.client.ts` (Reverb memakai protokol Pusher).
- Auth channel: route `POST /api/broadcasting/auth` (custom controller) menerima token Sanctum staff **atau** header `X-Chat-Session` (token visitor).

## Data Model (3 tabel baru, UUID `gen_random_uuid()` — pola existing)

### `chat_sessions`
| kolom | tipe | catatan |
|---|---|---|
| id | uuid PK | `default(DB::raw('gen_random_uuid()'))` |
| token | string(40) unik | secret visitor, `Str::random(40)` |
| visitor_name | string nullable | anonim → "Pengunjung" |
| status | string default 'open' | open \| closed |
| needs_attention | boolean default false | pertanyaan visitor belum dijawab staff |
| last_admin_seen_at | timestamp nullable | untuk kalkulasi unread |
| closed_at | timestamp nullable | |
| timestamps | | |

### `chat_messages`
| kolom | tipe | catatan |
|---|---|---|
| id | uuid PK | |
| session_id | uuid FK → chat_sessions | cascade |
| sender_type | string | visitor \| admin \| bot |
| user_id | bigint nullable FK → users | pengirim admin |
| message | text | |

### `chatbot_faqs`
| kolom | tipe | catatan |
|---|---|---|
| id | uuid PK | |
| keywords | json | array kata kunci (cast array, pola `hasil` di `rekomendasi_ai`) |
| answer | text | jawaban template |
| aktif | boolean default true | |
| timestamps | | |

## Alur

1. Visitor buka widget → `POST /api/public/chat/sessions` → session dibuat + pesan sapaan bot (`sender_type=bot`). Client simpan `{sessionId, token}` di localStorage, subscribe `chat.{sessionId}`.
2. Visitor kirim → `POST /api/public/chat/{session}/messages` → simpan pesan visitor → `ChatbotService::match()`:
   - Cocok → simpan balasan bot + broadcast `ChatMessageSent`.
   - Tak cocok → simpan balasan fallback (bot) + `needs_attention=true` + broadcast `ChatMessageSent` (ke `chat.{id}`) + `ChatUnreadUpdated` (ke `private-chat.staff`).
3. Admin buka `/admin/live-chat` → `GET /api/chat/sessions` (open dulu, unread + preview pesan terakhir). Klik session → `POST .../{id}/read` (update `last_admin_seen_at`), subscribe `chat.{sessionId}`.
4. Admin balas → `POST /api/chat/sessions/{id}/messages` → simpan `sender_type=admin` + broadcast.
5. Badge sidebar: subscribe `private-chat.staff` → increment + toast + browser notification. Safety-net poll 5s (ponytail: hanya bila ws putus).

## API Routes

### Publik (throttle 10 msg/menit/session)
- `POST /api/public/chat/sessions` — buat session (+ sapaan bot). Body: `visitor_name`? (opsional, diabaikan → anonim).
- `GET /api/public/chat/{session}/messages` — riwayat pesan (untuk reload widget).
- `POST /api/public/chat/{session}/messages` — kirim pesan, terima balasan bot.

### Staff (`auth:sanctum`, semua role)
- `GET /api/chat/sessions` — daftar session + unread + last message.
- `POST /api/chat/sessions/{id}/messages` — balas.
- `POST /api/chat/sessions/{id}/read` — tandai dibaca.
- `POST /api/chat/sessions/{id}/close` — tutup session.
- `GET /api/chat/unread-count` — total unread badge.
- `GET/POST/PUT/DELETE /api/chat/faqs` — CRUD FAQ.

### Broadcast auth
- `POST /api/broadcasting/auth` — validasi channel: staff autentik boleh `chat.*` + `private-chat.staff`; `X-Chat-Session` valid boleh `chat.{sessionId}` yang sesuai.

## ChatbotService

- `match(string $message, array $faqs): ?array` — tokenisasi (lowercase, buang tanda baca), cocokkan substring keyword, skor = jumlah keyword cocok, ambil tertinggi. Pure function, DB-free (mudah di-test).
- Balasan fallback dari `config('services.chatbot.fallback_reply')`.

## FAQ Template (18, di-seed, admin-editable)

1. Apa itu PESET → portal informasi & rekomendasi pemanfaatan aset Pemko Medan.
2. Aset apa saja yang bisa dimanfaatkan → tanah & gedung berstatus tersedia/idle, lihat beranda.
3. Skema pemanfaatan → SEWA, PKP, KSP, BGS, BSG + penjelasan singkat.
4. Cara mengajukan → ajukan ke OPD pengelola; proposal + legalitas; dievaluasi & ditetapkan.
5. Syarat pihak ketiga → berbadan hukum, tidak punya tunggakan pajak.
6. Tarif/sewa → berdasar NJOP/appraisal & perda; kontribusi ke kas daerah.
7. Masa pemanfaatan → beda per skema (sewa ≤5 th, KSP ≤30 th).
8. Lelang/tender → pemilihan mitra terbuka sesuai Permendagri 19/2016.
9. Status "Idle" → belum dimanfaatkan, potensial dikerjasamakan.
10. Rekomendasi AI → analisis otomatis ide pemanfaatan + alasan berbasis POI.
11. Melihat rekomendasi AI → buka detail aset bagian "Rekomendasi AI".
12. Aset ingin dipakai, hubungi siapa → OPD terkait / bagian Kontak.
13. Regulasi → Permendagri 19/2016, PP 27/2014, PP 28/2020.
14. Lapor kendala → lewat chat / hubungi petugas.
15. Pembayaran kontribusi → setor ke kas daerah sesuai surat ketetapan.
16. Aset rusak/terpakai → rusak ringan via KSP; terpakai = tidak tersedia.
17. Dokumen yang disiapkan → KTP/NPWP, akta perusahaan, proposal.
18. Lama proses pengajuan → tergantung kompleksitas; diinformasikan petugas.

## Frontend

### Widget publik (global via `app.vue`)
- `components/public/ChatWidget.vue`: tombol headset melayang `fixed bottom-6 right-6 z-50`, lingkaran teal w-14 h-14, icon SVG inline. Panel `fixed bottom-24 right-6 w-96 max-h-[32rem]`, header "PESET Live Chat — CS 24 jam", daftar pesan, input + kirim.
- Gaya pesan: bot=violet, visitor=teal kanan, admin=biru.
- Sesi di localStorage (`peset_chat_session`).

### Admin
- `pages/admin/live-chat.vue` (layout admin): dua panel — kiri daftar session (open dulu, badge unread, preview, waktu), kanan percakapan + balas.
- Sidebar `components/admin/Sidebar.vue`: tambah item "Live Chat" (icon SVG headset) dengan badge `item.notifs` (fitur sudah ada).
- `composables/useLiveChat.ts`: state unread total, subscribe `private-chat.staff`, toast + browser Notification, safety-net poll 5s.

## Error Handling

- Rate-limit route publik (`throttle:10,1` per session + global). Pesan max 1000 char, wajib.
- Session closed → tolak pesan visitor (403).
- Reverb down → Echo fallback ke poll (pesan tetap muncul via fetch riwayat, badge via unread-count).

## Testing

- Unit test `ChatbotService` (keyword match, fallback, normalisasi) DB-free — pola `GeminiServiceTest`. Constraint: sqlite tak support `gen_random_uuid()`, jadi tanpa feature test route.
- Verifikasi: `php artisan test` (host, `DB_HOST=127.0.0.1 DB_PORT=5433`), rebuild backend + up reverb, `npm run build`, uji manual end-to-end (browser visitor + admin).

## Deploy Notes

- Set `REVERB_*` + `BROADCAST_CONNECTION=reverb` di `.env` root & `backend/.env`; port `8080` harus di-expose.
- Jalankan migrate + `ChatbotFaqSeeder`.
- Jangan `migrate:fresh` (destruktif); verifikasi migrate dari host dengan `DB_HOST=127.0.0.1 DB_PORT=5433`.
