<template>
  <div class="auth-container">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
          <div class="card border-0 shadow-lg rounded-3">
            <div class="card-header bg-primary text-white text-center py-4 border-bottom-0">
              <i class="bi bi-envelope-check display-5 mb-3"></i>
              <h4 class="fw-bold mb-1">Vérification de l'email</h4>
            </div>
            
            <div class="card-body p-4 p-lg-5 text-center">
              <!-- Si on a un ID et un hash, on vérifie -->
              <div v-if="isVerifying">
                <div class="spinner-border text-primary mb-4" role="status">
                  <span class="visually-hidden">Chargement...</span>
                </div>
                <h5>Vérification de votre adresse email...</h5>
                <p class="text-muted">Veuillez patienter, nous vérifions votre adresse email.</p>
              </div>
              
              <!-- Succès -->
              <div v-else-if="status === 'success'" class="text-center">
                <div class="icon-success mb-4">
                  <i class="bi bi-check-circle-fill text-success display-1"></i>
                </div>
                <h3 class="text-success mb-3">Félicitations !</h3>
                <p class="mb-4">Votre adresse email a été vérifiée avec succès.</p>
                <p>Vous allez être redirigé vers la page de connexion dans quelques secondes...</p>
                <div class="mt-4">
                  <router-link to="/login" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter maintenant
                  </router-link>
                </div>
              </div>
              
              <!-- Erreur -->
              <div v-else-if="status === 'error'" class="text-center">
                <div class="icon-error mb-4">
                  <i class="bi bi-exclamation-circle-fill text-danger display-1"></i>
                </div>
                <h3 class="text-danger mb-3">Vérification échouée</h3>
                <p class="mb-4">Le lien de vérification est invalide ou a expiré.</p>
                <div class="mt-4">
                  <router-link to="/login" class="btn btn-outline-primary me-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                  </router-link>
                </div>
              </div>
              
              <!-- Si pas de paramètres -->
              <div v-else class="text-center">
                <div class="icon-info mb-4">
                  <i class="bi bi-envelope-check text-primary display-1"></i>
                </div>
                <h3 class="mb-3">Vérifiez votre boîte de réception</h3>
                <p class="mb-4">Un email de vérification a été envoyé à votre adresse email. Veuillez vérifier votre boîte de réception et cliquer sur le lien pour activer votre compte.</p>
                <div class="alert alert-info mb-4" role="alert">
                  <i class="bi bi-info-circle-fill me-2"></i>
                  Si vous ne trouvez pas l'email, vérifiez votre dossier de spam ou courrier indésirable.
                </div>
                <div class="mt-4">
                  <router-link to="/login" class="btn btn-outline-primary me-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Retour à la connexion
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api/axios'

const route = useRoute()
const router = useRouter()
const status = ref(null)
const isVerifying = ref(false)

onMounted(async () => {
  const id = route.query.id
  const hash = route.query.hash
  
  // Vérifier uniquement si on a les paramètres nécessaires
  if (id && hash) {
    isVerifying.value = true
    
    try {
      await api.post('/verify-email', { id, hash })
      status.value = 'success'
      
      // Rediriger après un délai pour permettre à l'utilisateur de voir le message
      setTimeout(() => {
        router.push('/login')
      }, 5000)
    } catch (e) {
      console.error('Erreur lors de la vérification:', e)
      status.value = 'error'
    } finally {
      isVerifying.value = false
    }
  }
})
</script>

<style scoped>
.auth-container {
  min-height: calc(100vh - 72px); /* Hauteur moins la navbar */
  background-color: #f8f9fa;
  background-image: url('data:image/svg+xml;charset=utf8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"%3E%3Cpath fill="%230d6efd" fill-opacity="0.05" d="M0,160L48,144C96,128,192,96,288,106.7C384,117,480,171,576,186.7C672,203,768,181,864,154.7C960,128,1056,96,1152,96C1248,96,1344,128,1392,144L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"%3E%3C/path%3E%3C/svg%3E');
  background-repeat: no-repeat;
  background-position: bottom;
  background-size: 100%;
}

.icon-success, .icon-error, .icon-info {
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>