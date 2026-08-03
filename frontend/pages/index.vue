<template>
  <div class="min-h-screen bg-teal-50/40 text-slate-800">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-teal-900/90 border-b border-teal-700/40 shadow-lg shadow-teal-900/10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
          <a href="#" class="flex items-center gap-3 group">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/15 flex items-center justify-center overflow-hidden ring-1 ring-white/30 shadow-inner group-hover:scale-105 transition-transform duration-300">
              <img src="/logo-pemko.jpg" alt="Pemko Medan" class="h-7 sm:h-8">
            </div>
            <div>
              <p class="text-base sm:text-lg font-extrabold text-white leading-tight tracking-tight">PESET</p>
              <p class="text-[11px] sm:text-xs text-teal-200 leading-tight">Pemanfaatan Aset Daerah</p>
            </div>
          </a>
          <nav class="hidden md:flex items-center gap-1">
            <a href="#beranda" class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-teal-700/60 shadow-sm">Beranda</a>
            <a href="#aset" class="px-4 py-2 text-sm font-medium text-slate-200 hover:text-white rounded-lg hover:bg-white/10 transition-all">Daftar Aset</a>
            <a href="#cara" class="px-4 py-2 text-sm font-medium text-slate-200 hover:text-white rounded-lg hover:bg-white/10 transition-all">Cara Pemanfaatan</a>
            <a href="#faq" class="px-4 py-2 text-sm font-medium text-slate-200 hover:text-white rounded-lg hover:bg-white/10 transition-all">FAQ</a>
            <a href="#tentang" class="px-4 py-2 text-sm font-medium text-slate-200 hover:text-white rounded-lg hover:bg-white/10 transition-all">Tentang</a>
          </nav>
          <div class="flex items-center gap-3">
            <a href="#aset" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-sm font-bold text-teal-900 bg-teal-300 hover:bg-teal-200 hover:shadow-lg hover:shadow-teal-400/30 transition-all duration-300">
              Ajukan
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </header>

    <!-- Hero Carousel -->
    <section id="beranda" class="relative overflow-hidden text-white h-[62vh] sm:h-[70vh] lg:h-[75vh]">
      <div class="absolute inset-0 bg-teal-950">
        <img v-for="(bg, i) in heroBg" :key="i"
          :src="bg"
          class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000"
          :class="slideIndex === i ? 'opacity-100' : 'opacity-0'" />
        <div class="absolute inset-0 bg-gradient-to-br from-teal-950/90 via-teal-900/70 to-teal-800/40" />
        <div class="absolute inset-0 bg-gradient-to-t from-teal-950/80 via-transparent to-transparent" />
      </div>
      <div class="relative flex flex-col h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-28">
        <div class="max-w-3xl">
          <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-xs sm:text-sm font-semibold text-teal-100 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-teal-300 animate-pulse"></span>
            Portal Resmi Pemerintah Kota Medan
          </span>
          <h1 class="mt-4 sm:mt-6 text-3xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight [text-shadow:0_2px_24px_rgba(0,0,0,0.45)]">
            Manfaatkan Aset<br class="sm:hidden">
            <span class="text-teal-300">Daerah Kota Medan</span>
          </h1>
          <p class="text-base sm:text-lg text-slate-200 mt-4 sm:mt-6 leading-relaxed max-w-2xl [text-shadow:0_1px_10px_rgba(0,0,0,0.35)]">
            Temukan lahan, gedung, dan aset daerah yang tersedia untuk kerjasama, sewa,
            dan pemanfaatan lainnya. Proses transparan, terpercaya, dan mudah.
          </p>
          <div class="mt-6 sm:mt-9">
            <div class="flex gap-2 sm:gap-3 max-w-xl">
              <div class="relative flex-1">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input v-model="search" @input="onSearch" type="text" placeholder="Cari aset, lokasi, atau kategori..."
                  class="w-full pl-12 pr-4 py-3.5 sm:py-4 bg-white/10 border border-white/25 rounded-xl text-white placeholder-teal-200/70 text-base focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-teal-400 backdrop-blur-sm shadow-xl shadow-teal-950/20 transition-all">
              </div>
              <button @click="fetchData" class="flex-shrink-0 px-5 sm:px-6 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base font-bold text-teal-900 bg-teal-300 hover:bg-teal-200 hover:scale-[1.03] active:scale-95 shadow-lg shadow-teal-400/30 transition-all duration-200">
                Cari
              </button>
            </div>
          </div>
          <div class="flex items-center gap-3 mt-6 sm:mt-8 text-xs sm:text-sm text-teal-100/80">
            <a href="#aset" class="inline-flex items-center gap-1.5 hover:text-white transition-colors font-medium">
              Jelajahi aset tersedia
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
      <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-2.5">
        <button v-for="(_, i) in heroBg" :key="i" @click="slideIndex = i" :aria-label="'Slide ' + (i+1)"
          class="h-2 rounded-full transition-all duration-300 cursor-pointer"
          :class="slideIndex === i ? 'w-8 bg-teal-300' : 'w-2 bg-white/50 hover:bg-white/80'" />
      </div>
    </section>

    <!-- Statistik Real-time -->
    <section class="relative z-10 -mt-10 sm:-mt-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
          <div v-for="(s, key) in statCards" :key="key"
            class="reveal bg-white rounded-2xl p-4 sm:p-6 border border-teal-100 shadow-xl shadow-teal-900/5 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between">
              <p class="text-[11px] sm:text-xs font-semibold text-teal-700 uppercase tracking-wider">{{ s.label }}</p>
              <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center" :class="s.iconBg">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="s.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="s.iconD"/>
                </svg>
              </div>
            </div>
            <p class="mt-2 sm:mt-3 font-extrabold text-slate-900 tracking-tight tabular-nums break-words leading-tight" :style="statFontStyle(s.format(stats[s.key]))">{{ s.format(stats[s.key]) }}</p>
            <p class="mt-1 text-[11px] sm:text-xs text-slate-400">{{ s.note }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Filter Kategori -->
    <section class="bg-white/80 backdrop-blur-md border-b border-teal-100 sticky top-16 sm:top-20 z-40 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
          <button @click="kategoriFilter = ''" :class="kategoriFilter === '' ? 'bg-teal-700 text-white shadow-md shadow-teal-200' : 'bg-teal-50 text-teal-700 hover:bg-teal-100'"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
            Semua
          </button>
          <button v-for="k in kategoriList" :key="k.id" @click="kategoriFilter = k.id"
            :class="kategoriFilter === k.id ? 'bg-teal-700 text-white shadow-md shadow-teal-200' : 'bg-teal-50 text-teal-700 hover:bg-teal-100'"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
            {{ k.kode_kib }} — {{ k.nama_kategori }}
          </button>
        </div>
      </div>
    </section>

    <!-- Daftar Aset -->
    <section id="aset" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-7">
        <div v-for="i in 6" :key="i" class="rounded-2xl border border-teal-100 bg-white overflow-hidden animate-pulse shadow-sm">
          <div class="h-48 sm:h-52 bg-teal-50" />
          <div class="p-5 space-y-3">
            <div class="h-3 bg-teal-50 rounded w-1/3" />
            <div class="h-5 bg-teal-50 rounded w-3/4" />
            <div class="h-4 bg-teal-50 rounded w-2/3" />
            <div class="h-px bg-teal-50 my-2" />
            <div class="flex justify-between">
              <div class="h-4 bg-teal-50 rounded w-1/3" />
              <div class="h-4 bg-teal-50 rounded w-1/4" />
            </div>
          </div>
        </div>
      </div>

      <!-- Kosong -->
      <div v-else-if="items.length === 0" class="reveal text-center py-16 sm:py-20">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-teal-50 flex items-center justify-center">
          <svg class="w-8 h-8 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
        </div>
        <p class="text-lg font-bold text-slate-700 mt-4">Belum ada aset ditemukan</p>
        <p class="text-sm text-slate-400 mt-1">Coba ubah kata kunci pencarian atau filter</p>
      </div>

      <!-- Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-7">
        <div v-for="item in items" :key="item.id"
          class="group relative rounded-2xl border border-teal-100 bg-white overflow-hidden hover:shadow-2xl hover:shadow-teal-900/10 hover:-translate-y-1 hover:border-teal-300 transition-all duration-300 cursor-pointer"
          @click="openDetail(item)">
          <div class="absolute top-0 left-0 right-0 h-1.5 z-10" :style="{ backgroundColor: kibColor(item.kategori?.kode_kib) }" />
          <div class="relative h-48 sm:h-52 bg-teal-50 overflow-hidden">
            <div v-if="item.foto && item.foto.length" class="w-full h-full">
              <img :src="fotoUrl(item.foto[0].file_path)" :alt="item.nama_barang" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            </div>
            <div v-else class="flex items-center justify-center h-full">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-12 h-12 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-xs text-teal-400">Tidak ada foto</span>
              </div>
            </div>
            <div v-if="item.foto && item.foto.length" class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/30 to-transparent" />
            <div class="absolute top-3 right-3">
              <span :class="statusClass(item.status)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold shadow-lg backdrop-blur-sm">
                {{ statusLabel(item.status) }}
              </span>
            </div>
            <div v-if="item.kategori?.kode_kib" class="absolute top-3 left-3">
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold text-white shadow-lg backdrop-blur-sm" :style="{ backgroundColor: kibColor(item.kategori.kode_kib) }">
                {{ item.kategori.kode_kib }}
              </span>
            </div>
          </div>
          <div class="p-5">
            <p class="text-[11px] font-semibold text-teal-600 uppercase tracking-widest">{{ item.kategori?.nama_kategori || '—' }}</p>
            <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-1.5 leading-snug group-hover:text-teal-700 transition-colors">{{ item.nama_barang }}</h3>
            <div class="flex items-center gap-1.5 mt-2.5 text-sm text-slate-500">
              <svg class="w-4 h-4 flex-shrink-0 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span class="truncate">{{ item.alamat || '—' }}</span>
            </div>
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-teal-50">
              <div class="flex items-center gap-2 text-sm text-slate-500">
                <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="font-medium text-slate-600 truncate">{{ item.opd?.nama_opd || '—' }}</span>
              </div>
              <span class="inline-flex items-center gap-1 text-sm font-semibold text-teal-700 group-hover:text-teal-600 transition-colors">
                Detail
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-center gap-3 mt-10 sm:mt-14">
        <button :disabled="page <= 1" @click="page--; fetchData()"
          class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold border border-teal-200 bg-white text-slate-700 hover:bg-teal-50 hover:border-teal-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Sebelumnya
        </button>
        <div class="flex items-center gap-2">
          <template v-for="p in Math.min(totalPages, 5)" :key="p">
            <button v-if="p === page || p === 1 || p === totalPages || Math.abs(p - page) <= 1"
              @click="page = p; fetchData()"
              :class="p === page ? 'bg-teal-700 text-white shadow-md shadow-teal-200' : 'bg-white text-slate-600 border border-teal-200 hover:bg-teal-50'"
              class="w-10 h-10 rounded-xl text-sm font-semibold transition-all">
              {{ p }}
            </button>
          </template>
          <span v-if="totalPages > 5 && page < totalPages - 2" class="text-slate-400 px-1">...</span>
        </div>
        <span class="text-sm text-slate-400 px-1 hidden sm:block">dari {{ totalPages }}</span>
        <button :disabled="page >= totalPages" @click="page++; fetchData()"
          class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold border border-teal-200 bg-white text-slate-700 hover:bg-teal-50 hover:border-teal-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm">
          Berikutnya
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </section>

    <!-- Cara Pemanfaatan -->
    <section id="cara" class="bg-white border-t border-teal-50 py-16 sm:py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-2xl mx-auto text-center">
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-50 text-teal-700 text-xs font-bold uppercase tracking-widest">Cara Kerja</span>
          <h2 class="mt-4 text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Cara Pemanfaatan Aset</h2>
          <p class="mt-3 text-slate-500 leading-relaxed">Empat langkah sederhana untuk mulai memanfaatkan aset daerah</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-8 mt-12">
          <div v-for="(step, i) in steps" :key="i"
            class="reveal group relative bg-teal-50/60 rounded-2xl p-6 sm:p-7 border border-teal-100 hover:bg-white hover:shadow-2xl hover:shadow-teal-900/10 hover:-translate-y-1.5 transition-all duration-300">
            <div class="absolute -top-3 -left-3 w-8 h-8 rounded-full bg-teal-700 text-white flex items-center justify-center text-sm font-bold shadow-lg">{{ i + 1 }}</div>
            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-md group-hover:bg-teal-700 group-hover:scale-110 transition-all duration-300">
              <svg class="w-6 h-6 text-teal-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="step.icon"/>
              </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-slate-900">{{ step.title }}</h3>
            <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Aset Unggulan -->
    <section id="unggulan" class="bg-teal-50/60 border-t border-teal-100 py-16 sm:py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col sm:flex-row sm:items-end justify-between gap-4">
          <div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-teal-700 text-xs font-bold uppercase tracking-widest border border-teal-100">Pilihan</span>
            <h2 class="mt-4 text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Aset Unggulan</h2>
            <p class="mt-3 text-slate-500">Beberapa aset terbaik yang siap untuk dimanfaatkan</p>
          </div>
          <div class="flex items-center gap-2">
            <button @click="scrollFeatured(-1)" :aria-label="'Sebelumnya'" class="w-11 h-11 rounded-xl bg-white border border-teal-200 flex items-center justify-center text-teal-700 hover:bg-teal-700 hover:text-white transition-all shadow-sm">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="scrollFeatured(1)" :aria-label="'Berikutnya'" class="w-11 h-11 rounded-xl bg-teal-700 border border-teal-700 flex items-center justify-center text-white hover:bg-teal-800 transition-all shadow-md">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>
        <div ref="featuredTrack" class="flex gap-5 sm:gap-7 overflow-x-auto scrollbar-hide snap-x snap-mandatory mt-10 pb-2 -mx-4 px-4">
          <div v-for="item in featured" :key="item.id"
            class="reveal snap-start shrink-0 w-full sm:w-[calc(50%-14px)] lg:w-[calc(33.333%-19px)] group relative rounded-2xl border border-teal-100 bg-white overflow-hidden hover:shadow-2xl hover:shadow-teal-900/10 hover:-translate-y-1 transition-all duration-300 cursor-pointer"
            @click="openDetail(item)">
            <div class="absolute top-0 left-0 right-0 h-1.5 z-10" :style="{ backgroundColor: kibColor(item.kategori?.kode_kib) }" />
            <div class="relative h-44 sm:h-48 bg-teal-50 overflow-hidden">
              <img v-if="item.foto && item.foto.length" :src="fotoUrl(item.foto[0].file_path)" :alt="item.nama_barang" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
              <div v-else class="flex items-center justify-center h-full">
                <svg class="w-12 h-12 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
              <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/30 to-transparent" />
              <span class="absolute top-3 left-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-teal-700/90 backdrop-blur-sm shadow-lg">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.9 6.26L21 9.27l-5 4.87L17.18 21 12 17.77 6.82 21 8 14.14l-5-4.87 6.1-1.01z"/></svg>
                Unggulan
              </span>
            </div>
            <div class="p-5">
              <p class="text-[11px] font-semibold text-teal-600 uppercase tracking-widest">{{ item.kategori?.nama_kategori || '—' }}</p>
              <h3 class="text-base font-bold text-slate-900 mt-1.5 leading-snug group-hover:text-teal-700 transition-colors">{{ item.nama_barang }}</h3>
              <div class="flex items-center justify-between mt-3 pt-3 border-t border-teal-50">
                <div class="flex items-center gap-2 text-sm text-slate-500 min-w-0">
                  <svg class="w-3.5 h-3.5 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  <span class="truncate">{{ item.alamat || '—' }}</span>
                </div>
                <span :class="statusClass(item.status)" class="flex-shrink-0 px-2.5 py-0.5 rounded-full text-xs font-semibold">{{ statusLabel(item.status) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Alur Permohonan + FAQ -->
    <section id="faq" class="bg-white border-t border-teal-50 py-16 sm:py-24">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-2xl mx-auto text-center">
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-50 text-teal-700 text-xs font-bold uppercase tracking-widest">Alur & FAQ</span>
          <h2 class="mt-4 text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Alur Permohonan &amp; Pertanyaan</h2>
          <p class="mt-3 text-slate-500 leading-relaxed">Semua yang perlu Anda ketahui sebelum mengajukan pemanfaatan</p>
        </div>

        <div class="mt-12 space-y-3">
          <div v-for="(faq, i) in faqs" :key="i"
            class="reveal rounded-2xl border border-teal-100 bg-white overflow-hidden transition-all duration-300"
            :class="faqOpen === i ? 'shadow-lg shadow-teal-900/5 border-teal-200' : 'hover:border-teal-200'">
            <button @click="faqOpen = faqOpen === i ? -1 : i"
              class="w-full flex items-center justify-between gap-4 px-5 sm:px-6 py-4 sm:py-5 text-left">
              <div class="flex items-start gap-3">
                <span class="mt-0.5 w-8 h-8 flex-shrink-0 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="faq.icon"/>
                  </svg>
                </span>
                <span class="text-sm sm:text-base font-semibold text-slate-800">{{ faq.q }}</span>
              </div>
              <svg class="w-5 h-5 flex-shrink-0 text-teal-600 transition-transform duration-300" :class="faqOpen === i ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div v-show="faqOpen === i" class="px-5 sm:px-6 pb-5 sm:pb-6 pl-16">
              <p class="text-sm text-slate-500 leading-relaxed">{{ faq.a }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tentang + Tim OPD -->
    <section id="tentang" class="bg-teal-50/60 border-t border-teal-100 py-16 sm:py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
          <div class="reveal">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-teal-700 text-xs font-bold uppercase tracking-widest border border-teal-100">Tentang PESET</span>
            <h2 class="mt-4 text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">Portal Pemanfaatan Aset Daerah Kota Medan</h2>
            <p class="mt-4 text-slate-500 leading-relaxed">
              PESET (Pemanfaatan Aset Daerah) adalah portal resmi Pemerintah Kota Medan
              untuk mempromosikan dan menawarkan aset daerah yang dapat dimanfaatkan oleh
              pihak ketiga melalui kerjasama, sewa, pinjam pakai, dan skema lainnya.
              Transparan, terverifikasi, dan mudah untuk semua pihak.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8">
              <div v-for="f in features" :key="f.title" class="bg-white rounded-2xl p-5 border border-teal-100 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center" :class="f.bg">
                  <svg class="w-5 h-5" :class="f.fg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="f.icon"/>
                  </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 mt-3">{{ f.title }}</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ f.desc }}</p>
              </div>
            </div>
          </div>
          <div class="reveal bg-white rounded-3xl border border-teal-100 shadow-2xl shadow-teal-900/10 p-6 sm:p-8">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center overflow-hidden ring-1 ring-teal-100">
                <img src="/logo-pemko.jpg" alt="Pemko Medan" class="h-10">
              </div>
              <div>
                <h3 class="text-lg font-bold text-slate-900">BPKAD Kota Medan</h3>
                <p class="text-sm text-slate-400">Badan Pengelolaan Keuangan dan Aset Daerah</p>
              </div>
            </div>
            <div class="mt-6 space-y-4">
              <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center flex-shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                  <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Alamat</p>
                  <p class="text-sm font-medium text-slate-700 mt-0.5">Jl. Kapten Maulana Lubis No. 2, Medan</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center flex-shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                  <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Telepon</p>
                  <p class="text-sm font-medium text-slate-700 mt-0.5">(061) 451-2345</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center flex-shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                  <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Email</p>
                  <p class="text-sm font-medium text-slate-700 mt-0.5">bpkad@pemkomedan.go.id</p>
                </div>
              </div>
            </div>
            <div class="mt-6 pt-6 border-t border-teal-50">
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Skema Pemanfaatan</p>
              <div class="flex flex-wrap gap-2 mt-3">
                <span v-for="s in skema" :key="s" class="px-3 py-1.5 rounded-full bg-teal-50 text-teal-700 text-xs font-semibold border border-teal-100">{{ s }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer id="kontak" class="bg-teal-950 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-12">
          <div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center overflow-hidden ring-1 ring-white/20">
                <img src="/logo-pemko.jpg" alt="Pemko Medan" class="h-7">
              </div>
              <div>
                <p class="text-lg font-extrabold">PESET</p>
                <p class="text-xs text-teal-200/70">Pemanfaatan Aset Daerah</p>
              </div>
            </div>
            <p class="text-sm text-slate-400 mt-4 leading-relaxed">
              Portal resmi Pemerintah Kota Medan untuk informasi dan pengelolaan pemanfaatan aset daerah.
            </p>
          </div>
          <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-teal-200/70">Kontak</h3>
            <div class="mt-4 space-y-3 text-sm text-slate-300">
              <p>BPKAD Kota Medan</p>
              <p>Jl. Kapten Maulana Lubis No. 2, Medan</p>
              <p>Telp: (061) 451-2345</p>
              <p>Email: bpkad@pemkomedan.go.id</p>
            </div>
          </div>
          <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-teal-200/70">Link Terkait</h3>
            <div class="mt-4 space-y-3 text-sm">
              <a href="https://pemkomedan.go.id" class="block text-slate-300 hover:text-white transition-colors">pemkomedan.go.id</a>
              <a href="https://bpkad.pemkomedan.go.id" class="block text-slate-300 hover:text-white transition-colors">bpkad.pemkomedan.go.id</a>
            </div>
          </div>
        </div>
        <div class="border-t border-teal-800/60 mt-8 sm:mt-12 pt-6 sm:pt-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
          <p>&copy; {{ new Date().getFullYear() }} Pemerintah Kota Medan. Seluruh hak cipta dilindungi.</p>
          <p class="text-teal-200/50 text-xs">Portal Pemanfaatan Aset Daerah</p>
        </div>
      </div>
    </footer>

    <!-- Modal Detail -->
    <div v-if="detailItem" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto pt-4 sm:pt-10 pb-10" @click.self="detailItem = null">
      <div class="fixed inset-0 bg-teal-950/60 backdrop-blur-sm" @click="detailItem = null" />
      <div class="relative bg-white rounded-2xl w-full max-w-3xl mx-4 shadow-2xl overflow-hidden z-10">
        <button @click="detailItem = null" class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/90 rounded-full flex items-center justify-center shadow-md hover:bg-white hover:scale-105 transition-all">
          <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <div v-if="detailFoto && detailFoto.length" class="relative h-56 sm:h-72 bg-teal-50 group">
          <img :src="fotoUrl(detailFoto[fotoIdx].file_path)" :key="fotoIdx" :alt="detailItem.nama_barang" class="w-full h-full object-cover transition-opacity duration-300">
          <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent" />
          <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5">
            <span v-for="(_, i) in detailFoto" :key="i" class="w-1.5 h-1.5 rounded-full transition-all" :class="i === fotoIdx ? 'bg-white w-3' : 'bg-white/50'" />
          </div>
          <button v-if="detailFoto.length > 1" @click="fotoIdx = fotoIdx > 0 ? fotoIdx - 1 : detailFoto.length - 1" type="button" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <button v-if="detailFoto.length > 1" @click="fotoIdx = fotoIdx < detailFoto.length - 1 ? fotoIdx + 1 : 0" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
          <div class="absolute top-3 right-3 bg-black/50 text-white text-xs font-semibold px-2.5 py-1 rounded-full backdrop-blur-sm">
            {{ fotoIdx + 1 }} / {{ detailFoto.length }}
          </div>
        </div>
        <div v-else class="h-56 sm:h-72 bg-teal-50 flex items-center justify-center">
          <svg class="w-16 h-16 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <div class="p-6 sm:p-8">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold text-teal-600 uppercase tracking-wider">{{ detailItem.kategori?.nama_kategori || '—' }}</p>
              <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">{{ detailItem.nama_barang }}</h2>
            </div>
            <span :class="statusClass(detailItem.status)" class="flex-shrink-0 px-3 py-1 rounded-full text-xs font-semibold">
              {{ statusLabel(detailItem.status) }}
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-6 sm:mt-8">
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kode Barang</p>
              <p class="text-base font-medium text-slate-900 mt-1">{{ detailItem.kode_barang || '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Register</p>
              <p class="text-base font-medium text-slate-900 mt-1">{{ detailItem.register || '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">OPD Pengelola</p>
              <p class="text-base font-medium text-slate-900 mt-1">{{ detailItem.opd?.nama_opd || '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tahun Perolehan</p>
              <p class="text-base font-medium text-slate-900 mt-1">{{ detailItem.tahun_perolehan || '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nilai Perolehan</p>
              <p class="text-base font-medium text-slate-900 mt-1">{{ detailItem.nilai_perolehan ? formatRp(detailItem.nilai_perolehan) : '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nilai Buku</p>
              <p class="text-base font-medium text-slate-900 mt-1">{{ detailItem.nilai_buku ? formatRp(detailItem.nilai_buku) : '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Luas</p>
              <p class="text-base font-medium text-slate-900 mt-1">{{ detailItem.luas ? detailItem.luas + ' m²' : '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kondisi</p>
              <p class="text-base font-medium text-slate-900 mt-1">{{ kondisiLabel(detailItem.kondisi) || '—' }}</p>
            </div>
          </div>

          <div v-if="detailItem.alamat" class="mt-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Alamat</p>
            <p class="text-base text-slate-700 mt-1 leading-relaxed">{{ detailItem.alamat }}</p>
          </div>

          <div v-if="detailItem.keterangan" class="mt-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Keterangan</p>
            <p class="text-base text-slate-700 mt-1">{{ detailItem.keterangan }}</p>
          </div>

          <!-- GIS / Peta -->
          <div v-if="detailGis || detailItem.luas" class="mt-6 border-t border-teal-50 pt-6">
            <p class="text-sm font-bold text-slate-900 mb-3">Peta & Koordinat</p>
            <div v-if="detailGis" class="grid grid-cols-2 gap-3 mb-3">
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Latitude</p>
                <p class="text-sm font-medium text-slate-900 mt-0.5">{{ detailGis.latitude || '—' }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Longitude</p>
                <p class="text-sm font-medium text-slate-900 mt-0.5">{{ detailGis.longitude || '—' }}</p>
              </div>
            </div>
            <div v-if="detailGis" class="grid grid-cols-2 gap-3 mb-3">
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Layer</p>
                <p class="text-sm font-medium text-slate-900 mt-0.5">{{ detailGis.layer?.nama_layer || '—' }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tipe Geometri</p>
                <p class="text-sm font-medium text-slate-900 mt-0.5">{{ detailGis.tipe_geometri || '—' }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Luas Tanah</p>
                <p class="text-sm font-medium text-slate-900 mt-0.5">{{ detailItem.luas ? detailItem.luas + ' m²' : '—' }}</p>
              </div>
              <div v-if="calcPolygonArea">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Luas Polygon</p>
                <p class="text-sm font-medium text-slate-900 mt-0.5">{{ calcPolygonArea }} m²</p>
              </div>
            </div>
            <div ref="mapContainer" class="w-full h-64 sm:h-80 rounded-xl border border-teal-100 overflow-hidden z-0"></div>
          </div>

          <div v-if="detailPemanfaatan && detailPemanfaatan.length" class="mt-6 border-t border-teal-50 pt-6">
            <p class="text-sm font-bold text-slate-900 mb-3">Riwayat Pemanfaatan</p>
            <div class="space-y-3">
              <div v-for="p in detailPemanfaatan" :key="p.id" class="bg-teal-50/60 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-slate-900">{{ p.jenis?.nama || '—' }}</p>
                  <span :class="p.status === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'" class="px-2.5 py-0.5 rounded-full text-xs font-medium">{{ p.status }}</span>
                </div>
                <p class="text-sm text-slate-500 mt-1">{{ p.nomor_perjanjian }} — {{ p.pihak_ketiga?.nama || '—' }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ formatDate(p.tanggal_mulai) }} s/d {{ formatDate(p.tanggal_selesai) }}</p>
              </div>
            </div>
          </div>

          <div v-if="detailRekomendasi" class="mt-6 border-t border-teal-50 pt-6">
            <p class="text-sm font-bold text-slate-900 mb-3">Rekomendasi Pemanfaatan (AI)</p>
            <div class="rounded-xl bg-violet-50 border border-violet-100 p-4">
              <div class="flex items-start justify-between gap-3">
                <p class="text-base font-bold text-slate-900 leading-snug">{{ detailRekomendasi.ide_utama }}</p>
                <span class="flex-shrink-0 px-2.5 py-1 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">{{ detailRekomendasi.jenis_pemanfaatan }}</span>
              </div>
              <ul class="mt-3 space-y-2">
                <li v-for="(a, i) in detailRekomendasi.alasan" :key="i" class="flex gap-2 text-sm text-slate-700">
                  <span class="text-violet-600 mt-0.5">•</span><span>{{ a }}</span>
                </li>
              </ul>
              <div v-if="detailRekomendasi.alternatif && detailRekomendasi.alternatif.length" class="mt-3 space-y-2">
                <div v-for="(alt, i) in detailRekomendasi.alternatif" :key="'alt' + i" class="bg-white rounded-lg p-3 border border-violet-100">
                  <p class="text-sm font-semibold text-slate-800">{{ alt.ide }}</p>
                  <p class="text-xs text-slate-500 mt-1">{{ alt.alasan }}</p>
                </div>
              </div>
              <p class="text-[11px] text-slate-400 mt-3">Rekomendasi otomatis dari AI sebagai bahan pertimbangan.</p>
            </div>
          </div>

          <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <a :href="'https://wa.me/628617861111?text=Halo%20saya%20tertarik%20dengan%20aset%20' + encodeURIComponent(detailItem.nama_barang) + '%20(kode:%20' + encodeURIComponent(detailItem.kode_barang) + ')'" target="_blank"
              class="flex-1 flex items-center justify-center gap-2 bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3 sm:py-4 px-6 rounded-xl text-base transition-all duration-200 hover:shadow-xl hover:shadow-teal-700/20">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              Ajukan via WhatsApp
            </a>
            <a :href="'mailto:bpkad@pemkomedan.go.id?subject=Minat%20Pemanfaatan%20Aset:%20' + encodeURIComponent(detailItem.nama_barang) + '&body=Assalamualaikum%20wr%20wb%0A%0ASaya%20tertarik%20dengan%20aset:%0A%0ANama:%20' + encodeURIComponent(detailItem.nama_barang) + '%0AKode:%20' + encodeURIComponent(detailItem.kode_barang) + '%0A%0AMohon%20informasi%20lebih%20lanjut.%0A%0ATerima%20kasih.'"
              class="flex-1 flex items-center justify-center gap-2 bg-white border-2 border-teal-200 hover:border-teal-400 text-slate-700 hover:text-teal-700 font-semibold py-3 sm:py-4 px-6 rounded-xl text-base transition-all duration-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              Kirim Email
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { area } from '@turf/turf'

const config = useRuntimeConfig()
const apiBase = config.public.apiBase

const search = ref('')
const kategoriFilter = ref('')
const page = ref(1)
const totalPages = ref(1)
const items = ref<any[]>([])
const loading = ref(true)
const kategoriList = ref<any[]>([])
const heroBg = ['/logo-pemko.jpg', '/bkad-jam.jpg']
const slideIndex = ref(0)
let slideTimer: ReturnType<typeof setInterval>
onMounted(() => { slideTimer = setInterval(() => { slideIndex.value = (slideIndex.value + 1) % heroBg.length }, 5000) })
onUnmounted(() => clearInterval(slideTimer))

const steps = [
  { title: 'Pilih Aset', desc: 'Telusuri dan pilih aset yang ingin dimanfaatkan dari daftar yang tersedia.', icon: 'M4 7v10m4-10v10m4-10v10m8-10v10M4 7a4 4 0 008 0m-4 4a4 4 0 008 0M2 21h20' },
  { title: 'Ajukan Proposal', desc: 'Kirim proposal pemanfaatan melalui WhatsApp atau email resmi BPKAD.', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
  { title: 'Verifikasi', desc: 'Tim BPKAD memverifikasi kelengkapan dan kelayakan proposal Anda.', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
  { title: 'Tandatangani Kontrak', desc: 'Proses kerjasama resmi ditandatangani dan aset siap dimanfaatkan.', icon: 'M12 4v16m8-8H4m16 0a8 8 0 11-16 0 8 8 0 0116 0z' },
]

const faqs = [
  { q: 'Siapa yang bisa mengajukan pemanfaatan aset?', a: 'Perseorangan, badan usaha, maupun organisasi dapat mengajukan pemanfaatan aset daerah selama memenuhi persyaratan dan skema pemanfaatan yang berlaku.', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
  { q: 'Apa saja skema pemanfaatan yang tersedia?', a: 'Tersedia skema kerjasama pemanfaatan (KSP), sewa, pinjam pakai, dan bangun guna serah (BGS) sesuai ketentuan yang berlaku.', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  { q: 'Bagaimana cara mengetahui biaya sewa aset?', a: 'Nilai sewa ditetapkan berdasarkan penilaian wajar oleh tim penilai aset dan diumumkan secara transparan melalui portal ini.', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  { q: 'Berapa lama proses pengajuan berlangsung?', a: 'Setelah proposal diterima, proses verifikasi dan peninjauan umumnya berlangsung 7–14 hari kerja tergantung kompleksitas aset.', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
  { q: 'Apakah saya bisa mengajukan untuk aset dengan status Dimanfaatkan?', a: 'Aset berstatus Dimanfaatkan sedang digunakan pihak lain. Kami sarankan memilih aset berstatus Tersedia untuk pengajuan yang lebih cepat.', icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
]

const features = [
  { title: 'Transparan', desc: 'Seluruh informasi aset dan pemanfaatan dapat diakses publik secara terbuka', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', bg: 'bg-teal-50', fg: 'text-teal-700' },
  { title: 'Terverifikasi', desc: 'Data aset diverifikasi oleh OPD pengelola dan BPKAD Kota Medan', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', bg: 'bg-sky-50', fg: 'text-sky-700' },
  { title: 'Mudah', desc: 'Proses pengajuan dan informasi dirancang sederhana dan mudah dipahami', icon: 'M13 10V3L4 14h7v7l9-11h-7z', bg: 'bg-emerald-50', fg: 'text-emerald-700' },
]

const skema = ['Kerjasama Pemanfaatan', 'Sewa', 'Pinjam Pakai', 'Bangun Guna Serah']

const featured = computed(() => items.value.filter((i) => ['Idle', 'Aktif'].includes(i.status)).slice(0, 6))
const featuredTrack = ref<HTMLElement | null>(null)
function scrollFeatured(dir: number) {
  featuredTrack.value?.scrollBy({ left: dir * 380, behavior: 'smooth' })
}

const faqOpen = ref(-1)

const statCards = [
  { key: 'total', label: 'Total Aset', note: 'Aset tanah & bangunan', iconBg: 'bg-teal-50', iconColor: 'text-teal-700', iconD: 'M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6', format: (v: number) => v.toLocaleString('id-ID') },
  { key: 'nilai', label: 'Nilai Aset', note: 'Nilai perolehan total', iconBg: 'bg-sky-50', iconColor: 'text-sky-700', iconD: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', format: (v: number) => formatRp(v) },
  { key: 'tersedia', label: 'Aset Tersedia', note: 'Siap untuk dimanfaatkan', iconBg: 'bg-emerald-50', iconColor: 'text-emerald-700', iconD: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', format: (v: number) => v.toLocaleString('id-ID') },
  { key: 'opd', label: 'OPD Pengelola', note: 'Instansi pengelola aset', iconBg: 'bg-amber-50', iconColor: 'text-amber-700', iconD: 'M3 21h18M5 21V7a2 2 0 012-2h10a2 2 0 012 2v14M9 9h6M9 13h6', format: (v: number) => v.toLocaleString('id-ID') },
]
const stats = ref({ total: 0, nilai: 0, tersedia: 0, opd: 0 })
const shown = reactive({ total: 0, nilai: 0, tersedia: 0, opd: 0 })
let statsAnimated = false
let statsIo: IntersectionObserver | null = null

async function loadStats() {
  try {
    const res = await fetch(`${apiBase}/api/public/statistik`)
    const json = await res.json()
    if (json.data) stats.value = json.data
  } catch {}
}

function animateStats() {
  if (statsAnimated) return
  statsAnimated = true
  statsIo?.disconnect()
  const t = performance.now()
  const dur = 1200
  const from: Record<string, number> = { total: 0, nilai: 0, tersedia: 0, opd: 0 }
  const to = { ...stats.value }
  function step(now: number) {
    const p = Math.min((now - t) / dur, 1)
    const ease = 1 - Math.pow(1 - p, 3)
    ;(['total', 'nilai', 'tersedia', 'opd'] as const).forEach((k) => {
      shown[k] = Math.round(from[k] + (to[k] - from[k]) * ease)
    })
    if (p < 1) requestAnimationFrame(step)
  }
  requestAnimationFrame(step)
}

let revealIo: IntersectionObserver | null = null

function setupReveal() {
  if (import.meta.server || typeof document === 'undefined') return
  if (typeof IntersectionObserver === 'undefined') {
    document.querySelectorAll('.reveal').forEach((el) => el.classList.add('reveal-visible'))
    return
  }
  revealIo = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        ;(e.target as HTMLElement).classList.add('reveal-visible')
        revealIo?.unobserve(e.target)
      }
    })
  }, { threshold: 0.12 })
  document.querySelectorAll('.reveal:not(.reveal-visible)').forEach((el) => revealIo?.observe(el))
}

onMounted(() => {
  loadStats()
  setupReveal()
  const cards = document.querySelectorAll('.grid.lg\\:grid-cols-4 .reveal')
  if (typeof IntersectionObserver !== 'undefined' && cards.length) {
    statsIo = new IntersectionObserver((entries) => {
      if (entries.some((e) => e.isIntersecting)) animateStats()
    }, { threshold: 0.3 })
    statsIo.observe(cards[0])
  } else {
    animateStats()
  }
})

const detailItem = ref<any | null>(null)
const detailFoto = ref<any[]>([])
const fotoIdx = ref(0)
const detailPemanfaatan = ref<any[]>([])
const detailGis = ref<any | null>(null)
const detailRekomendasi = ref<any | null>(null)
const calcPolygonArea = computed(() => {
  const g = detailGis.value?.polygon_geojson
  if (!g) return null
  try {
    const geo = typeof g === 'string' ? JSON.parse(g) : g
    if (geo.type !== 'Polygon' && geo.type !== 'MultiPolygon') return null
    return Math.round(area(geo) * 100) / 100
  } catch { return null }
})
const mapContainer = ref<HTMLDivElement | null>(null)
let mapInstance: any = null

let searchTimer: ReturnType<typeof setTimeout>

function fotoUrl(path: string) {
  if (!path) return ''
  return `${apiBase}/storage/${path}`
}

function formatRp(val: number) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val)
}

// ponytail: shrink font by text length so any value fits the card; floor at readable size
function statFontStyle(text: string) {
  const len = text.replace(/[^\d]/g, '').length
  const px = len <= 9 ? 30 : len <= 13 ? 24 : len <= 17 ? 20 : 16
  return { fontSize: `${px}px`, lineHeight: 1.2 }
}

function formatDate(d: string) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}

function statusLabel(s: string) {
  const map: Record<string, string> = { Aktif: 'Aktif', Idle: 'Tersedia', Dimanfaatkan: 'Dimanfaatkan' }
  return map[s] || s || '—'
}

function statusClass(s: string) {
  const map: Record<string, string> = {
    Aktif: 'bg-emerald-100 text-emerald-700',
    Idle: 'bg-amber-100 text-amber-700',
    Dimanfaatkan: 'bg-blue-100 text-blue-700',
  }
  return map[s] || 'bg-slate-100 text-slate-600'
}

function kondisiLabel(k: string) {
  const map: Record<string, string> = { Baik: 'Baik', Rusak_Ringan: 'Rusak Ringan', Rusak_Berat: 'Rusak Berat' }
  return map[k] || k || '—'
}

function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; fetchData() }, 400)
}

async function fetchData() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: String(page.value), per_page: '12', kib: 'KIB A,KIB C' })
    if (search.value) params.set('search', search.value)
    if (kategoriFilter.value) params.set('kategori', kategoriFilter.value)
    const res = await fetch(`${apiBase}/api/public/aset?${params}`)
    const json = await res.json()
    items.value = json.data || []
    totalPages.value = json.meta?.last_page || 1
  } catch { items.value = [] } finally {
    loading.value = false
    nextTick(() => setupReveal())
  }
}

function kibColor(kode: string | undefined) {
  const map: Record<string, string> = {
    'KIB A': '#22c55e',
    'KIB B': '#f97316',
    'KIB C': '#3b82f6',
    'KIB D': '#a855f7',
    'KIB E': '#ec4899',
    'KIB F': '#06b6d4',
  }
  return map[kode || ''] || '#64748b'
}

function initMap(retries = 0) {
  mapInstance?.remove()
  mapInstance = null
  if (!mapContainer.value || !detailGis.value) return
  if (!(window as any).L) {
    if (retries < 20) setTimeout(() => initMap(retries + 1), 250)
    return
  }
  const g = detailGis.value
  const L = (window as any).L
  let lat = parseFloat(g.latitude)
  let lng = parseFloat(g.longitude)
  if ((isNaN(lat) || isNaN(lng)) && g.polygon_geojson) {
    try {
      const geo = typeof g.polygon_geojson === 'string' ? JSON.parse(g.polygon_geojson) : g.polygon_geojson
      const center = L.geoJSON(geo).getBounds().getCenter()
      lat = center.lat; lng = center.lng
    } catch {}
  }
  if (isNaN(lat) || isNaN(lng)) return

  mapInstance = L.map(mapContainer.value, { zoomControl: true }).setView([lat, lng], 16)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(mapInstance)

  const color = kibColor(detailItem.value?.kategori?.kode_kib)

  if (g.polygon_geojson) {
    try {
      const geo = typeof g.polygon_geojson === 'string' ? JSON.parse(g.polygon_geojson) : g.polygon_geojson
      L.geoJSON(geo, {
        style: { color, weight: 2, fillColor: color, fillOpacity: 0.25 }
      }).addTo(mapInstance).bindPopup(`<b>${detailItem.value?.nama_barang}</b>`)
    } catch {}
  } else if (g.tipe_geometri === 'Point' || g.tipe_geometri === 'point') {
    L.marker([lat, lng])
      .addTo(mapInstance)
      .bindPopup(`<b>${detailItem.value?.nama_barang}</b><br>${lat}, ${lng}`)
  } else {
    L.circleMarker([lat, lng], {
      radius: 10, color, fillColor: color, fillOpacity: 0.4, weight: 2
    }).addTo(mapInstance).bindPopup(`<b>${detailItem.value?.nama_barang}</b>`)
  }

  setTimeout(() => mapInstance?.invalidateSize(), 300)
}

function genPolygonFromLuas(luas: number, lat = 3.5850, lng = 98.6753) {
  const offset = Math.sqrt(luas) / 222000 // ponytail: uniform square, improve with turf if precision needed
  const coords = [[lng - offset, lat - offset], [lng + offset, lat - offset], [lng + offset, lat + offset], [lng - offset, lat + offset], [lng - offset, lat - offset]]
  return { type: 'Polygon', coordinates: [coords] }
}

async function openDetail(item: any) {
  detailItem.value = item
  detailFoto.value = []
  fotoIdx.value = 0
  detailPemanfaatan.value = []
  detailGis.value = null
  detailRekomendasi.value = null
  try {
    const res = await fetch(`${apiBase}/api/public/aset/${item.id}`)
    const json = await res.json()
    if (json.foto) detailFoto.value = json.foto
    if (json.pemanfaatan) detailPemanfaatan.value = json.pemanfaatan
    if (json.data?.gis_aset) {
      detailGis.value = json.data.gis_aset
    } else if (parseFloat(item.luas) > 0) {
      detailGis.value = { latitude: 3.5850, longitude: 98.6753, polygon_geojson: genPolygonFromLuas(parseFloat(item.luas)), tipe_geometri: 'Polygon' }
    }
    await nextTick()
    initMap()
    fetch(`${apiBase}/api/public/aset/${item.id}/rekomendasi`)
      .then(r => r.json())
      .then(j => { detailRekomendasi.value = j.data || null })
      .catch(() => { detailRekomendasi.value = null })
  } catch {}
}

watch(detailGis, (v) => { if (v) nextTick(() => initMap()) })

watch(kategoriFilter, () => { page.value = 1; fetchData() })

// ponytail: derives kategori from aset data; add /public/kategori if categories with 0 assets needed
async function loadKategori() {
  try {
    const res = await fetch(`${apiBase}/api/public/aset?per_page=100`)
    const json = await res.json()
    const cats = new Map()
    ;(json.data || []).forEach((a: any) => {
      const k = a.kategori
      if (k && k.id && ['KIB A','KIB C'].includes(k.kode_kib) && !cats.has(k.id)) {
        cats.set(k.id, { id: k.id, nama_kategori: k.nama_kategori, kode_kib: k.kode_kib })
      }
    })
    kategoriList.value = Array.from(cats.values())
  } catch {}
}

fetchData()
loadKategori()
</script>

<style>
html {
  scroll-behavior: smooth;
}
@media (prefers-reduced-motion: reduce) {
  html {
    scroll-behavior: auto;
  }
}
</style>

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.reveal.reveal-visible {
  opacity: 1;
  transform: translateY(0);
}
@media (prefers-reduced-motion: reduce) {
  .reveal {
    opacity: 1;
    transform: none;
    transition: none;
  }
}
</style>
