import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import { useAuthStore } from './store/authStore'

// Importation de Bootstrap
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

// Import du CSS global
import './style.css'

// Créer l'application
const app = createApp(App)

// Installer Pinia
const pinia = createPinia()
app.use(pinia)

// Initialiser le store d'authentification
const authStore = useAuthStore()
authStore.init().catch(error => {
  console.error('Erreur lors de l\'initialisation du store:', error)
})

// Installer le router et monter l'application
app.use(router)
app.mount('#app')