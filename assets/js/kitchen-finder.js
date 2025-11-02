/**
 * Kitchen Finder Widget - Wizard a 4 step
 * Mobile-first, lead-oriented
 */

(function() {
    'use strict';

    const KitchenFinder = {
        currentStep: 1,
        totalSteps: 4,
        data: {
            space_a: 0,
            space_b: 0,
            small_kitchen: false,
            layout: '',
            stile: '',
            budget: ''
        },

        init: function() {
            const widget = document.getElementById('kitchen-finder-widget');
            if (!widget) return;

            this.widget = widget;
            this.bindEvents();
            this.showStep(1);
        },

        bindEvents: function() {
            // Auto-calcola totale spazio quando cambiano gli input
            const spaceInputs = document.querySelectorAll('#kf-space-a, #kf-space-b');
            spaceInputs.forEach(input => {
                input.addEventListener('input', () => {
                    this.updateSmallKitchenToggle();
                });
            });

            // Toggle cucina piccola
            const smallKitchenToggle = document.getElementById('kf-small-kitchen');
            if (smallKitchenToggle) {
                smallKitchenToggle.addEventListener('change', (e) => {
                    this.data.small_kitchen = e.target.checked;
                });
            }

            // Keyboard navigation - solo se il widget ha focus
            document.addEventListener('keydown', (e) => {
                const widget = document.getElementById('kitchen-finder-widget');
                if (widget && widget.contains(document.activeElement) && e.key === 'Escape' && this.currentStep > 1) {
                    e.preventDefault();
                    this.prevStep();
                }
            });
        },

        updateSmallKitchenToggle: function() {
            const spaceA = parseInt(document.getElementById('kf-space-a')?.value || 0);
            const spaceB = parseInt(document.getElementById('kf-space-b')?.value || 0);
            const total = spaceA + spaceB;
            
            this.data.space_a = spaceA;
            this.data.space_b = spaceB;

            // Auto-check se totale < 240
            const toggle = document.getElementById('kf-small-kitchen');
            if (toggle && total > 0) {
                toggle.checked = total < 240;
                this.data.small_kitchen = toggle.checked;
            }
        },

        selectOption: function(type, value) {
            // Validazione tipo
            if (!type || typeof type !== 'string' || !['layout', 'stile', 'budget'].includes(type)) {
                console.error('Invalid option type:', type);
                return;
            }
            
            // Validazione valore
            if (!value || typeof value !== 'string') {
                console.error('Invalid option value:', value);
                return;
            }
            
            this.data[type] = value;

            // Aggiorna UI: deseleziona altri, evidenzia selezionato
            const step = type === 'layout' ? 2 : type === 'stile' ? 3 : 4;
            const cards = document.querySelectorAll(`#kf-step-${step} .kf-option-card, #kf-step-${step} .kf-budget-card`);
            cards.forEach(card => {
                const isSelected = card.dataset.value === value;
                card.setAttribute('aria-pressed', isSelected);
                card.classList.toggle('kf-selected', isSelected);
            });
        },

        nextStep: function() {
            // Validazione step corrente
            if (!this.validateCurrentStep()) {
                return;
            }

            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.showStep(this.currentStep);
            }
        },

        prevStep: function() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.showStep(this.currentStep);
            }
        },

        showStep: function(step) {
            // Nascondi tutti gli step
            const allSteps = document.querySelectorAll('.kf-step');
            allSteps.forEach(s => {
                s.classList.add('kf-hidden');
                s.setAttribute('aria-hidden', 'true');
            });

            // Mostra step corrente
            const currentStepEl = document.getElementById(`kf-step-${step}`);
            if (currentStepEl) {
                currentStepEl.classList.remove('kf-hidden');
                currentStepEl.setAttribute('aria-hidden', 'false');
                // Focus management per accessibilità
                const firstInput = currentStepEl.querySelector('input, button, .kf-option-card, .kf-budget-card');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 100);
                }
            }

            // Aggiorna progress bar
            this.updateProgress(step);

            // Scroll smooth al widget
            const widget = document.getElementById('kitchen-finder-widget');
            if (widget) {
                widget.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        },

        updateProgress: function(step) {
            const progressEl = document.querySelector('.kf-progress-bar');
            const currentStepEl = document.getElementById('kf-current-step');
            
            if (progressEl) {
                const percentage = (step / this.totalSteps) * 100;
                progressEl.style.width = percentage + '%';
            }
            
            if (currentStepEl) {
                currentStepEl.textContent = step;
            }

            // ARIA
            const progressbar = document.querySelector('.kf-progress');
            if (progressbar) {
                progressbar.setAttribute('aria-valuenow', step);
            }
        },

        validateCurrentStep: function() {
            if (this.currentStep === 1) {
                const spaceA = parseInt(document.getElementById('kf-space-a')?.value || 0);
                const spaceB = parseInt(document.getElementById('kf-space-b')?.value || 0);
                
                if (spaceA <= 0 || spaceB <= 0) {
                    alert('Inserisci le dimensioni dello spazio disponibile.');
                    return false;
                }
            } else if (this.currentStep === 2) {
                if (!this.data.layout) {
                    alert('Seleziona un layout.');
                    return false;
                }
            } else if (this.currentStep === 3) {
                if (!this.data.stile) {
                    alert('Seleziona uno stile.');
                    return false;
                }
            } else if (this.currentStep === 4) {
                if (!this.data.budget) {
                    alert('Seleziona una fascia di budget.');
                    return false;
                }
            }
            return true;
        },

        calculateResult: function() {
            if (!this.validateCurrentStep()) {
                return;
            }

            // Mostra loader
            const loader = document.getElementById('kf-loader');
            const widget = document.getElementById('kitchen-finder-widget');
            if (loader) loader.classList.remove('kf-hidden');
            if (widget) widget.classList.add('kf-loading');

            // Ottieni URL e nonce dal widget
            const ajaxUrl = widget?.dataset.ajaxUrl || window.kitchenFinderData?.ajaxUrl || '';
            const nonce = widget?.dataset.nonce || window.kitchenFinderData?.nonce || '';

            // Chiamata AJAX
            const formData = new FormData();
            formData.append('action', 'kitchen_finder_calculate');
            formData.append('nonce', nonce);
            formData.append('space_a', this.data.space_a);
            formData.append('space_b', this.data.space_b);
            formData.append('small_kitchen', this.data.small_kitchen ? 'true' : 'false');
            formData.append('layout', this.data.layout);
            formData.append('stile', this.data.stile);
            formData.append('budget', this.data.budget);

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    this.displayResult(result.data);
                } else {
                    alert(result.data?.message || 'Errore nel calcolo. Riprova più tardi.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore di connessione. Riprova più tardi.');
            })
            .finally(() => {
                if (loader) loader.classList.add('kf-hidden');
                if (widget) widget.classList.remove('kf-loading');
            });

            // Track event
            this.track('kitchen_finder_start', { step: 'result' });
        },

        displayResult: function(data) {
            const resultEl = document.getElementById('kf-result');
            const contentEl = document.getElementById('kf-result-content');
            
            if (!resultEl || !contentEl) return;
            
            // Validazione dati
            if (!data || typeof data !== 'object') {
                console.error('Invalid data received');
                alert('Errore nei dati ricevuti. Riprova più tardi.');
                return;
            }

            // Costruisci HTML risultato
            let html = `
                <div class="kf-result-header">
                    <h2 id="kf-result-label" class="kf-result-title">Il sistema perfetto per te</h2>
                    <p class="kf-result-subtitle">Basato sulle tue preferenze, ti consigliamo:</p>
                </div>

                <div class="kf-result-main" id="kitchen-finder-result">
                    <div class="kf-system-primary">
                        <div class="kf-system-badge kf-badge-primary">Consigliato</div>
                        <h3 class="kf-system-name">${this.escapeHtml(data.primary_system)}</h3>
                        <p class="kf-system-price">Prezzo indicativo: <strong>${this.escapeHtml(data.price_range)}</strong></p>
                        <p class="kf-system-note">* Prezzo esclusi elettrodomestici e montaggio</p>
                    </div>

                    <div class="kf-system-alternative">
                        <div class="kf-system-badge kf-badge-alternative">Alternativa</div>
                        <h4 class="kf-system-name">${this.escapeHtml(data.alternative_system)}</h4>
                        <p class="kf-system-desc">Una valida alternativa se preferisci un approccio diverso</p>
                    </div>
                </div>

                <div class="kf-checklist">
                    <h3 class="kf-checklist-title">Checklist misure</h3>
                    <ul class="kf-checklist-list">
                        ${(data.checklist || []).map(item => `<li>${this.escapeHtml(item)}</li>`).join('')}
                    </ul>
                </div>

                <div class="kf-internal-links">
                    <h3 class="kf-links-title">Approfondisci</h3>
                    <div class="kf-links-grid">
                        ${(data.internal_links || []).map(link => `
                            <a href="${this.escapeHtml(link.url)}" class="kf-link-card" target="_blank" rel="noopener">
                                <h4>${this.escapeHtml(link.title)}</h4>
                                <p>${this.escapeHtml(link.description)}</p>
                            </a>
                        `).join('')}
                    </div>
                </div>

                <div class="kf-faq-section">
                    <h3 class="kf-faq-title">Domande frequenti</h3>
                    <div class="kf-faq-list">
                        ${(data.faqs || []).map((faq, idx) => `
                            <details class="kf-faq-item">
                                <summary class="kf-faq-question">${this.escapeHtml(faq.question)}</summary>
                                <div class="kf-faq-answer">${this.escapeHtml(faq.answer)}</div>
                            </details>
                        `).join('')}
                    </div>
                </div>

                <div class="kf-cta-lead">
                    <h3 class="kf-cta-title">Ricevi il preventivo completo in PDF</h3>
                    <p class="kf-cta-desc">Ti invieremo via email la checklist delle misure e una lista dettagliata dei moduli consigliati per il sistema ${this.escapeHtml(data.primary_system)}.</p>
                    <form class="kf-lead-form" id="kf-lead-form">
                        <div class="kf-form-group">
                            <label for="kf-lead-email">La tua email</label>
                            <input type="email" id="kf-lead-email" class="kf-input" required placeholder="nome@esempio.com" aria-required="true">
                        </div>
                        <div class="kf-form-group">
                            <label class="kf-checkbox-label">
                                <input type="checkbox" id="kf-montage" name="montage">
                                <span>Desidero informazioni sul servizio di montaggio</span>
                            </label>
                        </div>
                        <button type="submit" class="kf-btn kf-btn-primary kf-btn-large">Invia PDF</button>
                    </form>
                </div>
            `;

            contentEl.innerHTML = html;

            // Bind evento form
            const leadForm = document.getElementById('kf-lead-form');
            if (leadForm) {
                leadForm.addEventListener('submit', (e) => {
                    this.submitLead(e);
                });
            }

            // Aggiungi schema JSON-LD
            this.addSchemaMarkup(data);

            // Mostra risultato
            const allSteps = document.querySelectorAll('.kf-step');
            allSteps.forEach(s => {
                s.classList.add('kf-hidden');
                s.setAttribute('aria-hidden', 'true');
            });
            resultEl.classList.remove('kf-hidden');
            resultEl.setAttribute('aria-hidden', 'false');

            // Scroll al risultato
            resultEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Track
            this.track('kitchen_finder_result_viewed', {
                primary_system: data.primary_system,
                layout: data.layout,
                budget: data.budget
            });
        },

        addSchemaMarkup: function(data) {
            // Rimuovi schema esistente se presente
            const existingSchema = document.getElementById('kitchen-finder-schema');
            if (existingSchema) {
                existingSchema.remove();
            }

            // Validazione dati prima di creare schema
            if (!data || !data.primary_system || !data.layout || !data.stile || !data.budget) {
                console.error('Invalid data for schema markup');
                return;
            }

            // Crea nuovo schema
            const schema = {
                "@context": "https://schema.org",
                "@type": "Service",
                "name": `Sistema cucina ${data.primary_system} - Consulenza e preventivo`,
                "description": `Sistema cucina IKEA ${data.primary_system} consigliato per spazi ${data.layout}, stile ${data.stile}, budget ${data.budget}`,
                "provider": {
                    "@type": "Organization",
                    "name": "TotalDesign.it"
                }
            };

            const script = document.createElement('script');
            script.id = 'kitchen-finder-schema';
            script.type = 'application/ld+json';
            script.textContent = JSON.stringify(schema);
            document.head.appendChild(script);
        },

        submitLead: function(event) {
            event.preventDefault();

            const email = document.getElementById('kf-lead-email').value;
            const montage = document.getElementById('kf-montage')?.checked || false;
            const widget = document.getElementById('kitchen-finder-widget');
            const ajaxUrl = widget?.dataset.ajaxUrl || window.kitchenFinderData?.ajaxUrl || '';
            const nonce = widget?.dataset.nonce || window.kitchenFinderData?.nonce || '';
            
            const formData = new FormData();
            formData.append('action', 'kitchen_finder_pdf');
            formData.append('nonce', nonce);
            formData.append('email', email);
            formData.append('montage', montage ? 'true' : 'false');
            formData.append('result_data', JSON.stringify(this.data));

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Invio in corso...';

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    event.target.innerHTML = `
                        <div class="kf-success-message">
                            <svg class="kf-success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p><strong>Grazie!</strong></p>
                            <p>${this.escapeHtml(result.data.message)}</p>
                        </div>
                    `;
                    this.track('kitchen_finder_lead_submitted', { montage: montage });
                } else {
                    alert(result.data?.message || 'Errore nell\'invio. Riprova.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore di connessione. Riprova più tardi.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        },

        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        track: function(eventName, data) {
            // Integrazione con analytics (GA4, Facebook Pixel, ecc.)
            if (typeof gtag !== 'undefined') {
                gtag('event', eventName, data);
            }
            if (typeof fbq !== 'undefined') {
                fbq('trackCustom', eventName, data);
            }
        }
    };

    // Inizializza quando il DOM è pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => KitchenFinder.init());
    } else {
        KitchenFinder.init();
    }

    // Esponi globalmente per onclick handlers
    window.kitchenFinder = KitchenFinder;

})();

