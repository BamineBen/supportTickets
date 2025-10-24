<template>
  <div class="container-fluid p-0">
    <!-- Header avec fond et informations utilisateur -->
    <div class="bg-primary text-white p-4 mb-4 shadow">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1 class="display-5 fw-bold mb-2">Bienvenue, {{ user?.name }}</h1>
            <p class="text-white-50">{{ isAdmin ? 'Administrateur' : 'Utilisateur' }} | {{ user?.email }}</p>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="badge bg-info me-2 py-2 px-3">
              {{ new Date().toLocaleDateString() }}
            </span>
            <button @click="logout" class="btn btn-danger btn-lg fw-bold">
              <i class="fas fa-sign-out-alt me-2"></i>
              DÉCONNEXION
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="container">
      <!-- Métriques principales avec compteurs -->
      <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-4">
          <div class="card h-100 border-start border-primary border-5">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="fas fa-ticket-alt text-primary fa-2x"></i>
                </div>
                <div>
                  <h6 class="text-muted">Tickets Totaux</h6>
                  <h2 class="fs-3 fw-bold">{{ stats.totalTickets || '...' }}</h2>
                </div>
              </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
              <span class="text-primary">Voir tous</span>
              <router-link to="/tickets" class="text-primary"><i class="fas fa-arrow-right"></i></router-link>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
          <div class="card h-100 border-start border-success border-5">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="fas fa-check-circle text-success fa-2x"></i>
                </div>
                <div>
                  <h6 class="text-muted">Tickets Résolus</h6>
                  <h2 class="fs-3 fw-bold">{{ stats.resolvedTickets || '...' }}</h2>
                </div>
              </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
              <span class="text-success">Voir résolus</span>
              <router-link to="/tickets?statut=fermé" class="text-success"><i class="fas fa-arrow-right"></i></router-link>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
          <div class="card h-100 border-start border-warning border-5">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="fas fa-clock text-warning fa-2x"></i>
                </div>
                <div>
                  <h6 class="text-muted">En Cours</h6>
                  <h2 class="fs-3 fw-bold">{{ stats.inProgressTickets || '...' }}</h2>
                </div>
              </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
              <span class="text-warning">Voir en cours</span>
              <router-link to="/tickets?statut=en cours" class="text-warning"><i class="fas fa-arrow-right"></i></router-link>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
          <div class="card h-100 border-start border-danger border-5">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                </div>
                <div>
                  <h6 class="text-muted">Urgents</h6>
                  <h2 class="fs-3 fw-bold">{{ stats.urgentTickets || '...' }}</h2>
                </div>
              </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
              <span class="text-danger">Voir urgents</span> 
              <router-link to="/tickets?deadline=soon" class="text-danger"><i class="fas fa-arrow-right"></i></router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Accès rapide aux fonctionnalités principales -->
      <div class="mb-4">
        <h2 class="fs-4 fw-bold mb-3">Actions Rapides</h2>
        <div class="row g-3">
          <div class="col-sm-6 col-lg-3">
            <div @click="logout" class="card text-decoration-none h-100 border-start border-danger border-5 cursor-pointer">
              <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                  <i class="fas fa-sign-out-alt text-danger"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Déconnexion</h5>
                  <p class="card-text text-muted small">Quitter l'application</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <router-link to="/tickets/create" class="card text-decoration-none h-100 border-start border-success border-5">
              <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                  <i class="fas fa-plus text-success"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Nouveau Ticket</h5>
                  <p class="card-text text-muted small">Créer un ticket</p>
                </div>
              </div>
            </router-link>
          </div>

          <div class="col-sm-6 col-lg-3">
            <router-link to="/tickets" class="card text-decoration-none h-100 border-start border-primary border-5">
              <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                  <i class="fas fa-search text-primary"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Consulter Tickets</h5>
                  <p class="card-text text-muted small">Voir tous les tickets</p>
                </div>
              </div>
            </router-link>
          </div>

          <div class="col-sm-6 col-lg-3">
            <router-link to="/profile" class="card text-decoration-none h-100 border-start border-info border-5">
              <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                  <i class="fas fa-user-cog text-info"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Profil</h5>
                  <p class="card-text text-muted small">Gérer votre profil</p>
                </div>
              </div>
            </router-link>
          </div>

          <div class="col-sm-6 col-lg-3">
            <router-link to="/tickets?statut=ouvert" class="card text-decoration-none h-100 border-start border-warning border-5">
              <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                  <i class="fas fa-folder-open text-warning"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Tickets Ouverts</h5>
                  <p class="card-text text-muted small">Voir les tickets actifs</p>
                </div>
              </div>
            </router-link>
          </div>

          <div v-if="isAdmin" class="col-sm-6 col-lg-3">
            <div @click="goToAdmin" class="card text-decoration-none h-100 border-start border-secondary border-5 cursor-pointer">
              <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                  <i class="fas fa-users-cog text-secondary"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Administration</h5>
                  <p class="card-text text-muted small">Panneau admin</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tickets récents -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
          <h5 class="card-title mb-0 fw-bold">Tickets Récents</h5>
          <router-link to="/tickets" class="btn btn-sm btn-link text-primary">Voir tous <i class="fas fa-chevron-right ms-1"></i></router-link>
        </div>

        <div class="card-body">
          <div v-if="recentTickets.length" class="table-responsive">
            <table class="table table-hover table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Titre</th>
                  <th scope="col">Statut</th>
                  <th scope="col">Date</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in recentTickets" :key="ticket.id">
                  <td>{{ ticket.id }}</td>
                  <td>{{ ticket.titre }}</td>
                  <td>
                    <span :class="getStatusBadgeBootstrap(ticket.statut)" class="badge">
                      {{ ticket.statut }}
                    </span>
                  </td>
                  <td>{{ formatDate(ticket.created_at) }}</td>
                  <td>
                    <router-link :to="`/tickets/${ticket.id}`" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></router-link>
                    <router-link :to="`/tickets/${ticket.id}/edit`" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-3 text-muted">
              <i class="fas fa-ticket-alt text-secondary fa-3x mb-2"></i>
              <p>Aucun ticket récent à afficher</p>
            </div>
          </div>
        </div>

        <!-- Prochaines échéances -->
        <div class="card mb-4">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">Deadlines à Venir</h5>
          </div>
          <div class="card-body">
            <div v-if="upcomingDeadlines.length" class="d-flex flex-column gap-2">
              <div v-for="ticket in upcomingDeadlines" :key="ticket.id" class="border-start border-warning border-4 ps-3 py-2">
                <div class="d-flex justify-content-between">
                  <h6 class="fw-bold text-truncate">{{ ticket.titre }}</h6>
                  <span :class="{'text-danger fw-bold': isDeadlineSoon(ticket.deadline)}" class="small">
                    {{ formatDeadline(ticket.deadline) }}
                  </span>
                </div>
                <p class="small text-muted text-truncate">{{ ticket.description }}</p>
                <router-link :to="`/tickets/${ticket.id}`" class="btn btn-sm btn-link p-0">Voir</router-link>
              </div>
            </div>
            <div v-else class="text-center py-3 text-muted">
              <i class="fas fa-calendar-check text-secondary fa-3x mb-2"></i>
              <p>Aucune échéance proche</p>
            </div>
          </div>
        </div>

        <!-- Activité récente -->
        <div class="card mb-4">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">Activité Récente</h5>
          </div>
          <div class="card-body">
            <div v-if="recentActivity.length" class="d-flex flex-column gap-3">
              <div v-for="(activity, index) in recentActivity" :key="index" class="d-flex align-items-start">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                  <i :class="getActivityIcon(activity.type)" class="text-primary"></i>
                </div>
                <div>
                  <p class="mb-1 small fw-medium">
                    <span class="fw-bold">{{ activity.user }}</span>
                    <span class="text-muted">{{ activity.action }}</span>
                  </p>
                  <p class="small text-muted mb-0">{{ activity.time }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-3 text-muted">
              <i class="fas fa-history text-secondary fa-3x mb-2"></i>
              <p>Aucune activité récente</p>
            </div>
          </div>
        </div>

        <!-- Liens rapides pour admins -->
        <div v-if="isAdmin" class="card mb-4">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">Outils d'Admin</h5>
          </div>
          <div class="card-body">
            <router-link to="/admin/dashboard" class="btn btn-outline-primary d-flex align-items-center mb-2">
              <i class="fas fa-tachometer-alt me-2"></i>
              <span>Tableau de bord admin</span>
            </router-link>
            
            <router-link to="/admin/users" class="btn btn-outline-info d-flex align-items-center mb-2">
              <i class="fas fa-users me-2"></i>
              <span>Gestion des utilisateurs</span>
            </router-link>
            
            <router-link to="/admin/settings" class="btn btn-outline-success d-flex align-items-center mb-2">
              <i class="fas fa-cog me-2"></i>
              <span>Paramètres système</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../store/authStore'
import { useTicketStore } from '../store/ticketStore'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const ticketStore = useTicketStore()
const router = useRouter()

const user = auth.user
const isAdmin = computed(() => auth.hasRole('admin'))

// Données des statistiques
const stats = ref({
  totalTickets: 0,
  resolvedTickets: 0,
  inProgressTickets: 0,
  urgentTickets: 0
})

// Données des tickets récents et échéances
const recentTickets = ref([])
const upcomingDeadlines = ref([])
const recentActivity = ref([])

// Charger les données au montage du composant
onMounted(async () => {
  // Données de secours pour les statistiques
  const fallbackStats = {
    totalTickets: 10,
    resolvedTickets: 3,
    inProgressTickets: 5,
    urgentTickets: 2
  }
  
  // Données de secours pour les tickets récents
  const fallbackTickets = [
    { id: 1, titre: 'Problème de connexion', description: 'Je n\'arrive pas à me connecter à mon compte', statut: 'ouvert', created_at: new Date().toISOString(), priorite: 'haute' },
    { id: 2, titre: 'Page d\'erreur 404', description: 'J\'obtiens une erreur 404 sur la page produits', statut: 'en cours', created_at: new Date().toISOString(), priorite: 'moyenne' },
    { id: 3, titre: 'Problème de paiement', description: 'Le paiement par carte est refusé', statut: 'en cours', created_at: new Date().toISOString(), priorite: 'haute' }
  ]
  
  try {
    // Essayer de charger les tickets récents
    try {
      await ticketStore.fetchTickets(1, { sort: 'newest' })
      recentTickets.value = ticketStore.tickets.slice(0, 5)
    } catch (ticketError) {
      console.error('Erreur lors du chargement des tickets:', ticketError)
      recentTickets.value = fallbackTickets
    }
    
    // Essayer de filtrer les tickets avec deadline proche
    try {
      upcomingDeadlines.value = ticketStore.tickets
        .filter(ticket => ticket.deadline && new Date(ticket.deadline) > new Date())
        .sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
        .slice(0, 3)
    } catch (deadlineError) {
      console.error('Erreur lors du traitement des échéances:', deadlineError)
      upcomingDeadlines.value = []
    }
    
    // Essayer de calculer les statistiques
    try {
      if (isAdmin.value) {
        stats.value = {
          totalTickets: ticketStore.tickets.length || fallbackStats.totalTickets,
          resolvedTickets: ticketStore.tickets.filter(t => t.statut === 'fermé').length || fallbackStats.resolvedTickets,
          inProgressTickets: ticketStore.tickets.filter(t => t.statut === 'en cours').length || fallbackStats.inProgressTickets,
          urgentTickets: upcomingDeadlines.value.filter(t => isDeadlineSoon(t.deadline)).length || fallbackStats.urgentTickets
        }
      } else {
        // Récupérer uniquement les tickets de l'utilisateur
        try {
          await ticketStore.fetchMyTickets(1)
        } catch (myTicketsError) {
          console.error('Erreur lors du chargement des tickets personnels:', myTicketsError)
        }
        
        stats.value = {
          totalTickets: ticketStore.tickets.length || fallbackStats.totalTickets,
          resolvedTickets: ticketStore.tickets.filter(t => t.statut === 'fermé').length || fallbackStats.resolvedTickets,
          inProgressTickets: ticketStore.tickets.filter(t => t.statut === 'en cours').length || fallbackStats.inProgressTickets,
          urgentTickets: upcomingDeadlines.value.filter(t => isDeadlineSoon(t.deadline)).length || fallbackStats.urgentTickets
        }
      }
    } catch (statsError) {
      console.error('Erreur lors du calcul des statistiques:', statsError)
      stats.value = fallbackStats
    }
    
    // Exemple de données d'activité récente (dans un vrai système, cela viendrait d'une API)
    recentActivity.value = [
      { type: 'comment', user: 'Sophie Dubois', action: 'a commenté sur un ticket', time: 'Il y a 10 minutes' },
      { type: 'create', user: 'Marc Leroy', action: 'a créé un nouveau ticket', time: 'Il y a 2 heures' },
      { type: 'update', user: 'David Martin', action: 'a mis à jour un ticket', time: 'Il y a 5 heures' },
      { type: 'close', user: 'Admin', action: 'a fermé un ticket', time: 'Hier' }
    ]
  } catch (error) {
    console.error('Erreur lors du chargement des données du tableau de bord:', error)
  }
})

// Méthodes utilitaires
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const formatDeadline = (deadline) => {
  if (!deadline) return 'Pas de deadline'
  const deadlineDate = new Date(deadline)
  const today = new Date()
  const diffDays = Math.ceil((deadlineDate - today) / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'En retard!'
  if (diffDays === 0) return 'Aujourd\'hui!'
  if (diffDays === 1) return 'Demain!'
  if (diffDays < 7) return `Dans ${diffDays} jours`
  
  return deadlineDate.toLocaleDateString()
}

const isDeadlineSoon = (deadline) => {
  if (!deadline) return false
  const deadlineDate = new Date(deadline)
  const today = new Date()
  const diffDays = Math.ceil((deadlineDate - today) / (1000 * 60 * 60 * 24))
  return diffDays >= 0 && diffDays <= 3
}

const getStatusBadgeClass = (status) => {
  switch (status.toLowerCase()) {
    case 'ouvert': return 'bg-blue-100 text-blue-800'
    case 'en cours': return 'bg-yellow-100 text-yellow-800'
    case 'fermé': return 'bg-green-100 text-green-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getStatusBadgeBootstrap = (status) => {
  switch (status.toLowerCase()) {
    case 'ouvert': return 'bg-primary'
    case 'en cours': return 'bg-warning'
    case 'fermé': return 'bg-success'
    default: return 'bg-secondary'
  }
}

const getActivityIcon = (type) => {
  switch (type) {
    case 'comment': return 'fas fa-comment'
    case 'create': return 'fas fa-plus-circle'
    case 'update': return 'fas fa-edit'
    case 'close': return 'fas fa-check-circle'
    default: return 'fas fa-bell'
  }
}

const logout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>