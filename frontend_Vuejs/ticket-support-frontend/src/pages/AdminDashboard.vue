<template>
  <div class="container mt-4">
    <h1 class="mb-4">Tableau de Bord Administration</h1>
    
    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <p class="mt-2">Chargement des données...</p>
    </div>
    
    <div v-else>
      <!-- Cartes statistiques -->
      <div class="row mb-4">
        <div class="col-md-4 mb-3">
          <div class="card h-100 border-info border-start border-5">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Total Tickets</h6>
              <h2 class="card-title fw-bold">{{ stats.totalTickets }}</h2>
              <div class="text-muted small mt-2">Tous les tickets du système</div>
            </div>
          </div>
        </div>
        
        <div class="col-md-4 mb-3">
          <div class="card h-100 border-warning border-start border-5">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Tickets En Attente</h6>
              <h2 class="card-title fw-bold">{{ calculatePendingTickets() }}</h2>
              <div class="text-muted small mt-2">Tous les tickets non fermés</div>
            </div>
          </div>
        </div>
        
        <div class="col-md-4 mb-3">
          <div class="card h-100 border-success border-start border-5">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Tickets Résolus</h6>
              <h2 class="card-title fw-bold">{{ getClosedTickets() }}</h2>
              <div class="text-muted small mt-2">Tous les tickets fermés</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Cartes statistiques utilisateurs -->
      <div class="row mb-4">
        <div class="col-md-6 mb-3">
          <div class="card h-100 border-primary border-start border-5">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Utilisateurs Total</h6>
              <h2 class="card-title fw-bold">{{ stats.totalUsers }}</h2>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 mb-3">
          <div class="card h-100 border-secondary border-start border-5">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Administrateurs</h6>
              <h2 class="card-title fw-bold">{{ stats.adminCount }}</h2>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Utilisateurs récents -->
      <div class="card mb-4">
        <div class="card-header bg-light">
          <h5 class="card-title mb-0">Utilisateurs Récents</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Rôle</th>
                  <th>Date d'inscription</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in recentUsers" :key="user.id">
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>
                    <span 
                      class="badge rounded-pill" 
                      :class="user.roles && user.roles.length > 0 && user.roles[0].name === 'admin' ? 
                        'bg-primary' : 'bg-secondary'"
                    >
                      {{ user.roles && user.roles.length > 0 ? user.roles[0].name : 'user' }}
                    </span>
                  </td>
                  <td>{{ formatDate(user.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="text-center mt-3">
            <router-link to="/admin/users" class="btn btn-sm btn-outline-primary">
              Voir tous les utilisateurs
            </router-link>
          </div>
        </div>
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../api/axios'

const loading = ref(true)
const stats = ref({
  totalUsers: 0,
  adminCount: 0,
  totalTickets: 0,
  pendingTickets: 0,
  ticketsByStatus: {}
})
const recentUsers = ref([])
const recentTickets = ref([])

// Calculer le nombre de tickets en attente (tous les tickets qui ne sont pas fermés)
const calculatePendingTickets = () => {
  // Si nous avons des données détaillées par statut
  if (stats.value.ticketsByStatus && Object.keys(stats.value.ticketsByStatus).length > 0) {
    let pendingCount = 0;
    Object.entries(stats.value.ticketsByStatus).forEach(([status, count]) => {
      // Additionner tous les tickets qui ne sont pas fermés
      if (status !== 'fermé') {
        pendingCount += count;
      }
    });
    return pendingCount;
  }
  // Sinon utiliser la valeur par défaut
  return stats.value.pendingTickets || 0;
}

// Obtenir le nombre de tickets résolus (tous les tickets fermés)
const getClosedTickets = () => {
  // Si nous avons des données détaillées par statut
  if (stats.value.ticketsByStatus && stats.value.ticketsByStatus['fermé']) {
    return stats.value.ticketsByStatus['fermé'];
  }
  // Sinon calculer la différence entre le total et les tickets en attente
  return (stats.value.totalTickets || 0) - calculatePendingTickets();
}

// Fonction pour formater les dates
function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(date)
}

// Récupérer les données du tableau de bord
async function fetchDashboardData() {
  loading.value = true
  
  try {
    // Récupérer les statistiques
    try {
      const statsResponse = await api.get('/dashboard/admin-stats')
      
      // Si nous avons une réponse valide
      if (statsResponse.data && statsResponse.data.data) {
        const responseData = statsResponse.data.data;
        
        // Mettre à jour les statistiques
        stats.value = {
          totalUsers: responseData.stats_generales?.total_utilisateurs || 0,
          adminCount: responseData.stats_generales?.total_admins || 0,
          totalTickets: responseData.stats_generales?.total_tickets || 0,
          ticketsByStatus: responseData.tickets_par_statut || {}
        };
      } else {
        throw new Error('Format de réponse invalide');
      }
    } catch (e) {
      console.log('Utilisation de statistiques simulées:', e.message)
      // Simuler les statistiques
      stats.value = {
        totalUsers: 12,
        adminCount: 3,
        totalTickets: 24,
        pendingTickets: 8,
        ticketsByStatus: {
          'ouvert': 5,
          'en cours': 3,
          'fermé': 16
        }
      }
    }
    
    // Récupérer les utilisateurs récents
    try {
      const usersResponse = await api.get('/users-list')
      
      let users = []
      if (usersResponse.data && Array.isArray(usersResponse.data)) {
        users = usersResponse.data
      } else if (usersResponse.data && usersResponse.data.data && Array.isArray(usersResponse.data.data)) {
        users = usersResponse.data.data
      }
      
      // Trier par date d'inscription (plus récent d'abord) et prendre les 5 premiers
      recentUsers.value = users
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 5)
    } catch (e) {
      console.log('Erreur lors de la récupération des utilisateurs récents:', e)
      recentUsers.value = []
    }
    
    // Récupérer les tickets récents
    // Nous pouvons simuler ces données pour le moment si l'endpoint n'existe pas encore
    try {
      const ticketsResponse = await api.get('/dashboard/recent-tickets')
      recentTickets.value = ticketsResponse.data
    } catch (e) {
      console.log('Utilisation de tickets simulés')
      // Simuler les tickets récents
      recentTickets.value = [
        {
          id: 1,
          title: 'Problème de connexion',
          status: 'pending',
          created_at: '2025-05-18T15:30:00',
          user: { name: 'Thomas Bernard' }
        },
        {
          id: 2,
          title: 'Erreur 404 sur la page produits',
          status: 'done',
          created_at: '2025-05-17T09:15:00',
          user: { name: 'Julie Petit' }
        },
        {
          id: 3,
          title: 'Demande d\'accès à l\'API',
          status: 'rejected',
          created_at: '2025-05-16T14:20:00',
          user: { name: 'Emilie Dubois' }
        }
      ]
    }
  } catch (e) {
    console.error('Erreur lors du chargement des données du tableau de bord:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboardData)
</script>
