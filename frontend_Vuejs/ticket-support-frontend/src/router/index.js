import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import Register from '../pages/Register.vue'
import VerifyEmail from '../pages/VerifyEmail.vue'
import AdminUsers from '../pages/AdminUsers.vue'
import AdminDashboard from '../pages/AdminDashboard.vue'
import UserDashboard from '../pages/UserDashboard.vue'
import Profile from '../pages/Profile.vue'
import About from '../pages/About.vue'
import Contact from '../pages/Contact.vue'
import NotFound from '../pages/NotFound.vue'
import GoogleCallback from '../pages/GoogleCallback.vue'

// Import des pages de gestion des tickets
import TicketList from '../pages/TicketList.vue'
import TicketCreate from '../pages/TicketCreate.vue'
import TicketDetail from '../pages/TicketDetail.vue'
import TicketEdit from '../pages/TicketEdit.vue'

import { useAuthStore } from '../store/authStore'

const routes = [
  // Routes publiques
  { path: '/', component: Home, name: 'home' },
  { path: '/login', component: Login, name: 'login' },
  { path: '/register', component: Register, name: 'register' },
  { path: '/verify-email', component: VerifyEmail, name: 'verify-email' },
  { path: '/about', component: About, name: 'about' },
  { path: '/contact', component: Contact, name: 'contact' },
  { path: '/auth/google/callback', component: GoogleCallback, name: 'google-callback' },
  
  // Routes générales protégées par authentification
  { path: '/dashboard', component: UserDashboard, name: 'dashboard', meta: { requiresAuth: true } },
  { path: '/profile', component: Profile, name: 'profile', meta: { requiresAuth: true } },
  
  // Routes pour la gestion des tickets
  { path: '/tickets', component: TicketList, name: 'tickets', meta: { requiresAuth: true } },
  { path: '/tickets/create', component: TicketCreate, name: 'create-ticket', meta: { requiresAuth: true } },
  { path: '/tickets/:id', component: TicketDetail, name: 'ticket-detail', meta: { requiresAuth: true }, props: true },
  { path: '/tickets/:id/edit', component: TicketEdit, name: 'ticket-edit', meta: { requiresAuth: true }, props: true },
  
  // Routes admin
  { path: '/admin/dashboard', component: AdminDashboard, name: 'admin-dashboard', meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/users', component: AdminUsers, name: 'admin-users', meta: { requiresAuth: true, requiresAdmin: true } },
  
  // Route 404 pour les pages non trouvées
  { path: '/:pathMatch(.*)*', component: NotFound, name: 'not-found' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// 🔐 Auth et admin guards
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  const isLoggedIn = auth.token && auth.user

  // Si l'utilisateur est connecté et essaie d'accéder à la page d'accueil ou de login/register
  if (isLoggedIn && (to.path === '/' || to.path === '/login' || to.path === '/register')) {
    console.log("Utilisateur déjà connecté, redirection vers le tableau de bord")
    return next('/dashboard')
  }

  // Si une route nécessite l'authentification et que l'utilisateur n'est pas connecté
  if (to.meta.requiresAuth && !isLoggedIn) {
    console.log("Accès refusé : utilisateur non authentifié")
    return next('/login')
  }
  
  // Protection pour admin
  if (to.meta.requiresAdmin) {
    // Utilise la méthode hasRole du store
    if (!auth.hasRole('admin')) {
      console.log("Accès refusé : privilèges administrateur requis")
      return next('/dashboard')
    } else {
      console.log("Accès autorisé : privilèges administrateur vérifiés")
    }
  }
  
  next()
})

export default router
