<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-5">
            <h1 class="display-5 fw-bold text-center mb-4">À propos de Support Tickets</h1>
            
            <div class="mb-5">
              <h3 class="h4 mb-3">Notre mission</h3>
              <p>Support Tickets est une plateforme de gestion de tickets développée pour simplifier le processus de support technique. Notre mission est de fournir un système intuitif et efficace permettant aux organisations de toute taille de gérer leurs demandes d'assistance.</p>
              <p>Notre plateforme a été conçue par une équipe d'experts en développement web et en expérience utilisateur, dans le but de créer un outil aussi puissant que facile à utiliser.</p>
            </div>
            
            <div class="mb-5">
              <h3 class="h4 mb-3">Pourquoi nous choisir ?</h3>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="d-flex align-items-start">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                      <i class="bi bi-lightning-charge text-primary fs-3"></i>
                    </div>
                    <div>
                      <h4 class="h5">Simplicité et efficacité</h4>
                      <p>Notre interface utilisateur intuitive permet une prise en main rapide et une utilisation efficace au quotidien.</p>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="d-flex align-items-start">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                      <i class="bi bi-shield-check text-success fs-3"></i>
                    </div>
                    <div>
                      <h4 class="h5">Sécurité des données</h4>
                      <p>Vos données sont cryptées et protégées selon les normes les plus strictes en matière de sécurité informatique.</p>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="d-flex align-items-start">
                    <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                      <i class="bi bi-bar-chart text-info fs-3"></i>
                    </div>
                    <div>
                      <h4 class="h5">Statistiques détaillées</h4>
                      <p>Suivez et analysez la performance de votre équipe grâce à nos tableaux de bord et rapports détaillés.</p>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="d-flex align-items-start">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                      <i class="bi bi-chat-dots text-warning fs-3"></i>
                    </div>
                    <div>
                      <h4 class="h5">Communication fluide</h4>
                      <p>Facilitez les échanges entre les équipes et les clients grâce à notre système de commentaires intégré.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mb-5">
              <h3 class="h4 mb-3">Notre équipe</h3>
              <p>Nous sommes une équipe de développeurs passionnés par la création d'outils qui simplifient le quotidien des entreprises. Chaque membre de notre équipe apporte son expertise unique pour créer un produit de qualité supérieure.</p>
            </div>
            
            <div class="text-center mt-5">
              <h3 class="h4 mb-4">Vous avez des questions ?</h3>
              <router-link to="/contact" class="btn btn-primary btn-lg px-4">
                <i class="bi bi-envelope me-2"></i> Contactez-nous
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Pas de logique nécessaire pour cette page statique
</script>

<style scoped>
/* Animations pour les icônes */
.bi {
  transition: transform 0.3s ease;
}

.d-flex:hover .bi {
  transform: scale(1.2);
}
</style>
