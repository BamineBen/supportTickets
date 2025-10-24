<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Indicateur de chargement -->
    <div v-if="ticketStore.loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
    </div>
    
    <!-- Message d'erreur si le ticket n'est pas trouvé -->
    <div v-else-if="!ticket" class="bg-red-50 border-l-4 border-red-500 text-red-700 p-6 rounded-lg shadow">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold mb-2">Ticket non trouvé!</h2>
          <p>Le ticket que vous recherchez n'existe pas ou a été supprimé.</p>
          <router-link to="/tickets" class="inline-flex items-center mt-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors duration-150">
            <i class="fas fa-arrow-left mr-2"></i> Retour à la liste des tickets
          </router-link>
        </div>
      </div>
    </div>
    
    <!-- Affichage du ticket une fois chargé -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Carte du ticket principal -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <!-- En-tête avec gradient, titre et statut -->
          <div class="relative px-6 py-4 border-b">
            <!-- Background avec gradient transparent selon le statut -->
            <div class="absolute inset-0 opacity-10" :class="{
              'bg-blue-600': ticket.statut === 'ouvert',
              'bg-yellow-600': ticket.statut === 'en cours',
              'bg-green-600': ticket.statut === 'fermé',
            }"></div>
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center relative">
              <h1 class="text-2xl font-bold text-gray-900 mb-2 sm:mb-0">{{ ticket.titre }}</h1>
              <span :class="getStatusBadgeClass(ticket.statut)" class="px-4 py-1 rounded-full text-sm font-semibold inline-flex items-center">
                <span :class="{
                  'bg-blue-600': ticket.statut === 'ouvert',
                  'bg-yellow-600': ticket.statut === 'en cours',
                  'bg-green-600': ticket.statut === 'fermé',
                }" class="h-2 w-2 rounded-full mr-2"></span>
                {{ getStatusLabel(ticket.statut) }}
              </span>
            </div>
          </div>
          
          <!-- Métadonnées ticket -->
          <div class="px-6 py-2 bg-gray-50 border-b">
            <div class="flex flex-wrap items-center text-sm text-gray-500">
              <div class="flex items-center mr-6 mb-2 sm:mb-0">
                <div class="bg-blue-100 rounded-full p-1 mr-2">
                  <i class="fas fa-user text-blue-600"></i>
                </div>
                <span class="mr-1">Créé par:</span>
                <span class="font-medium text-gray-900">{{ ticket.user ? ticket.user.name : 'Utilisateur inconnu' }}</span>
              </div>
              
              <div class="flex items-center mr-6 mb-2 sm:mb-0">
                <div class="bg-purple-100 rounded-full p-1 mr-2">
                  <i class="fas fa-calendar-alt text-purple-600"></i>
                </div>
                <span class="mr-1">Créé le:</span>
                <span class="font-medium text-gray-900">{{ formatDate(ticket.created_at) }}</span>
              </div>
              
              <div v-if="ticket.categorie" class="flex items-center mb-2 sm:mb-0">
                <div class="bg-green-100 rounded-full p-1 mr-2">
                  <i class="fas fa-tag text-green-600"></i>
                </div>
                <span class="mr-1">Catégorie:</span>
                <span class="font-medium text-gray-900">{{ ticket.categorie }}</span>
              </div>
            </div>
          </div>
          
          <!-- Contenu principal -->
          <div class="px-6 py-4">
            <!-- Date limite (si présente) -->
            <div v-if="ticket.deadline" class="mb-6 p-4 rounded-md" :class="isDeadlineSoon(ticket.deadline) ? 'bg-red-50 border-l-4 border-red-500 text-red-700' : 'bg-blue-50 border-l-4 border-blue-500 text-blue-700'">
              <div class="flex">
                <div class="flex-shrink-0">
                  <i :class="isDeadlineSoon(ticket.deadline) ? 'fas fa-exclamation-circle text-red-500' : 'fas fa-calendar-check text-blue-500'" class="mr-2"></i>
                </div>
                <div>
                  <p class="font-bold">Date limite: {{ formatDate(ticket.deadline) }}</p>
                  <p v-if="isDeadlineSoon(ticket.deadline)" class="text-sm">
                    Attention: ce ticket a une échéance proche ou dépassée!
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Description du ticket -->
            <div class="space-y-3">
              <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <i class="fas fa-align-left text-gray-400 mr-2"></i> Description
              </h3>
              <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <p class="description-text text-gray-700 whitespace-pre-line">{{ ticket.description }}</p>
              </div>
            </div>
            
            <!-- Actions sur le ticket (pour les admins) -->
            <div v-if="isAdmin" class="mt-8 border border-gray-200 rounded-lg overflow-hidden">
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                  <i class="fas fa-tools text-gray-500 mr-2"></i> Actions administrateur
                </h3>
              </div>
              <div class="p-4 bg-white">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <button 
                    class="flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                    :class="{ 
                      'bg-yellow-600 text-white hover:bg-yellow-700': ticket.statut !== 'en cours',
                      'bg-gray-100 text-gray-400 cursor-not-allowed': ticket.statut === 'en cours'
                    }"
                    @click="updateStatus('en cours')"
                    :disabled="ticket.statut === 'en cours' || isUpdatingStatus"
                  >
                    <i class="fas fa-hourglass-half mr-2"></i> Marquer en cours
                  </button>
                  
                  <button 
                    class="flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    :class="{ 
                      'bg-green-600 text-white hover:bg-green-700': ticket.statut !== 'fermé',
                      'bg-gray-100 text-gray-400 cursor-not-allowed': ticket.statut === 'fermé'
                    }"
                    @click="updateStatus('fermé')"
                    :disabled="ticket.statut === 'fermé' || isUpdatingStatus"
                  >
                    <i class="fas fa-check-circle mr-2"></i> Fermer le ticket
                  </button>
                  
                  <button 
                    class="flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :class="{ 
                      'bg-blue-600 text-white hover:bg-blue-700': ticket.statut !== 'ouvert',
                      'bg-gray-100 text-gray-400 cursor-not-allowed': ticket.statut === 'ouvert'
                    }"
                    @click="updateStatus('ouvert')"
                    :disabled="ticket.statut === 'ouvert' || isUpdatingStatus"
                  >
                    <i class="fas fa-envelope-open mr-2"></i> Réouvrir
                  </button>
                </div>
                
                <!-- Indicateur de mise à jour en cours -->
                <div v-if="isUpdatingStatus" class="flex items-center justify-center mt-3 text-sm text-gray-500">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Mise à jour du statut...
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Section des commentaires -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
              <i class="fas fa-comments text-gray-500 mr-2"></i> 
              Commentaires 
              <span v-if="ticket.commentaires && ticket.commentaires.length > 0" class="ml-2 bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">
                {{ ticket.commentaires.length }}
              </span>
            </h3>
          </div>
          
          <div class="p-6">
            <!-- Liste des commentaires -->
            <div v-if="ticket.commentaires && ticket.commentaires.length > 0" class="space-y-4 mb-6">
              <div v-for="comment in ticket.commentaires" :key="comment.id" class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                <div class="flex justify-between items-start mb-2">
                  <div class="flex items-center">
                    <!-- Avatar (exemple simple) -->
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-2">
                      {{ comment.user ? comment.user.name.charAt(0).toUpperCase() : 'U' }}
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">{{ comment.user ? comment.user.name : 'Utilisateur' }}</div>
                      <div v-if="comment.user && isUserAdmin(comment.user)" class="text-xs px-1.5 py-0.5 bg-purple-100 text-purple-800 rounded inline-block">
                        Admin
                      </div>
                    </div>
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatDate(comment.created_at) }}
                  </div>
                </div>
                <div class="ml-10 bg-gray-50 p-3 rounded-lg text-gray-700">
                  {{ comment.contenu }}
                </div>
              </div>
            </div>
            
            <!-- Message si aucun commentaire -->
            <div v-else class="flex flex-col items-center justify-center py-6 text-center text-gray-500">
              <i class="fas fa-comment-slash text-gray-300 text-4xl mb-2"></i>
              <p>Aucun commentaire pour ce ticket.</p>
              <p class="text-sm">Soyez le premier à ajouter un commentaire.</p>
            </div>
            
            <!-- Formulaire pour ajouter un commentaire -->
            <div class="mt-6 border-t border-gray-200 pt-6">
              <form @submit.prevent="addComment" class="space-y-4">
                <div>
                  <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Ajouter un commentaire</label>
                  <div class="relative rounded-md shadow-sm">
                    <textarea 
                      class="block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 sm:text-sm transition-colors"
                      :class="{
                        'border-gray-300 focus:border-blue-500 placeholder-gray-400': !errors.contenu,
                        'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500': errors.contenu
                      }"
                      id="comment" 
                      v-model="commentForm.contenu" 
                      rows="3" 
                      placeholder="Votre commentaire..."
                      required
                    ></textarea>
                  </div>
                  <p v-if="errors.contenu" class="mt-1 text-sm text-red-600">
                    {{ errors.contenu[0] }}
                  </p>
                </div>
                
                <div class="flex justify-end">
                  <button 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                    :disabled="isAddingComment"
                    :class="{ 'opacity-75 cursor-not-allowed': isAddingComment }"
                  >
                    <svg v-if="isAddingComment" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <i v-else class="fas fa-paper-plane mr-2"></i>
                    Envoyer
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Sidebar d'information et actions -->
      <div class="space-y-6">
        <!-- Actions sur le ticket -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
              <i class="fas fa-cog text-gray-500 mr-2"></i> Actions
            </h3>
          </div>
          <div class="p-4">
            <div class="space-y-3">
              <router-link to="/tickets" class="flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 transition-colors duration-150">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
              </router-link>
              
              <button 
                v-if="canEditTicket"
                class="flex items-center justify-center w-full px-4 py-2 rounded-md border border-blue-300 bg-blue-50 text-blue-700 shadow-sm hover:bg-blue-100 transition-colors duration-150"
                @click="editTicket"
              >
                <i class="fas fa-edit mr-2"></i> Modifier le ticket
              </button>
              
              <button 
                v-if="canDeleteTicket"
                class="flex items-center justify-center w-full px-4 py-2 rounded-md border border-red-300 bg-red-50 text-red-700 shadow-sm hover:bg-red-100 transition-colors duration-150"
                @click="confirmDelete"
              >
                <i class="fas fa-trash-alt mr-2"></i> Supprimer le ticket
              </button>
            </div>
          </div>
        </div>
        
        <!-- Informations sur le statut -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
              <i class="fas fa-info-circle text-gray-500 mr-2"></i> Informations
            </h3>
          </div>
          <div class="divide-y divide-gray-200">
            <div class="flex justify-between items-center px-4 py-3">
              <span class="text-gray-500">Statut</span>
              <span :class="getStatusBadgeClass(ticket.statut)" class="px-3 py-1 rounded-full text-xs font-semibold inline-flex items-center">
                <span :class="{
                  'bg-blue-600': ticket.statut === 'ouvert',
                  'bg-yellow-600': ticket.statut === 'en cours',
                  'bg-green-600': ticket.statut === 'fermé',
                }" class="h-2 w-2 rounded-full mr-2"></span>
                {{ getStatusLabel(ticket.statut) }}
              </span>
            </div>
            
            <div class="flex justify-between items-center px-4 py-3">
              <span class="text-gray-500">Créé le</span>
              <span class="text-gray-900 font-medium">{{ formatDate(ticket.created_at) }}</span>
            </div>
            
            <div v-if="ticket.updated_at" class="flex justify-between items-center px-4 py-3">
              <span class="text-gray-500">Mis à jour le</span>
              <span class="text-gray-900 font-medium">{{ formatDate(ticket.updated_at) }}</span>
            </div>
            
            <div class="flex justify-between items-center px-4 py-3">
              <span class="text-gray-500">Priorité</span>
              <span 
                class="px-3 py-1 rounded-full text-xs font-semibold" 
                :class="{
                  'bg-green-100 text-green-800': ticket.priorite === 'basse',
                  'bg-blue-100 text-blue-800': ticket.priorite === 'normale' || !ticket.priorite,
                  'bg-orange-100 text-orange-800': ticket.priorite === 'haute',
                  'bg-red-100 text-red-800': ticket.priorite === 'urgente',
                }"
              >
                {{ ticket.priorite ? ticket.priorite.charAt(0).toUpperCase() + ticket.priorite.slice(1) : 'Normale' }}
              </span>
            </div>
            
            <div class="flex justify-between items-center px-4 py-3">
              <span class="text-gray-500">Commentaires</span>
              <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                {{ ticket.commentaires ? ticket.commentaires.length : 0 }}
              </span>
            </div>
          </div>
        </div>
        
        <!-- Autres tickets de l'utilisateur (si admin ou propriétaire) -->
        <div v-if="isAdmin || isOwner" class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
              <i class="fas fa-ticket-alt text-gray-500 mr-2"></i> 
              {{ isOwner ? 'Mes autres tickets' : 'Autres tickets de cet utilisateur' }}
            </h3>
          </div>
          
          <div v-if="relatedTickets.length > 0" class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
            <router-link 
              v-for="relatedTicket in relatedTickets" 
              :key="relatedTicket.id" 
              :to="`/tickets/${relatedTicket.id}`" 
              class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-150"
            >
              <div class="flex justify-between items-center">
                <div class="truncate text-gray-900">{{ truncateText(relatedTicket.titre, 30) }}</div>
                <span 
                  :class="getStatusBadgeClass(relatedTicket.statut)" 
                  class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap"
                >
                  {{ getStatusLabel(relatedTicket.statut) }}
                </span>
              </div>
              <div class="text-xs text-gray-500 mt-1">{{ formatDate(relatedTicket.created_at) }}</div>
            </router-link>
          </div>
          
          <div v-else class="px-4 py-6 text-center text-gray-500">
            <i class="fas fa-search text-gray-300 text-2xl mb-2"></i>
            <p>Aucun autre ticket trouvé.</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal de confirmation de suppression avec Tailwind CSS -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDeleteModal = false"></div>
        
        <!-- Centrage modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <!-- Contenu du modal -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <!-- En-tête -->
          <div class="bg-red-600 px-4 py-3 sm:px-6">
            <div class="flex items-center justify-between">
              <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                Confirmer la suppression
              </h3>
              <button type="button" class="bg-red-600 rounded-md text-white hover:text-gray-200 focus:outline-none" @click="showDeleteModal = false">
                <span class="sr-only">Fermer</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Corps du modal -->
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Supprimer ce ticket
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Êtes-vous sûr de vouloir supprimer ce ticket? Cette action est irréversible et tous les commentaires associés seront également supprimés.
                  </p>
                  <div class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                    <p class="font-medium text-gray-700">Titre: <span class="text-gray-900">{{ ticket.titre }}</span></p>
                    <p class="text-sm text-gray-500 mt-1">Créé le {{ formatDate(ticket.created_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Pied de page avec boutons d'action -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
              @click="deleteTicket"
              :disabled="isDeletingTicket"
              :class="{ 'opacity-75 cursor-not-allowed': isDeletingTicket }"
            >
              <svg v-if="isDeletingTicket" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <i v-else class="fas fa-trash-alt mr-2"></i>
              Supprimer
            </button>
            <button 
              type="button" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
              @click="showDeleteModal = false"
            >
              Annuler
            </button>
          </div>
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
const isAddingComment = ref(false)
const isUpdatingStatus = ref(false)
const isDeletingTicket = ref(false)
const showDeleteModal = ref(false)
const errors = ref({})
const relatedTickets = ref([])

// Formulaire de commentaire
const commentForm = ref({
  contenu: ''
})

// Récupérer les données du ticket depuis l'état du store
const ticket = computed(() => ticketStore.currentTicket)

// Droits et rôles
const isAdmin = computed(() => authStore.hasRole('admin'))
const isOwner = computed(() => {
  return ticket.value && authStore.user && ticket.value.user_id === authStore.user.id
})

// Permissions pour les actions
const canEditTicket = computed(() => isAdmin.value || isOwner.value)
const canDeleteTicket = computed(() => isAdmin.value || isOwner.value)

// Charger le ticket au montage du composant
onMounted(async () => {
  const ticketId = route.params.id
  
  try {
    // Charger les données du ticket
    await ticketStore.fetchTicket(ticketId)
    
    // Si admin ou propriétaire, charger les tickets liés
    if (isAdmin.value || isOwner.value) {
      await fetchRelatedTickets()
    }
  } catch (error) {
    console.error(`Erreur lors du chargement du ticket ${ticketId}:`, error)
  }
})

// Formater la date
function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
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

// Obtenir la classe pour le badge de statut
function getStatusBadgeClass(status) {
  switch (status) {
    case 'ouvert':
      return 'bg-primary'
    case 'en cours':
      return 'bg-warning'
    case 'fermé':
      return 'bg-success'
    default:
      return 'bg-secondary'
  }
}

// Obtenir le libellé du statut
function getStatusLabel(status) {
  switch (status) {
    case 'ouvert':
      return 'Ouvert'
    case 'en cours':
      return 'En cours'
    case 'fermé':
      return 'Fermé'
    default:
      return status
  }
}

// Vérifier si un utilisateur est admin
function isUserAdmin(user) {
  return user && user.roles && user.roles.includes('admin')
}

// Tronquer le texte s'il est trop long
function truncateText(text, length) {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Modifier le ticket
function editTicket() {
  router.push({ path: `/tickets/${ticket.value.id}/edit` })
}

// Confirmation avant suppression
function confirmDelete() {
  showDeleteModal.value = true
}

// Suppression du ticket
async function deleteTicket() {
  isDeletingTicket.value = true
  
  try {
    await ticketStore.deleteTicket(ticket.value.id)
    showDeleteModal.value = false
    
    // Rediriger vers la liste des tickets
    router.push({ path: '/tickets' })
  } catch (error) {
    console.error('Erreur lors de la suppression du ticket:', error)
  } finally {
    isDeletingTicket.value = false
  }
}

// Ajouter un commentaire
async function addComment() {
  if (!commentForm.value.contenu.trim()) {
    errors.value = { contenu: ['Le contenu du commentaire ne peut pas être vide'] }
    return
  }
  
  // Vérifier que nous avons bien l'ID du ticket
  const ticketId = route.params.id
  if (!ticketId) {
    errors.value = { contenu: ['Impossible d\'identifier le ticket'] }
    return
  }
  
  isAddingComment.value = true
  errors.value = {}
  
  try {
    console.log(`Ajout d'un commentaire au ticket ${ticketId}:`, commentForm.value)
    
    // Utiliser l'ID extrait de l'URL plutôt que celui du ticket dans le store
    await ticketStore.addComment(ticketId, commentForm.value)
    
    // Réinitialiser le formulaire
    commentForm.value.contenu = ''
  } catch (error) {
    console.error('Erreur lors de l\'ajout du commentaire:', error)
    
    // Gérer les erreurs de validation
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors
    } else {
      errors.value = { contenu: ['Une erreur est survenue lors de l\'envoi du commentaire'] }
    }
  } finally {
    isAddingComment.value = false
  }
}

// Mettre à jour le statut du ticket (admin uniquement)
async function updateStatus(newStatus) {
  if (!isAdmin.value) return
  
  isUpdatingStatus.value = true
  
  try {
    await ticketStore.updateTicketStatus(ticket.value.id, newStatus)
  } catch (error) {
    console.error('Erreur lors de la mise à jour du statut:', error)
  } finally {
    isUpdatingStatus.value = false
  }
}

// Récupérer les tickets liés (du même utilisateur ou autres)
async function fetchRelatedTickets() {
  if (!ticket.value || !ticket.value.user_id) return
  
  try {
    // Si c'est un admin, on récupère d'autres tickets de cet utilisateur
    // Si c'est le propriétaire, on récupère ses autres tickets
    const userId = ticket.value.user_id
    const response = await api.get(`/tickets?user_id=${userId}&sort=newest&per_page=5`)
    
    // Extraire les données selon la structure de l'API
    let ticketsList = []
    
    if (response.data && response.data.status === 'success' && response.data.data && response.data.data.data) {
      // Structure avec data.data (pagination)
      ticketsList = response.data.data.data
    } else if (response.data && response.data.status === 'success' && response.data.data) {
      // Structure avec data directement
      ticketsList = response.data.data
    } else if (Array.isArray(response.data)) {
      // Si la réponse est directement un tableau
      ticketsList = response.data
    }
    
    // Filtrer pour ne pas inclure le ticket actuel
    const currentTicketId = ticket.value.id
    relatedTickets.value = ticketsList
      .filter(t => t && t.id !== currentTicketId)
      .slice(0, 5)
    
    console.log('Tickets liés chargés:', relatedTickets.value)
  } catch (error) {
    console.error('Erreur lors de la récupération des tickets liés:', error)
    relatedTickets.value = []
  }
}
</script>

<style scoped>
.description-text {
  white-space: pre-line;
}

.comment-item {
  border-left: 3px solid #0d6efd;
  padding-left: 10px;
}

.comment-body {
  white-space: pre-line;
}

.related-tickets a {
  color: inherit;
  text-decoration: none;
}

.related-tickets a:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.admin-actions {
  border-left: 4px solid #ffc107;
}
</style>
