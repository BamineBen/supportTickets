<template>
  <div class="container py-5 text-center">
    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 60vh;">
      <div class="spinner-border text-primary mb-4" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <h2 class="mt-3">Connexion en cours...</h2>
      <p class="text-muted">Veuillez patienter pendant que nous terminons votre connexion avec Google.</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../store/authStore';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

onMounted(async () => {
  try {
    // Récupérer le token de l'URL (nouvelle approche)
    const token = route.query.token;
    
    if (token) {
      console.log('Token Google reçu, authentification en cours...');
      
      // Stocker le token
      localStorage.setItem('token', token);
      
      // Mettre à jour le store
      await authStore.init();
      
      // Rediriger vers le dashboard
      router.push('/dashboard');
      return;
    }
    
    // Si on arrive ici, c'est qu'il n'y a pas de token
    throw new Error('Données d\'authentification Google manquantes');
  } catch (error) {
    console.error('Erreur lors de la connexion avec Google:', error);
    
    // Rediriger vers la page de login avec un message d'erreur
    router.push('/login?error=google_auth_failed');
  }
});
</script>
