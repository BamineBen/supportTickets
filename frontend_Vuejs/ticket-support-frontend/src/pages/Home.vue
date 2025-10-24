<template>
  <div class="home-container">
    <!-- Héro section avec image de fond -->    
    <section class="hero-section text-white text-center py-5" data-v-933e9cdf>
      <div class="container py-5" data-v-933e9cdf>
        <div class="row justify-content-center" data-v-933e9cdf>
          <div class="col-12 col-xl-10" data-v-933e9cdf>
            <h1 class="display-4 fw-bold mb-4" data-v-933e9cdf>Système de Gestion <span class="d-none d-md-inline" data-v-933e9cdf>de Tickets</span> de Support</h1>
            <p class="lead mb-4" data-v-933e9cdf>Une solution complète pour gérer vos demandes d'assistance technique de manière efficace et organisée.</p>
            
            <div class="mt-5" data-v-933e9cdf>
              <router-link :to="{name: 'dashboard'}" custom v-slot="{navigate}" v-if="isAuthenticated">
                <button @click="navigate" class="btn btn-light btn-lg me-3 px-4 py-2" role="link" data-v-933e9cdf>
                  <i class="bi bi-speedometer2 me-2" data-v-933e9cdf></i> Tableau de bord
                </button>
              </router-link>
              
              <router-link :to="{name: 'create-ticket'}" custom v-slot="{navigate}" v-if="isAuthenticated">
                <button @click="navigate" class="btn btn-outline-light btn-lg px-4 py-2" role="link" data-v-933e9cdf>
                  <i class="bi bi-plus-circle me-2" data-v-933e9cdf></i> Nouveau ticket
                </button>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Section fonctionnalités -->    
    <section class="features-section py-5">
      <div class="container py-4">
        <h2 class="text-center mb-5">Pourquoi utiliser notre système ?</h2>
        
        <div class="row g-4">
          <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle mb-3">
                  <i class="bi bi-ticket-perforated fs-2"></i>
                </div>
                <h5 class="card-title">Gestion simplifiée</h5>
                <p class="card-text">Création, suivi et gestion de tickets en quelques clics, avec une interface intuitive et conviviale.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="feature-icon bg-success bg-opacity-10 text-success rounded-circle mb-3">
                  <i class="bi bi-graph-up fs-2"></i>
                </div>
                <h5 class="card-title">Suivi efficace</h5>
                <p class="card-text">Surveillez l'état de vos demandes avec des statuts clairs et des notifications en temps réel.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="feature-icon bg-info bg-opacity-10 text-info rounded-circle mb-3">
                  <i class="bi bi-people fs-2"></i>
                </div>
                <h5 class="card-title">Collaboration facile</h5>
                <p class="card-text">Communiquez directement avec les techniciens à travers les commentaires des tickets.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Section "Comment ça marche" -->
    <section class="how-it-works-section py-5 bg-light">
      <div class="container py-4">
        <h2 class="text-center mb-5">Comment ça marche ?</h2>
        
        <div class="row g-4">
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="step-number bg-primary text-white rounded-circle mb-3">1</div>
                <h5 class="card-title">Créez un compte</h5>
                <p class="card-text">Inscrivez-vous en quelques secondes pour accéder à toutes les fonctionnalités.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="step-number bg-primary text-white rounded-circle mb-3">2</div>
                <h5 class="card-title">Soumettez un ticket</h5>
                <p class="card-text">Décrivez votre problème en détail et assignez une priorité.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="step-number bg-primary text-white rounded-circle mb-3">3</div>
                <h5 class="card-title">Suivez la progression</h5>
                <p class="card-text">Restez informé de chaque évolution de votre demande.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="step-number bg-primary text-white rounded-circle mb-3">4</div>
                <h5 class="card-title">Problème résolu</h5>
                <p class="card-text">Validez la résolution et laissez un commentaire si nécessaire.</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="text-center mt-5">
          <router-link to="/register" class="btn btn-primary btn-lg px-4 py-2" v-if="!isAuthenticated">
            <i class="bi bi-person-plus me-2"></i> Commencer maintenant
          </router-link>
          <router-link to="/dashboard" class="btn btn-primary btn-lg px-4 py-2" v-if="isAuthenticated">
            <i class="bi bi-speedometer2 me-2"></i> Accéder à mon espace
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../store/authStore';

const authStore = useAuthStore();
const isAuthenticated = computed(() => authStore.isLoggedIn);
</script>

<style scoped>
/* Style pour la section héro */
.hero-section {
  background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
  background-size: cover;
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: url('data:image/svg+xml;charset=utf8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"%3E%3Cpath fill="%23ffffff" fill-opacity="0.1" d="M0,160L48,144C96,128,192,96,288,106.7C384,117,480,171,576,186.7C672,203,768,181,864,154.7C960,128,1056,96,1152,96C1248,96,1344,128,1392,144L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"%3E%3C/path%3E%3C/svg%3E');
  background-size: cover;
  background-position: center;
  opacity: 0.1;
}

/* Style pour les icônes des fonctionnalités */
.feature-icon {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

/* Style pour les étapes numérotées */
.step-number {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
  margin: 0 auto;
}

/* Animations subtiles pour les cartes */
.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
