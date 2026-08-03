# Data Flow & Arsitektur — PESET

> Diagram arsitektur dan alur data sistem PESET.
> Diagram memakai **Mermaid** — otomatis dirender di GitHub & VS Code (ekstensi *Markdown Preview Mermaid Support*).

---

## 1. Arsitektur Sistem

```mermaid
flowchart TB
    subgraph Pengguna["👤 Pengguna"]
        V["Pengunjung (Browser)"]
        A["Petugas / Admin / Super Admin (Browser)"]
    end

    subgraph Frontend["Frontend — Nuxt 3"]
        PUB["Portal Publik<br/>katalog · GIS · chat"]
        ADM["Panel Admin<br/>9 modul · dashboard · chat"]
    end

    subgraph Backend["Backend — Laravel 13 (API)"]
        API["REST API"]
        AUTH["Sanctum Auth"]
        CB["ChatbotService<br/>FAQ + anti-typo"]
        AI["GeminiService<br/>Rekomendasi AI"]
        EVT["Broadcast Events<br/>(Reverb)"]
    end

    subgraph Data["Data"]
        DB[("PostgreSQL 15<br/>aset · pemanfaatan · POI · chat · dll.")]
    end

    subgraph Eksternal["Eksternal"]
        GEM["Google Gemini API"]
        WS["Reverb WebSocket<br/>localhost:8080"]
    end

    V --> PUB
    A --> ADM
    PUB --> API
    ADM --> API
    API --> AUTH
    API --> DB
    API --> CB
    API --> AI
    AI --> GEM
    API --> EVT
    EVT --> WS
    WS --> V
    WS --> A
    PUB --> WS
    ADM --> WS
```

---

## 2. Alur Publik — Melihat Aset & Rekomendasi

```mermaid
sequenceDiagram
    participant V as Pengunjung (Browser)
    participant FE as Frontend (Nuxt)
    participant API as Backend (Laravel)
    participant DB as PostgreSQL

    V->>FE: Buka portal / cari aset
    FE->>API: GET /api/public/aset
    API->>DB: Query aset (filter, paginate)
    DB-->>API: Daftar aset
    API-->>FE: Data aset (JSON)
    FE-->>V: Grid katalog aset

    V->>FE: Klik "Detail"
    FE->>API: GET /api/public/aset/{id}
    API->>DB: Ambil detail + foto + pemanfaatan
    DB-->>API: Detail lengkap
    API-->>FE: Detail aset

    V->>FE: Buka bagian "Rekomendasi AI"
    FE->>API: GET /api/public/aset/{id}/rekomendasi
    API->>DB: Ambil hasil sukses terakhir
    DB-->>API: Hasil rekomendasi (jika ada)
    API-->>FE: Tampilkan (tanpa panggil AI)
    FE-->>V: Ide, alasan, alternatif, estimasi
```

---

## 3. Alur Rekomendasi AI (Tombol di Panel Admin)

```mermaid
sequenceDiagram
    participant A as Petugas/Admin
    participant FE as Frontend Admin
    participant API as Backend (Laravel)
    participant DB as PostgreSQL
    participant GEM as Gemini API

    A->>FE: Klik "Rekomendasi AI" pada aset
    FE->>API: POST /api/rekomendasi-ai/{aset}
    API->>DB: Load aset + kategori + koordinat
    API->>DB: Ambil semua POI aktif
    API->>API: Hitung jarak (haversine), ambil ≤3 km
    API->>API: Susun prompt (aset + POI + peta kebutuhan + jenis legal)
    API->>GEM: generateContent (prompt, temp 0.4)
    GEM-->>API: JSON rekomendasi
    API->>API: Parse & validasi struktur
    API->>DB: Simpan ke rekomendasi_ai (sukses/gagal)
    API-->>FE: Hasil rekomendasi
    FE-->>A: Modal hasil (ide, alasan, alternatif)
```

**Alur cadangan (fallback):**
- Gemini timeout/error → simpan `status=gagal` + pesan → frontend tampilkan notifikasi error.
- Aset tanpa koordinat → prompt tanpa blok POI (tetap jalan).

---

## 4. Alur Live Chat + Chatbot

```mermaid
sequenceDiagram
    participant V as Pengunjung
    participant FE as Frontend (Widget)
    participant API as Backend (Laravel)
    participant CB as ChatbotService
    participant DB as PostgreSQL
    participant R as Reverb (WS)
    participant S as Petugas (Admin)

    V->>FE: Buka widget chat
    FE->>API: POST /api/public/chat/sessions
    API->>DB: Buat sesi (token rahasia)
    API-->>FE: sessionId + token + sapaan bot
    FE->>R: Subscribe chat.{sessionId}

    V->>FE: Kirim pertanyaan
    FE->>API: POST .../messages
    API->>CB: match(pertanyaan, 18 FAQ)
    CB-->>API: FAQ cocok (atau null)
    alt FAQ cocok
        API->>DB: Simpan balasan bot
        API->>R: Broadcast ChatMessageSent
    else Tidak cocok
        API->>DB: Simpan balasan standar + needs_attention
        API->>R: Broadcast ChatMessageSent
        API->>R: Broadcast ChatUnreadUpdated (ke staff)
        R-->>S: Badge unread + notifikasi
    end
    R-->>FE: Pesan bot tampil real-time

    S->>FE: Buka /admin/live-chat
    FE->>API: GET /api/chat/sessions
    API->>DB: Sesi open + unread + pesan terakhir
    S->>FE: Klik sesi, balas
    FE->>API: POST .../messages
    API->>DB: Simpan pesan admin
    API->>R: Broadcast ChatMessageSent
    R-->>FE: Pengunjung lihat balasan real-time
```

**Fallback:** bila WebSocket putus → widget memuat riwayat via fetch + unread via poll (5 detik).

---

## 5. Alur CRUD Aset & Import Excel

```mermaid
flowchart LR
    P["Petugas login"] --> M["Modul Aset"]
    M --> CRUD["Tambah / Edit / Hapus aset<br/>+ foto, koordinat, riwayat"]
    M --> IMP["Import Excel"]
    IMP --> TM["Download template"]
    IMP --> UP["Upload file .xlsx"]
    UP --> VX["Validasi & import massal"]
    VX --> DB[("PostgreSQL")]
    CRUD --> DB
```

---

## 6. Alur Autentikasi & Peran

```mermaid
flowchart LR
    U["User buka /admin"] --> L["Halaman Login"]
    L --> L1{"Punya akun?"}
    L1 -->|"Belum"| R["Daftar (email @pemkomedan.go.id)"]
    R --> MAP["Role otomatis dari domain email"]
    MAP --> TOK["Token + cookie"]
    L1 -->|"Ya"| LOG["Login"]
    LOG --> TOK
    TOK --> RB{"Cek peran"}
    RB -->|"Super Admin"| SA["Semua modul<br/>termasuk Users & Roles"]
    RB -->|"Admin"| AD["Modul master & operasional"]
    RB -->|"Petugas"| PT["Aset, pemanfaatan, chat, AI"]
```

---

## 7. Alur Data — Ringkasan End-to-End

```mermaid
flowchart TB
    subgraph Input["Masukan"]
        I1["Petugas (input/import aset)"]
        I2["Admin (master data)"]
        I3["Publik (pertanyaan chat)"]
        I4["Sistem (jarak ke POI)"]
    end

    subgraph Proses["Proses Inti"]
        P1["Kelola & simpan data aset"]
        P2["Sinkron status pemanfaatan"]
        P3["Rekomendasi AI (Gemini + POI)"]
        P4["Chatbot FAQ + anti-typo"]
    end

    subgraph Output["Keluaran"]
        O1["Portal publik (katalog, peta, detail)"]
        O2["Dashboard & laporan admin"]
        O3["Usulan pemanfaatan (AI)"]
        O4["Jawaban chat real-time"]
    end

    I1 --> P1
    I2 --> P1
    P1 --> P2
    P3 --> O3
    I4 --> P3
    I3 --> P4
    P4 --> O4
    P1 --> O1
    P2 --> O2
```

---
*Selengkapnya: [SRS](./03-srs.md) · [MVP & Roadmap](./05-mvp-roadmap.md)*
