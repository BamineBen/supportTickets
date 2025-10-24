<template>
  <div class="ticket-platform-container bg-gray-50 min-h-screen">
    <!-- Notification simple -->
    <transition name="fade">
      <div v-if="showNotification" class="notification-toast">
        <div class="notification-content">
          <i class="fas fa-check-circle mr-2 text-green-500"></i>
          {{ notificationMessage }}
        </div>
      </div>
    </transition>
    <!-- En-tête avec titre et actions -->
    <div class="header-section bg-white border-b border-gray-200 shadow-sm">
      <div class="container mx-auto px-4 py-5">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <div>
            <h1 class="text-3xl font-semibold text-gray-800 flex items-center">
              <span class="icon-wrapper bg-blue-100 text-blue-600 p-2 rounded-lg mr-3 inline-flex items-center justify-center">
                <i class="fas fa-ticket-alt"></i>
              </span>
              Gestion des Tickets
            </h1>
            <p class="text-gray-500 mt-1">Gérez vos tickets de support en toute simplicité</p>
          </div>
          
         
        </div>
      </div>
    </div>

    <!-- Alerte d'erreur avec informations détaillées -->
    <div v-if="error" class="container mx-auto px-4 py-3">
      <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-md relative" role="alert">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
          </div>
          <div class="flex-grow">
            <p class="font-bold">Erreur</p>
            <p>{{ error }}</p>
            <p v-if="errorDetails" class="mt-1 text-sm">{{ errorDetails }}</p>
          </div>
          <div class="flex-shrink-0">
            <button class="text-red-500 hover:text-red-700" @click="clearError">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Indicateur de chargement global -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-30 flex justify-center items-center z-50">
      <div class="bg-white p-5 rounded-lg shadow-lg flex flex-col items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-3"></div>
        <p class="text-gray-700">Chargement en cours...</p>
      </div>
    </div>
    
    <!-- Section des filtres et recherche -->
    <div class="filters-section container mx-auto px-4 py-4">

      <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
        <!-- En-tête des filtres -->
        <div class="flex justify-between items-center p-4 border-b border-gray-100">
          <h3 class="text-lg font-medium text-gray-800 flex items-center">
            <i class="fas fa-filter text-blue-500 mr-2"></i> Filtres et recherche
          </h3>
          
          <!-- Filtres actifs -->
          <div class="flex flex-wrap gap-2">
            <span v-if="searchQuery" class="filter-badge bg-blue-50 text-blue-700">
              <i class="fas fa-search text-blue-400 mr-1"></i> {{ searchQuery }}
              <button @click="searchQuery = ''; applyFilters()" class="filter-badge-close">
                <i class="fas fa-times"></i>
              </button>
            </span>
            
            <span v-if="statusFilter" class="filter-badge bg-green-50 text-green-700">
              <i class="fas fa-circle text-xs mr-1" :class="getStatusDotClass(statusFilter)"></i> {{ getStatusLabel(statusFilter) }}
              <button @click="statusFilter = ''; applyFilters()" class="filter-badge-close">
                <i class="fas fa-times"></i>
              </button>
            </span>
            
            <span v-if="sortOrder !== 'newest'" class="filter-badge bg-purple-50 text-purple-700">
              <i class="fas fa-sort text-purple-400 mr-1"></i> {{ getSortLabel(sortOrder) }}
              <button @click="sortOrder = 'newest'; applyFilters()" class="filter-badge-close">
                <i class="fas fa-times"></i>
              </button>
            </span>
          </div>
        </div>
        
        <!-- Formulaire de recherche et filtres -->
        <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50">
          <!-- Barre de recherche -->
          <div class="search-container">
            <div class="relative">
              <input 
                type="text" 
                class="search-input pl-10" 
                placeholder="Rechercher un ticket..." 
                v-model="searchQuery"
                @input="debounceSearch"
              >
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <div v-if="searchQuery" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <button @click="searchQuery = ''; applyFilters()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Filtre par statut -->
          <div class="relative">
            <div class="select-wrapper">
              <select 
                v-model="statusFilter" 
                @change="applyFilters"
                class="select-input pl-10"
              >
                <option value="">Tous les statuts</option>
                <option value="ouvert">Ouverts</option>
                <option value="en cours">En cours</option>
                <option value="fermé">Résolus</option>
                <option value="rejeté">Rejetés</option>
              </select>
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-filter text-gray-400"></i>
              </div>
            </div>
          </div>
          
          <!-- Tri -->
          <div class="relative">
            <div class="select-wrapper">
              <select 
                v-model="sortOrder" 
                @change="applyFilters"
                class="select-input pl-10"
              >
                <option value="newest">Plus récents d'abord</option>
                <option value="oldest">Plus anciens d'abord</option>
                <option value="title_asc">Titre (A-Z)</option>
                <option value="title_desc">Titre (Z-A)</option>
                <option value="deadline">Prochaine échéance</option>
              </select>
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-sort text-gray-400"></i>
              </div>
            </div>
          </div>

          <!-- Nombre d'éléments par page -->
          <div class="relative">
            <div class="select-wrapper">
              <select 
                v-model="perPage" 
                @change="applyFilters"
                class="select-input pl-10"
              >
                <option :value="10">10 par page</option>
                <option :value="25">25 par page</option>
                <option :value="50">50 par page</option>
                <option :value="100">100 par page</option>
                <option :value="200">Tous les tickets</option>
              </select>
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-list-ol text-gray-400"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
        
      <!-- Indicateur de chargement spécifique pour les tickets -->
      <div v-if="ticketStore.loading && !loading" class="flex justify-center py-5">
        <div class="flex flex-col items-center">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500 mb-2"></div>
          <span class="text-gray-500 text-sm">Chargement des tickets...</span>
        </div>
      </div>
      
      <!-- Message si pas de tickets -->
      <div v-if="!loading && !ticketStore.loading && ticketStore.tickets.length === 0" class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="max-w-md mx-auto">
          <div class="icon-wrapper bg-blue-50 mx-auto mb-4 w-16 h-16 rounded-full flex items-center justify-center">
            <i class="fas fa-ticket-alt text-blue-400 text-2xl"></i>
          </div>
          <h3 class="text-xl font-medium text-gray-800 mb-2">Aucun ticket trouvé</h3>
          <p class="text-gray-600 mb-6">Aucun ticket ne correspond à vos critères de recherche actuels.</p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <router-link to="/tickets/new" class="btn-primary">
              <i class="fas fa-plus mr-2"></i> Créer un ticket
            </router-link>
            <button @click="resetFilters" class="btn-secondary">
              <i class="fas fa-sync-alt mr-2"></i> Réinitialiser les filtres
            </button>
          </div>
        </div>
      </div>

      <!-- Liste des tickets avec design inspiré de Mailtrap -->
      <div v-if="!loading && !ticketStore.loading && ticketStore.tickets.length > 0" class="tickets-table-container">
        <table class="tickets-table">
          <thead>
            <tr>
              <th class="pl-4 pr-2 py-3 w-12">#</th>
              <th class="px-2 py-3">Ticket</th>
              <th class="px-2 py-3 w-24 text-center">Statut</th>
              <th class="px-2 py-3 w-32">Date</th>
              <th class="px-2 py-3 w-32">Échéance</th>
              <th class="px-2 py-3 w-40">Demandeur</th>
              <th class="px-2 py-3 w-20 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ticket in ticketStore.tickets" :key="ticket.id" class="ticket-row">
              <!-- ID du ticket -->
              <td class="pl-4 pr-2 py-3 font-mono text-xs text-gray-500">#{{ ticket.id }}</td>
              
              <!-- Titre et description du ticket -->
              <td class="px-2 py-3">
                <router-link :to="`/tickets/${ticket.id}`" class="ticket-title-link">
                  <div class="font-medium text-gray-900 hover:text-blue-600 transition-colors">{{ ticket.titre }}</div>
                  <div class="text-gray-500 text-sm truncate mt-1">{{ truncateText(ticket.description, 70) }}</div>
                </router-link>
              </td>
              
              <!-- Statut du ticket -->
              <td class="px-2 py-3 text-center">
                <span class="status-badge" :class="getStatusBadgeClass(ticket.statut)">
                  <i class="fas fa-circle text-xs mr-1.5" :class="getStatusDotClass(ticket.statut)"></i>
                  {{ getStatusLabel(ticket.statut) }}
                </span>
              </td>
              
              <!-- Date de création -->
              <td class="px-2 py-3 text-sm text-gray-600">
                <div class="flex items-center">
                  <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                  {{ formatDate(ticket.created_at) }}
                </div>
              </td>
              
              <!-- Échéance -->
              <td class="px-2 py-3 text-sm" :class="{'text-red-600': isDeadlineSoon(ticket.deadline), 'text-gray-600': !isDeadlineSoon(ticket.deadline) && ticket.deadline, 'text-gray-400': !ticket.deadline}">
                <div class="flex items-center">
                  <i class="fas fa-clock mr-2" :class="{'text-red-400': isDeadlineSoon(ticket.deadline), 'text-gray-400': !isDeadlineSoon(ticket.deadline)}"></i>
                  <span v-if="ticket.deadline">
                    {{ formatDeadline(ticket.deadline) }}
                    <i v-if="isDeadlineSoon(ticket.deadline)" class="fas fa-exclamation-circle ml-1.5 animate-pulse"></i>
                  </span>
                  <span v-else>Non définie</span>
                </div>
              </td>
              
              <!-- Demandeur -->
              <td class="px-2 py-3">
                <div class="flex items-center">
                  <div class="avatar-circle mr-2">
                    {{ getInitials(ticket.user?.name || 'Inconnu') }}
                  </div>
                  <span class="text-gray-700 text-sm">{{ ticket.user?.name || 'Inconnu' }}</span>
                </div>
              </td>
              
              <!-- Actions -->
              <td class="px-2 py-3 text-center">
                <div class="flex justify-center gap-2">
                  <router-link :to="`/tickets/${ticket.id}`" class="action-button bg-blue-50 text-blue-600 hover:bg-blue-100" title="Voir détails">
                    <i class="fas fa-eye"></i>
                  </router-link>
                  <router-link :to="`/tickets/${ticket.id}/edit`" class="action-button bg-gray-50 text-gray-600 hover:bg-gray-100" title="Modifier">
                    <i class="fas fa-edit"></i>
                  </router-link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

        <!-- Pagination moderne inspirée de Mailtrap -->
      <div v-if="ticketStore.pagination && ticketStore.pagination.last_page > 1" class="pagination-container">
        <div class="flex items-center justify-between bg-white px-4 py-3 rounded-lg shadow-sm mt-4 border border-gray-200">
          <!-- Information sur la pagination -->
          <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Affichage de
                <span class="font-medium">{{ ticketStore.pagination.from || 0 }}</span>
                à
                <span class="font-medium">{{ ticketStore.pagination.to || 0 }}</span>
                sur
                <span class="font-medium">{{ ticketStore.pagination.total }}</span>
                tickets
              </p>
            </div>
            
            <div>
              <nav class="pagination-nav" aria-label="Pagination">
                <!-- Bouton Précédent -->
                <button 
                  @click="changePage(currentPage - 1)" 
                  :disabled="currentPage === 1" 
                  class="pagination-arrow-button" 
                  :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                >
                  <i class="fas fa-chevron-left"></i>
                  <span class="sr-only">Précédent</span>
                </button>
                
                <!-- Première page -->
                <button 
                  v-if="paginationRange[0] > 1" 
                  @click="changePage(1)" 
                  class="pagination-number-button"
                  :class="currentPage === 1 ? 'bg-blue-50 text-blue-600 border-blue-500' : 'hover:bg-gray-50'"
                >
                  1
                </button>
                
                <!-- Ellipse début -->
                <span v-if="paginationRange[0] > 2" class="pagination-ellipsis">...</span>
                
                <!-- Pages -->
                <button 
                  v-for="page in paginationRange" 
                  :key="page" 
                  @click="changePage(page)" 
                  class="pagination-number-button" 
                  :class="currentPage === page ? 'bg-blue-50 text-blue-600 border-blue-500' : 'hover:bg-gray-50'"
                >
                  {{ page }}
                </button>
                
                <!-- Ellipse fin -->
                <span v-if="paginationRange[paginationRange.length - 1] < ticketStore.pagination.last_page - 1" class="pagination-ellipsis">...</span>
                
                <!-- Dernière page -->
                <button 
                  v-if="paginationRange[paginationRange.length - 1] < ticketStore.pagination.last_page" 
                  @click="changePage(ticketStore.pagination.last_page)" 
                  class="pagination-number-button"
                  :class="currentPage === ticketStore.pagination.last_page ? 'bg-blue-50 text-blue-600 border-blue-500' : 'hover:bg-gray-50'"
                >
                  {{ ticketStore.pagination.last_page }}
                </button>
                
                <!-- Bouton Suivant -->
                <button 
                  @click="changePage(currentPage + 1)" 
                  :disabled="currentPage === ticketStore.pagination.last_page" 
                  class="pagination-arrow-button" 
                  :class="currentPage === ticketStore.pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                >
                  <i class="fas fa-chevron-right"></i>
                  <span class="sr-only">Suivant</span>
                </button>
              </nav>
            </div>
          </div>
          
          <!-- Version mobile de la pagination -->
          <div class="flex flex-1 justify-between sm:hidden">
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="pagination-mobile-button"
              :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
            >
              <i class="fas fa-chevron-left mr-1"></i> Précédent
            </button>
            
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">
              Page {{ currentPage }} sur {{ ticketStore.pagination.last_page }}
            </span>
            
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === ticketStore.pagination.last_page"
              class="pagination-mobile-button"
              :class="currentPage === ticketStore.pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
            >
              Suivant <i class="fas fa-chevron-right ml-1"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, watchEffect, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTicketStore } from '../store/ticketStore'
import { useAuthStore } from '../store/authStore'

const ticketStore = useTicketStore()
const authStore = useAuthStore()
const router = useRouter()
const notificationMessage = ref('')
const showNotification = ref(false)

// Variables réactives
const currentPage = ref(1)
const searchQuery = ref('')
const statusFilter = ref('')
const sortOrder = ref('newest')
const searchTimeout = ref(null)
const perPage = ref(50) // Nombre de tickets par page augmenté à 50 par défaut

// Calculer si des filtres sont actifs
const hasActiveFilters = computed(() => {
  return searchQuery.value !== '' || statusFilter.value !== '' || sortOrder.value !== 'newest'
})

// Variables pour la gestion d'erreur et chargement
const loading = ref(false)
const error = ref(null)
const errorDetails = ref(null)
const retryCount = ref(0)
const maxRetries = 3 // Nombre maximum de tentatives en cas d'erreur

// Méthode pour effacer les erreurs
const clearError = () => {
  error.value = null
  errorDetails.value = null
}

// Méthode pour formater les erreurs de manière conviviale
const formatError = (err) => {
  // Erreur réseau / CORS
  if (!err.response) {
    return {
      message: 'Erreur de connexion au serveur. Vérifiez que le backend est lancé.',
      details: err.message
    }
  }
  
  // Erreur d'authentification
  if (err.response.status === 401) {
    return {
      message: 'Session expirée ou non autorisé. Veuillez vous reconnecter.',
      details: 'Erreur 401: ' + (err.response.data?.message || 'Non autorisé')
    }
  }
  
  // Erreur serveur
  if (err.response.status === 500) {
    return {
      message: 'Erreur serveur. L\'administrateur a été notifié.',
      details: 'Erreur 500: ' + (err.response.data?.message || 'Erreur interne du serveur')
    }
  }
  
  // Erreur de validation
  if (err.response.status === 422) {
    return {
      message: 'Données invalides.',
      details: 'Erreur 422: ' + (err.response.data?.message || 'Erreur de validation')
    }
  }
  
  // Erreur générique
  return {
    message: 'Une erreur est survenue.',
    details: `${err.response?.status || ''} ${err.message}`
  }
}

// Charger les tickets au montage du composant
onMounted(async () => {
  clearError()
  loading.value = true
  retryCount.value = 0
  
  try {
    // Vérifier et réinitialiser le token si nécessaire
    const token = localStorage.getItem('token')
    if (!token && authStore.user?.token) {
      localStorage.setItem('token', authStore.user.token)
    }
    
    // Chargement des tickets selon le rôle
    if (authStore.hasRole('admin')) {
      await loadAdminTickets()
    } else {
      await loadUserTickets()
    }
  } catch (err) {
    const formattedError = formatError(err)
    error.value = formattedError.message
    errorDetails.value = formattedError.details
    console.error('Erreur lors du chargement des tickets:', err)
  } finally {
    loading.value = false
  }
})

// Charger les tickets pour un administrateur
async function loadAdminTickets() {
  try {
    // Les administrateurs voient tous les tickets
    await ticketStore.fetchTickets(currentPage.value, {
      status: statusFilter.value,
      search: searchQuery.value,
      sort: sortOrder.value
    })
  } catch (err) {
    // Si on a atteint le nombre max de tentatives, propager l'erreur
    if (retryCount.value >= maxRetries) throw err
    
    // Sinon, incrémenter le compteur et réessayer
    retryCount.value++
    console.warn(`Tentative ${retryCount.value}/${maxRetries} de chargement des tickets admin...`)
    
    // Attente de 1 seconde avant nouvelle tentative
    await new Promise(resolve => setTimeout(resolve, 1000))
    return loadAdminTickets()
  }
}

// Charger les tickets pour un utilisateur standard
async function loadUserTickets() {
  try {
    // D'abord essayer avec mes tickets
    await ticketStore.fetchMyTickets(currentPage.value, {
      status: statusFilter.value,
      search: searchQuery.value,
      sort: sortOrder.value
    })
  } catch (myTicketsError) {
    console.warn('Problème avec /my-tickets, tentative avec /tickets filtrés:', myTicketsError)
    
    try {
      // Fallback: récupérer tous les tickets et filtrer par user_id
      await ticketStore.fetchTickets(currentPage.value, {
        user_id: authStore.user?.id,
        status: statusFilter.value,
        search: searchQuery.value,
        sort: sortOrder.value
      })
    } catch (ticketsError) {
      // Si on a atteint le nombre max de tentatives, propager l'erreur
      if (retryCount.value >= maxRetries) throw ticketsError
      
      // Sinon, incrémenter le compteur et réessayer
      retryCount.value++
      console.warn(`Tentative ${retryCount.value}/${maxRetries} de chargement des tickets utilisateur...`)
      
      // Attente de 1 seconde avant nouvelle tentative
      await new Promise(resolve => setTimeout(resolve, 1000))
      return loadUserTickets()
    }
  }
}

// Fonction pour retarder la recherche pendant la saisie (debounce)
function debounceSearch() {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

// Appliquer les filtres de recherche
async function applyFilters() {
  try {
    loading.value = true
    clearError()
    
    // Réinitialiser la page à 1
    currentPage.value = 1
    
    // Préparer les filtres pour le backend
    const filters = {
      per_page: perPage.value // Ajouter le nombre de tickets par page
    }
    
    // Filtre de recherche - s'assurer qu'il est nettoyé
    if (searchQuery.value && searchQuery.value.trim()) {
      filters.search = searchQuery.value.trim()
    }
    
    // Filtre par statut
    if (statusFilter.value) {
      filters.status = statusFilter.value
    }
    
    // Définir le tri
    filters.sort = sortOrder.value
    
    console.log('Application des filtres:', filters)
    
    // Appliquer les filtres selon le rôle de l'utilisateur
    if (authStore.hasRole('admin')) {
      // Les administrateurs peuvent voir tous les tickets
      await ticketStore.fetchTickets(currentPage.value, filters)
    } else {
      // Les utilisateurs standards ne voient que leurs tickets
      try {
        // Essayer d'abord fetchMyTickets qui est optimisé pour les utilisateurs
        await ticketStore.fetchMyTickets(currentPage.value, filters)
      } catch (err) {
        console.warn('Erreur avec fetchMyTickets, utilisation du fallback:', err)
        // Fallback: ajouter user_id au filtre pour fetchTickets
        filters.user_id = authStore.user?.id
        await ticketStore.fetchTickets(currentPage.value, filters)
      }
    }
    
    // Afficher un résumé des résultats de la recherche
    if (hasActiveFilters.value) {
      const count = ticketStore.tickets.length
      const total = ticketStore.pagination.total || 0
      
      if (count === 0) {
        console.log(`Aucun résultat trouvé (sur ${total} tickets au total)`)
      } else {
        console.log(`${count} ticket(s) trouvé(s) correspondant aux critères`)
      }
    }
  } catch (error) {
    console.error('Erreur lors de l\'application des filtres:', error)
    formatError(error)
  } finally {
    loading.value = false
  }
}

// Réinitialiser les filtres
function resetFilters() {
  searchQuery.value = ''
  statusFilter.value = ''
  sortOrder.value = 'newest'
  currentPage.value = 1
  
  // Nettoyer le store
  ticketStore.resetFilters()
  
  // Effet visuel de chargement pour feedback utilisateur
  loading.value = true
  setTimeout(() => {
    applyFilters()
    loading.value = false
    
    // Afficher une notification de confirmation
    notificationMessage.value = 'Filtres réinitialisés'
    showNotification.value = true
    
    // Masquer la notification après 3 secondes
    setTimeout(() => {
      showNotification.value = false
    }, 3000)
  }, 300)
}

// Changer de page de pagination
async function changePage(page) {
  if (page < 1 || page > ticketStore.pagination.last_page) return
  
  try {
    // Indiquer le chargement
    loading.value = true
    
    // Mettre à jour la page courante
    currentPage.value = page
    
    // Préparer les filtres pour le backend - identique à applyFilters()
    const filters = {
      per_page: perPage.value // Ajouter le nombre de tickets par page
    }
    
    // Conserver les filtres existants
    if (searchQuery.value && searchQuery.value.trim()) {
      filters.search = searchQuery.value.trim()
    }
    
    if (statusFilter.value) {
      filters.status = statusFilter.value
    }
    
    // Définir le tri
    filters.sort = sortOrder.value
    
    console.log(`Changement de page vers ${page} avec filtres:`, filters)
    
    // Appliquer les filtres selon le rôle de l'utilisateur (même logique que dans applyFilters)
    if (authStore.hasRole('admin')) {
      // Les administrateurs peuvent voir tous les tickets
      await ticketStore.fetchTickets(page, filters)
    } else {
      // Les utilisateurs standards ne voient que leurs tickets
      try {
        // Essayer d'abord fetchMyTickets qui est optimisé pour les utilisateurs
        await ticketStore.fetchMyTickets(page, filters)
      } catch (err) {
        console.warn('Erreur avec fetchMyTickets lors du changement de page, utilisation du fallback:', err)
        // Fallback: ajouter user_id au filtre pour fetchTickets
        filters.user_id = authStore.user?.id
        await ticketStore.fetchTickets(page, filters)
      }
    }
  } catch (error) {
    console.error('Erreur lors du changement de page:', error)
    // Ne pas modifier l'état d'erreur général pour ne pas perturber l'expérience utilisateur
  } finally {
    loading.value = false
  }
}

// Calculer la plage de pagination à afficher
const paginationRange = computed(() => {
  const lastPage = ticketStore.pagination.last_page
  const current = currentPage.value
  const delta = 2
  const range = []
  
  const start = Math.max(1, current - delta)
  const end = Math.min(lastPage, current + delta)
  
  for (let i = start; i <= end; i++) {
    range.push(i)
  }
  
  return range
})

// Formater la date
function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

// Formater la deadline
function formatDeadline(deadlineString) {
  const deadline = new Date(deadlineString)
  return deadline.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

// Vérifier si la deadline est proche (moins de 3 jours)
function isDeadlineSoon(deadlineString) {
  const deadline = new Date(deadlineString)
  const today = new Date()
  const diffTime = deadline - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays <= 3 && diffDays >= 0
}

// Tronquer le texte s'il est trop long
function truncateText(text, length) {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Obtenir la classe CSS pour la carte en fonction du statut
function getTicketStatusClass(status) {
  switch (status) {
    case 'ouvert':
      return 'border-primary'
    case 'en cours':
      return 'border-warning'
    case 'fermé':
      return 'border-success'
    default:
      return ''
  }
}

// Obtenir la classe pour le badge de statut
function getStatusBadgeClass(status) {
  if (!status) return 'bg-gray-100 text-gray-800'
  
  switch(status.toLowerCase()) {
    case 'ouvert':
      return 'bg-yellow-100 text-yellow-800'
    case 'en cours':
      return 'bg-blue-100 text-blue-800'
    case 'fermé':
      return 'bg-green-100 text-green-800'
    case 'rejeté':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

// Obtenir la classe pour le point de statut
function getStatusDotClass(status) {
  if (!status) return 'text-gray-400'
  
  switch(status.toLowerCase()) {
    case 'ouvert':
      return 'text-yellow-500'
    case 'en cours':
      return 'text-blue-500'
    case 'fermé':
      return 'text-green-500'
    case 'rejeté':
      return 'text-red-500'
    default:
      return 'text-gray-400'
  }
}

// Obtenir le libellé du statut
function getStatusLabel(status) {
  if (!status) return 'Inconnu'
  
  switch(status.toLowerCase()) {
    case 'ouvert': return 'Ouvert'
    case 'en cours': return 'En cours'
    case 'fermé': return 'Résolu'
    case 'rejeté': return 'Rejeté'
    default: return status
  }
}

// Fonction pour obtenir le libellé du tri sélectionné
function getSortLabel(sort) {
  switch(sort) {
    case 'newest': return 'Plus récents'
    case 'oldest': return 'Plus anciens'
    case 'title_asc': return 'Titre (A-Z)'
    case 'title_desc': return 'Titre (Z-A)'
    case 'deadline': return 'Date limite'
    default: return 'Tri personnalisé'
  }
}

// Fonction pour obtenir les initiales d'un nom
function getInitials(name) {
  if (!name) return 'NA'
  return name
    .split(' ')
    .map(part => part.charAt(0))
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

</script>

<style scoped>
/* Styles généraux et conteneurs */
.ticket-platform-container {
  font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
  color: #333;
  min-height: 100vh;
}

.header-section {
  border-bottom: 1px solid #e5e7eb;
  background-color: white;
}

/* Boutons avec une meilleure apparence Bootstrap */
.btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  background-color: #0d6efd;
  color: white;
  font-weight: 500;
  border-radius: 0.375rem;
  border: 1px solid #0d6efd;
  transition: all 0.2s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  text-decoration: none;
  font-size: 0.875rem;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  background-color: #f8f9fa;
  color: #212529;
  font-weight: 500;
  border-radius: 0.375rem;
  border: 1px solid #f8f9fa;
  transition: all 0.2s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  text-decoration: none;
  font-size: 0.875rem;
}

.btn-secondary:hover {
  background-color: #e2e6ea;
  border-color: #dae0e5;
}

/* Filtres et recherche avec style Bootstrap */
.filter-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  margin-right: 0.5rem;
  margin-bottom: 0.25rem;
  white-space: nowrap;
}

.filter-badge-close {
  margin-left: 0.375rem;
  opacity: 0.7;
  cursor: pointer;
  transition: opacity 0.15s ease-in-out;
}

.filter-badge-close:hover {
  opacity: 1;
}

.search-container {
  width: 100%;
  margin-bottom: 0.5rem;
}

.search-input {
  display: block;
  width: 100%;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 400;
  line-height: 1.5;
  color: #212529;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.search-input:focus {
  color: #212529;
  background-color: #fff;
  border-color: #86b7fe;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.select-wrapper {
  position: relative;
  display: block;
  width: 100%;
  margin-bottom: 0.5rem;
}

.select-wrapper::after {
  content: '';
  position: absolute;
  top: 50%;
  right: 10px;
  transform: translateY(-50%);
  width: 0;
  height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid #6c757d;
  pointer-events: none;
}

.select-input {
  display: block;
  width: 100%;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 400;
  line-height: 1.5;
  color: #212529;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
  appearance: none;
}

.select-input:focus {
  border-color: #86b7fe;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Table des tickets avec style Bootstrap */
.tickets-table-container {
  background: white;
  border-radius: 0.375rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  overflow: hidden;
  margin-bottom: 1rem;
}

.tickets-table {
  width: 100%;
  margin-bottom: 0;
  color: #212529;
  vertical-align: top;
  border-color: #dee2e6;
  border-collapse: collapse;
}

.tickets-table thead {
  background-color: #f8f9fa;
  vertical-align: bottom;
  border-color: inherit;
  border-style: solid;
  border-width: 0;
  border-bottom: 1px solid #dee2e6;
}

.tickets-table th {
  font-weight: 500;
  text-align: left;
  color: #495057;
  font-size: 0.8125rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  padding: 0.75rem;
  border-bottom: 2px solid #dee2e6;
  vertical-align: bottom;
}

.ticket-row {
  border-bottom: 1px solid #dee2e6;
  transition: background-color 0.2s;
}

.ticket-row:hover {
  background-color: rgba(0, 0, 0, 0.075);
}

.ticket-row:last-child {
  border-bottom: none;
}

.tickets-table td {
  padding: 0.75rem;
  vertical-align: middle;
}

.ticket-title-link {
  display: block;
  text-decoration: none;
  padding: 0.25rem 0;
}

/* Badges de statut style Bootstrap */
.status-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.35em 0.65em;
  font-size: 0.75em;
  font-weight: 700;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 0.375rem;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

/* Styles Bootstrap pour l'avatar */
.avatar-circle {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background-color: #0d6efd;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

/* Boutons d'action style Bootstrap */
.action-button {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease-in-out;
  border: 1px solid transparent;
  font-size: 0.875rem;
}

.action-button.bg-blue-50 {
  background-color: #cfe2ff !important;
  color: #084298 !important;
  border-color: #b6d4fe;
}

.action-button.bg-blue-50:hover {
  background-color: #bacbe6 !important;
  color: #084298 !important;
  border-color: #9ec5fe;
}

.action-button.bg-gray-50 {
  background-color: #f8f9fa !important;
  color: #495057 !important;
  border-color: #dae0e5;
}

.action-button.bg-gray-50:hover {
  background-color: #e2e6ea !important;
  color: #212529 !important;
  border-color: #c8cbcf;
}

/* Animation pour les changements de page et filtres */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Style pour l'animation de chargement */
.animate-pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

/* Styles pour la notification toast */
.notification-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  max-width: 300px;
  padding: 12px 16px;
  background-color: white;
  border-left: 4px solid #10b981; /* vert */
  border-radius: 4px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  animation: slide-in 0.3s ease-out forwards;
}

.notification-content {
  display: flex;
  align-items: center;
  color: #374151;
  font-size: 0.875rem;
}

@keyframes slide-in {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

/* Styles de pagination style Bootstrap */
.pagination-container {
  margin-top: 1.5rem;
}

.pagination-nav {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-left: 0;
  list-style: none;
  gap: 0.25rem;
}

.pagination-arrow-button {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.25rem;
  height: 2.25rem;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  background-color: #fff;
  color: #0d6efd;
  transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
  text-decoration: none;
}

.pagination-arrow-button:hover:not(:disabled) {
  z-index: 2;
  color: #0a58ca;
  background-color: #e9ecef;
  border-color: #dee2e6;
}

.pagination-arrow-button:focus {
  z-index: 3;
  color: #0a58ca;
  background-color: #e9ecef;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.pagination-number-button {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2.25rem;
  height: 2.25rem;
  padding: 0.375rem 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  background-color: #fff;
  color: #0d6efd;
  font-size: 0.875rem;
  font-weight: 400;
  transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
  text-decoration: none;
  margin: 0 0.125rem;
}

.pagination-number-button:hover:not(.active) {
  z-index: 2;
  color: #0a58ca;
  background-color: #e9ecef;
  border-color: #dee2e6;
}

.pagination-number-button.active {
  z-index: 3;
  color: #fff;
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.pagination-number-button:focus {
  z-index: 3;
  color: #0a58ca;
  background-color: #e9ecef;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.pagination-ellipsis {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.25rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.pagination-mobile-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.375rem 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  background-color: #fff;
  color: #0d6efd;
  font-size: 0.875rem;
  font-weight: 400;
  transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
  text-decoration: none;
  margin: 0 0.25rem;
}

.pagination-mobile-button:hover:not(:disabled) {
  color: #0a58ca;
  background-color: #e9ecef;
  border-color: #dee2e6;
}

.ticket-card {
  transition: transform 0.3s;
  border-left-width: 5px;
}

.ticket-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.deadline .text-danger {
  font-weight: bold;
}

.filters {
  border-left: 4px solid #0d6efd;
}
</style>
