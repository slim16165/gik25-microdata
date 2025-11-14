/**
 * Personality Color Test
 * Test personalità basato su colori
 */

(function() {
    'use strict';
    
    class PersonalityColorTest {
        constructor(container) {
            this.container = container;
            this.questions = this.getQuestions();
            this.currentQuestion = 0;
            this.answers = [];
            this.scores = {
                red: 0,    // Energico, passionale
                blue: 0,   // Calmo, riflessivo
                yellow: 0, // Ottimista, creativo
                green: 0   // Equilibrato, armonioso
            };
            
            this.init();
        }
        
        getQuestions() {
            return [
                {
                    text: 'In una giornata libera preferisci:',
                    options: [
                        { text: 'Fare sport o attività dinamiche', color: 'red', value: { red: 3 } },
                        { text: 'Leggere o meditare', color: 'blue', value: { blue: 3 } },
                        { text: 'Socializzare e divertirti', color: 'yellow', value: { yellow: 3 } },
                        { text: 'Passeggiare nella natura', color: 'green', value: { green: 3 } }
                    ]
                },
                {
                    text: 'Il tuo colore preferito è:',
                    options: [
                        { text: 'Rosso', color: 'red', value: { red: 2 } },
                        { text: 'Blu', color: 'blue', value: { blue: 2 } },
                        { text: 'Giallo', color: 'yellow', value: { yellow: 2 } },
                        { text: 'Verde', color: 'green', value: { green: 2 } }
                    ]
                },
                {
                    text: 'Quando prendi decisioni importanti:',
                    options: [
                        { text: 'Agisci rapidamente e con determinazione', color: 'red', value: { red: 3 } },
                        { text: 'Analizzi attentamente tutte le opzioni', color: 'blue', value: { blue: 3 } },
                        { text: 'Segui il tuo istinto e l\'entusiasmo', color: 'yellow', value: { yellow: 3 } },
                        { text: 'Cerchi equilibrio e armonia', color: 'green', value: { green: 3 } }
                    ]
                },
                {
                    text: 'Il tuo ambiente ideale è:',
                    options: [
                        { text: 'Dinamico e stimolante', color: 'red', value: { red: 2 } },
                        { text: 'Tranquillo e ordinato', color: 'blue', value: { blue: 2 } },
                        { text: 'Vivace e colorato', color: 'yellow', value: { yellow: 2 } },
                        { text: 'Naturale e rilassante', color: 'green', value: { green: 2 } }
                    ]
                },
                {
                    text: 'Quando sei stressato:',
                    options: [
                        { text: 'Hai bisogno di sfogare l\'energia', color: 'red', value: { red: 3 } },
                        { text: 'Hai bisogno di solitudine e riflessione', color: 'blue', value: { blue: 3 } },
                        { text: 'Hai bisogno di distrazione e divertimento', color: 'yellow', value: { yellow: 3 } },
                        { text: 'Hai bisogno di pace e equilibrio', color: 'green', value: { green: 3 } }
                    ]
                },
                {
                    text: 'Il tuo stile di comunicazione è:',
                    options: [
                        { text: 'Diretto e assertivo', color: 'red', value: { red: 2 } },
                        { text: 'Riflessivo e analitico', color: 'blue', value: { blue: 2 } },
                        { text: 'Enthusiastico e espressivo', color: 'yellow', value: { yellow: 2 } },
                        { text: 'Empatico e armonioso', color: 'green', value: { green: 2 } }
                    ]
                },
                {
                    text: 'Preferisci lavorare:',
                    options: [
                        { text: 'Sotto pressione con scadenze', color: 'red', value: { red: 3 } },
                        { text: 'Con tempo per pianificare', color: 'blue', value: { blue: 3 } },
                        { text: 'In team creativo e dinamico', color: 'yellow', value: { yellow: 3 } },
                        { text: 'In ambiente collaborativo', color: 'green', value: { green: 3 } }
                    ]
                },
                {
                    text: 'Il tuo approccio ai problemi è:',
                    options: [
                        { text: 'Affrontarli direttamente', color: 'red', value: { red: 2 } },
                        { text: 'Analizzarli in profondità', color: 'blue', value: { blue: 2 } },
                        { text: 'Trovare soluzioni creative', color: 'yellow', value: { yellow: 2 } },
                        { text: 'Cercare compromessi', color: 'green', value: { green: 2 } }
                    ]
                },
                {
                    text: 'La tua energia è:',
                    options: [
                        { text: 'Alta e costante', color: 'red', value: { red: 3 } },
                        { text: 'Stabile e controllata', color: 'blue', value: { blue: 3 } },
                        { text: 'Variabile e entusiasta', color: 'yellow', value: { yellow: 3 } },
                        { text: 'Equilibrata e armoniosa', color: 'green', value: { green: 3 } }
                    ]
                },
                {
                    text: 'Il tuo obiettivo principale è:',
                    options: [
                        { text: 'Raggiungere risultati', color: 'red', value: { red: 3 } },
                        { text: 'Comprendere profondamente', color: 'blue', value: { blue: 3 } },
                        { text: 'Esprimere creatività', color: 'yellow', value: { yellow: 3 } },
                        { text: 'Mantenere equilibrio', color: 'green', value: { green: 3 } }
                    ]
                }
            ];
        }
        
        init() {
            this.showQuestion();
        }
        
        showQuestion() {
            const question = this.questions[this.currentQuestion];
            if (!question) {
                this.showResult();
                return;
            }
            
            const questionEl = this.container.querySelector('#question-text');
            const optionsEl = this.container.querySelector('#test-options');
            const progressBar = this.container.querySelector('#progress-bar');
            const progressText = this.container.querySelector('#progress-text');
            
            if (questionEl) {
                questionEl.textContent = question.text;
            }
            
            if (progressBar) {
                const progress = ((this.currentQuestion + 1) / this.questions.length) * 100;
                progressBar.style.width = progress + '%';
            }
            
            if (progressText) {
                progressText.textContent = `Domanda ${this.currentQuestion + 1} di ${this.questions.length}`;
            }
            
            if (optionsEl) {
                optionsEl.innerHTML = question.options.map((option, index) => `
                    <button class="test-option" data-index="${index}" data-color="${option.color}">
                        <span class="option-color" style="background: var(--color-${option.color})"></span>
                        <span class="option-text">${option.text}</span>
                    </button>
                `).join('');
                
                optionsEl.querySelectorAll('.test-option').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const index = parseInt(e.currentTarget.dataset.index);
                        this.selectAnswer(index);
                    });
                });
            }
            
            // Animate
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(optionsEl.querySelectorAll('.test-option'),
                    { opacity: 0, y: 20 },
                    { opacity: 1, y: 0, duration: 0.5, stagger: 0.1 }
                );
            }
        }
        
        selectAnswer(index) {
            const question = this.questions[this.currentQuestion];
            const option = question.options[index];
            
            // Update scores
            Object.keys(option.value).forEach(color => {
                this.scores[color] += option.value[color];
            });
            
            this.answers.push({
                question: this.currentQuestion,
                answer: index,
                color: option.color
            });
            
            // Animate selection
            const options = this.container.querySelectorAll('.test-option');
            options.forEach((opt, i) => {
                if (i === index) {
                    opt.classList.add('selected');
                    if (typeof gsap !== 'undefined') {
                        gsap.to(opt, {
                            scale: 1.05,
                            duration: 0.3,
                            onComplete: () => {
                                this.nextQuestion();
                            }
                        });
                    } else {
                        setTimeout(() => this.nextQuestion(), 500);
                    }
                } else {
                    opt.style.opacity = '0.5';
                }
            });
        }
        
        nextQuestion() {
            this.currentQuestion++;
            if (this.currentQuestion < this.questions.length) {
                this.showQuestion();
            } else {
                this.showResult();
            }
        }
        
        showResult() {
            const questionEl = this.container.querySelector('.test-question');
            const optionsEl = this.container.querySelector('.test-options');
            const resultEl = this.container.querySelector('#test-result');
            
            if (questionEl) questionEl.style.display = 'none';
            if (optionsEl) optionsEl.style.display = 'none';
            
            // Find dominant color
            const maxScore = Math.max(...Object.values(this.scores));
            const dominantColor = Object.keys(this.scores).find(
                color => this.scores[color] === maxScore
            );
            
            const personalities = {
                red: {
                    name: 'Energico e Passionale',
                    description: 'Sei una persona dinamica, determinata e piena di energia. Il rosso rappresenta la tua passione e la tua capacità di agire con determinazione.',
                    traits: ['Determinazione', 'Energia', 'Passione', 'Leadership'],
                    recommendations: ['Usa il rosso per aumentare la motivazione', 'Evita il rosso quando hai bisogno di calma']
                },
                blue: {
                    name: 'Calmo e Riflessivo',
                    description: 'Sei una persona tranquilla, riflessiva e analitica. Il blu rappresenta la tua capacità di pensare profondamente e mantenere la calma.',
                    traits: ['Riflessione', 'Calma', 'Analisi', 'Stabilità'],
                    recommendations: ['Usa il blu per migliorare la concentrazione', 'Il blu favorisce il riposo']
                },
                yellow: {
                    name: 'Ottimista e Creativo',
                    description: 'Sei una persona positiva, creativa e piena di entusiasmo. Il giallo rappresenta la tua gioia di vivere e la tua creatività.',
                    traits: ['Ottimismo', 'Creatività', 'Entusiasmo', 'Socialità'],
                    recommendations: ['Usa il giallo per stimolare la creatività', 'Il giallo aumenta l\'energia positiva']
                },
                green: {
                    name: 'Equilibrato e Armonioso',
                    description: 'Sei una persona equilibrata, armoniosa e in sintonia con la natura. Il verde rappresenta il tuo bisogno di equilibrio e pace.',
                    traits: ['Equilibrio', 'Armonia', 'Pace', 'Naturalezza'],
                    recommendations: ['Usa il verde per rilassarti', 'Il verde favorisce l\'equilibrio emotivo']
                }
            };
            
            const personality = personalities[dominantColor];
            
            if (resultEl) {
                resultEl.style.display = 'block';
                resultEl.innerHTML = `
                    <div class="result-header">
                        <h2>Il tuo colore personalità</h2>
                        <div class="result-color" style="background: var(--color-${dominantColor})"></div>
                        <h3>${personality.name}</h3>
                    </div>
                    <div class="result-description">
                        <p>${personality.description}</p>
                    </div>
                    <div class="result-traits">
                        <h4>Le tue caratteristiche:</h4>
                        <ul>
                            ${personality.traits.map(trait => `<li>${trait}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="result-recommendations">
                        <h4>Raccomandazioni:</h4>
                        <ul>
                            ${personality.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                        </ul>
                    </div>
                    <button class="retake-test" onclick="location.reload()">Rifai il test</button>
                `;
                
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(resultEl,
                        { opacity: 0, y: 30 },
                        { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }
                    );
                }
            }
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.personality-color-test');
        containers.forEach(container => {
            new PersonalityColorTest(container);
        });
    });
    
    window.PersonalityColorTest = PersonalityColorTest;
})();

