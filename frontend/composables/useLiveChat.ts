import { ref } from 'vue'
import { useApi } from './useApi'
import { useAuth } from './useAuth'
import { useToast } from './useToast'

const unreadTotal = ref(0)
let pollTimer: ReturnType<typeof setInterval> | null = null
let staffChannel: any = null

export function useLiveChat() {
  const api = useApi()
  const { token, isLoggedIn } = useAuth()
  const toast = useToast()
  const echo = useState<any>('live-echo', () => null)
  const authHeaders = useState<Record<string, string>>('live-auth-headers', () => ({}))

  function setSessionToken(sessionToken: string | null) {
    if (sessionToken) authHeaders.value['X-Chat-Session'] = sessionToken
    else delete authHeaders.value['X-Chat-Session']
  }

  function setStaffAuth() {
    if (token.value) authHeaders.value['Authorization'] = `Bearer ${token.value}`
    else delete authHeaders.value['Authorization']
  }

  async function refreshUnread() {
    if (!isLoggedIn.value) { unreadTotal.value = 0; return }
    try {
      const r = await api.get<any>('/chat/unread-count')
      unreadTotal.value = Number(r.unread ?? 0)
    } catch { /* reverb/api down — keep last value */ }
  }

  function browserNotify() {
    if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
      try { new Notification('LENSA Live Chat', { body: 'Ada pesan baru dari pengunjung.' }) } catch { /* noop */ }
    }
  }

  function onUnread() {
    refreshUnread()
    toast.show('Pesan baru dari pengunjung', 'info')
    browserNotify()
  }

  function watchStaff() {
    if (!echo.value || staffChannel) return
    setStaffAuth()
    staffChannel = echo.value.private('chat.staff').listen('.unread.updated', onUnread)
  }

  function joinSession(sessionId: string, cb: (msg: any) => void): any {
    if (!echo.value) return null
    return echo.value.private(`chat.${sessionId}`).listen('.message.sent', cb)
  }

  function startPoll() {
    if (pollTimer) return
    refreshUnread()
    pollTimer = setInterval(() => { if (isLoggedIn.value) refreshUnread() }, 5000)
  }

  function stopPoll() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
  }

  function requestNotificationPermission() {
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
      Notification.requestPermission()
    }
  }

  return {
    echo, authHeaders, unreadTotal,
    setSessionToken, setStaffAuth, refreshUnread,
    watchStaff, joinSession, startPoll, stopPoll,
    requestNotificationPermission,
  }
}
