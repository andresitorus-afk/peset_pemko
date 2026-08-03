<template>
  <div class="min-h-screen bg-white">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-gradient-to-r from-slate-900 via-slate-800 to-teal-900 border-b border-teal-700/30 shadow-lg shadow-black/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/15 flex items-center justify-center overflow-hidden ring-1 ring-white/20 shadow-inner">
              <img src="/logo-pemko.jpg" alt="Pemko Medan" class="h-7 sm:h-8">
            </div>
            <div>
              <p class="text-base sm:text-lg font-bold text-white leading-tight">PESET</p>
              <p class="text-[11px] sm:text-xs text-teal-200 leading-tight">Pemanfaatan Aset Daerah</p>
            </div>
          </div>
          <nav class="hidden sm:flex items-center gap-1">
            <a href="#" class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-teal-700/60 shadow-sm">Beranda</a>
            <a href="#tentang" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition-all">Tentang</a>
            <a href="#kontak" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition-all">Kontak</a>
          </nav>

        </div>
      </div>
    </header>

    <!-- Hero -->
    <section class="relative overflow-hidden text-white h-[60vh] sm:h-[70vh]">
      <div class="absolute inset-0 bg-slate-900">
        <img v-for="(bg, i) in heroBg" :key="i"
          :src="bg"
          class="absolute inset-0 w-full h-full object-contain transition-opacity duration-1000"
          :class="slideIndex === i ? 'opacity-100' : 'opacity-0'" />
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/60 to-slate-900/20" />
      </div>
      <div class="relative flex flex-col h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-24">
        <div class="max-w-3xl">
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight [text-shadow:0_2px_20px_rgba(0,0,0,0.4)]">
            Pemanfaatan Aset<br>
            <span class="text-teal-300">Daerah Kota Medan</span>
          </h1>
          <p class="text-base sm:text-lg text-slate-200 mt-4 sm:mt-6 leading-relaxed max-w-2xl [text-shadow:0_1px_8px_rgba(0,0,0,0.3)]">
            Temukan dan manfaatkan aset daerah yang tersedia untuk kerjasama, sewa, 
            dan pemanfaatan lainnya. Proses transparan dan terpercaya.
          </p>
          <!-- Search -->
          <div class="mt-6 sm:mt-8">
            <div class="flex gap-2 sm:gap-3 max-w-xl">
              <div class="relative flex-1">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input v-model="search" @input="onSearch" type="text" placeholder="Cari aset, lokasi, atau kategori..."
                  class="w-full pl-12 pr-4 py-3 sm:py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 text-base focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-teal-400 backdrop-blur-sm">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2">
        <span v-for="(_, i) in heroBg" :key="i" @click="slideIndex = i"
          class="w-2 h-2 rounded-full transition-all cursor-pointer"
          :class="slideIndex === i ? 'bg-white w-4' : 'bg-white/50 hover:bg-white/70'" />
      </div>
    </section>
    
    <!-- Filter Kategori -->
    <section class="bg-white border-b border-slate-200 sticky top-16 sm:top-20 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
          <button @click="kategoriFilter = ''" :class="kategoriFilter === '' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Semua
          </button>
          <button v-for="k in kategoriList" :key="k.id" @click="kategoriFilter = k.id"
            :class="kategoriFilter === k.id ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            {{ k.kode_kib }} — {{ k.nama_kategori }}
          </button>
        </div>
      </div>
    </section>

    <!-- Daftar Aset -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div v-for="i in 6" :key="i" class="rounded-2xl border border-slate-200 bg-white overflow-hidden animate-pulse shadow-sm">
          <div class="h-48 sm:h-52 bg-slate-200" />
          <div class="p-5 space-y-3">
            <div class="h-3 bg-slate-200 rounded w-1/3" />
            <div class="h-5 bg-slate-200 rounded w-3/4" />
            <div class="h-4 bg-slate-200 rounded w-2/3" />
            <div class="h-px bg-slate-100 my-2" />
            <div class="flex justify-between">
              <div class="h-4 bg-slate-200 rounded w-1/3" />
              <div class="h-4 bg-slate-200 rounded w-1/4" />
            </div>
          </div>
        </div>
      </div>

      <!-- Kosong -->
      <div v-else-if="items.length === 0" class="text-center py-16 sm:py-20">
        <svg class="w-16 h-16 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <p class="text-lg font-medium text-slate-600 mt-4">Belum ada aset ditemukan</p>
        <p class="text-sm text-slate-400 mt-1">Coba ubah kata kunci pencarian atau filter</p>
      </div>

      <!-- Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-7">
        <div v-for="item in items" :key="item.id"
          class="group relative rounded-2xl border border-slate-200 bg-white overflow-hidden hover:shadow-xl hover:shadow-slate-200/60 hover:-translate-y-0.5 hover:border-teal-300 transition-all duration-300 cursor-pointer"
          @click="openDetail(item)">
          <!-- KIB color accent bar -->
          <div class="absolute top-0 left-0 right-0 h-1 z-10" :style="{ backgroundColor: kibColor(item.kategori?.kode_kib) }" />
          <!-- Foto -->
          <div class="relative h-48 sm:h-52 bg-slate-100 overflow-hidden">
            <div v-if="item.foto && item.foto.length" class="w-full h-full">
              <img :src="fotoUrl(item.foto[0].file_path)" :alt="item.nama_barang" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            </div>
            <div v-else class="flex items-center justify-center h-full">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-xs text-slate-400">Tidak ada foto</span>
              </div>
            </div>
            <!-- Gradient overlay on image bottom -->
            <div v-if="item.foto && item.foto.length" class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/30 to-transparent" />
            <!-- Status badge -->
            <div class="absolute top-3 right-3">
              <span :class="statusClass(item.status)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold shadow-lg backdrop-blur-sm">
                {{ statusLabel(item.status) }}
              </span>
            </div>
            <!-- KIB badge -->
            <div v-if="item.kategori?.kode_kib" class="absolute top-3 left-3">
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold text-white shadow-lg backdrop-blur-sm" :style="{ backgroundColor: kibColor(item.kategori.kode_kib) }">
                {{ item.kategori.kode_kib }}
              </span>
            </div>
          </div>
          <!-- Info -->
          <div class="p-5">
            <p class="text-[11px] font-semibold text-teal-600 uppercase tracking-widest">{{ item.kategori?.nama_kategori || '—' }}</p>
            <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-1.5 leading-snug group-hover:text-teal-700 transition-colors">{{ item.nama_barang }}</h3>
            <div class="flex items-center gap-1.5 mt-2.5 text-sm text-slate-500">
              <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span class="truncate">{{ item.alamat || '—' }}</span>
            </div>
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
              <div class="flex items-center gap-2 text-sm text-slate-500">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Sebelumnya
        </button>
        <div class="flex items-center gap-2">
          <template v-for="p in Math.min(totalPages, 5)" :key="p">
            <button v-if="p === page || p === 1 || p === totalPages || Math.abs(p - page) <= 1"
              @click="page = p; fetchData()"
              :class="p === page ? 'bg-teal-700 text-white shadow-md shadow-teal-200' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-50'"
              class="w-10 h-10 rounded-xl text-sm font-semibold transition-all">
              {{ p }}
            </button>
          </template>
          <span v-if="totalPages > 5 && page < totalPages - 2" class="text-slate-400 px-1">...</span>
        </div>
        <span class="text-sm text-slate-400 px-1 hidden sm:block">dari {{ totalPages }}</span>
        <button :disabled="page >= totalPages" @click="page++; fetchData()"
          class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm">
          Berikutnya
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </section>

    <!-- Tentang -->
    <section id="tentang" class="bg-slate-50 border-t border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Tentang PESET</h2>
          <p class="text-base sm:text-lg text-slate-600 mt-4 leading-relaxed">
            PESET (Pemanfaatan Aset Daerah) adalah portal resmi Pemerintah Kota Medan 
            untuk mempromosikan dan menawarkan aset daerah yang dapat dimanfaatkan oleh 
            pihak ketiga melalui kerjasama, sewa, pinjam pakai, dan skema lainnya.
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-10 sm:mt-12">
            <div class="bg-white rounded-xl p-6 border border-slate-200">
              <div class="w-12 h-12 rounded-lg bg-teal-100 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </div>
              <h3 class="text-base font-bold text-slate-900 mt-4">Transparan</h3>
              <p class="text-sm text-slate-600 mt-2">Seluruh informasi aset dan pemanfaatan dapat diakses publik secara terbuka</p>
            </div>
            <div class="bg-white rounded-xl p-6 border border-slate-200">
              <div class="w-12 h-12 rounded-lg bg-teal-100 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <h3 class="text-base font-bold text-slate-900 mt-4">Terverifikasi</h3>
              <p class="text-sm text-slate-600 mt-2">Data aset diverifikasi oleh OPD pengelola dan BPKAD Kota Medan</p>
            </div>
            <div class="bg-white rounded-xl p-6 border border-slate-200">
              <div class="w-12 h-12 rounded-lg bg-teal-100 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
              </div>
              <h3 class="text-base font-bold text-slate-900 mt-4">Mudah</h3>
              <p class="text-sm text-slate-600 mt-2">Proses pengajuan dan informasi dirancang sederhana dan mudah dipahami</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer id="kontak" class="bg-slate-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-12">
          <div>
            <div class="flex items-center gap-3">
              <img src="/logo-pemko.jpg" alt="Pemko Medan" class="h-10">
              <div>
                <p class="text-lg font-bold">PESET</p>
                <p class="text-xs text-slate-400">Pemanfaatan Aset Daerah</p>
              </div>
            </div>
            <p class="text-sm text-slate-400 mt-4 leading-relaxed">
              Portal resmi Pemerintah Kota Medan untuk informasi dan pengelolaan pemanfaatan aset daerah.
            </p>
          </div>
          <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Kontak</h3>
            <div class="mt-4 space-y-3 text-sm text-slate-300">
              <p>BPKAD Kota Medan</p>
              <p>Jl. Kapten Maulana Lubis No. 2, Medan</p>
              <p>Telp: (061) 451-2345</p>
              <p>Email: bpkad@pemkomedan.go.id</p>
            </div>
          </div>
          <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Link Terkait</h3>
            <div class="mt-4 space-y-3 text-sm">
              <a href="https://pemkomedan.go.id" class="block text-slate-300 hover:text-white transition-colors">pemkomedan.go.id</a>
              <a href="https://bpkad.pemkomedan.go.id" class="block text-slate-300 hover:text-white transition-colors">bpkad.pemkomedan.go.id</a>
              <NuxtLink :to="loginPath" class="block text-slate-300 hover:text-white transition-colors">Admin Panel</NuxtLink>
            </div>
          </div>
        </div>
        <div class="border-t border-slate-800 mt-8 sm:mt-12 pt-6 sm:pt-8 text-center text-sm text-slate-500">
          &copy; {{ new Date().getFullYear() }} Pemerintah Kota Medan. Seluruh hak cipta dilindungi.
        </div>
      </div>
    </footer>

    <!-- Modal Detail -->
    <div v-if="detailItem" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto pt-4 sm:pt-10 pb-10" @click.self="detailItem = null">
      <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="detailItem = null" />
      <div class="relative bg-white rounded-2xl w-full max-w-3xl mx-4 shadow-2xl overflow-hidden z-10">
        <!-- Close -->
        <button @click="detailItem = null" class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/90 rounded-full flex items-center justify-center shadow-md hover:bg-white transition-colors">
          <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <!-- Foto slider -->
        <div v-if="detailFoto && detailFoto.length" class="relative h-56 sm:h-72 bg-slate-100 group">
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
        <div v-else class="h-56 sm:h-72 bg-slate-100 flex items-center justify-center">
          <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <!-- Content -->
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
          <div v-if="detailGis || detailItem.luas" class="mt-6 border-t border-slate-200 pt-6">
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
            <div ref="mapContainer" class="w-full h-64 sm:h-80 rounded-xl border border-slate-200 overflow-hidden z-0"></div>
          </div>

          <div v-if="detailPemanfaatan && detailPemanfaatan.length" class="mt-6 border-t border-slate-200 pt-6">
            <p class="text-sm font-bold text-slate-900 mb-3">Riwayat Pemanfaatan</p>
            <div class="space-y-3">
              <div v-for="p in detailPemanfaatan" :key="p.id" class="bg-slate-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-slate-900">{{ p.jenis?.nama || '—' }}</p>
                  <span :class="p.status === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'" class="px-2.5 py-0.5 rounded-full text-xs font-medium">{{ p.status }}</span>
                </div>
                <p class="text-sm text-slate-500 mt-1">{{ p.nomor_perjanjian }} — {{ p.pihak_ketiga?.nama || '—' }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ formatDate(p.tanggal_mulai) }} s/d {{ formatDate(p.tanggal_selesai) }}</p>
              </div>
            </div>
          </div>

          <div v-if="detailRekomendasi" class="mt-6 border-t border-slate-200 pt-6">
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
              class="flex-1 flex items-center justify-center gap-2 bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3 sm:py-4 px-6 rounded-xl text-base transition-colors">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              Ajukan via WhatsApp
            </a>
            <a :href="'mailto:bpkad@pemkomedan.go.id?subject=Minat%20Pemanfaatan%20Aset:%20' + encodeURIComponent(detailItem.nama_barang) + '&body=Assalamualaikum%20wr%20wb%0A%0ASaya%20tertarik%20dengan%20aset:%0A%0ANama:%20' + encodeURIComponent(detailItem.nama_barang) + '%0AKode:%20' + encodeURIComponent(detailItem.kode_barang) + '%0A%0AMohon%20informasi%20lebih%20lanjut.%0A%0ATerima%20kasih.'"
              class="flex-1 flex items-center justify-center gap-2 bg-white border-2 border-slate-300 hover:border-teal-400 text-slate-700 hover:text-teal-700 font-semibold py-3 sm:py-4 px-6 rounded-xl text-base transition-colors">
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
const loginPath = '/auth/' + config.public.loginHash

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
onMounted(() => { slideTimer = setInterval(() => { slideIndex.value = (slideIndex.value + 1) % heroBg.length }, 4000) })
onUnmounted(() => clearInterval(slideTimer))

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
  } catch { items.value = [] } finally { loading.value = false }
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
