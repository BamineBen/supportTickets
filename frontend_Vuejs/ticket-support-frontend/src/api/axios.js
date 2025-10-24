import axios from 'axios'
import { useAuthStore } from '../store/authStore'

// Créer une instance axios avec la configuration de base
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true
})

/**
 * Récupère le token d'authentification depuis le localStorage
 * @returns {string|null} Le token ou null si non disponible
 */
const getAuthToken = () => {
  return localStorage.getItem('token')
}

/**
 * Ajoute le token d'authentification à chaque requête
 */
api.interceptors.request.use(config => {
  // Récupérer le token
  const token = getAuthToken()
  
  // Si on a un token, l'ajouter aux headers
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  
  // Log pour débugger (version simplifiée)
  console.log(`Requête API ${config.method?.toUpperCase()} vers ${config.url}`, {
    token: token ? 'Présent' : 'Absent', 
    withCredentials: config.withCredentials
  })
  
  return config
}, error => {
  console.error('Erreur de configuration de la requête:', error)
  return Promise.reject(error)
})

// Intercepteur de réponse simplifié pour la gestion des erreurs
api.interceptors.response.use(
  // Gestion des réponses réussies
  response => {
    console.log('API Success:', response.config.url)
    return response
  },
  // Gestion des erreurs
  error => {
    // Récupérer le store d'authentification seulement si nécessaire
    const handleAuthError = () => {
      const authStore = useAuthStore()
      if (window.location.pathname !== '/login') {
        console.log('Erreur dauthentification, déconnexion et redirection')
        authStore.logout()
        window.location.href = '/login?session_expired=true'
      }
    }
    
    // Traiter différents types d'erreurs
    if (error.response) {
      // Le serveur a répondu avec un code d'erreur
      switch (error.response.status) {
        case 401:
          console.log('Erreur dauthentification (401)')
          handleAuthError()
          break
          
        case 403:
          console.error('Accès refusé (403):', error.response.data)
          break
          
        case 404:
          console.error('Ressource non trouvée (404):', error.response.config.url)
          break
          
        case 422:
          console.error('Erreur de validation (422):', error.response.data)
          break
          
        case 500:
          console.error('Erreur serveur (500):', error.response.data || 'Erreur serveur interne')
          break
          
        default:
          console.error(`Erreur HTTP ${error.response.status}:`, error.response.data)
      }
    } else if (error.request) {
      // La requête a été envoyée mais aucune réponse n'a été reçue
      console.error('Erreur réseau - Pas de réponse du serveur:', error.message)
    } else {
      // Une erreur s'est produite lors de la configuration de la requête
      console.error('Erreur de configuration de la requête:', error.message)
    }
    
    return Promise.reject(error)
  }
)

export default api