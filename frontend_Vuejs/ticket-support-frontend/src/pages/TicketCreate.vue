<template>
  <div class="container py-4">
    <div class="card shadow border-0">
      <!-- En-tête avec titre et icône -->
      <div class="card-header bg-primary text-white py-3">
        <div class="d-flex align-items-center">
          <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3 d-flex justify-content-center align-items-center">
            <i class="bi bi-ticket-perforated fs-4"></i>
          </div>
          <h1 class="fs-4 fw-bold mb-0">Créer un nouveau ticket</h1>
        </div>
      </div>
      
      <!-- Étapes du processus -->
      <div class="bg-light border-bottom py-3 px-3">
        <div class="row align-items-center">
          <div class="col-md-4 d-flex align-items-center">
            <span class="badge rounded-pill bg-primary d-inline-flex align-items-center justify-content-center" style="width: 25px; height: 25px;">1</span>
            <span class="ms-2 fw-medium">Création du ticket</span>
          </div>
          <div class="col-md-4 d-flex align-items-center opacity-50">
            <span class="badge rounded-pill bg-secondary d-inline-flex align-items-center justify-content-center" style="width: 25px; height: 25px;">2</span>
            <span class="ms-2">Traitement</span>
          </div>
          <div class="col-md-4 d-flex align-items-center opacity-50">
            <span class="badge rounded-pill bg-secondary d-inline-flex align-items-center justify-content-center" style="width: 25px; height: 25px;">3</span>
            <span class="ms-2">Résolution</span>
          </div>
        </div>
      </div>
      
      <div class="card-body p-4">
        <!-- Message de succès -->
        <div v-if="successMessage" class="alert alert-success d-flex align-items-center mb-4" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <div>
            <strong>Succès!</strong> {{ successMessage }}
          </div>
        </div>
      
        <!-- Message d'erreur général -->
        <div v-if="errors.general" class="alert alert-danger d-flex align-items-center mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <div>
            <strong>Erreur!</strong> {{ errors.general[0] }}
          </div>
        </div>
      
        <form @submit.prevent="createTicket" class="needs-validation" novalidate>
          <div class="row g-4">
            <!-- Titre du ticket -->
            <div class="col-12">
              <label for="titre" class="form-label">Titre du ticket <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-type-h1"></i></span>
                <input 
                  type="text" 
                  class="form-control" 
                  :class="{'is-invalid': errors.titre}"
                  id="titre" 
                  v-model="ticketForm.titre" 
                  placeholder="Résumé concis du problème"
                  required
                >
                <div v-if="errors.titre" class="invalid-feedback">
                  {{ errors.titre[0] }}
                </div>
              </div>
            </div>
            
            <!-- Description du ticket -->
            <div class="col-12">
              <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
              <textarea 
                class="form-control" 
                :class="{'is-invalid': errors.description}"
                id="description" 
                v-model="ticketForm.description" 
                rows="5" 
                placeholder="Décrivez votre problème ou votre demande de manière détaillée"
                required
              ></textarea>
              <div v-if="errors.description" class="invalid-feedback">
                {{ errors.description[0] }}
              </div>
              <div class="form-text">
                <i class="bi bi-info-circle me-1"></i> Veuillez fournir tous les détails pertinents pour nous aider à traiter votre demande.
              </div>
            </div>
            
            <!-- Section priorité supprimée comme demandé -->
            
            <!-- Date limite (deadline) -->
            <div class="col-md-6">
              <label for="deadline" class="form-label">Date limite (optionnelle)</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                <input 
                  type="date" 
                  class="form-control" 
                  :class="{'is-invalid': errors.deadline}"
                  id="deadline" 
                  v-model="ticketForm.deadline" 
                  :min="minDate"
                >
                <div v-if="errors.deadline" class="invalid-feedback">
                  {{ errors.deadline[0] }}
                </div>
              </div>
              <div class="form-text">
                <i class="bi bi-info-circle me-1"></i> Indiquez une date limite si votre demande a une échéance.
              </div>
            </div>
            
            <!-- Notes supplémentaires (optionnel) -->
            <div class="col-12">
              <label for="notes" class="form-label">Notes supplémentaires (optionnel)</label>
              <textarea 
                class="form-control"
                id="notes" 
                v-model="ticketForm.notes" 
                rows="3" 
                placeholder="Informations supplémentaires qui pourraient être utiles"
              ></textarea>
            </div>
            
            <!-- Boutons d'action -->
            <div class="col-12 d-flex justify-content-end gap-2 border-top pt-4 mt-3">
              <button 
                type="button" 
                class="btn btn-outline-secondary"
                @click="router.back()"
              >
                <i class="bi bi-x-circle me-1"></i> Annuler
              </button>
              <button 
                type="submit" 
                class="btn btn-primary"
                :disabled="submitting"
              >
                <i v-if="submitting" class="bi bi-arrow-repeat spinner-border spinner-border-sm me-1"></i>
                <i v-else class="bi bi-send me-1"></i>
                Soumettre le ticket
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useTicketStore } from '../store/ticketStore'
import { useAuthStore } from '../store/authStore'

const router = useRouter()
const ticketStore = useTicketStore()
const authStore = useAuthStore()

const ticketForm = ref({
  titre: '',
  description: '',
  deadline: '',
  statut: 'ouvert', // Statut par défaut pour un nouveau ticket
  categorie: 'assistance', // Catégorie par défaut
  priorite: 'normale', // Priorité par défaut
  fichiers: [] // Pour les pièces jointes futures
})

const errors = ref({})
const submitting = ref(false)
const successMessage = ref('')

// Obtenir la date minimale (aujourd'hui) pour le sélecteur de date
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

// Fonctions d'assistance pour la priorité
const getPriorityIcon = (priorite) => {
  switch (priorite) {
    case 'basse': return 'fas fa-arrow-down text-green-600'
    case 'normale': return 'fas fa-minus text-blue-600'
    case 'haute': return 'fas fa-arrow-up text-orange-600'
    case 'urgente': return 'fas fa-exclamation text-red-600'
    default: return 'fas fa-minus text-blue-600'
  }
}

const getPriorityActiveClass = (priorite) => {
  switch (priorite) {
    case 'basse': return 'bg-green-50 border-green-500 text-green-700'
    case 'normale': return 'bg-blue-50 border-blue-500 text-blue-700'
    case 'haute': return 'bg-orange-50 border-orange-500 text-orange-700'
    case 'urgente': return 'bg-red-50 border-red-500 text-red-700'
    default: return 'bg-blue-50 border-blue-500 text-blue-700'
  }
}

// Créer un nouveau ticket
async function createTicket() {
  submitting.value = true
  errors.value = {}
  successMessage.value = ''
  
  try {
    // Validation rapide côté client
    const validationErrors = {}
    
    if (!ticketForm.value.titre.trim()) {
      validationErrors.titre = ['Le titre est obligatoire']
    }
    
    if (!ticketForm.value.description.trim()) {
      validationErrors.description = ['La description est obligatoire']
    }
    
    // Si la deadline est définie, valider qu'elle n'est pas dans le passé
    if (ticketForm.value.deadline) {
      const selectedDate = new Date(ticketForm.value.deadline)
      const today = new Date()
      today.setHours(0, 0, 0, 0) // Réinitialiser l'heure pour comparer seulement les dates
      
      if (selectedDate < today) {
        validationErrors.deadline = ['La date limite ne peut pas être dans le passé']
      }
    }
    
    // Vérifier s'il y a des erreurs de validation
    if (Object.keys(validationErrors).length > 0) {
      errors.value = validationErrors
      submitting.value = false
      return
    }
    
    // Préparer la requête en parallèle avec le CSRF
    const csrfPromise = initCsrf()
    
    // Démarrer la redirection en position masquée
    // Cela précharge la page des tickets en arrière-plan
    const preloadTicketsPromise = router.push({ path: '/tickets', replace: false })
    
    // Attendre que le CSRF soit initialisé
    await csrfPromise
    
    // Envoyer la requête de création
    const newTicket = await ticketStore.createTicket(ticketForm.value)
    
    // Afficher un message de succès et rediriger immédiatement
    successMessage.value = 'Votre ticket a été créé avec succès!'
    router.push({ path: `/tickets/${newTicket.id}` })
    
    // Réinitialiser le formulaire (même si on redirige)
    ticketForm.value = {
      titre: '',
      description: '',
      deadline: '',
      statut: 'ouvert'
    }
    
  } catch (error) {
    console.error('Erreur lors de la création du ticket:', error)
    
    // Gérer les erreurs SMTP spécifiques (erreur 500 liée à Mailtrap)
    if (error.response && error.response.status === 500 && 
        error.response.data && error.response.data.message && 
        error.response.data.message.includes('SMTP')) {
      
      // Afficher un message de succès malgré l'erreur SMTP
      successMessage.value = "Votre ticket a été traité ! (Note: notification email non envoyée)"
      
      // Redémarrer une autre page pour forcer le rafraîchissement des données
      setTimeout(() => {
        router.push({ path: '/tickets' })
      }, 1500)
      
      // Réinitialiser le formulaire
      ticketForm.value = {
        titre: '',
        description: '',
        deadline: '',
        statut: 'ouvert'
      }
      
      return
    }
    
    // Gérer les erreurs de validation du serveur
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors
    } else {
      // Erreur générale
      errors.value = {
        general: ['Une erreur est survenue lors de la création du ticket. Veuillez réessayer.']
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

.form-label {
  font-weight: 500;
}

textarea {
  resize: vertical;
}
</style>
