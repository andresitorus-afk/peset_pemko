<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'

const auth = useAuth()
if (auth.token.value) {
  auth.fetchUser()
}
</script>
