<template>
  <div class="container mt-4">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h2 class="mb-0">Modifier le ticket</h2>
      </div>
      
      <!-- Indicateur de chargement -->
      <div v-if="ticketStore.loading" class="card-body text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
      </div>
      
      <div v-else-if="ticket" class="card-body">
        <form @submit.prevent="updateTicket">
          <!-- Titre du ticket -->
          <div class="mb-3">
            <label for="titre" class="form-label">Titre du ticket *</label>
            <input 
              type="text" 
              class="form-control" 
              id="titre" 
              v-model="ticketForm.titre" 
              placeholder="Titre du ticket"
              :class="{ 'is-invalid': errors.titre }"
              required
            >
            <div v-if="errors.titre" class="invalid-feedback">
              {{ errors.titre[0] }}
            </div>
          </div>
          
          <!-- Description du ticket -->
          <div class="mb-3">
            <label for="description" class="form-label">Description *</label>
            <textarea 
              class="form-control" 
              id="description" 
              v-model="ticketForm.description" 
              rows="6" 
              placeholder="Description détaillée du problème"
              :class="{ 'is-invalid': errors.description }"
              required
            ></textarea>
            <div v-if="errors.description" class="invalid-feedback">
              {{ errors.description[0] }}
            </div>
          </div>
          
          <!-- Date limite (deadline) -->
          <div class="mb-3">
            <label for="deadline" class="form-label">Date limite (optionnelle)</label>
            <input 
              type="date" 
              class="form-control" 
              id="deadline" 
              v-model="ticketForm.deadline" 
              :min="minDate"
              :class="{ 'is-invalid': errors.deadline }"
            >
            <div v-if="errors.deadline" class="invalid-feedback">
              {{ errors.deadline[0] }}
            </div>
            <small class="form-text text-muted">
              Laissez vide si votre demande n'a pas d'échéance particulière.
            </small>
          </div>
          
          <!-- Statut du ticket (admin uniquement) -->
          <div v-if="isAdmin" class="mb-3">
            <label for="statut" class="form-label">Statut</label>
            <select 
              class="form-select" 
              id="statut" 
              v-model="ticketForm.statut"
              :class="{ 'is-invalid': errors.statut }"
            >
              <option value="ouvert">Ouvert</option>
              <option value="en cours">En cours</option>
              <option value="fermé">Fermé</option>
            </select>
            <div v-if="errors.statut" class="invalid-feedback">
              {{ errors.statut[0] }}
            </div>
          </div>
          
          <!-- Message général d'erreur -->
          <div v-if="errors.general" class="alert alert-danger" role="alert">
            {{ errors.general[0] }}
          </div>
          
          <!-- Message de succès -->
          <div v-if="successMessage" class="alert alert-success" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ successMessage }}
          </div>
          
          <!-- Boutons d'action -->
          <div class="d-flex justify-content-between mt-4">
            <router-link :to="`/tickets/${ticketId}`" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left"></i> Annuler
            </router-link>
            <button 
              type="submit" 
              class="btn btn-primary" 
              :disabled="submitting"
            >
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="bi bi-save me-1"></i>
              Enregistrer les modifications
            </button>
          </div>
        </form>
      </div>
      
      <!-- Message d'erreur si le ticket n'est pas trouvé -->
      <div v-else class="card-body">
        <div class="alert alert-danger">
          <h4 class="alert-heading">Ticket non trouvé!</h4>
          <p>Le ticket que vous essayez de modifier n'existe pas ou vous n'avez pas les autorisations nécessaires pour y accéder.</p>
          <hr>
          <router-link to="/tickets" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Retour à la liste des tickets
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTicketStore } from '../store/ticketStore'
import { useAuthStore } from '../store/authStore'
import api from '../api/axios'

const route = useRoute()
const router = useRouter()
const ticketStore = useTicketStore()
const authStore = useAuthStore()

// Variables réactives
const ticketId = ref(route.params.id)
const errors = ref({})
const submitting = ref(false)
const successMessage = ref('')
const ticketForm = ref({
  titre: '',
  description: '',
  deadline: '',
  statut: 'ouvert'
})

// Ticket actuel depuis le store
const ticket = computed(() => ticketStore.currentTicket)

// Vérifier si l'utilisateur est admin
const isAdmin = computed(() => authStore.hasRole('admin'))

// Vérifier si l'utilisateur peut modifier ce ticket (admin ou propriétaire)
const canEditTicket = computed(() => {
  if (isAdmin.value) return true
  return ticket.value && authStore.user && ticket.value.user_id === authStore.user.id
})

// Date minimale pour le sélecteur de date (aujourd'hui)
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

// Charger les données du ticket au montage du composant
onMounted(async () => {
  try {
    // Récupérer les données du ticket
    await ticketStore.fetchTicket(ticketId.value)
    
    // Remplir le formulaire avec les données du ticket
    if (ticket.value) {
      ticketForm.value = {
        titre: ticket.value.titre || '',
        description: ticket.value.description || '',
        statut: ticket.value.statut || 'ouvert',
        deadline: ticket.value.deadline ? formatDateForInput(new Date(ticket.value.deadline)) : ''
      }
      
      // Vérifier les autorisations
      if (!canEditTicket.value) {
        // Rediriger si l'utilisateur n'a pas les droits
        router.push({ path: '/tickets' })
      }
    }
  } catch (error) {
    console.error(`Erreur lors du chargement du ticket ${ticketId.value}:`, error)
  }
})

// Formater une date pour l'input date HTML (YYYY-MM-DD)
function formatDateForInput(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Mettre à jour le ticket
async function updateTicket() {
  // Valider les données côté client
  if (!ticketForm.value.titre.trim() || !ticketForm.value.description.trim()) {
    errors.value = {
      general: ['Veuillez remplir tous les champs obligatoires.']
    }
    return
  }
  
  submitting.value = true
  errors.value = {}
  successMessage.value = ''
  
  try {
    // Initialiser le CSRF token
    await initCsrf()
    
    // Préparation des données pour la mise à jour
    const updateData = {
      titre: ticketForm.value.titre,
      description: ticketForm.value.description,
      deadline: ticketForm.value.deadline || null // null si vide
    }
    
    // Ajouter le statut seulement si l'utilisateur est admin
    if (isAdmin.value) {
      updateData.statut = ticketForm.value.statut
    }
    
    // Envoyer la requête de mise à jour
    await ticketStore.updateTicket(ticketId.value, updateData)
    
    // Afficher le message de succès
    successMessage.value = 'Le ticket a été mis à jour avec succès!'
    
    // Rediriger vers la page de détail après un délai
    setTimeout(() => {
      router.push({ path: `/tickets/${ticketId.value}` })
    }, 2000)
  } catch (error) {
    console.error('Erreur lors de la mise à jour du ticket:', error)
    
    // Traiter les erreurs de validation
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors
    } else {
      errors.value = {
        general: ['Une erreur est survenue lors de la mise à jour du ticket.']
      }
    }
  } finally {
    submitting.value = false
  }
}

// Initialiser le CSRF token pour Laravel Sanctum
async function initCsrf() {
  try {
    const response = await fetch('http://localhost:8000/sanctum/csrf-cookie', {
      credentials: 'include',
    })
    return response.ok
  } catch (error) {
    console.error('Erreur lors de la récupération du CSRF token:', error)
    return false
  }
}
</script>

<style scoped>
.card {
  border: none;
}

.card-header {
  border-radius: 0.375rem 0.375rem 0 0;
}

textarea {
  resize: vertical;
}
</style>
