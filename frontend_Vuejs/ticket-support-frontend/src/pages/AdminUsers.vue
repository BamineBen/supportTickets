<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Gestion des utilisateurs</h1>
    
    <div v-if="loading" class="text-center p-4">
      <p>Chargement des utilisateurs...</p>
    </div>
    
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
    </div>
    
    <table v-if="users.length && !loading" class="min-w-full bg-white border">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-4 py-2">ID</th>
          <th class="border px-4 py-2">Nom</th>
          <th class="border px-4 py-2">Email</th>
          <th class="border px-4 py-2">Rôle</th>
          <th class="border px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td class="border px-4 py-2">{{ user.id }}</td>
          <td class="border px-4 py-2">{{ user.name }}</td>
          <td class="border px-4 py-2">{{ user.email }}</td>
          <td class="border px-4 py-2">
              <select v-model="user.selectedRole" @change="changeRole(user)" class="w-full p-2 border rounded" 
                :disabled="user.id === currentUserId || !isAdmin">
                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
              </select>
              <small v-if="!isAdmin" class="text-sm text-gray-500 block">Seuls les administrateurs peuvent changer les rôles</small>
            </td>
            <td class="border px-4 py-2">
              <button class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded" @click="deleteUser(user)" 
                :disabled="user.id === currentUserId || !isAdmin">
                Supprimer
              </button>
              <small v-if="!isAdmin" class="text-sm text-gray-500 block">Seuls les administrateurs peuvent supprimer</small>
            </td>
        </tr>
      </tbody>
    </table>
    <div v-if="users.length === 0 && !loading" class="alert alert-info">
      Aucun utilisateur à afficher.
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import api from '../api/axios'
  import { useAuthStore } from '../store/authStore'
  
  const users = ref([])
  const roles = ref(['admin', 'user']) // adapte si tu as d'autres rôles
  const loading = ref(false)
  const error = ref(null)
  
  // Variables pour la gestion des utilisateurs
  
  const auth = useAuthStore()
  const currentUserId = auth.user?.id // pour désactiver la modif/suppression de soi-même
  const isAdmin = auth.hasRole('admin') // vérifie si l'utilisateur est administrateur
  
  // Les utilisateurs seront chargés dynamiquement depuis l'API
  // Données de secours en cas d'indisponibilité du serveur
  const mockUsers = [
    { id: 1, name: "Bamine Admin", email: "bamine@gmail.com", roles: [{name: "admin"}] },
    { id: 2, name: "test user", email: "test@gmail.com", roles: [{name: "user"}] },
    { id: 3, name: "Sophie Martin", email: "sophie.martin@example.com", roles: [{name: "admin"}] },
    { id: 4, name: "Thomas Bernard", email: "thomas.bernard@example.com", roles: [{name: "user"}] },
    { id: 5, name: "Emilie Dubois", email: "emilie.dubois@example.com", roles: [{name: "user"}] },
    { id: 6, name: "Lucas Robert", email: "lucas.robert@example.com", roles: [{name: "user"}] },
    { id: 7, name: "Julie Petit", email: "julie.petit@example.com", roles: [{name: "user"}] },
    { id: 8, name: "David Leroy", email: "david.leroy@example.com", roles: [{name: "admin"}] },
    { id: 9, name: "sky", email: "sky@gmail.com", roles: [{name: "user"}] },
    { id: 10, name: "Alice Test", email: "alice.test@example.com", roles: [{name: "user"}] },
    { id: 11, name: "moha", email: "moha@gmail.com", roles: [{name: "user"}] },
    { id: 12, name: "ply", email: "ply@gmail.com", roles: [{name: "user"}] },
  ];

  async function fetchUsers() {
    loading.value = true
    error.value = null

    try {
      console.log('Récupération des utilisateurs depuis l\'API')
      // Route protégée (auth + rôle admin vérifié côté serveur)
      const res = await api.get('/users', { params: { per_page: 100 } })
      console.log('Réponse brute:', res)
      
      let userData = []
      
      // Extraire les données utilisateurs selon la structure de réponse
      if (res.data && res.data.data && Array.isArray(res.data.data)) {
        userData = res.data.data
        console.log('Données extraites de res.data.data')
      } else if (res.data && Array.isArray(res.data)) {
        userData = res.data
        console.log('Données extraites directement de res.data')
      } else if (res.data && res.data.data && res.data.data.data && Array.isArray(res.data.data.data)) {
        // Format potentiel avec pagination
        userData = res.data.data.data
        console.log('Données extraites de res.data.data.data (pagination)')
      }
      
      console.log('Données utilisateurs après extraction:', userData)
      
      users.value = userData.map(u => ({
        ...u,
        selectedRole: u.roles && u.roles.length > 0 ? u.roles[0].name : 'user'
      }))
      
      console.log('Users après traitement:', users.value)
    } catch (e) {
      console.error('Erreur complète:', e)
      console.error('Message d\'erreur:', e.message)
      console.error('Réponse du serveur:', e.response)
      
      // Utiliser les données mock en cas d'erreur de connexion
      console.log('Utilisation des données simulées suite à une erreur de connexion')
      users.value = mockUsers.map(u => ({
        ...u,
        selectedRole: u.roles && u.roles.length > 0 ? u.roles[0].name : 'user'
      }))
      
      error.value = `Affichage des données simulées suite à une erreur: ${e.message}`
    } finally {
      loading.value = false
    }
  }
  
  async function changeRole(user) {
    try {
      console.log(`Changement de rôle pour l'utilisateur ${user.id} vers ${user.selectedRole}`)
      
      await api.patch(`/users/${user.id}/role`, { role: user.selectedRole })
      console.log(`Rôle modifié avec succès pour ${user.name} -> ${user.selectedRole}`)
      
      // Rafraîchir la liste des utilisateurs pour voir les changements
      await fetchUsers()
    } catch (e) {
      console.error('Erreur lors du changement de rôle:', e)
      error.value = `Erreur lors de la modification du rôle: ${e.message}`
    }
  }
  
  async function deleteUser(user) {
    if (!confirm(`Supprimer ${user.name} ?`)) return
    try {
      // Utiliser l'API réelle pour supprimer l'utilisateur
      await api.delete(`/users/${user.id}`)
      console.log(`Utilisateur ${user.name} supprimé avec succès`)
      
      // Rafraîchir la liste des utilisateurs pour voir les changements
      await fetchUsers()
    } catch (e) {
      console.error('Erreur lors de la suppression:', e)
      error.value = `Erreur lors de la suppression: ${e.message}`
    }
  }
  
  // Fonction supprímée - Interface en lecture seule
  
  onMounted(fetchUsers)
  </script>
  