<template>
  <div>
    <!-- Floating toggle button -->
    <button
      type="button"
      aria-label="Buka Live Chat"
      class="fixed bottom-6 right-6 z-[70] w-14 h-14 rounded-full bg-teal-600 hover:bg-teal-700 text-white shadow-xl flex items-center justify-center transition-all hover:scale-105"
      @click="openPanel = !openPanel"
    >
      <svg v-if="!openPanel" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1118 0v2a2 2 0 01-2 2h-2v-4h2a9 9 0 00-6.5-8.62A9 9 0 0020 12h2v3a2 2 0 01-2 2h-1.5" />
      </svg>
      <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Chat panel -->
    <Transition name="chat">
      <div v-if="openPanel" class="fixed bottom-24 right-6 z-[70] w-96 max-w-[calc(100vw-3rem)] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col" style="height: 32rem; max-height: calc(100vh - 8rem)">
        <!-- Header -->
        <div class="bg-teal-600 text-white px-4 py-3 flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1118 0v2a2 2 0 01-2 2h-2v-4h2a9 9 0 00-6.5-8.62A9 9 0 0020 12h2v3a2 2 0 01-2 2h-1.5" />
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-bold text-sm">PESET Live Chat</p>
            <p class="text-[11px] text-teal-100 flex items-center gap-1">
              <span class="inline-block w-2 h-2 rounded-full bg-green-300 animate-pulse"></span>
              CS 24 jam — bot menjawab otomatis
            </p>
          </div>
        </div>

        <!-- Messages -->
        <div ref="msgBox" class="flex-1 overflow-y-auto px-4 py-3 space-y-3 bg-slate-50">
          <p v-if="loading" class="text-xs text-slate-400 text-center py-4">Menghubungkan…</p>
          <div v-for="m in messages" :key="m.id" class="flex" :class="m.sender_type === 'visitor' ? 'justify-end' : 'justify-start'">
            <div
              class="max-w-[80%] px-3 py-2 rounded-2xl text-sm shadow-sm break-words"
              :class="m.sender_type === 'visitor'
                ? 'bg-teal-600 text-white rounded-br-sm'
                : m.sender_type === 'admin'
                  ? 'bg-blue-600 text-white rounded-bl-sm'
                  : 'bg-white border border-violet-200 text-slate-700 rounded-bl-sm'"
            >
              <p class="text-[10px] font-semibold mb-0.5" :class="m.sender_type === 'visitor' ? 'text-teal-100' : m.sender_type === 'admin' ? 'text-blue-100' : 'text-violet-500'">
                {{ label(m.sender_type) }}
              </p>
              <p class="whitespace-pre-wrap">{{ m.message }}</p>
            </div>
          </div>
          <p v-if="typing" class="text-xs text-slate-400 italic">Petugas sedang mengetik…</p>
        </div>

        <!-- Input -->
        <form class="border-t border-slate-200 bg-white p-3 flex gap-2" @submit.prevent="send">
          <input
            v-model="input"
            type="text"
            placeholder="Ketik pertanyaan Anda…"
            class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
            :disabled="sending"
          />
          <button type="submit" :disabled="sending || !input.trim()" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-sm font-semibold transition-colors">
            Kirim
          </button>
        </form>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const apiBase = config.public.apiBase
const { echo, setSessionToken } = useLiveChat()

const openPanel = ref(false)
const messages = ref<any[]>([])
const input = ref('')
const sending = ref(false)
const loading = ref(false)
const typing = ref(false)
const msgBox = ref<HTMLDivElement | null>(null)
const seenIds = new Set<string>()

let session: { id: string; token: string } | null = null
let channel: any = null
const STORAGE_KEY = 'peset_chat_session'

function label(t: string) {
  return t === 'visitor' ? 'Anda' : t === 'admin' ? 'Petugas' : 'PESET Bot'
}

function scrollBottom() {
  nextTick(() => { if (msgBox.value) msgBox.value.scrollTop = msgBox.value.scrollHeight })
}

function appendMessage(m: any) {
  if (!m || seenIds.has(m.id)) return
  seenIds.add(m.id)
  messages.value.push(m)
  scrollBottom()
}

async function createSession() {
  const res = await fetch(`${apiBase}/api/public/chat/sessions`, { method: 'POST' })
  if (!res.ok) throw new Error('gagal membuat sesi chat')
  const { data } = await res.json()
  session = { id: data.id, token: data.token }
  localStorage.setItem(STORAGE_KEY, JSON.stringify(session))
  setSessionToken(session.token)
  data.messages?.forEach(appendMessage)
}

async function loadHistory() {
  const res = await fetch(`${apiBase}/api/public/chat/${session!.id}/messages`, { headers: { 'X-Chat-Session': session!.token } })
  if (!res.ok) return
  const { data } = await res.json()
  data.forEach(appendMessage)
}

async function ensureSession() {
  if (session) return
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (raw) {
      const s = JSON.parse(raw)
      if (s && s.id && s.token) session = s
    }
  } catch { /* corrupted storage */ }

  if (!session) {
    loading.value = true
    try {
      await createSession()
    } catch {
      loading.value = false
      return
    }
    loading.value = false
  } else {
    setSessionToken(session.token)
  }

  if (!echo.value) return
  channel = echo.value.private(`chat.${session.id}`).listen('.message.sent', (e: any) => {
    appendMessage(e)
  })
  loadHistory()
}

async function send() {
  const text = input.value.trim()
  if (!text || sending.value || !session) return
  sending.value = true
  typing.value = false
  try {
    const res = await fetch(`${apiBase}/api/public/chat/${session.id}/messages`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Chat-Session': session.token },
      body: JSON.stringify({ message: text }),
    })
    if (!res.ok) throw new Error('gagal mengirim')
    const { data } = await res.json()
    input.value = ''
    appendMessage(data.visitor)
    appendMessage(data.bot)
  } catch {
    /* keep input, show nothing fancy */
  } finally {
    sending.value = false
  }
}

onMounted(() => {
  setSessionToken(session?.token ?? null)
})

onUnmounted(() => {
  if (channel) { try { channel.stopListening('.message.sent') } catch { /* noop */ } }
})
</script>

<style scoped>
.chat-enter-active, .chat-leave-active { transition: all .2s ease; }
.chat-enter-from, .chat-leave-to { opacity: 0; transform: translateY(10px) scale(.98); }
</style>
