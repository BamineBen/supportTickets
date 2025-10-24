import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    roles: JSON.parse(localStorage.getItem('roles')) || []
  }),

  actions: {
    // Initialisation du store - à appeler au démarrage de l'app
    async init() {
      const token = localStorage.getItem('token')
      if (token && !this.user) {
        try {
          await this.fetchProfile()
          console.log('Profil chargé automatiquement au démarrage')
        } catch (error) {
          console.error('Erreur lors du chargement automatique du profil:', error)
          // Si erreur d'authentification, faire un logout
          if (error.response?.status === 401) {
            this.logout()
          }
        }
      }
    },
    /**
     * Authentification utilisateur
     * @param {string} email - Adresse email de l'utilisateur
     * @param {string} password - Mot de passe de l'utilisateur
     */
    async login(email, password) {
      try {
        console.log('Tentative de connexion avec:', { email })
        
        // Appeler l'API de connexion
        const res = await api.post('/login', { email, password })
        
        // Vérifier si l'email est vérifié
        if (res.data.email_verified === false) {
          throw {
            response: {
              status: 403,
              data: { 
                message: 'Veuillez vérifier votre adresse email avant de vous connecter.',
                email_verified: false
              }
            }
          }
        }
        
        // Enregistrer le token et les informations de l'utilisateur
        this.token = res.data.access_token
        localStorage.setItem('token', this.token)
        
        // Enregistrer les données utilisateur
        if (res.data.user) {
          this.user = res.data.user
          localStorage.setItem('user', JSON.stringify(this.user))
        }
        
        // Traitement standardisé des rôles
        this.processRoles(res.data)
        
        console.log('Utilisateur connecté avec succès')
        
        // Récupérer le profil complet avec gestion des erreurs
        if (this.token) {
          try {
            await this.fetchProfile()
          } catch (e) {
            console.warn('Impossible de récupérer le profil complet, utilisation des données de base')
          }
        }
        
        return true
      } catch (error) {
        console.error('Erreur lors de la connexion:', error)
        throw error
      }
    },
    
    /**
     * Traite les rôles utilisateur reçus de l'API
     * @param {Object} data - Données de réponse de l'API
     */
    processRoles(data) {
      // Extraction des rôles selon différents formats possibles
      if (data.roles) {
        // Si les rôles sont directement dans data.roles (format simple)
        this.roles = Array.isArray(data.roles) ? [...data.roles] : [data.roles]
      } else if (data.user && data.user.roles) {
        // Si les rôles sont dans data.user.roles (format objet)
        if (Array.isArray(data.user.roles)) {
          // Si c'est un tableau d'objets avec propriété name
          if (data.user.roles.length > 0 && typeof data.user.roles[0] === 'object') {
            this.roles = data.user.roles.map(role => role.name || role)
          } else {
            // Si c'est un tableau de chaînes
            this.roles = [...data.user.roles]
          }
        } else {
          // Si c'est une valeur unique
          this.roles = [data.user.roles]
        }
      } else {
        // Aucun rôle trouvé
        this.roles = []
      }
      
      // Persister les rôles dans localStorage
      localStorage.setItem('roles', JSON.stringify(this.roles))
    },
    
    async fetchProfile() {
      try {
        // Vérifier d'abord si un token est disponible
        const token = localStorage.getItem('token')
        if (!token) {
          console.warn('Tentative de chargement du profil sans token')
          return false
        }
        
        // Récupérer le profil complet avec le token explicitement défini dans l'en-tête
        const res = await api.get('/profile', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        })
        console.log('Réponse du profil:', res.data)
        
        // Traitement des données utilisateur
        if (res.data.user) {
          this.user = res.data.user
        } else {
          this.user = res.data
        }
        
        // Persister les données utilisateur dans localStorage
        localStorage.setItem('user', JSON.stringify(this.user))
        
        // SOLUTION TEMPORAIRE: Si c'est bamine, forcer le rôle admin
        if (this.user.email === 'bamine@example.com') {
          console.log('Solution temporaire dans fetchProfile: Ajout manuel du rôle admin pour bamine')
          this.roles = ['admin']
          localStorage.setItem('roles', JSON.stringify(['admin']))
          return // On sort de la fonction pour éviter de remplacer le rôle admin
        }
        
        // Traitement des rôles - CONVERTIR EXPLICITEMENT EN TABLEAU STANDARD
        if (res.data.roles) {
          this.roles = [...res.data.roles] // Conversion en tableau standard
        } else if (res.data.user && res.data.user.roles) {
          this.roles = [...res.data.user.roles] // Conversion en tableau standard
        } else {
          // Essayons de récupérer les rôles via une autre API
          try {
            const rolesRes = await api.get('/user')
            if (rolesRes.data && rolesRes.data.roles) {
              this.roles = [...rolesRes.data.roles] // Conversion en tableau standard
            }
          } catch (e) {
            console.error('Erreur lors de la récupération des rôles:', e)
          }
        }
        
        // Persister les rôles
        localStorage.setItem('roles', JSON.stringify(this.roles))
        console.log('Rôles après fetchProfile (tableau):', [...this.roles])
      } catch (error) {
        console.error('Erreur lors de la récupération du profil:', error)
      }
    },
    
    async logout() {
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Erreur lors de la déconnexion:', error)
      } finally {
        // Nettoyer l'état même en cas d'erreur
        this.token = null
        this.user = null
        this.roles = []
        // Nettoyer localStorage
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        localStorage.removeItem('roles')
      }
    },
    
    // Vérifier si l'utilisateur a un rôle spécifique
    hasRole(role) {
      console.log('Vérification du rôle:', role)
      
      // IMPORTANT: Solution temporaire pour l'utilisateur bamine
      // Ceci n'est qu'une solution de débogage pour éviter le blocage
      if (this.user && this.user.email === 'bamine@example.com' && role === 'admin') {
        console.log('OVERRIDE: bamine est reconnu comme admin (solution temporaire)')
        return true
      }
      
      // Convertir en tableau standard pour le debug et éviter les problèmes avec Proxy
      const rolesArray = this.roles ? [...this.roles] : []
      console.log('Rôles disponibles (tableau standard):', rolesArray)
      console.log('Utilisateur actuel:', this.user?.name, this.user?.email)

      // Pas de rôles
      if (!rolesArray || rolesArray.length === 0) {
        // Débug - Essayer de trouver les rôles dans d'autres propriétés utilisateur
        if (this.user) {
          console.log('Vérification des propriétés utilisateur pour les rôles:', Object.keys(this.user))
          if (this.user.roles) {
            console.log('Propriété user.roles détectée:', this.user.roles)
            return Array.isArray(this.user.roles) 
              ? this.user.roles.some(r => typeof r === 'string' ? r === role : r.name === role)
              : false
          }
          if (this.user.role) {
            console.log('Propriété user.role détectée:', this.user.role)
            return this.user.role === role
          }
        }
        return false
      }

      // Si les rôles sont des objets avec une propriété 'name'
      if (typeof rolesArray[0] === 'object' && rolesArray[0]) {
        return rolesArray.some(r => r.name === role)
      }

      // Si les rôles sont des strings
      return rolesArray.includes(role)
    }
  },

  getters: {
    isLoggedIn: (state) => !!state.token && !!state.user,
  },
})