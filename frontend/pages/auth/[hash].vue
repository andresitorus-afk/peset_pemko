<template>
  <div class="min-h-screen flex flex-col lg:flex-row">
    <!-- Left: Form Panel -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 bg-white relative">
      <div class="w-full max-w-md">
        <!-- Brand -->
        <div class="flex items-center gap-3 mb-10">
          <img src="/logo-pemko.jpg" alt="Pemko Medan" class="h-10">
          <div>
            <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">PESET</h1>
            <p class="text-xs text-slate-500 font-medium">Pemanfaatan Aset Daerah</p>
          </div>
        </div>

        <!-- Greeting -->
        <h2 class="text-2xl font-bold text-slate-900 mb-1">Selamat Datang</h2>
        <p class="text-sm text-slate-500 mb-8">Silakan masuk ke akun Anda untuk melanjutkan</p>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-5" novalidate>
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <input
                id="email"
                v-model="email"
                type="email"
                autocomplete="email"
                placeholder="contoh: admin@pemkomedan.go.id"
                :class="[
                  'w-full pl-12 pr-4 py-4 border-2 rounded-xl text-base outline-none transition-all duration-200 bg-white',
                  'focus:ring-4',
                  validation.email.error ? 'border-red-400 bg-red-50 focus:ring-red-200 focus:border-red-500' : 'border-slate-200 focus:ring-teal-200 focus:border-teal-500 hover:border-slate-300'
                ]"
                @input="clearFieldError('email')"
              />
            </div>
            <p v-if="validation.email.error" role="alert" class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ validation.email.error }}
            </p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Kata Sandi</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
                :class="[
                  'w-full pl-12 pr-12 py-4 border-2 rounded-xl text-base outline-none transition-all duration-200 bg-white',
                  'focus:ring-4',
                  validation.password.error ? 'border-red-400 bg-red-50 focus:ring-red-200 focus:border-red-500' : 'border-slate-200 focus:ring-teal-200 focus:border-teal-500 hover:border-slate-300'
                ]"
                @input="clearFieldError('password')"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                :aria-label="showPassword ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
                tabindex="-1"
              >
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
            <p v-if="validation.password.error" role="alert" class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ validation.password.error }}
            </p>
          </div>

          <!-- Options -->
          <div class="flex items-center justify-between">
            <label class="flex items-center gap-3 cursor-pointer group">
              <div class="relative">
                <input type="checkbox" v-model="remember" class="sr-only" />
                <div :class="[
                  'w-5 h-5 border-2 rounded-md flex items-center justify-center transition-all duration-200',
                  remember ? 'bg-teal-600 border-teal-600' : 'border-slate-300 group-hover:border-slate-400'
                ]">
                  <svg v-if="remember" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
              </div>
              <span class="text-sm text-slate-600 group-hover:text-slate-800 select-none">Ingat saya</span>
            </label>
            <a href="#" class="text-sm font-semibold text-teal-600 hover:text-teal-700 hover:underline transition-all">Lupa kata sandi?</a>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            :class="[
              'w-full py-4 rounded-xl text-base font-bold shadow-lg transition-all duration-200',
              loading
                ? 'bg-teal-400 text-white cursor-not-allowed'
                : 'bg-gradient-to-r from-teal-600 to-teal-500 text-white hover:from-teal-700 hover:to-teal-600 hover:shadow-xl active:scale-[0.98]'
            ]"
          >
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Memproses...
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              Masuk
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </span>
          </button>
        </form>

        <!-- Error Alert -->
        <Transition name="alert">
          <div v-if="loginError" role="alert" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-semibold text-red-800">Gagal masuk</p>
              <p class="text-sm text-red-600 mt-0.5">{{ loginError }}</p>
            </div>
            <button @click="loginError = ''" type="button" class="text-red-400 hover:text-red-600 transition-colors" aria-label="Tutup">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </Transition>

        <!-- Footer -->
        <p class="mt-10 text-center text-xs text-slate-400">
          &copy; {{ new Date().getFullYear() }} PESET v1.0 &mdash; Pemerintah Kota Medan
        </p>
      </div>
    </div>

    <!-- Right: Visual Panel -->
    <div class="hidden lg:flex w-1/2 relative bg-gradient-to-br from-teal-800 via-teal-700 to-emerald-800 items-center justify-center overflow-hidden">
      <!-- Decorative grid pattern -->
      <div class="absolute inset-0 opacity-[0.03]">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;" />
      </div>

      <!-- Decorative Shapes -->
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-white/[0.04]" />
        <div class="absolute -bottom-32 -left-32 w-80 h-80 rounded-full bg-white/[0.03]" />
        <div class="absolute top-1/3 -left-16 w-48 h-48 rounded-full bg-teal-500/10" />
        <div class="absolute bottom-1/3 right-1/4 w-64 h-64 rounded-full bg-emerald-500/10" />
        <div class="absolute top-1/4 right-1/4 w-4 h-4 rounded-full bg-teal-300/40 animate-ping" style="animation-duration: 3s;" />
        <div class="absolute top-3/4 right-1/3 w-2 h-2 rounded-full bg-emerald-300/40 animate-ping" style="animation-duration: 4s; animation-delay: 1s;" />
        <div class="absolute top-1/2 left-1/4 w-1.5 h-1.5 rounded-full bg-white/30 animate-ping" style="animation-duration: 3.5s; animation-delay: 0.5s;" />
      </div>

      <div class="relative z-10 text-center px-12 max-w-lg">
        <!-- Logo Icon -->
        <div class="w-32 h-32 mx-auto mb-8 bg-white/10 backdrop-blur-xl rounded-3xl flex items-center justify-center shadow-2xl ring-1 ring-white/20">
          <svg class="w-16 h-16 text-white/90" fill="none" stroke="currentColor" stroke-linejoin="round" viewBox="0 0 80 80">
            <path d="M12 52V36L40 18L68 36V52" stroke-width="2"/>
            <rect x="12" y="38" width="10" height="14" rx="1" stroke-width="1.5"/>
            <rect x="58" y="38" width="10" height="14" rx="1" stroke-width="1.5"/>
            <path d="M16 34C16 26 64 26 64 34" stroke-width="1.5"/>
            <path d="M30 30C30 20 50 20 50 30" stroke-width="2"/>
            <rect x="34" y="44" width="12" height="8" rx="4" stroke-width="1.5"/>
            <rect x="18" y="42" width="4" height="4" rx="1" stroke-width="1"/>
            <rect x="58" y="42" width="4" height="4" rx="1" stroke-width="1"/>
          </svg>
        </div>

        <h2 class="text-4xl font-extrabold text-white mb-3 tracking-tight">PEMKO MEDAN</h2>
        <div class="w-16 h-1 bg-gradient-to-r from-teal-400 to-emerald-400 mx-auto mb-5 rounded-full" />
        <p class="text-lg text-teal-100/80 leading-relaxed max-w-sm mx-auto">
          Kelola Aset Daerah untuk Medan Berkah
        </p>

        <!-- Feature List -->
        <div class="mt-12 space-y-5 text-left max-w-xs mx-auto">
          <div v-for="(item, i) in features" :key="i" class="flex items-center gap-4 group">
            <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0 group-hover:bg-white/20 transition-colors">
              <svg class="w-4 h-4 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
              </svg>
            </div>
            <span class="text-sm text-teal-100/80 font-medium">{{ item.label }}</span>
          </div>
        </div>

        <!-- Bottom dots -->
        <div class="mt-12 flex justify-center gap-2.5">
          <span class="w-2.5 h-2.5 rounded-full bg-teal-400 ring-2 ring-teal-400/30" />
          <span class="w-2.5 h-2.5 rounded-full bg-white/25" />
          <span class="w-2.5 h-2.5 rounded-full bg-white/15" />
        </div>
      </div>
    </div>

    <!-- Mobile Header -->
    <div class="lg:hidden px-6 py-5 bg-gradient-to-r from-teal-700 to-teal-600">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center flex-shrink-0 shadow-sm">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-bold text-white tracking-tight">SIPEMKO</p>
          <p class="text-xs text-teal-200 font-medium">Sistem Pemanfaatan Aset Daerah</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

definePageMeta({ layout: false })

const config = useRuntimeConfig()
const route = useRoute()
const router = useRouter()

if (route.params.hash !== config.public.loginHash) {
  router.replace('/')
}

const { login, fetchUser } = useAuth()

const email = ref('')
const password = ref('')
const remember = ref(false)
const showPassword = ref(false)
const loading = ref(false)
const loginError = ref('')

const validation = reactive({
  email: { error: '' },
  password: { error: '' },
})

const features = [
  { label: 'Sewa Aset Daerah', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { label: 'Pinjam Pakai', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
  { label: 'Kerja Sama Pemanfaatan', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
  { label: 'Bangun Guna Serah', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
]

onMounted(() => {
  const el = document.getElementById('email')
  if (el) setTimeout(() => el.focus(), 100)
})

function clearFieldError(field: string) {
  if (field === 'email') validation.email.error = ''
  if (field === 'password') validation.password.error = ''
}

function validate(): boolean {
  let valid = true
  if (!email.value) {
    validation.email.error = 'Email harus diisi'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    validation.email.error = 'Format email tidak valid'
    valid = false
  }
  if (!password.value) {
    validation.password.error = 'Kata sandi harus diisi'
    valid = false
  }
  return valid
}

async function handleLogin() {
  loginError.value = ''
  if (!validate()) return

  loading.value = true
  try {
    await login(email.value, password.value)
    await fetchUser()
    router.push('/admin')
  } catch (e: any) {
    loginError.value = e.message || 'Email atau kata sandi yang Anda masukkan salah. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.alert-enter-active { transition: all 0.3s ease-out; }
.alert-leave-active { transition: all 0.2s ease-in; }
.alert-enter-from { opacity: 0; transform: translateY(-8px); }
.alert-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
