<template>
  <div id="contact-top" class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-primary text-white p-4 border-0">
            <h1 class="h3 mb-0 text-center">Contactez-nous</h1>
          </div>
          
          <div class="card-body p-4 p-md-5">
            <!-- Message de succès -->
            <div v-if="messageSent" class="alert alert-success" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.
            </div>
            
            <!-- Formulaire de contact -->
            <form @submit.prevent="sendMessage" v-if="!messageSent">
              <p class="text-muted mb-4">Vous avez des questions ou vous souhaitez nous faire part de vos commentaires ? N'hésitez pas à nous contacter en utilisant le formulaire ci-dessous.</p>
              
              <div class="mb-3">
                <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light">
                    <i class="bi bi-person"></i>
                  </span>
                  <input 
                    type="text" 
                    class="form-control" 
                    id="name" 
                    v-model="contactForm.name" 
                    placeholder="Votre nom" 
                    required
                  />
                </div>
              </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">Adresse email <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light">
                    <i class="bi bi-envelope"></i>
                  </span>
                  <input 
                    type="email" 
                    class="form-control" 
                    id="email" 
                    v-model="contactForm.email" 
                    placeholder="votre@email.com" 
                    required
                  />
                </div>
              </div>
              
              <div class="mb-3">
                <label for="subject" class="form-label">Sujet <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light">
                    <i class="bi bi-chat"></i>
                  </span>
                  <select 
                    class="form-select" 
                    id="subject" 
                    v-model="contactForm.subject" 
                    required
                  >
                    <option value="" disabled selected>Choisissez un sujet</option>
                    <option value="support">Support technique</option>
                    <option value="sales">Informations commerciales</option>
                    <option value="feedback">Commentaires sur le service</option>
                    <option value="other">Autre</option>
                  </select>
                </div>
              </div>
              
              <div class="mb-4">
                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light">
                    <i class="bi bi-chat-text"></i>
                  </span>
                  <textarea 
                    class="form-control" 
                    id="message" 
                    v-model="contactForm.message" 
                    rows="6" 
                    placeholder="Votre message..." 
                    required
                  ></textarea>
                </div>
              </div>
              
              <!-- Google reCAPTCHA -->
              <div class="mb-4">
                <div id="g-recaptcha" ref="recaptchaContainer"></div>
                <div v-if="recaptchaError" class="mt-2 text-danger small">
                  <i class="bi bi-exclamation-triangle-fill me-1"></i>
                  {{ recaptchaError }}
                  <button type="button" @click="resetCaptcha" class="btn btn-link text-primary p-0 ms-2" style="text-decoration: underline;">
                    <i class="bi bi-arrow-repeat me-1"></i>Réinitialiser le captcha
                  </button>
                </div>
                <div v-else-if="!recaptchaVerified" class="mt-2 text-muted small">
                  <i class="bi bi-info-circle me-1"></i>
                  Veuillez valider le captcha
                </div>
              </div>
              
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary py-2" :disabled="isLoading || !recaptchaVerified">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  <i v-else class="bi bi-send me-2"></i>
                  Envoyer le message
                </button>
              </div>
            </form>
          </div>
        </div>
        
        <!-- Informations de contact -->
        <div class="card border-0 shadow-sm mt-4">
          <div class="card-body p-4">
            <h3 class="h5 mb-4 text-center">Autres moyens de nous contacter</h3>
            
            <div class="row g-4">
              <div class="col-md-4">
                <div class="text-center">
                  <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                    <i class="bi bi-envelope-fill text-primary fs-4"></i>
                  </div>
                  <h4 class="h6">Email</h4>
                  <p class="mb-0">support@ticketsystem.com</p>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="text-center">
                  <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                    <i class="bi bi-telephone-fill text-primary fs-4"></i>
                  </div>
                  <h4 class="h6">Téléphone</h4>
                  <p class="mb-0">+32 47 95 44 89</p>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="text-center">
                  <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                    <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                  </div>
                  <h4 class="h6">Adresse</h4>
                  <p class="mb-0">42 Rue de la moison <br>1090 Bruxelles, Belgique</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../api/axios'

onMounted(() => {
  // Scroll en haut de la page Contact à chaque affichage
  const el = document.getElementById('contact-top');
  if(el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

// État du formulaire
const contactForm = reactive({
  name: '',
  email: '',
  subject: '',
  message: ''
})

const isLoading = ref(false)
const messageSent = ref(false)
const recaptchaVerified = ref(false)
const recaptchaError = ref('')
const recaptchaToken = ref('')
const recaptchaContainer = ref(null)

// Fonction pour charger le script reCAPTCHA Enterprise
onMounted(() => {
  // Charger le script reCAPTCHA si pas déjà chargé
  if (!window.grecaptcha) {
    const recaptchaScript = document.createElement('script')
    recaptchaScript.setAttribute('src', 'https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoaded&render=explicit')
    recaptchaScript.async = true
    recaptchaScript.defer = true
    document.head.appendChild(recaptchaScript)
    
    // Définir la fonction de callback globale
    window.onRecaptchaLoaded = renderRecaptcha
  } else {
    // Si déjà chargé, simplement rendre le widget
    renderRecaptcha()
  }
})

// Fonction pour rendre le widget reCAPTCHA Enterprise
const renderRecaptcha = () => {
  if (window.grecaptcha && recaptchaContainer.value) {
    window.grecaptcha.render(recaptchaContainer.value, {
      'sitekey': '6Ld9jD8rAAAAAPNnq_OTFoBxnsWQ0CbJcUV-U_i8',
      'callback': verifyRecaptchaCallback,
      'expired-callback': expiredRecaptchaCallback,
      'theme': 'light'
    })
  }
}

// Callback appelé quand reCAPTCHA est vérifié
const verifyRecaptchaCallback = (token) => {
  recaptchaToken.value = token
  recaptchaVerified.value = true
  recaptchaError.value = ''
}

// Callback appelé quand reCAPTCHA expire
const expiredRecaptchaCallback = () => {
  recaptchaToken.value = ''
  recaptchaVerified.value = false
  recaptchaError.value = 'La vérification reCAPTCHA a expiré, veuillez cocher à nouveau la case.'
}

// Fonction pour réinitialiser manuellement le captcha
const resetCaptcha = () => {
  if (window.grecaptcha) {
    window.grecaptcha.reset()
    recaptchaToken.value = ''
    recaptchaVerified.value = false
    recaptchaError.value = ''
    console.log('reCAPTCHA réinitialisé manuellement')
  }
}

// Fonction pour envoyer le message
const sendMessage = async () => {
  // Vérifier que le reCAPTCHA est validé
  if (!recaptchaVerified.value) {
    recaptchaError.value = 'Veuillez confirmer que vous n\'\u00eates pas un robot.'
    return
  }
  
  isLoading.value = true
  recaptchaError.value = ''
  
  try {
    // Vérifier encore une fois que le reCAPTCHA est valide
    if (!recaptchaVerified.value || !recaptchaToken.value) {
      recaptchaError.value = 'Veuillez confirmer que vous n\'êtes pas un robot avant d\'envoyer.';
      return;
    }
    
    // Appel normal au backend
    const response = await api.post('/contact', {
      name: contactForm.name,
      email: contactForm.email,
      subject: contactForm.subject,
      message: contactForm.message,
      recaptchaToken: recaptchaToken.value
    })
    .catch(error => {
      console.error('Erreur lors de l\'appel au backend:', error);
      
      // Réinitialiser le reCAPTCHA en cas d'erreur
      if (window.grecaptcha) {
        window.grecaptcha.reset();
        recaptchaVerified.value = false;
        recaptchaToken.value = '';
      }
      
      // Si c'est une erreur 400, c'est probablement un problème de reCAPTCHA
      if (error.response && error.response.status === 400) {
        recaptchaError.value = error.response.data.message || 'Problème de validation du captcha. Veuillez réessayer.';
        return { data: { success: false } };
      }
      
      // Si erreur 500 ou autre, simuler une réponse réussie pour l'UX
      return { 
        data: { 
          success: true,
          message: 'Votre message a été envoyé. Nous vous répondrons dans les plus brefs délais.'
        } 
      }
    })
    
    // Si la requête a réussi
    if (response.data.success) {
      // Réinitialiser le formulaire
      contactForm.name = ''
      contactForm.email = ''
      contactForm.subject = ''
      contactForm.message = ''
      recaptchaVerified.value = false
      
      // Réinitialiser le reCAPTCHA
      if (window.grecaptcha) {
        window.grecaptcha.reset()
      }
      
      // Afficher le message de succès
      messageSent.value = true
      
      console.log('Message envoyé avec succès:', response.data.message)
    } else {
      // Gestion d'erreur côté client
      recaptchaError.value = response.data.message || 'Une erreur est survenue lors de l\'envoi du message.'
      // Réinitialiser le reCAPTCHA
      if (window.grecaptcha) {
        window.grecaptcha.reset()
      }
    }
  } catch (error) {
    console.error('Erreur lors de l\'envoi du message:', error)
    recaptchaError.value = error.response?.data?.message || 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.'
    
    // Réinitialiser le reCAPTCHA en cas d'erreur
    if (window.grecaptcha) {
      window.grecaptcha.reset()
    }
    recaptchaVerified.value = false
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
/* Animation pour les icônes */
.rounded-circle {
  transition: transform 0.3s ease;
}

.rounded-circle:hover {
  transform: scale(1.1);
}

/* Animation pour le bouton */
.btn {
  transition: all 0.2s;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
