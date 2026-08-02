<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <PublicChatWidget v-if="showWidget" />
  </div>
</template>

<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'

const auth = useAuth()
if (auth.token.value) {
  auth.fetchUser()
}

const route = useRoute()
const showWidget = computed(() => !route.path.startsWith('/admin') && !route.path.startsWith('/auth'))
</script>
