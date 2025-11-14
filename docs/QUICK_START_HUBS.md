# Quick Start - Hub Dinamici

**Versione**: 1.0.0  
**Data**: 2025-01-30

---

## 🚀 Utilizzo Rapido

### Hub Colori Dinamico

Sostituisci:
```
[link_colori]
```

Con:
```
[hub_colori]
```

**Risultato**: Hub colori completo generato dinamicamente da WordPress invece di link hardcoded.

---

### Hub Architetti Dinamico

Sostituisci:
```
[archistar]
```

Con:
```
[hub_architetti]
```

**Risultato**: Hub architetti completo generato dinamicamente da WordPress.

---

### Hub Programmi 3D Dinamico

Sostituisci:
```
[grafica3d]
```

Con:
```
[hub_grafica3d]
```

**Risultato**: Hub programmi 3D completo generato dinamicamente da WordPress.

---

## 🔧 Configurazione WordPress

### Tag Richiesti

Per funzionamento ottimale, assicurati che i post abbiano:

**Hub Colori**:
- Tag: `colori`, `pantone`, `abbinamento-colori`, `palette`

**Hub Architetti**:
- Categoria: `archistar` OPPURE Tag: `architetti`

**Hub Programmi 3D**:
- Tag: `grafica-3d`, `cad`, `rendering`

**Nota**: Se i tag non sono disponibili, gli hub utilizzano automaticamente ricerca per keywords (fallback).

---

## ✨ Cross-Linker Avanzato

**Attivazione**: Automatica su tutti i post

**Funzionalità**: Genera link correlati intelligenti basati su:
- Colore + Stanza + IKEA (priorità alta)
- Colore + Stanza (priorità media)
- IKEA + Stanza (priorità media)
- Colore (priorità bassa)

**Personalizzazione**: Vedi `docs/HUBS_DYNAMIC_INTEGRATION.md`

---

## 📚 Documentazione Completa

- **Guida dettagliata**: `docs/HUBS_DYNAMIC_INTEGRATION.md`
- **Piano d'azione**: `docs/ACTION_PLAN_INTEGRATED.md`
- **Proposte integrazioni**: `docs/INTEGRATION_PROPOSALS.md`
- **Riepilogo lavoro**: `docs/WORK_SUMMARY_2025_01_30.md`

---

## 🐛 Problemi?

**Nessun link generato?**
→ Verifica che i post abbiano i tag corretti e siano pubblicati.

**Link duplicati?**
→ I post vengono automaticamente deduplicati.

**Performance lenta?**
→ Attiva cache WordPress o riduci il numero massimo di post nelle classi Hub.

---

**Supporto**: Vedi documentazione completa in `docs/HUBS_DYNAMIC_INTEGRATION.md`

