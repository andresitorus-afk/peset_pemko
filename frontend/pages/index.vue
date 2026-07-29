<template>
  <div />
</template>

<script setup lang="ts">
const { token } = useAuth()
if (token.value) {
  await navigateTo('/admin', { redirectCode: 302 })
} else {
  await navigateTo('/auth/login', { redirectCode: 302 })
}
</script>
