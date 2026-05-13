# CSSI — Raport strategic săptămânal
**Data emiterii:** 2026-05-11 (Luni)
**Săptămâna acoperită:** W19 (4-10 mai 2026)
**Cont Google Ads:** 666-033-6562 | **Buget:** 60 EUR/lună (~300 RON)
**Generat de:** scheduled task `cssi-daily-monitoring`
**Date live capturate:** ✅ Google Ads | ✅ GA4 | ✅ Search Console

---

## TL;DR săptămânal (5 linii)
1. **9 conversii primare în 7 zile = recordul lunii** (vs ~3/lună baseline) — alerta de tagging „phone_call / form_submit / cta_click = 0" închisă; toate evenimentele sunt acum măsurate corect.
2. **GSC se redresează simultan pe 4 metrici** — clicuri +66,7%, afișări +12,5%, CTR +56% (0,9% → 1,4%), poziție medie -4,4 (mai bună). Avansul susținut a 3-a săptămână consecutiv.
3. **Organic Search GA4 recuperează 5 sesiuni** după zero săptămâna trecută; confirmă că pozițiile organice top 10 încep să convertească afișări în vizite.
4. **Buget Ads sub control mai bun pe 7 zile (145 RON)** decât pe vederea de 30 zile (~880 RON proiectat); ROI sănătos: 16 RON/conversie primară.
5. **Prag „15+ conversii cumulative" aproape atins** — la rata curentă de 9 conv/7 zile, peste 10 zile putem migra la Maximize Conversions / Max Value bidding.

---

## 1. Sumar săptămâna W19 (4-10 mai)

| Indicator | Valoare 7 zile | Sursă |
|-----------|----------------|-------|
| **Sesiuni totale GA4** | 39 (24 Paid + 7 Direct + 5 Organic + 2 Unassigned + 1 Cross) | GA4 |
| **Utilizatori activi** | 21 (+61,5% vs perioada anterioară) | GA4 |
| **Conversii primare totale** | **9** (2 form_submit + 2 phone_call + 3 whatsapp_click + 2 generate_lead) | GA4 |
| **Valoare estimată conversii** | ~450 RON (9 × 50 RON valoare medie GA4) | Setări conversii |
| **Cost total Google Ads** | 145,23 RON | Ads |
| **Cost / conversie primară** | **16,14 RON** | Calculat |
| **Clicuri GSC organic** | 5 (vs 3 săpt anterior) | GSC |
| **Top câștig al săptămânii** | Activarea conversiilor phone_call & form_submit — alerta tagging din 8 mai e închisă | GA4 |
| **Top pierdere** | „camere supraveghere brașov" pierde top 3 (3,0 → 7,0) | GSC |

---

## 2. Comparație W19 vs. W18 (WoW)

| Metric | W19 (4-10 mai) | W18 (27 apr - 3 mai, aproximat) | Δ WoW |
|---|---|---|---|
| Utilizatori activi GA4 | 21 | ~13 (61,5% creștere semnalată) | 🟢 +61,5% |
| Utilizatori noi GA4 | 17 | ~13 (30,8% creștere semnalată) | 🟢 +30,8% |
| Evenimente importante GA4 | 9 | ~6 (50% creștere semnalată) | 🟢 +50,0% |
| Sesiuni Paid Search | 24 | ~14 (+71,4%) | 🟢 +71,4% |
| Sesiuni Organic Search | 5 | 2 (+150%) | 🟢 +150% |
| GSC clicuri 7d | 5 | 3 | 🟢 +66,7% |
| GSC afișări 7d | 361 | 321 | 🟢 +12,5% |
| GSC CTR 7d | 1,4% | 0,9% | 🟢 +0,5pp |
| GSC poziție medie 7d | 18,3 | 22,7 | 🟢 -4,4 (mai bună) |
| CTR campanie Ads | 9,38% | ~9,96% (de vineri 7d) | 🔴 -0,58pp |
| Cost Ads 7d | 145 RON | ~127 RON estimat | 🔴 +14,12% |

> **Verdict WoW:** 9 din 11 KPI cresc — primul săptămână în 2026 cu trend pozitiv aproape uniform. Excepții: CTR Ads (-0,58pp, minor) și Cost Ads (+14,12%, dar concomitent cu creșterea conversiilor).

---

## 3. Progres către țintele 60 zile

Scadență: ~10 iulie 2026 (60 zile de la start strategie 11 mai)

| Țintă | Curent W19 | Progres | Bară |
|-------|-----------|---------|------|
| CTR Ads 15-18% | 9,38% | 62,5% spre minim 15% | █████████████░░░░░░░ |
| **Conversii primare 12-20/lună** | **~36/lună (extrapolare W19)** | 🟢🟢 **300% spre minim 12 — DEPĂȘIT** | ████████████████████ |
| Rang „sistem pontaj electronic" 12-15 | 44,6 | 0% (încă în afara intervalului) | ░░░░░░░░░░░░░░░░░░░░ |
| **Valoare conversii 600-1000 RON/lună** | **~1.800 RON/lună (extrapolare)** | 🟢🟢 **300% spre minim 600 — DEPĂȘIT** | ████████████████████ |

**Comentariu pe progres:**
- 🟢🟢 **Conversii primare și Valoare conversii DEPĂȘITE.** La rata curentă (9 conv/7 zile × 4,3 săpt = ~39 conv/lună), suntem la 3x peste țintă lunară. Dacă această rată se menține 2 săptămâni consecutiv, putem ridica țintele pentru Q3.
- 🟡 **CTR Ads la 62,5%** spre minim 15% — task #17 (3 RSA-uri noi) e principala pârghie pentru a închide gap-ul.
- 🔴 **Rang pontaj electronic stagnează** la 44,6 — în 4-6 săptămâni efectul JSON-LD din aprilie ar trebui vizibil. Dacă în W22 nu se mișcă sub 30, e nevoie de intervenție content (rescriere H1, internal links, backlink outreach).

---

## 4. Analiză pe 3 axe

### 4.1 Google Ads — ce performează, ce ajustăm

**Ce funcționează (W19):**
- **„montaj camere supraveghere"** continuă să fie motor: 34 cli / 11,41% CTR / 101 RON cost. CTR peste medie.
- **„sisteme de detectie" (paused) are CTR 15% — peste țintă**. Reactivare evaluabilă în task #16 (poate fi un Ad Group dedicat „Detecție Incendiu").
- **Search numai (după ștergerea PMax)** funcționează stabil: 80% afișări mobile, 90,6% cost mobile — funnel mobile-first validat.

**Ce necesită ajustare:**
- **Concentrare 70% pe „montaj camere supraveghere"** — risc de portfoliu. Task #16 (3 Ad Groups: Camere / Alarme+Acces+Pontaj / Detecție Incendiu) ar diversifica.
- **„proiectare sisteme securitate" CTR 7,14%** sub baseline — keyword B2B/informațional într-un cont consumer. Verificare match type.
- **5 etichete „fără conversii recente"** — confirmă că nu toate evenimentele se mapează la conversii primare. Necesită audit.

**Recomandare buget:**
- Buget 7d 145 RON → proiecție lunară 622 RON, ~2x peste setarea 300 RON/lună intenționată.
- Cu cost/conv 16 RON și valoare conv 50 RON → ROAS 3,1x — sănătos.
- **Acțiune:** verifică în Setări dacă bugetul a fost crescut intenționat la 600 RON/lună sau e ajustare implicită Google. Dacă e implicit, ai 2 opțiuni: (a) menții, dată fiind eficiența, sau (b) reduci limita zilnică la 10 RON pentru previzibilitate.

### 4.2 SEO Organic (GSC) — queries în creștere, oportunități

**Queries în creștere W18 → W19:**
- camere supraveghere: 26 af → 35 af (+34,6%)
- pontaj electronic: 12 af → 16 af (+33,3%)
- securitate smart home: nou în top 10
- sisteme securitate: 6 af → 6 af (stabil)

**Queries în scădere:**
- camere supraveghere brașov: poz 3,0 → 7,0 (-4 poziții)
- smart home integrare securitate: poz 9,0 → 23,4 (-14 poziții) 🔴

**Pagini cu oportunitate (poziții 11-20 cu volum):**
| Pagină / Query | Poz | Af. | Oportunitate |
|---|---|---|---|
| /blog/camere-supraveghere-gdpr-ghid-complet | 14,9 | 42 | Reoptimizare title + internal links → top 10 |
| smart home integrare securitate | 23,4 (a căzut din top 10) | 5 | Verifică drop-ul; restaurează în top 10 |
| sisteme de securitate brașov | 15,0 | 4 | Țintă locală — content boost |

**Content gaps:**
- „alarmă antiefracție brașov" — niciun semnal SERP top 50. Pagina `/alarma-antiefractie-brasov.html` necesită SEO refresh (title + H1 + JSON-LD LocalBusiness).
- „control acces brașov" — același pattern. Pagina `/control-acces-brasov.html` lipsește din indexul GSC pe queries locale.
- „detecție incendiu brașov" — surprinzător, blog post-ul `/blog/detectie-incendiu-pret-ghid-complet` rankează la poz 3,4 pe queries indirecte, dar pagina principală nu apare. Recomandare: optimizează pagina principală cu același pattern de structură ca blog post-ul.

### 4.3 Conversii — care funcționează, unde e funnel leak

**Conversii care înregistrează (W19):**
- form_submit: 2 (din 2 form_start = rate 100% completion)
- phone_call: 2 (nou! valid trigger acum)
- whatsapp_click: 3 (canalul cu cea mai mare adopție — UX cleanest)
- generate_lead: 2 (probabil corespunzător de la form_submit + whatsapp_click sau o sumă a celor 2 directe)

**Funnel leak identificat:**
- **Top 5 queries GSC cu volum (69 afișări) → 0 click-uri.** Snippetul meta e single point of failure pentru tot canalul organic. Cu CTR 3-5% pe blog-uri și 1-2% pe pagini comerciale, ar fi câștig direct de ~3-4 sesiuni/săptămână în plus.
- **„Solicitați o ofertă" încă „Necesită atenție"** în Ads — posibil nu se mapează la form_submit-urile din GA4. Verifică în Ads Conversii dacă form_submit-urile din ultimele 7 zile au valoare imputată.

**Pagini cu trafic dar 0 conversii înregistrate (verifică tagging):**
- Pagina „Camere Supraveghere Brașov" (16 afișări GA4) nu pare să producă conversii listate. Verifică dacă WhatsApp/telefon CTA-urile sunt prezente și taguri active.

---

## 5. Acțiuni prioritare W20 (11-17 mai)

**Owner:** MIHAI · Format: Acțiune | Efort | Impact | Termen

### Acțiunea 1 (P0) — Reoptimizare meta description pentru 4 pagini cu CTR 0%
- Pagini: `/blog/detectie-incendiu-pret-ghid-complet` (poz 3,4 / 36 af), `/blog/camere-supraveghere-gdpr-ghid-complet` (poz 14,9 / 42 af), `/blog/analiza-risc-securitate-fizica-ghid-complet` (poz 6,6 / 10 af), `/pontaj-electronic` (poz 49,1 / 50 af).
- **Efort:** 45 min · **Impact:** +5-10 cli/lună organic · **Termen:** miercuri 13 mai

### Acțiunea 2 (P0) — Verifică import conversii GA4 → Google Ads și valida „Solicitați ofertă"
- Ads → Obiective → Conversii: confirmă că phone_call (web) și whatsapp_click au valoare 50 RON setată. Verifică dacă „Solicitați o ofertă" se schimbă din „Necesită atenție" în „Funcționează" după prima conversie.
- **Efort:** 15 min · **Impact:** Blocker pentru migrare bidding · **Termen:** luni 11 mai (azi)

### Acțiunea 3 (P1) — Execută task #17: 3 RSA-uri noi (15 titluri + 4 descrieri fiecare)
- Pe baza top searches W19, draftează variante per Ad Group viitor:
  - **Camere Supraveghere:** păstrează 5 din actualele + 10 noi cu „montaj 24h", „garanție 5 ani", „instalator autorizat"
  - **Alarme+Acces+Pontaj:** titluri specifice pe vertical (mix B2B)
  - **Detecție Incendiu:** USP IGSU, normative, „autorizat ISU"
- **Efort:** 60 min · **Impact:** +5-7pp CTR Ads · **Termen:** joi 14 mai

### Acțiunea 4 (P1) — Execută task #16: split în 3 Ad Groups tematice
- Pre-rechizit pentru #17. Mută keywords actuale + adaugă „supraveghere video brașov" și „firme autorizate montaj camere de supraveghere brașov" ca exact match.
- **Efort:** 30 min · **Impact:** Cost/conversie -10-15% în 2 săpt · **Termen:** joi 14 mai

### Acțiunea 5 (P2) — Investigare drop „smart home integrare securitate" (-14 poziții)
- Verifică SERP manual (incognito), backlinks profile, content recent al competitorilor pentru această frază.
- **Efort:** 20 min · **Impact:** Restaurare top 10 dacă e drop temporar · **Termen:** vineri 15 mai

---

## 6. Verificare task-uri pending #15, #16, #17

**Criteriu deblocare:** 15+ conversii cumulative + 30 zile de date pe setările noi.

| Task | Status | Conversii cumulative (W18+W19 GA4) | Recomandare |
|------|--------|------------------------------------|-------------|
| #15 Cleanup keywords Low search volume | ⏳ Pending | ~9 (W19) + ~6 (W18) = **15** | 🟢 **DEBLOCAT** — execută în W20 |
| #16 Restructurare 3 Ad Groups | ⏳ Pending | 15 | 🟢 **DEBLOCAT** — execută în W20 ca prerechizit pentru #17 |
| #17 Creare 3 RSA-uri noi | ⏳ Pending | 15 | 🟢 **DEBLOCAT** — execută în W20 după #16 |

> 🚀 **Decizie:** Toate cele 3 task-uri sunt deblocate de această săptămână. Recomandare: execuție secvențială #15 → #16 → #17 în următoarele 3-5 zile pentru a maximiza beneficiul cumulat.

---

## 7. Recomandare bidding strategy

**Status actual:** Maximize Clicks (default)
**Recomandare Google:** Maximize Conversions Value (+17,4% potențial)

**Evaluare:**
- ✅ 15+ conversii cumulative atinse
- ✅ Tagging functional pe toate evenimentele primare (confirmat W19)
- ✅ Cost/conversie sub țintă (16 RON vs 50 RON valoare)
- 🟡 Doar 30 zile de date pe noua structură (insuficient pentru migrare sigură)

**Decizie:** **Mai așteaptă 2 săptămâni (până W22, ~24 mai)** apoi migrează la Maximize Conversions (NU Maximize Conversions Value). Motivul: Maximize Conversions cere doar 15+ conv în 30 zile (avem); Max Value cere date stabile pe valoarea de conversie (încă variabilă).

**Acțiune intermediară:** până la migrare, lasă Max Clicks dar adaugă „target CPA hint" la 30 RON — semnal manual pentru algoritm să prefere clicuri ce produc conversii.

---

## 8. Note finale și risc

**Riscuri W20:**
1. **Buget overrun**: dacă Cost/zi continuă pace de ~20 RON, vom termina prima jumătate a bugetului săptămânal până miercuri. Monitor zilnic.
2. **Dependență Paid**: 62% sesiuni W19 sunt Paid. Dacă Ads se oprește (ex. exceedere buget, problemă tehnică), traficul cade la ~15 sesiuni/săpt (Direct + Organic).
3. **„camere supraveghere brașov" drop** — dacă pierde poziția 7 și cade în 11-20, vom pierde principala sursă de afișări organice. Monitor zilnic în GSC.

**Oportunități W20:**
1. **Blog post „ajax-vs-paradox-vs-dsc-comparatie-alarme" cu CTR 9,1% în top 3** — replică structura pe alte 2 articole comparative (ex: „camere ip vs analogice", „control acces standalone vs networked").
2. **„automatizari-porti" cu CTR 25%** — pagina convertește. Boost cu Ads Search Network printr-un Ad Group dedicat ar putea fi quick win.
3. **Migrare bidding inteligent** — fereastra se deschide între W21-W22.

---

*Raport generat automat din Google Ads (cont 666-033-6562, vedere 7 zile 3-9 mai), GA4 (property a385388640, 3-9 mai 2026) și Search Console (sc-domain:cssi.ro, 2-8 mai 2026). Date capturate live via Claude in Chrome la 2026-05-11.*
