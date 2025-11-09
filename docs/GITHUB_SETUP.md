# GitHub Setup Completo

Questa guida descrive tutte le configurazioni GitHub per sfruttare al meglio le funzionalità della piattaforma.

## 🔧 Configurazioni Implementate

### 1. Dependabot 🤖

**File**: `.github/dependabot.yml`

**Cosa fa**:
- Aggiorna automaticamente le dipendenze Composer (PHP)
- Aggiorna GitHub Actions
- Crea PR automatiche per aggiornamenti
- Raggruppa aggiornamenti patch/minor per ridurre PR

**Configurazione**:
- **Schedule**: Ogni lunedì alle 9:00
- **Limit PR**: Max 5 PR aperte per ecosistema
- **Labels**: Aggiunge label automatiche (`dependencies`, `php`, `github-actions`)
- **Commit Message**: Usa prefix `chore` con scope

**Uso**:
- Dependabot crea automaticamente PR per aggiornamenti
- Revisiona e merge le PR quando sono sicure
- Gli aggiornamenti major sono ignorati (richiedono test manuali)

### 2. CodeQL Security Analysis 🔒

**File**: `.github/workflows/codeql.yml`

**Cosa fa**:
- Analizza il codice per vulnerabilità di sicurezza
- Supporta JavaScript e PHP
- Esegue analisi su push, PR e schedule (ogni lunedì)

**Configurazione**:
- **Languages**: JavaScript, PHP
- **Schedule**: Ogni lunedì alle 3:00 UTC
- **Permissions**: Read contents, write security events

**Uso**:
- I risultati appaiono nella tab "Security" del repository
- Le vulnerabilità critiche bloccano il merge (se configurato)
- Consulta i report nella sezione Security → Code scanning

### 3. Dependency Review 🔍

**File**: `.github/workflows/dependency-review.yml`

**Cosa fa**:
- Analizza le dipendenze nelle PR
- Blocca PR con vulnerabilità moderate o superiori
- Commenta sulla PR con i dettagli delle vulnerabilità

**Configurazione**:
- **Fail on severity**: Moderate (blocca PR con vulnerabilità moderate+)
- **Comment summary**: Always (commenta sempre sulla PR)

**Uso**:
- Automatico su ogni PR
- Se una PR aggiunge dipendenze vulnerabili, viene bloccata
- Revisiona i commenti sulla PR per dettagli

### 4. SLSA Attestations 📜

**File**: `.github/workflows/slsa-attestations.yml`

**Cosa fa**:
- Genera attestazioni SLSA (Supply-chain Levels for Software Artifacts)
- Fornisce prove di provenienza del codice
- Migliora la sicurezza della supply chain

**Configurazione**:
- **Trigger**: Push su master, tag v*, workflow_dispatch
- **Permissions**: Read actions, write attestations, read contents

**Uso**:
- Le attestazioni vengono generate automaticamente
- Consulta le attestazioni nella sezione Security → Attestations
- Utile per verificare l'integrità del codice

### 5. Pull Request Template 📋

**File**: `.github/pull_request_template.md`

**Cosa fa**:
- Fornisce un template standardizzato per le PR
- Include checklist per qualità codice
- Facilita la review delle PR

**Uso**:
- Automatico quando si apre una PR
- Compila tutti i campi richiesti
- Segui la checklist prima di aprire la PR

### 6. Issue Templates 🐛

**File**: `.github/ISSUE_TEMPLATE/*.md`

**Cosa fa**:
- Fornisce template per bug report e feature request
- Standardizza le segnalazioni
- Facilita la gestione delle issue

**Template disponibili**:
- **Bug Report**: Template per segnalare bug
- **Feature Request**: Template per richiedere funzionalità
- **Config**: Configurazione issue templates

**Uso**:
- Seleziona il template appropriato quando apri una issue
- Compila tutti i campi richiesti
- Fornisci informazioni dettagliate

### 7. Security Policy 🔐

**File**: `.github/SECURITY.md`

**Cosa fa**:
- Definisce la policy di sicurezza
- Fornisce istruzioni per segnalare vulnerabilità
- Documenta versioni supportate

**Uso**:
- Consulta la policy prima di segnalare vulnerabilità
- Usa l'email per segnalazioni di sicurezza (non aprire issue pubbliche)
- Segui le best practice di sicurezza

### 8. Release Workflow 🚀

**File**: `.github/workflows/release.yml`

**Cosa fa**:
- Crea release GitHub automaticamente
- Estrae changelog dal README.md
- Genera note di release

**Configurazione**:
- **Trigger**: Push su tag v*.*.* o workflow_dispatch
- **Permissions**: Write contents

**Uso**:
- **Automatico**: Crea un tag `v2.3.0` e fai push → release automatica
- **Manuale**: Vai su Actions → "Release" → Run workflow → Inserisci versione

## 📊 Runners, Attestations, Metrics

### Runners 🏃

**Cosa sono**: Macchine che eseguono i workflow GitHub Actions.

**Configurazione attuale**: 
- ✅ Usa GitHub-hosted runners (ubuntu-latest)
- ❌ Non serve configurare self-hosted runners (a meno di esigenze specifiche)

**Quando servono self-hosted runners**:
- Hai bisogno di hardware specifico
- Hai requisiti di sicurezza particolari
- Vuoi ridurre i costi (per repository privati con molti workflow)

**Per questo progetto**: **NON necessario** - GitHub-hosted runners sono sufficienti.

### Attestations 📜

**Cosa sono**: Prove di provenienza del codice (SLSA).

**Configurazione**: 
- ✅ **Già configurato** in `.github/workflows/slsa-attestations.yml`
- ✅ Genera attestazioni automaticamente su push e tag

**Uso**:
- Consulta le attestazioni in: Security → Attestations
- Verifica l'integrità del codice
- Utile per compliance e sicurezza

**Per questo progetto**: **Già configurato** - funziona automaticamente.

### Usage Metrics 📈

**Cosa sono**: Metriche di utilizzo dei workflow (tempo esecuzione, costi, ecc.).

**Configurazione**: 
- ✅ **Nessuna configurazione necessaria** - GitHub le genera automaticamente
- ✅ Consulta in: Settings → Actions → Usage

**Metriche disponibili**:
- Minuti di esecuzione workflow
- Storage utilizzato
- Costi (per repository privati)
- Performance dei workflow

**Uso**:
- Monitora l'utilizzo in Settings → Actions → Usage
- Ottimizza i workflow se i costi sono elevati
- Verifica performance dei workflow

**Per questo progetto**: **Nessuna azione necessaria** - consulta le metriche quando necessario.

### Performance Metrics ⚡

**Cosa sono**: Metriche di performance dei workflow (tempo esecuzione, cache hit rate, ecc.).

**Configurazione**: 
- ✅ **Nessuna configurazione necessaria** - GitHub le genera automaticamente
- ✅ Consulta in: Settings → Actions → Performance metrics

**Metriche disponibili**:
- Tempo medio di esecuzione workflow
- Cache hit rate
- Tempo di setup
- Tempo di esecuzione job

**Uso**:
- Monitora performance in Settings → Actions → Performance metrics
- Ottimizza workflow lenti
- Migliora cache hit rate

**Per questo progetto**: **Nessuna azione necessaria** - consulta le metriche quando necessario.

## 🎯 Checklist Setup Completo

### ✅ Già Configurato

- [x] CI/CD Workflow
- [x] Test Suite
- [x] Code Coverage (Codecov)
- [x] Dependabot
- [x] CodeQL Security Analysis
- [x] Dependency Review
- [x] SLSA Attestations
- [x] Pull Request Template
- [x] Issue Templates
- [x] Security Policy
- [x] Release Workflow

### 🔧 Da Configurare Manualmente (GitHub UI)

1. **Branch Protection Rules**:
   - Vai su: Settings → Branches → Add rule
   - Proteggi `master` branch
   - Richiedi: PR reviews, status checks, up-to-date branches

2. **Code Scanning Alerts**:
   - Vai su: Settings → Code security and analysis
   - Abilita: "Dependabot alerts", "Dependabot security updates"
   - Abilita: "Code scanning" (se non già abilitato)

3. **Secrets** (se necessario):
   - Vai su: Settings → Secrets and variables → Actions
   - Aggiungi secrets per: Codecov token, deployment keys, ecc.

4. **Environments** (se necessario):
   - Vai su: Settings → Environments
   - Crea environment per: staging, production
   - Configura protection rules e secrets

## 📚 Risorse

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Dependabot Documentation](https://docs.github.com/en/code-security/dependabot)
- [CodeQL Documentation](https://codeql.github.com/docs/)
- [SLSA Documentation](https://slsa.dev/)
- [GitHub Security Best Practices](https://docs.github.com/en/code-security)

## 🚀 Prossimi Passi

1. **Configura Branch Protection** (Settings → Branches)
2. **Abilita Code Scanning Alerts** (Settings → Code security and analysis)
3. **Monitora Usage Metrics** (Settings → Actions → Usage)
4. **Revisiona Dependabot PR** (quando vengono create)
5. **Consulta Security Reports** (Security → Code scanning)

