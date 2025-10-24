<template>
  <div class="app-container d-flex flex-column min-vh-100">
    <!-- Indicateur de chargement global -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
    </div>

    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
      <div class="container-fluid">
        <RouterLink class="navbar-brand" to="/">
          <i class="bi bi-headset me-2"></i>
          <span class="d-none d-sm-inline">Support Tickets</span>
          <span class="d-inline d-sm-none">Support</span>
        </RouterLink>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item" v-if="!isAuthenticated">
              <RouterLink class="nav-link" to="/">Accueil</RouterLink>
            </li>
            <li class="nav-item" v-if="isAuthenticated">
              <RouterLink class="nav-link" to="/dashboard">Tableau de bord</RouterLink>
            </li>
            <li class="nav-item" v-if="isAuthenticated">
              <RouterLink class="nav-link" to="/tickets">Tickets</RouterLink>
            </li>
            <li class="nav-item" v-if="isAdmin">
              <RouterLink class="nav-link" to="/admin/dashboard">Administration</RouterLink>
            </li>
            <!-- Bouton de déconnexion pour mobile -->
            <li class="nav-item d-block d-lg-none" v-if="isAuthenticated">
              <a @click.prevent="logout" href="#" class="nav-link text-white bg-danger px-3 rounded-pill ms-2">
                <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
              </a>
            </li>
          </ul>
          
          <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center mt-2 mt-lg-0">
            <!-- Utilisateur connecté -->
            <div v-if="isAuthenticated" class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center">
              <!-- Bouton de création de ticket -->
              <RouterLink to="/tickets/create" class="btn btn-success me-lg-2 mb-2 mb-lg-0">
                <i class="bi bi-plus-circle me-1"></i> Nouveau ticket
              </RouterLink>
              
              <!-- Bouton de déconnexion unique -->
              <button @click="logout" class="btn btn-danger me-lg-2 mb-2 mb-lg-0 d-none d-lg-block">
                <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
              </button>
              
              <!-- Menu utilisateur avec dropdown Vue -->
              <div class="custom-dropdown position-relative">
                <button @click="toggleUserMenu" class="btn btn-outline-light d-flex align-items-center">
                  <i class="bi bi-person-circle me-1"></i>
                  <span class="d-none d-sm-inline me-1">{{ user?.name || 'Utilisateur' }}</span>
                  <i class="bi bi-chevron-down small"></i>
                </button>
                
                <!-- Menu déroulant avec transition -->
                <div v-if="userMenuOpen" class="position-absolute bg-white shadow rounded mt-1 py-2" style="right: 0; min-width: 200px; z-index: 1000;">
                  <div class="px-3 py-1 text-muted small fw-bold border-bottom mb-1">{{ user?.email }}</div>
                  
                  <RouterLink to="/profile" class="d-block px-3 py-2 text-decoration-none text-dark hover-bg-light">
                    <i class="bi bi-person me-2"></i> Mon profil
                  </RouterLink>
                  
                  <div class="dropdown-divider my-1"></div>
                  
                  <a @click.prevent="logout" href="#" class="d-block px-3 py-2 text-decoration-none text-danger hover-bg-light">
                    <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                  </a>
                </div>
              </div>
            </div>
            
            <!-- Utilisateur non connecté - visible sur toutes les pages -->
            <div v-if="!isAuthenticated" class="d-grid gap-2 d-lg-flex w-100">
              <RouterLink to="/login" class="btn btn-outline-light me-lg-2 mb-2 mb-lg-0">
                <i class="bi bi-box-arrow-in-right me-1"></i> Connexion
              </RouterLink>
              <RouterLink to="/register" class="btn btn-light">
                <i class="bi bi-person-plus me-1"></i> Inscription
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- Contenu principal -->
    <main class="flex-grow-1 py-3 py-md-4">
      <div class="container">
        <router-view />
      </div>
    </main>
    
    <!-- Pied de page -->
    <footer class="bg-light py-4 mt-auto">
      <div class="container">
        <div class="row align-items-center justify-content-between">
          <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
            <span class="text-muted">&copy; 2025 Support Tickets. Tous droits réservés.</span>
          </div>
          <div class="col-md-4 text-center mb-3 mb-md-0">
            <RouterLink to="/" class="text-decoration-none text-primary fw-bold">
              <i class="bi bi-headset me-2"></i>
              Support Tickets
            </RouterLink>
          </div>
          <div class="col-md-4 text-center text-md-end">
            <RouterLink to="/contact" class="text-decoration-none text-muted me-3">
              Contact
            </RouterLink>
            <RouterLink to="/about" class="text-decoration-none text-muted">
              À propos
            </RouterLink>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from './store/authStore'
import { useRouter } from 'vue-router'
import * as bootstrap from 'bootstrap'

// Initialisation des stores et du router
const auth = useAuthStore()
const router = useRouter()

// État de chargement global
const isLoading = ref(false)

// État du menu utilisateur
const userMenuOpen = ref(false)

// Fonction pour basculer le menu utilisateur
const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

// Fermer le menu quand on clique ailleurs
onMounted(() => {
  document.addEventListener('click', (e) => {
    const dropdown = document.querySelector('.custom-dropdown')
    if (dropdown && !dropdown.contains(e.target) && userMenuOpen.value) {
      userMenuOpen.value = false
    }
  })
})

// Propriétés calculées pour l'authentification
const isAuthenticated = computed(() => auth.isLoggedIn)
const isAdmin = computed(() => auth.hasRole('admin'))
const user = computed(() => auth.user)

// Déconnexion de l'utilisateur
const logout = async () => {
  isLoading.value = true
  try {
    await auth.logout()
    router.push('/')
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error)
  } finally {
    isLoading.value = false
  }
}

// Initialiser tous les dropdowns Bootstrap lorsque l'application est montée
onMounted(() => {
  // Initialiser les dropdowns Bootstrap
  document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(dropdown => {
    new bootstrap.Dropdown(dropdown)
  })
})
</script>

<style>
/* Importer les icônes Bootstrap */
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css");

/* Styles globaux */
:root {
  --bs-primary: #0d6efd;
  --bs-primary-rgb: 13, 110, 253;
  --bs-secondary: #6c757d;
  --bs-success: #198754;
  --bs-info: #0dcaf0;
  --bs-warning: #ffc107;
  --bs-danger: #dc3545;
}

.navbar-brand i {
  font-size: 1.2rem;
}

/* Overlay de chargement */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

/* Animation pour les transitions de page */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Styles pour les badges de statut */
.badge.bg-success-soft {
  background-color: rgba(25, 135, 84, 0.15);
  color: #198754;
}

/* Styles pour le menu déroulant personnalisé */
.hover-bg-light:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.dropdown-divider {
  height: 0;
  margin: 0.5rem 0;
  overflow: hidden;
  border-top: 1px solid rgba(0, 0, 0, 0.15);
}

.custom-dropdown button {
  transition: all 0.2s;
}

.custom-dropdown button:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.badge.bg-warning-soft {
  background-color: rgba(255, 193, 7, 0.15);
  color: #ac8603;
}

.badge.bg-primary-soft {
  background-color: rgba(13, 110, 253, 0.15);
  color: #0d6efd;
}

.badge.bg-danger-soft {
  background-color: rgba(220, 53, 69, 0.15);
  color: #dc3545;
}
</style>