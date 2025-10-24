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
              <p class="mb-0">Connectez-vous à votre compte</p>
            </div>
            
            <div class="card-body p-4 p-lg-5">
              <!-- Alerte d'erreur -->
              <div v-if="errorMessage && !needsEmailVerification" class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ errorMessage }}
              </div>
              
              <!-- Alerte pour vérification email -->
              <div v-if="needsEmailVerification" class="alert alert-warning" role="alert">
                <i class="bi bi-envelope-exclamation-fill me-2"></i>
                <div class="d-flex flex-column">
                  <span>{{ errorMessage }}</span>
                  <div class="mt-2">
                    <router-link to="/verify-email" class="btn btn-sm btn-primary me-2">
                      <i class="bi bi-arrow-right me-1"></i>Page de vérification
                    </router-link>
                  </div>
                </div>
              </div>
              
              <!-- Formulaire de connexion -->
              <form @submit.prevent="handleLogin">
                <div class="mb-4">
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
                
                <div class="mb-4">
                  <div class="d-flex justify-content-between">
                    <label for="password" class="form-label">Mot de passe</label>
                    <a href="#" class="small text-decoration-none">Mot de passe oublié ?</a>
                  </div>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="bi bi-lock"></i>
                    </span>
                    <input 
                      :type="showPassword ? 'text' : 'password'" 
                      id="password"
                      v-model="password" 
                      class="form-control" 
                      placeholder="Entrez votre mot de passe" 
                      required
                      autocomplete="current-password"
                    />
                    <button 
                      class="btn btn-outline-secondary" 
                      type="button"
                      @click="showPassword = !showPassword"
                    >
                      <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                  </div>
                </div>
                
                <div class="mb-4 form-check">
                  <input type="checkbox" class="form-check-input" id="rememberMe" v-model="rememberMe">
                  <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
                </div>
                
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary py-2" :disabled="isLoading">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <i v-else class="bi bi-box-arrow-in-right me-2"></i>
                    Connexion
                  </button>
                </div>
                
                <div class="text-center mt-4">
                  <span class="text-muted">Vous n'avez pas de compte ?</span>
                  <router-link to="/register" class="text-decoration-none ms-2">Inscrivez-vous</router-link>
                </div>
                
                <!-- Boutons de connexion sociale -->
                <div class="d-grid gap-2 mt-4">
                  <button type="button" class="btn btn-outline-danger" @click="handleGoogleLogin" :disabled="isLoading">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <i v-else class="bi bi-google me-2"></i>
                    Continuer avec Google
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
import { useAuthStore } from '../store/authStore'
import { useRouter, useRoute } from 'vue-router'

// Variables pour le formulaire
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const isLoading = ref(false)

// Stores et router
const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

// Vérifier si on arrive d'une authentification Google échouée
if (route.query.error === 'google_auth_failed') {
  errorMessage.value = 'L\'authentification Google a échoué. Veuillez réessayer.'
}

// Référence pour indiquer si l'utilisateur doit vérifier son email
const needsEmailVerification = ref(false)
const emailForVerification = ref('')

// Gestion de la connexion
const handleLogin = async () => {
  if (!email.value || !password.value) {
    errorMessage.value = 'Veuillez remplir tous les champs obligatoires.'
    return
  }
  
  try {
    isLoading.value = true
    errorMessage.value = ''
    needsEmailVerification.value = false
    
    // Tentative de connexion via l'API
    try {
      await auth.login(email.value, password.value)
      
      // Attendre un court instant pour s'assurer que l'authentification est bien enregistrée
      await new Promise(resolve => setTimeout(resolve, 300))
      
      // Vérifier si l'utilisateur est bien connecté avant de rediriger
      if (auth.isLoggedIn) {
        // Redirection vers le tableau de bord
        router.push('/dashboard')
      } else {
        throw new Error('Échec de la connexion. Veuillez réessayer.')
      }
    } catch (loginError) {
      console.error('Erreur de connexion détaillée:', loginError)
      
      // Vérifier si c'est une erreur de vérification d'email (code 403)
      if (loginError.response && loginError.response.status === 403 && loginError.response.data.email_verified === false) {
        needsEmailVerification.value = true
        emailForVerification.value = email.value
        errorMessage.value = "Votre compte n'est pas encore activé. Veuillez vérifier votre boîte de réception pour activer votre compte."
      } else {
        errorMessage.value = 'Identifiants incorrects ou problème de connexion. Veuillez réessayer.'
      }
    }
  } catch (error) {
    console.error('Erreur générale:', error)
    errorMessage.value = 'Une erreur s\'est produite. Veuillez réessayer.'
  } finally {
    isLoading.value = false
  }
}

// Connexion avec Google
const handleGoogleLogin = () => {
  // Rediriger vers l'API d'authentification Google du backend
  isLoading.value = true
  errorMessage.value = ''
  
  // Vérifier si le paramètre error est présent dans l'URL
  if (route.query.error === 'google_auth_failed') {
    errorMessage.value = 'L\'authentification Google a échoué. Veuillez réessayer.'
    isLoading.value = false
    return
  }
  
  // Rediriger vers l'endpoint d'authentification Google du backend
  window.location.href = 'http://localhost:8000/api/auth/google'
}
</script>

<style scoped>
/* Styles pour le divider avec texte */
.divider {
  position: relative;
  margin-top: 1.5rem;
  margin-bottom: 1.5rem;
}

.divider-text {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
}

/* Animation sur les boutons */
.btn {
  transition: all 0.2s;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Background pour la page d'authentification */
.auth-container {
  min-height: calc(100vh - 72px); /* Hauteur moins la navbar */
  background-color: #f8f9fa;
  background-image: url('data:image/svg+xml;charset=utf8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"%3E%3Cpath fill="%230d6efd" fill-opacity="0.05" d="M0,160L48,144C96,128,192,96,288,106.7C384,117,480,171,576,186.7C672,203,768,181,864,154.7C960,128,1056,96,1152,96C1248,96,1344,128,1392,144L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"%3E%3C/path%3E%3C/svg%3E');
  background-repeat: no-repeat;
  background-position: bottom;
  background-size: 100%;
}
</style>