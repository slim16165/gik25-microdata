/**
 * App Navigator - Multilivello Mobile/Desktop
 * Gestisce navigazione a tab con sottomenu multilivello
 */

(function() {
	'use strict';

	const AppNav = {
		init: function() {
			const root = document.querySelector('[data-appnav]');
			if (!root) return;

			this.root = root;
			this.tabs = Array.from(root.querySelectorAll('.td-appnav__tab'));
			this.sections = {};
			this.currentLevel = {};
			this.currentSubsection = {};

			// Mappa sezioni
			Array.from(root.querySelectorAll('.td-appnav__section')).forEach(sec => {
				const id = sec.id.replace('td-section-', '');
				this.sections[id] = sec;
				this.currentLevel[id] = 1;
				this.currentSubsection[id] = null;
			});

			// Rileva variante (auto/mobile/desktop)
			this.variant = root.dataset.variant || 'auto';
			if (this.variant === 'auto') {
				this.variant = window.innerWidth >= 768 ? 'desktop' : 'mobile';
				// Aggiorna su resize
				let resizeTimer;
				window.addEventListener('resize', () => {
					clearTimeout(resizeTimer);
					resizeTimer = setTimeout(() => {
						const newVariant = window.innerWidth >= 768 ? 'desktop' : 'mobile';
						if (newVariant !== this.variant) {
							this.variant = newVariant;
							root.dataset.variant = this.variant;
							this.updateLayout();
						}
					}, 250);
				});
			}

			this.bindEvents();
			this.updateLayout();
		},

		bindEvents: function() {
			// Click su tab principale
			this.tabs.forEach(tab => {
				tab.addEventListener('click', (e) => {
					e.preventDefault();
					const target = tab.dataset.section;
					this.switchSection(target);
				});

				// Keyboard navigation
				tab.addEventListener('keydown', (e) => {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						tab.click();
					}
				});
			});

			// Mobile: click su sottomenu
			const submenuItems = this.root.querySelectorAll('.td-appnav__submenu-item');
			submenuItems.forEach(item => {
				item.addEventListener('click', (e) => {
					e.preventDefault();
					const subsection = item.dataset.subsection;
					const parent = item.dataset.parent;
					this.showSubsection(parent, subsection);
				});
			});

			// Mobile: back button
			const backButtons = this.root.querySelectorAll('.td-appnav__back');
			backButtons.forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.preventDefault();
					const section = btn.closest('.td-appnav__section');
					if (section) {
						const sectionId = section.id.replace('td-section-', '');
						this.goBack(sectionId);
					}
				});
			});

			// Desktop: hover preview (opzionale)
			if (this.variant === 'desktop') {
				this.tabs.forEach(tab => {
					if (tab.dataset.hasSubmenu === 'true') {
						tab.addEventListener('mouseenter', () => {
							// Opzionale: mostra preview al hover
						});
					}
				});
			}
		},

		switchSection: function(sectionId) {
			// Reset livello mobile
			if (this.variant === 'mobile') {
				this.currentLevel[sectionId] = 1;
				this.currentSubsection[sectionId] = null;
			}

			// Aggiorna tab
			this.tabs.forEach(t => {
				t.classList.remove('is-active');
				t.setAttribute('aria-selected', 'false');
			});

			const activeTab = this.tabs.find(t => t.dataset.section === sectionId);
			if (activeTab) {
				activeTab.classList.add('is-active');
				activeTab.setAttribute('aria-selected', 'true');
			}

			// Nascondi tutte le sezioni
			Object.values(this.sections).forEach(sec => {
				sec.classList.remove('is-active');
				sec.setAttribute('aria-hidden', 'true');
			});

			// Mostra sezione corrente
			if (this.sections[sectionId]) {
				this.sections[sectionId].classList.add('is-active');
				this.sections[sectionId].setAttribute('aria-hidden', 'false');

				// Reset mobile navigation
				if (this.variant === 'mobile') {
					this.resetMobileNav(sectionId);
				}

				// Scroll smooth
				this.sections[sectionId].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
			}
		},

		showSubsection: function(parentId, subsectionId) {
			if (this.variant !== 'mobile') return;

			const section = this.sections[parentId];
			if (!section) return;

			// Nascondi livello 1
			const level1 = section.querySelector('.td-appnav__level[data-level="1"]');
			if (level1) {
				level1.style.display = 'none';
			}

			// Mostra livello 2
			const level2 = section.querySelector(`.td-appnav__level[data-level="2"][data-subsection="${subsectionId}"]`);
			if (level2) {
				level2.style.display = 'block';
				level2.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}

			// Aggiorna breadcrumb
			const backBtn = section.querySelector('.td-appnav__back');
			const backLabel = section.querySelector('.td-appnav__back-label');
			const submenuItem = section.querySelector(`[data-subsection="${subsectionId}"]`);
			
			if (backBtn && backLabel && submenuItem) {
				backBtn.style.display = 'flex';
				backLabel.textContent = submenuItem.querySelector('.td-appnav__submenu-label').textContent;
			}

			this.currentLevel[parentId] = 2;
			this.currentSubsection[parentId] = subsectionId;
		},

		goBack: function(sectionId) {
			if (this.variant !== 'mobile') return;

			const section = this.sections[sectionId];
			if (!section) return;

			// Nascondi livello 2 corrente
			const currentLevel2 = section.querySelector(`.td-appnav__level[data-level="2"][data-subsection="${this.currentSubsection[sectionId]}"]`);
			if (currentLevel2) {
				currentLevel2.style.display = 'none';
			}

			// Mostra livello 1
			const level1 = section.querySelector('.td-appnav__level[data-level="1"]');
			if (level1) {
				level1.style.display = 'block';
				level1.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}

			// Nascondi breadcrumb
			const backBtn = section.querySelector('.td-appnav__back');
			if (backBtn) {
				backBtn.style.display = 'none';
			}

			this.currentLevel[sectionId] = 1;
			this.currentSubsection[sectionId] = null;
		},

		resetMobileNav: function(sectionId) {
			const section = this.sections[sectionId];
			if (!section) return;

			// Mostra livello 1
			const level1 = section.querySelector('.td-appnav__level[data-level="1"]');
			if (level1) {
				level1.style.display = 'block';
			}

			// Nascondi tutti i livelli 2
			const levels2 = section.querySelectorAll('.td-appnav__level[data-level="2"]');
			levels2.forEach(lvl => {
				lvl.style.display = 'none';
			});

			// Nascondi breadcrumb
			const backBtn = section.querySelector('.td-appnav__back');
			if (backBtn) {
				backBtn.style.display = 'none';
			}
		},

		updateLayout: function() {
			// Layout viene gestito via CSS con data-variant
			// Questo metodo può essere esteso per logiche più complesse
		}
	};

	// Inizializza
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => AppNav.init());
	} else {
		AppNav.init();
	}

	// Esponi globalmente per debug
	window.tdAppNav = AppNav;

})();
