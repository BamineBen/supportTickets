<template>
  <div class="container py-5">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <div class="error-page mb-5">
          <h1 class="display-1 fw-bold text-primary">404</h1>
          <div class="error-image mb-4">
            <i class="bi bi-question-circle-fill text-muted display-1"></i>
          </div>
          <h2 class="mb-3">Page introuvable</h2>
          <p class="lead text-muted mb-4">
            Oups ! La page que vous recherchez semble avoir été déplacée ou n'existe pas.
          </p>
          <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mb-4">
            <button @click="goBack" class="btn btn-outline-primary">
              <i class="bi bi-arrow-left me-2"></i> Retour
            </button>
            <router-link to="/" class="btn btn-primary">
              <i class="bi bi-house me-2"></i> Accueil
            </router-link>
          </div>
        </div>
        
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h3 class="h5 mb-3">Vous cherchiez peut-être...</h3>
            <div class="row text-start">
              <div class="col-md-6">
                <ul class="list-unstyled">
                  <li class="mb-2">
                    <router-link to="/" class="text-decoration-none">
                      <i class="bi bi-house-door me-2"></i> Page d'accueil
                    </router-link>
                  </li>
                  <li class="mb-2">
                    <router-link to="/dashboard" class="text-decoration-none">
                      <i class="bi bi-speedometer2 me-2"></i> Tableau de bord
                    </router-link>
                  </li>
                  <li class="mb-2">
                    <router-link to="/tickets" class="text-decoration-none">
                      <i class="bi bi-ticket-perforated me-2"></i> Liste des tickets
                    </router-link>
                  </li>
                </ul>
              </div>
              <div class="col-md-6">
                <ul class="list-unstyled">
                  <li class="mb-2">
                    <router-link to="/tickets/create" class="text-decoration-none">
                      <i class="bi bi-plus-circle me-2"></i> Créer un ticket
                    </router-link>
                  </li>
                  <li class="mb-2">
                    <router-link to="/profile" class="text-decoration-none">
                      <i class="bi bi-person me-2"></i> Mon profil
                    </router-link>
                  </li>
                  <li class="mb-2">
                    <router-link to="/contact" class="text-decoration-none">
                      <i class="bi bi-envelope me-2"></i> Nous contacter
                    </router-link>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const goBack = () => {
  router.go(-1);
};
</script>

<style scoped>
.error-page {
  animation: fadeIn 0.6s ease-in-out;
}

.error-image {
  animation: pulse 2s infinite;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

a {
  transition: color 0.3s;
}

a:hover {
  color: var(--bs-primary) !important;
}

.btn {
  transition: all 0.2s;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
