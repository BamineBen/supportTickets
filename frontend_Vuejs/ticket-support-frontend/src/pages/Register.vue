<template>
  <div class="auth-container">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
          <div class="card border-0 shadow-lg rounded-3">
            <!-- En-tête de la carte avec logo -->
            <div class="card-header bg-primary text-white text-center py-4 border-bottom-0">
              <i class="bi bi-headset display-5 mb-3"></i>
              <h4 class="fw-bold mb-1">Support Tickets</h4>
              <p class="mb-0">Créez votre compte</p>
            </div>
            
            <div class="card-body p-4 p-lg-5">
              <!-- Alerte d'erreur -->
              <div v-if="errorMessage" class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ errorMessage }}
              </div>
              
              <!-- Message de succès -->
              <div v-if="successMessage" class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ successMessage }}
                <div class="mt-3">
                  <router-link to="/login" class="btn btn-primary btn-sm">Se connecter</router-link>
                </div>
              </div>
              
              <!-- Formulaire d'inscription -->
              <form v-if="!registrationComplete" @submit.prevent="register">
                <div class="mb-3">
                  <label for="name" class="form-label">Nom complet</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="bi bi-person"></i>
                    </span>
                    <input 
                      type="text" 
                      id="name"
                      v-model="name" 
                      class="form-control" 
                      placeholder="Entrez votre nom" 
                      required
                      autocomplete="name"
                    />
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="email" class="form-label">Adresse email</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="bi bi-envelope"></i>
                    </span>
                    <input 
                      type="email" 
                      id="email"
                      v-model="email" 
                      class="form-control" 
                      placeholder="Entrez votre email" 
                      required
                      autocomplete="email"
                    />
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="password" class="form-label">Mot de passe</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="bi bi-lock"></i>
                    </span>
                    <input 
                      :type="showPassword ? 'text' : 'password'" 
                      id="password"
                      v-model="password" 
                      class="form-control" 
                      placeholder="Choisissez un mot de passe" 
                      required
                      autocomplete="new-password"
                    />
                    <button 
                      class="btn btn-outline-secondary" 
                      type="button"
                      @click="showPassword = !showPassword"
                    >
                      <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                  </div>
                  <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                </div>
                
                <div class="mb-4">
                  <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="bi bi-shield-lock"></i>
                    </span>
                    <input 
                      :type="showPassword ? 'text' : 'password'" 
                      id="password_confirmation"
                      v-model="password_confirmation" 
                      class="form-control" 
                      placeholder="Confirmez votre mot de passe" 
                      required
                      autocomplete="new-password"
                    />
                  </div>
                </div>
                
                <div class="mb-4 form-check">
                  <input type="checkbox" class="form-check-input" id="termsCheck" v-model="acceptTerms" required>
                  <label class="form-check-label" for="termsCheck">J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a></label>
                </div>
                
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary py-2" :disabled="isLoading || !acceptTerms">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <i v-else class="bi bi-person-plus me-2"></i>
                    S'inscrire
                  </button>
                </div>
                
                <div class="text-center mt-4">
                  <span class="text-muted">Déjà inscrit(e) ?</span>
                  <router-link to="/login" class="text-decoration-none ms-2">Se connecter</router-link>
                </div>
                
                <div class="text-center mt-4">
                  <hr class="divider">
                  <span class="divider-text bg-white px-3">ou</span>
                </div>
                
                <!-- Bouton de connexion Google -->
                <div class="d-grid gap-2 mt-4">
                  <button type="button" class="btn btn-outline-danger" @click="handleGoogleLogin" :disabled="isLoading">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <i v-else class="bi bi-google me-2"></i>
                    S'inscrire avec Google
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../api/axios'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../store/authStore'

// Variables pour le formulaire
const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const errorMessage = ref('')
const successMessage = ref('')
const showPassword = ref(false)
const acceptTerms = ref(false)
const isLoading = ref(false)
const registrationComplete = ref(false)
const router = useRouter()
const route = useRoute()
const auth = useAuthStore() // Référence au store d'authentification

// Vérifier si on arrive d'une authentification Google échouée
if (route.query.error === 'google_auth_failed') {
  errorMessage.value = 'L\'authentification Google a échoué. Veuillez réessayer.'
}

// Connexion avec Google
const handleGoogleLogin = () => {
  isLoading.value = true
  errorMessage.value = ''
  
  // Rediriger vers l'endpoint d'authentification Google du backend
  window.location.href = 'http://localhost:8000/api/auth/google'
}

// Validation de base du formulaire
const validateForm = () => {
  if (!name.value || !email.value || !password.value || !password_confirmation.value) {
    errorMessage.value = 'Veuillez remplir tous les champs obligatoires.'
    return false
  }
  
  if (password.value.length < 8) {
    errorMessage.value = 'Le mot de passe doit contenir au moins 8 caractères.'
    return false
  }
  
  if (password.value !== password_confirmation.value) {
    errorMessage.value = 'Les mots de passe ne correspondent pas.'
    return false
  }
  
  if (!acceptTerms.value) {
    errorMessage.value = 'Vous devez accepter les conditions d\'utilisation pour vous inscrire.'
    return false
  }
  
  return true
}

// Inscription de l'utilisateur
const register = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  
  if (!validateForm()) {
    return
  }
  
  isLoading.value = true
  
  try {
    // Important: utiliser une version spécifique de l'API qui n'utilise pas le store d'authentification
    // pour éviter toute tentative de connexion automatique
    const response = await api.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    })
    
    // S'assurer qu'aucune tentative de connexion automatique n'est faite
    // en s'assurant que le store d'authentification reste dans un état déconnecté
    if (auth.isLoggedIn) {
      await auth.logout() // Déconnecter si pour une raison quelconque l'utilisateur est connecté
    }
    
    // Afficher un message de succès au lieu de rediriger
    registrationComplete.value = true
    successMessage.value = `Inscription réussie ! Un email de vérification a été envoyé à ${email.value}. Veuillez vérifier votre boîte de réception et cliquer sur le lien pour activer votre compte.`
    
    // Réinitialiser le formulaire
    name.value = ''
    email.value = ''
    password.value = ''
    password_confirmation.value = ''
    acceptTerms.value = false
    
  } catch (error) {
    console.error('Erreur d\'inscription:', error)
    
    // Extraire les messages d'erreur de l'API Laravel
    if (error.response && error.response.data && error.response.data.errors) {
      const errors = error.response.data.errors
      const firstError = Object.values(errors)[0]
      errorMessage.value = firstError ? firstError[0] : "L'inscription a échoué. Veuillez vérifier vos informations."
    } else {
      errorMessage.value = "L'inscription a échoué. Veuillez vérifier vos informations."
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
/* Utilisation des mêmes styles que la page de connexion */
.auth-container {
  min-height: calc(100vh - 72px); /* Hauteur moins la navbar */
  background-color: #f8f9fa;
  background-image: url('data:image/svg+xml;charset=utf8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"%3E%3Cpath fill="%230d6efd" fill-opacity="0.05" d="M0,160L48,144C96,128,192,96,288,106.7C384,117,480,171,576,186.7C672,203,768,181,864,154.7C960,128,1056,96,1152,96C1248,96,1344,128,1392,144L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"%3E%3C/path%3E%3C/svg%3E');
  background-repeat: no-repeat;
  background-position: bottom;
  background-size: 100%;
}

/* Animation sur les boutons */
.btn {
  transition: all 0.2s;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>