<template>
  <nav class="navbar navbar-dark bg-dark px-4">
    <span class="navbar-brand">Support Tickets</span>
    <button @click="logout" class="btn btn-outline-light">Déconnexion</button>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../store/authStore'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const logout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>