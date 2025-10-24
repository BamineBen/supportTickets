<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h1 class="h4 mb-0">Mon Profil</h1>
          </div>
          <div class="card-body">
            <!-- Alerte de succès -->
            <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
              {{ successMessage }}
              <button type="button" class="btn-close" @click="successMessage = ''" aria-label="Close"></button>
            </div>
            
            <!-- Informations du profil -->
            <form @submit.prevent="updateProfile" class="mb-5">
              <h2 class="h5 mb-3 border-bottom pb-2">Informations Personnelles</h2>
              
              <div class="mb-3">
                <label for="name" class="form-label">Nom</label>
                <input type="text" class="form-control" id="name" v-model="profile.name" required>
                <div v-if="errors.name" class="text-danger mt-1">{{ errors.name[0] }}</div>
              </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" v-model="profile.email" disabled>
                <small class="form-text text-muted">L'adresse email ne peut pas être modifiée.</small>
              </div>
              
              <button type="submit" class="btn btn-primary" :disabled="updatingProfile">
                <span v-if="updatingProfile" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Mettre à jour le profil
              </button>
            </form>
            
            <!-- Changement de mot de passe -->
            <form @submit.prevent="updatePassword">
              <h2 class="h5 mb-3 border-bottom pb-2">Modifier le mot de passe</h2>
              
              <div class="mb-3">
                <label for="current_password" class="form-label">Mot de passe actuel</label>
                <input type="password" class="form-control" id="current_password" v-model="passwordForm.current_password" required>
                <div v-if="errors.current_password" class="text-danger mt-1">{{ errors.current_password[0] }}</div>
              </div>
              
              <div class="mb-3">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="password" v-model="passwordForm.password" required>
                <div v-if="errors.password" class="text-danger mt-1">{{ errors.password[0] }}</div>
              </div>
              
              <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="password_confirmation" v-model="passwordForm.password_confirmation" required>
              </div>
              
              <button type="submit" class="btn btn-primary" :disabled="updatingPassword">
                <span v-if="updatingPassword" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Changer le mot de passe
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../store/authStore'
import api from '../api/axios'
import axios from 'axios'

const authStore = useAuthStore()

// Initialiser le CSRF token pour Laravel Sanctum
const initCsrf = async () => {
  try {
    // Appel au endpoint sanctum/csrf-cookie pour initialiser les cookies
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true })
    console.log('CSRF token récupéré avec succès.')
    return true
  } catch (error) {
    console.error('Erreur lors de la récupération du CSRF token:', error)
    return false
  }
}

// Variables pour la gestion du profil
const profile = ref({
  name: '',
  email: ''
})
const updatingProfile = ref(false)

// Variables pour la gestion du mot de passe
const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})
const updatingPassword = ref(false)

// Messages et erreurs
const successMessage = ref('')
const errors = ref({})

// Récupérer les informations du profil
onMounted(async () => {
  try {
    // Utiliser les données déjà présentes dans le store d'authentification
    const user = authStore.user
    if (user) {
      profile.value.name = user.name
      profile.value.email = user.email
    } else {
      // Si les données ne sont pas dans le store, les récupérer depuis l'API
      const response = await api.get('/profile')
      profile.value.name = response.data.name
      profile.value.email = response.data.email
    }
  } catch (error) {
    console.error('Erreur lors de la récupération des données du profil:', error)
  }
})

// Mettre à jour le profil
async function updateProfile() {
  updatingProfile.value = true
  errors.value = {}
  successMessage.value = ''
  
  try {
    // 1. Initialiser le CSRF token
    await initCsrf()
    
    // 2. S'assurer que le token Bearer est disponible
    const token = localStorage.getItem('token')
    if (!token) {
      throw new Error('Vous n\'\u00eates pas authentifié. Veuillez vous reconnecter.')
    }
    
    // 3. Faire la requête API avec le token explicite
    const response = await api.put('/profile', {
      name: profile.value.name
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'X-Requested-With': 'XMLHttpRequest'
      },
      withCredentials: true
    })
    
    console.log('Réponse de mise à jour du profil:', response)
    
    // 4. Mettre à jour les données utilisateur locales
    if (authStore.user) {
      authStore.user.name = profile.value.name
      localStorage.setItem('user', JSON.stringify(authStore.user))
    }
    
    // 5. Afficher le message de succès
    successMessage.value = 'Votre profil a été mis à jour avec succès.'
    
    // Reset du message après 3 secondes
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (error) {
    console.error('Erreur lors de la mise à jour du profil:', error)
    
    if (error.response?.status === 401) {
      // Problème d'authentification, réessayer de récupérer un nouveau token
      try {
        // Tentative de ré-authentification si possible
        await authStore.fetchProfile()
        successMessage.value = 'Veuillez réessayer de mettre à jour votre profil.'
      } catch (e) {
        errors.value = { auth: ['Session expirée, veuillez vous reconnecter'] }
      }
    } else if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errors.value = { general: ['Une erreur est survenue lors de la mise à jour du profil.'] }
    }
  } finally {
    updatingProfile.value = false
  }
}

// Mettre à jour le mot de passe
async function updatePassword() {
  updatingPassword.value = true
  errors.value = {}
  successMessage.value = ''
  
  try {
    // Vérification de base du formulaire
    if (!passwordForm.value.current_password || !passwordForm.value.password || !passwordForm.value.password_confirmation) {
      errors.value = { form: ['Tous les champs sont obligatoires'] }
      return
    }
    
    if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
      errors.value = { password_confirmation: ['Les mots de passe ne correspondent pas'] }
      return
    }
    
    if (passwordForm.value.password.length < 8) {
      errors.value = { password: ['Le mot de passe doit contenir au moins 8 caractères'] }
      return
    }
    
    // 1. Initialiser le CSRF token
    await initCsrf()
    
    // 2. S'assurer que le token Bearer est disponible
    const token = localStorage.getItem('token')
    if (!token) {
      throw new Error('Vous n\'\u00eates pas authentifié. Veuillez vous reconnecter.')
    }
    
    // 3. Faire la requête API avec le token explicite
    const response = await api.put('/password', passwordForm.value, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'X-Requested-With': 'XMLHttpRequest'
      },
      withCredentials: true
    })
    
    console.log('Réponse du changement de mot de passe:', response)
    
    // 4. Afficher le message de succès
    successMessage.value = 'Votre mot de passe a été mis à jour avec succès.'
    
    // 5. Réinitialiser le formulaire
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: ''
    }
    
    // Reset du message après 3 secondes
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (error) {
    console.error('Erreur lors de la mise à jour du mot de passe:', error)
    
    if (error.response?.status === 401) {
      // Problème d'authentification, réessayer de récupérer un nouveau token
      try {
        // Tentative de ré-authentification si possible
        await authStore.fetchProfile()
        successMessage.value = 'Veuillez réessayer de mettre à jour votre mot de passe.'
      } catch (e) {
        errors.value = { auth: ['Session expirée, veuillez vous reconnecter'] }
      }
    } else if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else if (error.response?.status === 404) {
      errors.value = { general: ['Le serveur ne prend pas encore en charge la modification du mot de passe.'] }
    } else {
      errors.value = { general: ['Une erreur est survenue lors de la mise à jour du mot de passe.'] }
    }
  } finally {
    updatingPassword.value = false
  }
}
</script>
