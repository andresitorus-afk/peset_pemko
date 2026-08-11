<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Live Chat</h1>

    <div class="grid grid-cols-1 lg:grid-cols-[340px_1fr] gap-4 h-[calc(100vh-8rem)]">
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-gray-800 overflow-hidden flex flex-col">
        <div class="p-3 border-b border-slate-200 dark:border-gray-800 flex items-center justify-between">
          <p class="text-sm font-semibold text-slate-700 dark:text-gray-200">Sesi Pengunjung</p>
          <button @click="loadSessions" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-gray-800 text-slate-500 dark:text-gray-400" title="Refresh">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M5.07 14a8 8 0 0114.45-4M18.93 10a8 8 0 01-14.45 4" /></svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto">
          <button
            v-for="s in sessions"
            :key="s.id"
            @click="openSession(s.id)"
            :class="[
              'w-full text-left px-4 py-3 border-b border-slate-100 dark:border-gray-800 hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors',
              activeSession === s.id ? 'bg-teal-50 dark:bg-teal-900/20' : ''
            ]"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 min-w-0">
                <span class="relative flex w-2.5 h-2.5">
                  <span v-if="s.status === 'open'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span :class="['relative inline-flex rounded-full w-2.5 h-2.5', s.status === 'open' ? 'bg-emerald-500' : 'bg-slate-300']"></span>
                </span>
                <p class="text-sm font-medium text-slate-800 dark:text-gray-100 truncate">{{ s.visitor_name || 'Pengunjung' }}</p>
              </div>
              <span v-if="s.unread > 0" class="flex-shrink-0 h-5 min-w-[20px] px-1 rounded-full bg-red-500 text-white text-[11px] font-medium flex items-center justify-center">{{ s.unread }}</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-gray-400 truncate mt-1">
              {{ s.last_message || 'Belum ada pesan' }}
            </p>
            <p class="text-[11px] text-slate-400 dark:text-gray-500 mt-0.5">
              {{ timeAgo(s.updated_at) }}
              <span v-if="s.needs_attention" class="ml-1 text-amber-600 dark:text-amber-400 font-medium">• menunggu</span>
              <span v-if="s.status === 'closed'" class="ml-1">• ditutup</span>
            </p>
          </button>
          <p v-if="sessions.length === 0" class="text-sm text-slate-500 dark:text-gray-400 text-center py-10">Belum ada sesi chat.</p>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-gray-800 overflow-hidden flex flex-col">
        <template v-if="current">
          <div class="p-3 border-b border-slate-200 dark:border-gray-800 flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-slate-800 dark:text-gray-100">{{ current.visitor_name || 'Pengunjung' }}</p>
              <p class="text-xs text-slate-400 dark:text-gray-500">{{ current.status === 'open' ? 'Terhubung' : 'Sesi ditutup' }}</p>
            </div>
            <div class="flex gap-2">
              <UiButton v-if="current.status === 'open'" variant="secondary" size="sm" @click="closeSession">Tutup Sesi</UiButton>
            </div>
          </div>

          <div ref="messagesEl" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50 dark:bg-gray-950">
            <div
              v-for="m in messages"
              :key="m.id"
              :class="['flex', m.sender_type === 'admin' ? 'justify-end' : 'justify-start']"
            >
              <div
                :class="[
                  'max-w-[75%] px-3 py-2 rounded-2xl text-sm shadow-sm',
                  m.sender_type === 'admin'
                    ? 'bg-teal-600 text-white rounded-br-sm'
                    : m.sender_type === 'bot'
                      ? 'bg-slate-200 dark:bg-gray-700 text-slate-800 dark:text-gray-100 rounded-bl-sm'
                      : 'bg-white dark:bg-gray-800 text-slate-800 dark:text-gray-100 border border-slate-200 dark:border-gray-700 rounded-bl-sm'
                ]"
              >
                <p class="text-[11px] font-medium mb-0.5 opacity-80">
                  {{ label(m.sender_type) }}
                </p>
                <p class="whitespace-pre-wrap break-words">{{ m.message }}</p>
              </div>
            </div>
          </div>

          <form @submit.prevent="send" class="p-3 border-t border-slate-200 dark:border-gray-800 flex gap-2">
            <input
              v-model="draft"
              placeholder="Tulis balasan..."
              class="flex-1 px-3 py-2 rounded-lg border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-slate-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
            />
            <UiButton type="submit" :disabled="sending || !draft.trim() || current.status !== 'open'">
              {{ sending ? 'Mengirim...' : 'Kirim' }}
            </UiButton>
          </form>
        </template>

        <div v-else class="flex-1 flex items-center justify-center text-sm text-slate-400 dark:text-gray-500">
          Pilih sesi pengunjung untuk melihat percakapan.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue'
import { useApi } from '~/composables/useApi'
import { useToast } from '~/composables/useToast'
import { useLiveChat } from '~/composables/useLiveChat'

definePageMeta({ layout: 'admin' })

const api = useApi()
const toast = useToast()
const { joinSession, refreshUnread } = useLiveChat()

const sessions = ref<any[]>([])
const current = ref<any>(null)
const messages = ref<any[]>([])
const activeSession = ref<string | null>(null)
const draft = ref('')
const sending = ref(false)
const messagesEl = ref<HTMLElement | null>(null)
let liveChannel: any = null

async function loadSessions() {
  try {
    const r = await api.get<any>('/chat/sessions')
    sessions.value = r.data ?? []
  } catch { toast.show('Gagal memuat sesi chat', 'error') }
}

async function openSession(id: string) {
  if (liveChannel) { liveChannel.stopListening('.message.sent'); liveChannel = null }
  activeSession.value = id
  current.value = sessions.value.find(s => s.id === id) ?? null
  try {
    const r = await api.get<any>(`/chat/sessions/${id}`)
    messages.value = r.data ?? []
    await api.post(`/chat/sessions/${id}/read`)
    await scrollBottom()
  } catch { toast.show('Gagal memuat percakapan', 'error') }
  liveChannel = joinSession(id, onLiveMessage)
}

function onLiveMessage(msg: any) {
  messages.value.push(msg)
  refreshUnread()
  loadSessions()
  scrollBottom()
}

async function send() {
  if (!draft.value.trim() || !activeSession.value || !current.value) return
  sending.value = true
  try {
    const r = await api.post<any>(`/chat/sessions/${activeSession.value}/messages`, { message: draft.value })
    messages.value.push(r.data)
    draft.value = ''
    loadSessions()
    await scrollBottom()
  } catch { toast.show('Gagal mengirim balasan', 'error') }
  sending.value = false
}

async function closeSession() {
  if (!activeSession.value) return
  try {
    await api.post(`/chat/sessions/${activeSession.value}/close`)
    toast.show('Sesi ditutup', 'info')
    if (liveChannel) { liveChannel.stopListening('.message.sent'); liveChannel = null }
    sessions.value = sessions.value.filter(s => s.id !== activeSession.value)
    activeSession.value = null
    current.value = null
    messages.value = []
    refreshUnread()
  } catch { toast.show('Gagal menutup sesi', 'error') }
}

async function scrollBottom() {
  await nextTick()
  if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight
}

function label(type: string) {
  return type === 'admin' ? 'Petugas' : type === 'bot' ? 'PESET Bot' : 'Pengunjung'
}

function timeAgo(iso?: string) {
  if (!iso) return ''
  const s = Math.floor((Date.now() - new Date(iso).getTime()) / 1000)
  if (s < 60) return 'baru saja'
  if (s < 3600) return `${Math.floor(s / 60)} menit lalu`
  if (s < 86400) return `${Math.floor(s / 3600)} jam lalu`
  return new Date(iso).toLocaleDateString('id-ID')
}

loadSessions()
let sessionsTimer: ReturnType<typeof setInterval>
onMounted(() => { sessionsTimer = setInterval(loadSessions, 15000) })
onUnmounted(() => clearInterval(sessionsTimer))
</script>
