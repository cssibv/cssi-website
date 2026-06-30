# CSSI — Raport Zilnic de Monitorizare
**Data:** 2026-06-22 (Luni) · Cont Google Ads 666-033-6562 · cssi.ro
**Stare rulare:** ✅ Date LIVE pe toate cele 3 platforme (Ads, GA4, GSC). Prima rulare complet nealterată după blocajul de selecție browser care a afectat ~8 rulări anterioare.

---

## TL;DR
- **Merge bine:** Săptămână puternică pe toate canalele. CTR Ads a sărit la **13,21%** (de la 7,59% în W24), Optimization score **96,8%** (de la 72,3%), ROAS **3,70**. Organic Search a **depășit** Paid Search ca sesiuni (86 vs 79 / 28 zile). Evenimente importante GA4 **7 (↑250%)**.
- **Nu merge:** Volumul de afișări Ads rămâne mic (333/7z) și toți termenii de căutare cad tot în grupul **Camere Supraveghere** — grupurile Alarme/Acces/Pontaj/Incendiu par în continuare fără tracțiune. Conversii blocate la **2/săpt**.
- **Acțiune azi:** Verifică în „Anunțuri" dacă grupurile Alarme/Acces/Pontaj/Incendiu chiar difuzează acum (salt opt. score 72→97 sugerează o îmbunătățire — confirmă sursa). Apoi decide dacă mai e nevoie de RSA-uri noi (task #17).

---

## 1. Google Ads — ultimele 7 zile (15–21 iun. 2026)

| Metrică | Valoare | Variație vs. perioada anterioară |
|---|---|---|
| Impresii (Af.) | 333 | ↓ 93 (~ -22%) |
| Clicuri | 44 | ↑ vs. 40 (raport 21 iun) |
| CTR | **13,21%** | foarte bun (țintă 15–18%) — salt mare de la 7,59% (W24) |
| CPC mediu | 2,76 RON | ↓ de la 3,42 RON |
| Cost | 121,58 RON | ↓ 23,62 RON |
| Conversii primare | 2,00 | plat |
| Rata de conversie | 4,55% | — |
| Cost / conversie | 60,79 RON | ↓ de la 71,73 RON |
| Valoarea conversiilor | 450,00 RON | ↑ de la 300 RON (+50%) |
| ROAS (val. conv. / cost) | 3,70 | bun (de la 3,44) |
| Optimization score | **96,8%** | ↑ mare de la 72,3% (W24) |
| Buget zilnic | 19,00 RON/zi | — |
| Strategie licitare | Maximizați valoarea conversiilor | (migrarea de la Maximize Clicks deja efectuată) |
| Status difuzare | Eligibilă — fără probleme de difuzare | OK |

**Top termeni de căutare (15–21 iun, toți în grupul Camere Supraveghere):**
1. camera de luat vederi exterior — 2 clicuri / 2 af / 100% CTR / 4,19 RON
2. camere de supraveghere — 2 / 6 / 33,33% / 1,56 RON
3. montare camere supraveghere — 2 / 2 / 100% / 1,83 RON
4. montaj camere supraveghere brasov — 1 / 7 / 14,29% / 3,52 RON
5. sistem supraveghere brasov — 1 / 2 / 50% / **8,94 RON** (CPC ridicat — de urmărit)

**Comentariu pe anomalii:**
- **Pozitiv major:** CTR aproape s-a dublat (7,59% → 13,21%) și opt. score a urcat +24,5 pp (72,3% → 96,8%). Acest salt indică fie publicarea RSA-urilor noi, fie o optimizare aplicată recent. **De confirmat cauza** în secțiunea Anunțuri.
- **De urmărit:** Afișările rămân la 333 (-22% vs. perioada anterioară). Contul „cheltuie cea mai mare parte a bugetului zilnic" → difuzare limitată de buget, nu de calitate. Toți termenii de căutare provin din grupul **Camere** → diversificarea pe Alarme/Acces/Pontaj/Incendiu încă nu se vede în date.
- **Fără candidați negative keywords noi** — toți termenii sunt relevanți (camere, supraveghere, montaj, securitate).
- Recomandare activă în cont: „Adăugați imagini în anunțuri" (+~0,2% CTR). Notificare: „anunțuri de tip clic spre WhatsApp" (oportunitate de extindere conversii WhatsApp).

---

## 2. Google Analytics 4 — CSSI.ro

**Ultimele 7 zile (15–21 iun, vs. perioada anterioară):**
- Utilizatori activi: **41** (↑ 32,3%)
- Evenimente importante (key events / conversii): **7** (↑ 250,0%) — recuperare puternică după scăderile din W23–W24
- Număr total evenimente: **373** (↑ 21,1%)
- Utilizatori activi acum (real-time): 0

**Atragere de trafic pe canale (28 zile, 25 mai – 21 iun):**
| Canal | Sesiuni | % | Rată implicare | Durată medie | Ev./sesiune |
|---|---:|---:|---:|---:|---:|
| **Total** | 198 | 100% | 57,07% | 58 s | 6,33 |
| Organic Search | **86** | 43,43% | 69,77% | 1m 33s | 6,30 |
| Paid Search | 79 | 39,90% | 51,90% | 37 s | 6,95 |
| Direct | 21 | 10,61% | 38,10% | 15 s | 4,76 |
| Unassigned | 6 | 3,03% | 0% | 28 s | 4,50 |
| Cross-network | 3 | 1,52% | 100% | 35 s | 7,33 |
| Referral | 2 | 1,01% | 50% | 14 s | 5,00 |
| Organic Social | 1 | 0,51% | 0% | — | 4,00 |

➡️ **Insight cheie:** Organic Search (86 sesiuni) a **depășit** Paid Search (79) pe 28 zile și are o rată de implicare mult mai bună (69,77% vs. 51,90%). SEO devine motorul principal de trafic, nu doar suport pentru Ads.

*Notă: defalcarea individuală pe `phone_call` / `whatsapp_click` / `form_submit` / `cta_click` nu a putut fi extrasă (sub-rapoartele GA4 redirecționează către Home prin deep-link). Totalul evenimente importante 7z = 7 (↑250%). De adăugat un raport de evenimente dedicat prin UI la rularea viitoare.*

---

## 3. Google Search Console — cssi.ro

**Ultimele 7 zile (14–20 iun, lag ~2 zile):**
- Clicuri: **30**
- Afișări: **1,23K**
- CTR mediu: **2,4%**
- Poziție medie: **11,7**

**Comparativ 3 luni:** 131 clicuri · 6,06K afișări · CTR 2,2% · poziție medie 16,7.
➡️ **Trend pozitiv puternic:** poziția medie a urcat 16,7 (3 luni) → 11,7 (7 zile); clicurile și afișările cresc clar în iunie. Aproape de pragul paginii 1.

**Top 5 interogări (7 zile, după afișări):**
1. camere supraveghere — 0 clicuri / 68 afișări
2. camere supraveghere brasov — 0 / 17
3. camere de supraveghere — 0 / 16
4. **hg 301 din 2012 actualizata 2026** — 0 / 15 *(interogare informațională nouă — legislație pază/securitate)*
5. sisteme de securitate — 0 / 15

*Notă: pozițiile individuale pentru „sistem pontaj electronic", „alarma antiefractie brasov", „control acces brasov", „detectie incendiu brasov" necesită filtrare per interogare (netcaptate în vizualizarea compactă 7z).*

---

## 🚨 Alerte
1. **Volum Ads concentrat 100% pe Camere** — toți termenii de căutare cad în grupul Camere Supraveghere. Grupurile Alarme/Acces/Pontaj/Incendiu nu generează încă afișări vizibile → ~50% din keyword universe rămâne neactivat.
2. **Conversii plate la 2/săpt** în ciuda CTR dublat și ROAS bun — volumul redus de afișări (333) limitează numărul absolut de lead-uri. Bottleneck = afișări, nu calitate.
3. **CTR organic 2,4%** la poziție medie 11,7 — majoritatea queries-urilor pe pagina 2; multe afișări cu 0 clicuri (ex. „camere supraveghere" 68 af / 0 clicuri).
4. **CPC ridicat pe „sistem supraveghere brasov" (8,94 RON)** — de monitorizat dacă se repetă.
5. Nicio eroare de difuzare Ads; nicio eroare GSC Coverage detectată.

---

## ✅ Acțiuni propuse azi
1. **Confirmă sursa saltului de performanță Ads** *(context: CTR 7,59%→13,21% și opt. score 72→97 într-o săptămână; efort: 10 min; impact: înțelegi ce a funcționat ca să replici).* Deschide Campanii → Anunțuri și verifică dacă RSA-urile noi (task #17) au fost publicate și dacă grupurile Alarme/Acces/Pontaj/Incendiu difuzează acum.
2. **Decide pe task #17 (RSA-uri noi)** *(context: dacă saltul vine din RSA publicat, #17 e parțial rezolvat; dacă nu, grupurile tematice sunt tot off-air; efort: 10 min verificare + 15 min publicare dacă lipsesc; impact: deblochează difuzare pe alarme/acces/pontaj/incendiu).*
3. **Optimizează meta pe „camere supraveghere"** *(context: 68 afișări / 0 clicuri pe 7 zile — cea mai mare oportunitate organică ratată; efort: 20 min; impact: ~5–8 clicuri organice/lună gratuit).* Title cu „Brașov + Garanție + CTA telefon".

---
*Raport generat automat — monitorizare zilnică CSSI. Astăzi e LUNI → vezi și raportul strategic săptămânal W25: `weekly-strategy/CSSI-Weekly-Strategy-2026-06-22.md`.*
