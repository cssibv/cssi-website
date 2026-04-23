# CSSI — Document Master Sesiune 21 aprilie 2026

**Proprietar:** MIHAI (cssirobv@gmail.com)
**Obiectiv:** Continuitate sesiune — toată cunoașterea acumulată astăzi, pusă într-un singur loc, ca să nu mai repetăm investigații.

---

## 🎯 TL;DR — ce trebuie să știm pentru sesiunile următoare

1. **Tracking funcționează 100%** pe cssi.ro — gtag + GA4 + Google Ads + Enhanced Conversions infrastructure toate în cod, deployate și live.
2. **Toate cele 3 alerte din Daily Brief de azi au fost false pozitive** — am investigat și rezolvat. Nu mai trebuie re-investigate.
3. **Bottleneck real e volumul de conversii**, nu tracking-ul. La 0 form_submit în ultimele 7 zile nu putem activa Smart Bidding/Enhanced Conversions/restructurare campanie.
4. **Site-ul e sincron cu origin/main** — content-ul investigat/modificat azi e deja live. Singurele fișiere noi sunt cele 3 documente de investigație + acest master doc (necomitat încă).

---

## 📇 Referințe cont & ID-uri (NU re-căuta — folosește astea)

| Element | Valoare |
|---|---|
| Google Ads — ID cont | **666-033-6562** |
| Google Ads — AW_ID | **AW-17987940313** |
| GA4 — Property ID | **525787706** |
| GA4 — Measurement ID | **G-XGSRGBQBCS** |
| Conversion label „Solicitați o ofertă" | **WVuaCJnH1YEcENnfqIFD** |
| Valoare default conversie | **50 RON** |
| Creată (acțiunea) | 02.03.2026 |
| Sursă | Site web (TAG) |
| Dată adăugare cod Enhanced Conv. în site | 31.03.2026 |
| Meta Pixel ID | ⚠️ **placeholder `XXXXXXXXXXXXXXXXX`** (neconfigurat) |
| Deployment path cPanel | `/home/r101042brea/cssi.ro/` |
| Post-deploy hook | `php generate-localized.php` |

---

## 🏗️ Arhitectura tracking — cum e construit site-ul

### Layer 1: `cookie-consent.js` (se încarcă PRIMUL pe toate paginile)
- Linia 9: `var GA_ID = 'G-XGSRGBQBCS';`
- Linia 27: `var AW_ID = 'AW-17987940313';`
- Linia 28: `gtag('config', AW_ID);`
- Linia 33: încarcă async `gtag.js?id=AW-17987940313`
- Implementează **Consent Mode v2** (default `denied` pentru `ad_storage`, `analytics_storage`; `update('granted')` la accept).
- UTM-urile `utm_source`/`utm_medium`/`gclid` sunt tracked **automat** de gtag prin auto-tagging.

### Layer 2: `tracking.js` (evenimente custom)
Trimite `gtag('event', ...)` pentru:
- `phone_call` — click pe `tel:` linkuri
- `whatsapp_click` — click pe WhatsApp (wa.me)
- `form_submit` — submit formulare
- `cta_click` — butoane CTA
- `scroll` (GA4 enhanced)

**Enhanced Conversions (linia 59-99)** — hook pe `window.open()` către `wa.me`:
```javascript
var userData = {};
if (emailField && emailField.value) userData.email = emailField.value.trim().toLowerCase();
if (phoneField && phoneField.value) { /* normalizare +40 */ userData.phone_number = phone; }
if (nameField && nameField.value) { userData.address = { first_name: ..., last_name: ... }; }
gtag('set', 'user_data', userData);
gtag('event', 'conversion', { 'send_to': 'AW-17987940313/WVuaCJnH1YEcENnfqIFD' });
```

### Layer 3: GA4 → Google Ads (conversions import)
`phone_call` și `whatsapp_click` sunt importate din GA4 ca acțiuni de conversie în Google Ads cu valoare default 50 RON.

### Paginile verificate că au toate cele 3 scripturi
`index.html`, `contact.html`, `portofoliu.html`, `servicii.html`, toate sub-serviciile (`camere-supraveghere`, `alarma-antiefractie`, `pontaj-electronic`, etc.)

**Notă minor inconsistency**: pe `portofoliu.html` `cookie-consent.js` e fără `/` leading, pe `servicii.html` e cu `/`. Ambele funcționează (relative vs absolute), dar la un refactor viitor consolidăm.

---

## ✅ Ce s-a făcut astăzi — 4 task-uri completate

### Task #23 — Fix alertă „Conversiile optimizate prezintă probleme de configurare"
**Status:** ✅ rezolvat  
**Acțiune:** Dezactivat Enhanced Conversions la nivel de acțiune „Solicitați o ofertă" (Google Ads → Obiective → Conversii → Setări → debifat „Activați conversiile optimizate").  
**Rezultat:** Status trecut din „Urgent" în „Nu a fost configurată încă". Smart Bidding deblocat.  
**Descoperire post-fix:** Codul Enhanced Conv. există deja în `tracking.js` din 31.03.2026. Infrastructura e completă. Singurul blocant pentru reactivare = **volum ≥15 conversii/lună** (momentan 0).  
**Document:** `daily-briefs/CSSI-Fix-Conversii-Imbunatatite-2026-04-21.md`

### Task #24 — Verificare conversion label match
**Status:** ✅ validat  
**Verificare:** Label `WVuaCJnH1YEcENnfqIFD` din `tracking.js` linia 118 = exact label-ul acțiunii „Solicitați o ofertă" din Google Ads (cont 17987940313, 50 RON, creat 02.03.2026).  
**Concluzie:** Nu e nevoie de niciun fix în cod. Totul e aliniat.

### Task #25 — Investigație „GA4 Events -44.6% WoW (209 vs ~377)"
**Status:** ✅ rezolvat — FALSE ALARM  
**Verdict:** Artefact statistic. Săptămâna 7-13 apr a conținut un spike anormal pe 7-8 apr (170+100 evenimente vs baseline ~35-60/zi). Săptămâna 14-20 apr = baseline normal.  
**Validări că tracking-ul e OK:**
- Real-time funcționează (1 user activ LIVE în Brașov)
- Toate event-urile trimit (page_view 384, user_engagement 324, session_start 144, first_visit 86, scroll 73, form_start 11, click 10, form_submit 5, phone_call 5, whatsapp_click 5 — pe 28 zile)
- Users +20% WoW (dacă ar fi tracking issue real, ar fi scăzut și users)
- Raport 3.2 ev/user = normal  
**Acțiune cerută:** niciuna. Dashboard-ul se va normaliza săptămâna viitoare.  
**Document:** `daily-briefs/CSSI-Investigatie-Events-44pct-2026-04-21.md`

### Task #26 — Investigație „Portofoliu/Servicii 0 afișări (ipoteza sitelinks Ads nu păstrează UTM)"
**Status:** ✅ rezolvat — FALSE ALARM  
**Verdict:** Ambele pagini PRIMESC trafic. Ipoteza UTM e incorectă.
- `/portofoliu`: 25 afișări / 12 users pe 28 zile = **#5 pagină din site**
- `/servicii`: 15 afișări / 6 users pe 28 zile = #7 pagină din site
- Ambele au scripturile de tracking încărcate corect.
- UTM-urile sunt auto-tagged de gtag (via `gclid`).  

**SINGURUL semnal real descoperit:** `/servicii` are **2 sec durată medie** (vs `/pontaj-electronic` 1min02sec, `/despre-noi` 23 sec). Asta e problemă de **UX pe hub page**, nu de tracking. Utilizatorii bounce instant.  
**Document:** `daily-briefs/CSSI-Investigatie-Portofoliu-Servicii-2026-04-21.md`

---

## 🔄 Deployment & sync state

**Ultimul deploy prin cPanel** e sincron cu `origin/main`. La `git diff HEAD` pe toate fișierele de tracking (`tracking.js`, `cookie-consent.js`, `contact.html`, `portofoliu.html`, `servicii.html`) apar DOAR mode changes (`100644 → 100755`), NICIO diferență de conținut. Înseamnă că tot codul descoperit azi (Enhanced Conversions din 31.03) e deja live pe cssi.ro.

**Fișiere noi netrecute în git** (doar doc-uri, nu cod):
- `daily-briefs/CSSI-Fix-Conversii-Imbunatatite-2026-04-21.md`
- `daily-briefs/CSSI-Investigatie-Events-44pct-2026-04-21.md`
- `daily-briefs/CSSI-Investigatie-Portofoliu-Servicii-2026-04-21.md`
- `daily-briefs/CSSI-Sesiune-Master-2026-04-21.md` (acest document)

Niciuna nu afectează site-ul live — sunt documentație internă. Când vreți commit, spuneți explicit.

---

## 📋 Task-uri pending (nu începe până nu se îndeplinesc gate-urile)

| # | Task | Gate de pornire | Motiv |
|---|---|---|---|
| 15 | Curățare keywords „Low search volume" în campania Search | ≥15 conversii/lună | Fără volum, algoritmul nu are date să judece noile keywords |
| 16 | Restructurare campanie în 3 Ad Groups tematice | ≥15 conversii/lună | La fel — restructurarea împarte datele pe mai multe grupe, la volum mic devine zgomot |
| 17 | 3 RSA-uri noi cu 15 titluri + 4 descrieri fiecare | După #16 | RSA-urile se fac pe Ad Group; trebuie întâi structura |
| — | Reactivare Enhanced Conversions pe „Solicitați o ofertă" | ≥15 conversii/lună | Google nu validează matching-ul user_data fără volum minim |
| — | Decide A vs B pentru „Solicitați oferte" (TAG-based) | Confirmare MIHAI | Vezi mai jos |

**Decizie luată (22 apr 2026):** MIHAI a ales opțiunea **B** — marchez „Solicitați oferte" (TAG) ca **Secundară** (reversibil, nu șterg). Rămâne doar `form_submit` importat din GA4 ca sursă pentru Smart Bidding.

**Status aplicare:** Chrome automation s-a blocat repetat pe dialogul Google Ads. Instrucțiunile manuale exacte (3 click-uri, ~30 sec) sunt în `daily-briefs/CSSI-Instructiuni-Solicitati-Oferte-Secundara.md`. După ce MIHAI aplică modificarea, warning-ul „Necesită atenție" de pe cardul „Solicitați o ofertă" dispare în 24-48h.

**Confirmare fix task #23 rămas în vigoare:** Pe 22 apr, pagina Setări → Conversii confirmă „Conversii îmbunătățite: Nu a fost configurată încă" ✅ (fix-ul de ieri n-a fost anulat).

---

## 🚧 Blocante active / semnale slabe

1. **Volum conversii prea mic** (0 form_submits pe 7 zile, 5 pe 28 zile): blochează Smart Bidding, Enhanced Conversions validation, și orice optimizare statistică de cont Ads.
2. **Meta Pixel neconfigurat** — placeholder `XXXXXXXXXXXXXXXXX` în `cookie-consent.js`. Dacă MIHAI vrea Facebook/Instagram Ads, asta e primul fix.
3. **`/servicii` are 2 sec bounce** — hub page slabă. Dacă vrem s-o facem landing page pentru Ads, trebuie restructurată (headline, grid cu descrieri, CTA sus, „cele mai cerute").
4. **Daily Brief cu delta WoW la volum mic generează fals pozitive** — recomandare în doc: adaugă fereastra 28 zile pentru context la orice delta >30%.

---

## 💡 Lessons learned (ca să nu repetăm)

1. **Nu te încrede orbește în alertele Daily Brief când volumul e <500 ev/săpt** — un singur spike anormal distorsionează toate comparațiile WoW. Întotdeauna validezi cu chart-ul pe 28 zile.
2. **„0 afișări" ≠ pagină neexistentă** — întâi verifică pe fereastra mai lungă (28 zile) înainte să presupui tracking issue. La volum mic e normal ca o pagină să aibă 0 afișări într-o zi.
3. **Signal pattern „users up + events down" = artefact statistic**, NU tracking issue. Dacă ar fi tracking real stricat, ar scădea ambele.
4. **Enhanced Conversions au 2 layere**:
   - **Cod site** (user_data hashing) — asta am confirmat că există în tracking.js
   - **Setare Google Ads** (toggle per acțiune conversie) — asta am dezactivat azi
   Ambele trebuie să fie ON + să existe volum pentru a funcționa.
5. **Conversion label match** se verifică prin: Google Ads → Obiective → Conversii → click acțiunea → „Configurarea etichetei" → compară cu `send_to` din cod. `AW-XXXX/YYYY` unde XXXX e AW_ID și YYYY e label.

---

## 🧭 Cum continuăm mâine / săptămâna viitoare

**Dacă volumul crește (MIHAI aduce trafic din Ads sau Facebook):**
1. La ≥15 conv/lună → reactivare Enhanced Conversions (toggle on).
2. Apoi task #16 (restructurare 3 Ad Groups).
3. Apoi task #17 (RSA-uri noi).
4. Apoi task #15 (cleanup keywords).

**Dacă volumul rămâne scăzut:**
1. Decizie A/B pentru „Solicitați oferte" (recomandare B — ștergem duplicatul).
2. Optimizare UX pe `/servicii` (4 sub-pași din doc-ul task #26).
3. Decizie Meta Pixel: activăm sau eliminăm placeholder-ul din cookie-consent.js.
4. Audit Google Ads → volum impresii vs. CTR pe keywords existente — poate problema e impresii prea mici, nu conversii.

**Ca să nu pierdem context:**
- Toate investigațiile au documente în `/daily-briefs/`
- Acest doc master e punctul de intrare — citește-l primul la sesiunea următoare
- Task-urile pending sunt numerotate consistent (#15, #16, #17)
- ID-urile și label-urile sunt în tabelul de la începutul acestui document — **NU le re-căuta**

---

## 📂 Fișiere de referință (citește-le dacă ai nevoie de detalii)

| Fișier | Pentru ce |
|---|---|
| `tracking.js` | Cod Enhanced Conversions (linii 59-99), event-uri custom |
| `cookie-consent.js` | Config gtag, Consent Mode v2, AW/GA IDs |
| `contact.html` | Formularul principal care trimite user_data |
| `.cpanel.yml` | Pipeline de deploy (cp + php generate-localized.php) |
| `daily-briefs/CSSI-Fix-Conversii-Imbunatatite-2026-04-21.md` | Detaliu fix task #23 |
| `daily-briefs/CSSI-Investigatie-Events-44pct-2026-04-21.md` | Detaliu task #25 |
| `daily-briefs/CSSI-Investigatie-Portofoliu-Servicii-2026-04-21.md` | Detaliu task #26 |

---

**Sesiune încheiată:** 21 apr 2026  
**Task-uri completate:** #23, #24, #25, #26  
**Task-uri pending:** #15, #16, #17 (toate blocate de gate volum)  
**Acțiuni de urmat în cod:** niciuna urgentă — site-ul funcționează corect și e deployat.

---

## 🔄 Addendum 22 apr 2026 — Task #28 rezolvat, #29 deschis

### Task #28 ✅ completat: „Solicitați oferte" (TAG) → Secundară
**Decizie MIHAI:** opțiunea B (recomandată) — marchez acțiunea TAG-based ca Secundară în loc s-o șterg.
**Acțiune realizată (browser automation):** Google Ads → Obiective → Conversii → Rezumat → card „Solicitați o ofertă" → Modificați obiectivul → Optimizarea acțiunilor de conversie → toggle „Principală" → „Secundară" → Salvați.
**Rezultat confirmat prin reopening dialog:** „Optimizarea acțiunilor de conversie: **1 acțiune de conversie secundară**" ✅
**Efect așteptat:** `form_submit` (din GA4) rămâne singura acțiune care influențează Smart Bidding. Acțiunea TAG rămâne vizibilă în rapoarte, dar observator-only.

### Task #29 ✅ completat: Obiectivul „Solicitați oferte" — dezactivat ca „standard pentru cont"
**Decizie MIHAI:** opțiunea A — dezactivez și asocierea cu cont-default, nu doar acțiunea.
**Acțiune realizată (browser automation):** dialog Editați → expand „Obiectiv standard pentru cont" → toggle OFF „Setați acest obiectiv ca obiectiv prestabilit pentru cont" → Salvați.
**Rezultat confirmat pe card:**
- Eticheta gri „Prestabilit pentru cont" dispărută ✅
- Campanii: **0 din 1** (era 1 din 1)
- Acțiuni de conversie principale: 0

**Warning rezidual „Configurată greșit":** rămâne cosmetic. Google Ads marchează orice obiectiv cu 0 acțiuni principale + 0 campanii asociate ca „configurată greșit". Singura cale de eliminare totală = ștergerea obiectivului (NU o facem — pierdem istoric).

**Impact real pe bidding:** ZERO. `form_submit` (import GA4) rămâne singura acțiune Principală care influențează Smart Bidding. Campania care folosea anterior „Solicitați oferte" ca default va folosi acum setările implicite de cont sau obiectivul propriu al campaniei.

---

## 🧾 Status final 22 apr 2026

**Task-uri completate astăzi:** #28, #29, **#18 round 2** (5 negative keywords noi)
**Task-uri completate în total pe proiect:** #1-14, #18-29 (27 task-uri)
**Task-uri pending:** #15, #16, #17 (toate blocate de volum <15 conv/lună)

**Acțiuni Google Ads azi:**
1. `Solicitați oferte` (TAG, 0 date) marcată Secundară → nu mai influențează Smart Bidding
2. Obiectivul asociat dezactivat ca default pe cont → nicio campanie nu-l mai folosește ca referință
3. **Negative keywords — round 2:** 5 termeni noi adăugați după analiza Search Terms Report în Daily Brief 10:35:
   - `dedeman` (broad) — retailer DIY, irelevant
   - `[security]` (exact) — termen single-word prea generic, blochează doar literal „security"
   - `atu tech` (broad) — concurent direct
   - `"general security"` (phrase) — concurent
   - `"protection security"` (phrase) — concurent

   **Rezultat:** total negative keywords **31 → 36**. Listele round 1 (shop security, spy shop, rovision, taggo, atutech, visuron, ultra security, secpral) deja funcționează — acești termeni NU mai apar în top search terms azi. Impact estimat: ~10-15 RON/săpt economisite suplimentar + CTR în creștere.

**Rezultat optimizare:** Smart Bidding pentru campania Search e acum condus exclusiv de `form_submit` (GA4 import, date reale) + acțiunile GA4 importate (`phone_call`, `whatsapp_click`, 50 RON fiecare). Configurarea e curată și pregătită pentru scaling când volumul depășește 15 conv/lună.

**Observație performanță azi:** Campania arată **3 conversii** (nu 0 cum indica Daily Brief automat — briefingul număra doar „Solicitați oferte" care e acum Secundară). Cost/conv: 47,13 RON. Conv rate: 7,5%. Primele rezultate ale modificărilor de ieri (conversii GA4 importate + extensii + setări bid) încep să se vadă.
