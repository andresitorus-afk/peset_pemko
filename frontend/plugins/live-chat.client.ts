import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const authHeaders = useState<Record<string, string>>('live-auth-headers', () => ({}))
  const echo = useState<Echo | null>('live-echo', () => null)

  if (!echo.value) {
    ;(globalThis as any).Pusher = Pusher
    echo.value = new Echo({
      broadcaster: 'reverb',
      key: config.public.reverbKey,
      wsHost: config.public.reverbHost,
      wsPort: 8080,
      forceTLS: false,
      enabledTransports: ['ws', 'wss'],
      authEndpoint: `${config.public.apiBase}/api/broadcasting/auth`,
      auth: { headers: authHeaders.value },
    })
  }
})
