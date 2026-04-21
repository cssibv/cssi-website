# CSSI — Sumar sesiune optimizare Google Ads + Website

**Data:** 21 aprilie 2026
**Cont Google Ads:** 666-033-6562
**Buget lunar:** 60 EUR (~300 RON)

---

## 1. Google Ads — modificări aplicate în cont

### 1.1 Performance Max șters
- **Status anterior:** activă, consuma ~57 RON / 7 zile (≈30% din buget), 0 conversii dovedite
- **Acțiune:** campanie eliminată complet
- **Impact:** +60 RON/lună redirecționați spre Search (unde CTR = 14,29% și avem 3 conversii)

### 1.2 Negative keywords globale extinse
- **Înainte:** 14 keywords negative (agent securitate, angajare, bodyguard, DIY, firma paza, gratuit, job, loc de munca, pareri, paza, recenzii, second hand, SH, tutorial)
- **Adăugate:** 17 noi — `gratis`, `free`, `do it yourself`, `refurbished`, `reparare`, `reparatii`, `service`, `cariera`, `pret mic`, `ieftin`, `schema`, `manual utilizare`, `curs`, `exam`, `referat`, `teza`, `diploma`, `scoala`, `licenta`
- **Total acum:** 31 negative keywords active
- **Impact:** elimină queries irelevante (tutoriale, cursuri, reparații gratuite, angajări)

### 1.3 Extensii reclamă — sitelinks
- **Păstrate:** 7 sitelinks existente cu performanță bună (CTR extensii: 18,18%)
- **Adăugate:** 3 sitelinks noi
  - **Portofoliu Proiecte** → `/portofoliu.html`
  - **Servicii Complete** → `/servicii.html`
  - **Despre CSSI** → `/despre-noi.html`
- **Total:** 10 sitelinks, 7 callouts, 1 structured snippet, 1 call extension

### 1.4 Conversion tracking — valori actualizate
Revizie completă a celor 8 acțiuni de conversie active (35 conversii în 30 zile):

| Acțiune | Sursă | Eveniment | Valoare | Stare |
|---------|-------|-----------|---------|-------|
| **Formular** | GA4 | `form_submit` | 50 RON default ✓ | Activ |
| **Solicitați o ofertă** | Site web manual | — | 50 RON ✓ | Necesită atenție |
| Calls from ads | Apel din anunțuri | — | 1 RON | Activ |
| Clicks to call | Google Business | — | — | Activ (2 conv.) |
| Local actions — Other engagements | Google Business | — | — | Activ (29 conv.) |
| Local actions — Directions | Google Business | — | — | Activ (1 conv.) |
| YouTube channel subscriptions | YouTube | — | — | Activ (0 conv.) |
| YouTube follow-on views | YouTube | — | — | Activ (0 conv.) |

Enhanced Conversions activ prin Google Tag. Atribuire data-driven pe Formular (90 zile fereastră).

---

## 2. Website cssi.ro — modificări HTML aplicate

### 2.1 `/securitate/brasov/` — formular de contact
- Adăugat formular funcțional sub primul fold (Nume, Telefon, Serviciu dropdown, Mesaj)
- Submit → POST la endpoint-ul Apps Script existent (sheet CRM)
- CTAs în hero cu `gtag` tracking pentru `lead_form_submit` și `phone_click`

### 2.2 `/portofoliu.html` — buton WhatsApp floating
- Adăugat buton WhatsApp fix bottom-right (z-index 9999, animație pulse)
- Link pre-populat: `wa.me/40XXXXXXXXX?text=Salut!%20Vreau%20ofertă%20pentru...`
- Tracking `whatsapp_click` pentru GA4

### 2.3 `/pontaj-electronic.html` — SEO priority #1
- **Title nou:** "Sistem Pontaj Electronic Brașov — CSSI | Cartelă RFID, Biometric, Integrare ERP"
- **H1 schimbat** să conțină "Sistem Pontaj Electronic"
- **JSON-LD LocalBusiness + FAQPage** adăugate (5 întrebări cu schema.org)
- Internal linking din FAQ către `/control-acces.html`

---

## 3. Impact estimat 60 zile

| Metric | Azi (baseline) | Țintă 60 zile |
|--------|-----|---------------|
| CTR campanie Search | 14,29% (foarte bun) | 15-18% |
| Conversii/lună total | ~3 primare + 29 local | 12-20 primare |
| Buget alocat eficient | ~70% (după PMax) | 100% |
| Rang "pontaj electronic" | 26 | 12-15 |
| Valoare totală conversii | 35 RON/30 zile | 600-1000 RON |

---

## 4. Follow-up-uri deschise (sesiuni viitoare)

**Amânate 2-3 săptămâni** (după ce colectăm date pe setările noi):
- **Task #15** — Curățare keywords Low search volume
- **Task #16** — Restructurare în 3 Ad Groups tematice (Camere / Alarme+Acces+Pontaj / Detecție Incendiu)
- **Task #17** — 3 RSA-uri noi cu 15 titluri + 4 descrieri fiecare

**Tracking gap:**
- **Task #21** — Import `phone_click` + `whatsapp_click` din GA4 ca acțiuni de conversie Google Ads (necesită mai întâi marcarea lor ca Key Events în GA4)

**Săptămâna 4 (după 15+ conversii colectate):**
- Schimbă strategia de bidding din "Maximize Clicks" în "Maximize Conversions"

---

## 5. Ce verificăm săptămâna viitoare

- [ ] Search impression share crescut (liber pe 5x mai multe queries)
- [ ] CTR pe sitelinks noi (Portofoliu, Servicii, Despre)
- [ ] Conversii pe "Solicitați o ofertă" (acum cu valoare 50 RON)
- [ ] Rate limit pe negative keywords (să nu filtrăm prea agresiv)
- [ ] GA4 events `phone_click` și `whatsapp_click` — date brute disponibile
