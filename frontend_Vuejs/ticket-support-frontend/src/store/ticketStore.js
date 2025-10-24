import { defineStore } from 'pinia'
import api from '../api/axios'

export const useTicketStore = defineStore('ticket', {
  state: () => ({
    tickets: [],
    currentTicket: null,
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      total: 0,
      per_page: 10,
      last_page: 0
    },
    filters: {
      status: '',
      search: '',
      sort: 'newest'
    }
  }),
  
  getters: {
    getTicketById: (state) => (id) => {
      return state.tickets.find(ticket => ticket.id === parseInt(id))
    },
    
    pendingTickets: (state) => {
      return state.tickets.filter(ticket => ticket.statut === 'ouvert' || ticket.statut === 'en cours')
    },
    
    completedTickets: (state) => {
      return state.tickets.filter(ticket => ticket.statut === 'fermé')
    }
  },
  
  actions: {
    async fetchTickets(page = 1, filters = {}) {
      try {
        this.loading = true
        
        // Fusionner les filtres par défaut avec ceux fournis
        const mergedFilters = { ...this.filters, ...filters }
        this.filters = mergedFilters
        
        // Construire les paramètres de requête
        const params = new URLSearchParams()
        params.append('page', page)
        
        if (mergedFilters.status) {
          params.append('statut', mergedFilters.status)
        }
        
        if (mergedFilters.search) {
          params.append('q', mergedFilters.search)
        }
        
        if (mergedFilters.user_id) {
          params.append('user_id', mergedFilters.user_id)
        }
        
        params.append('sort', mergedFilters.sort || 'newest')
        
        // Obtenir le token de localStorage
        const token = localStorage.getItem('token')
        
        // Préparer le CSRF pour Laravel Sanctum
        try {
          await fetch('http://localhost:8000/sanctum/csrf-cookie', {
            credentials: 'include'
          })
        } catch (csrfError) {
          console.warn('Erreur lors de l\'initialisation du CSRF:', csrfError)
          // On continue malgré l'erreur
        }
        
        // Configuration de l'en-tête pour l'autorisation
        const config = {
          withCredentials: true,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': token ? `Bearer ${token}` : ''
          }
        }
        
        // Récupérer les tickets avec les filtres
        const response = await api.get(`/tickets?${params.toString()}`, config)
        
        // Gérer les différentes structures de réponse possibles
        let ticketsData = []
        let paginationInfo = {
          current_page: 1,
          total: 0,
          per_page: 10,
          last_page: 1
        }
        
        if (response.data && response.data.status === 'success' && response.data.data) {
          // Structure avec status/data et pagination
          if (response.data.data.data && Array.isArray(response.data.data.data)) {
            ticketsData = response.data.data.data
            paginationInfo = {
              current_page: response.data.data.current_page || 1,
              total: response.data.data.total || 0,
              per_page: response.data.data.per_page || 10,
              last_page: response.data.data.last_page || 1
            }
          } 
          // Structure avec status/data mais sans pagination
          else if (Array.isArray(response.data.data)) {
            ticketsData = response.data.data
          }
        } 
        // Structure standard
        else if (response.data && response.data.data) {
          ticketsData = response.data.data
          paginationInfo = {
            current_page: response.data.current_page || 1,
            total: response.data.total || 0,
            per_page: response.data.per_page || 10,
            last_page: response.data.last_page || 1
          }
        }
        // Réponse directe sous forme de tableau
        else if (Array.isArray(response.data)) {
          ticketsData = response.data
        }
        
        // Mettre à jour l'état
        this.tickets = ticketsData
        this.pagination = paginationInfo
        
        return { data: ticketsData, pagination: paginationInfo }
      } catch (error) {
        console.error('Erreur lors de la récupération des tickets:', error)
        this.error = error.message || 'Erreur de chargement des tickets'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async fetchMyTickets(page = 1) {
      try {
        this.loading = true

        // Vérifier d'abord que le token est disponible
        const token = localStorage.getItem('token')
        if (!token) {
          console.warn('Tentative d\'accès à my-tickets sans token')
          this.error = 'Authentification requise. Veuillez vous reconnecter.'
          this.tickets = []
          return { data: [], pagination: { current_page: 1, total: 0, per_page: 10, last_page: 1 } }
        }

        // Initialiser le CSRF token si nécessaire
        try {
          await fetch('http://localhost:8000/sanctum/csrf-cookie', {
            credentials: 'include'
          })
        } catch (error) {
          console.warn('Erreur lors de l\'initialisation du CSRF token:', error)
        }
        
        // Construire les paramètres de requête
        const params = new URLSearchParams()
        params.append('page', page)
        
        // Configuration de l'en-tête pour l'autorisation
        const config = {
          withCredentials: true,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': `Bearer ${token}`
          }
        }
        
        // Récupérer mes tickets
        const response = await api.get(`/my-tickets?${params.toString()}`, config)
        
        // Gérer les différentes structures de réponse possibles
        let ticketsData = []
        let paginationInfo = {
          current_page: 1,
          total: 0,
          per_page: 10,
          last_page: 1
        }
        
        if (response.data && response.data.data) {
          // Si nous avons une structure avec pagination
          if (response.data.current_page) {
            ticketsData = response.data.data
            paginationInfo = {
              current_page: response.data.current_page || 1,
              total: response.data.total || 0,
              per_page: response.data.per_page || 10,
              last_page: response.data.last_page || 1
            }
          } 
          // Si la pagination est imbriquée
          else if (response.data.data.data) {
            ticketsData = response.data.data.data
            paginationInfo = {
              current_page: response.data.data.current_page || 1,
              total: response.data.data.total || 0,
              per_page: response.data.data.per_page || 10,
              last_page: response.data.data.last_page || 1
            }
          }
          // Si c'est juste un tableau
          else {
            ticketsData = response.data.data
          }
        } 
        // Si la réponse est directement un tableau
        else if (Array.isArray(response.data)) {
          ticketsData = response.data
        }
        
        // Mettre à jour l'état
        this.tickets = ticketsData
        this.pagination = paginationInfo
        
        return { data: ticketsData, pagination: paginationInfo }
      } catch (error) {
        console.error('Erreur lors de la récupération de mes tickets:', error)
        this.error = error.message || 'Erreur de chargement des tickets'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async fetchTicket(ticketId) {
      try {
        this.loading = true
        
        // Obtenir le token de localStorage
        const token = localStorage.getItem('token')
        if (!token) {
          console.warn(`Tentative d'accès au ticket ${ticketId} sans token`)
          this.error = 'Authentification requise. Veuillez vous reconnecter.'
          return null
        }
        
        // Initialiser le CSRF token si nécessaire
        try {
          await fetch('http://localhost:8000/sanctum/csrf-cookie', {
            credentials: 'include'
          })
        } catch (error) {
          console.warn('Erreur lors de l\'initialisation du CSRF token:', error)
        }
        
        // Configuration de l'en-tête pour l'autorisation
        const config = {
          withCredentials: true,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': `Bearer ${token}`
          }
        }
        
        const response = await api.get(`/tickets/${ticketId}`, config)
        
        // Gérer les différentes structures de réponse possibles
        let ticketData = null
        
        if (response.data && response.data.status === 'success' && response.data.data) {
          ticketData = response.data.data
        } else if (response.data && response.data.data) {
          ticketData = response.data.data
        } else {
          ticketData = response.data
        }
        
        this.currentTicket = ticketData
        
        console.log('Ticket courant chargé:', this.currentTicket)
        return this.currentTicket
      } catch (error) {
        console.error(`Erreur lors de la récupération du ticket ${ticketId}:`, error)
        this.error = error.message || 'Erreur de chargement du ticket'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async createTicket(ticketData) {
      try {
        this.loading = true
        
        // Obtenir le token de localStorage
        const token = localStorage.getItem('token')
        if (!token) {
          console.warn('Tentative de création de ticket sans token')
          this.error = 'Authentification requise. Veuillez vous reconnecter.'
          return null
        }
        
        // Initialiser le CSRF token si nécessaire
        try {
          await fetch('http://localhost:8000/sanctum/csrf-cookie', {
            credentials: 'include'
          })
        } catch (error) {
          console.warn('Erreur lors de l\'initialisation du CSRF token:', error)
        }
        
        // Configuration de l'en-tête pour l'autorisation
        const config = {
          withCredentials: true,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': `Bearer ${token}`
          }
        }
        
        const response = await api.post('/tickets', ticketData, config)
        
        // Gérer les différentes structures de réponse possibles
        let createdTicket = null
        
        if (response.data && response.data.status === 'success' && response.data.data) {
          createdTicket = response.data.data
        } else if (response.data && response.data.data) {
          createdTicket = response.data.data
        } else {
          createdTicket = response.data
        }
        
        // Ajouter le nouveau ticket à la liste si nécessaire
        this.tickets = [createdTicket, ...this.tickets]
        return createdTicket
      } catch (error) {
        console.error('Erreur lors de la création du ticket:', error)
        this.error = error.message || 'Erreur lors de la création du ticket'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async updateTicket(id, ticketData) {
      try {
        this.loading = true
        
        // Obtenir le token de localStorage
        const token = localStorage.getItem('token')
        if (!token) {
          console.warn(`Tentative de mise à jour du ticket ${id} sans token`)
          this.error = 'Authentification requise. Veuillez vous reconnecter.'
          return null
        }
        
        // Initialiser le CSRF token si nécessaire
        try {
          await fetch('http://localhost:8000/sanctum/csrf-cookie', {
            credentials: 'include'
          })
        } catch (error) {
          console.warn('Erreur lors de l\'initialisation du CSRF token:', error)
        }
        
        // Configuration de l'en-tête pour l'autorisation
        const config = {
          withCredentials: true,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': `Bearer ${token}`
          }
        }
        
        const response = await api.put(`/tickets/${id}`, ticketData, config)
        
        // Gérer les différentes structures de réponse possibles
        let updatedTicket = null
        
        if (response.data && response.data.status === 'success' && response.data.data) {
          updatedTicket = response.data.data
        } else if (response.data && response.data.data) {
          updatedTicket = response.data.data
        } else {
          updatedTicket = response.data
        }
        
        // Mettre à jour le ticket dans la liste
        const index = this.tickets.findIndex(t => t.id === parseInt(id))
        if (index !== -1) {
          this.tickets[index] = { ...this.tickets[index], ...updatedTicket }
        }
        
        // Mettre à jour le ticket courant si c'est celui qui est modifié
        if (this.currentTicket && this.currentTicket.id === parseInt(id)) {
          this.currentTicket = { ...this.currentTicket, ...updatedTicket }
        }
        
        return updatedTicket
      } catch (error) {
        console.error(`Erreur lors de la mise à jour du ticket ${id}:`, error)
        this.error = error.message || 'Erreur lors de la mise à jour du ticket'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async deleteTicket(id) {
      try {
        this.loading = true
        
        // Obtenir le token de localStorage
        const token = localStorage.getItem('token')
        if (!token) {
          console.warn(`Tentative de suppression du ticket ${id} sans token`)
          this.error = 'Authentification requise. Veuillez vous reconnecter.'
          return false
        }
        
        // Initialiser le CSRF token si nécessaire
        try {
          await fetch('http://localhost:8000/sanctum/csrf-cookie', {
            credentials: 'include'
          })
        } catch (error) {
          console.warn('Erreur lors de l\'initialisation du CSRF token:', error)
        }
        
        // Configuration de l'en-tête pour l'autorisation
        const config = {
          withCredentials: true,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': `Bearer ${token}`
          }
        }
        
        await api.delete(`/tickets/${id}`, config)
        
        // Supprimer le ticket de la liste
        this.tickets = this.tickets.filter(t => t.id !== parseInt(id))
        
        // Réinitialiser le ticket courant si c'est celui qui est supprimé
        if (this.currentTicket && this.currentTicket.id === parseInt(id)) {
          this.currentTicket = null
        }
        
        return true
      } catch (error) {
        console.error(`Erreur lors de la suppression du ticket ${id}:`, error)
        this.error = error.message || 'Erreur lors de la suppression du ticket'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async addComment(ticketId, comment) {
      try {
        this.loading = true
        const response = await api.post(`/tickets/${ticketId}/commentaires`, comment)
        
        // Extraire le commentaire de la réponse API selon sa structure
        let newComment = null
        
        if (response.data && response.data.status === 'success' && response.data.data) {
          // Structure avec status/data
          newComment = response.data.data
          console.log('Nouveau commentaire extrait:', newComment)
        } else {
          // Structure standard
          newComment = response.data
        }
        
        // Vérifier que le commentaire contient les données nécessaires
        if (newComment && !newComment.user && response.data && response.data.user) {
          newComment.user = response.data.user
        }
        
        // Si le ticket courant est celui qui reçoit le commentaire, mettre à jour sa liste de commentaires
        if (this.currentTicket && this.currentTicket.id === parseInt(ticketId)) {
          if (!this.currentTicket.commentaires) {
            this.currentTicket.commentaires = []
          }
          
          // Ajouter l'utilisateur au commentaire s'il est manquant
          if (!newComment.user) {
            // Récupérer l'utilisateur depuis le store d'authentification si disponible
            const authStore = window.$nuxt?.$pinia?.state?.value?.auth
            if (authStore && authStore.user) {
              newComment.user = authStore.user
            }
          }
          
          // Ajouter le commentaire à la liste des commentaires
          this.currentTicket.commentaires.push(newComment)
        }
        
        // Rafraîchir le ticket pour s'assurer que tous les commentaires sont à jour
        if (ticketId) {
          this.fetchTicket(ticketId).catch(err => {
            console.warn('Erreur lors du rafraîchissement du ticket après ajout de commentaire:', err)
          })
        }
        
        return newComment
      } catch (error) {
        console.error(`Erreur lors de l'ajout d'un commentaire au ticket ${ticketId}:`, error)
        this.error = error.message || 'Erreur lors de l\'ajout du commentaire'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    // Changer le statut d'un ticket (pour les admins)
    async updateTicketStatus(id, status) {
      try {
        return await this.updateTicket(id, { statut: status })
      } catch (error) {
        console.error(`Erreur lors du changement de statut du ticket ${id}:`, error)
        throw error
      }
    },
    
    // Réinitialiser les filtres
    resetFilters() {
      this.filters = {
        status: '',
        search: '',
        sort: 'newest'
      }
    }
  }
})
