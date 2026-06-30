# CSSI — Raport Zilnic de Monitorizare
**Data:** 2026-06-30 (Marți) · Cont Google Ads 666-033-6562 · cssi.ro
**Stare rulare:** ⚠️ Date LIVE doar pe Google Ads. **GA4 și GSC BLOCATE** — contul logat (cssirobv@gmail.com) primește „Lipsesc permisiuni" (GA4) și „Nu ai acces la această proprietate" (GSC) pe authuser 0 și 3. Necesită reautentificare/reacordare permisiuni manuală.

---

## TL;DR
- **Merge bine:** Eficiența rămâne sănătoasă — 2 conversii primare pe 7 zile la CPA ~57 RON, scor de optimizare 96,8%, fără probleme de difuzare, bugetul zilnic cheltuit aproape integral.
- **Nu merge:** Trend descendent pe toate metricile de volum vs. săptămâna trecută — afișări ~313 (↓ ~19%), clicuri 33 (de la 47), CTR **10,54%** (de la 12,18%, sub ținta 15–18%), CPC în creștere la 3,45 RON. Cuvântul „montaj camere supraveghere" înghite 72% din buget la CTR doar 9,62%.
- **Acțiune azi:** Reconectează GA4 + GSC (reautentificare) ca să nu pierdem 2/3 din raport, apoi taie risipa pe „montaj camere supraveghere" (revizuiește anunțul/landing sau adaugă negative pentru termenii de competiție: spyshop, do security, ecamere).

---

## 1. Google Ads — ultimele 7 zile (23–29 iun. 2026)

| Metrică | Valoare | Variație vs. perioada anterioară (16–22 iun) |
|---|---|---|
| Afișări | **~313** (derivat: clicuri/CTR) | ↓ de la 386 (~ -19%) |
| Clicuri / Interacțiuni | **33** | ↓ de la 47 |
| CTR | **10,54%** | ↓ de la 12,18% (sub ținta 15–18%) |
| CPC mediu | **3,45 RON** | ↑ de la 2,79 RON |
| Cost | **113,75 RON** | ↓ de la 131,06 RON |
| Conversii primare | **2,00** | ↓ de la 3,00 |
| Cost / conversie (CPA) | **~56,9 RON** | ↑ de la 43,69 RON |
| Valoarea conversiilor | *neextrasă acest run* | (599,67 RON la raportul precedent) |
| Optimization score | **96,8%** | plat (foarte bun) |
| Status difuzare | Eligibilă — fără probleme | OK |

**Top 5 cuvinte cheie (după cost):**
| # | Cuvânt cheie | Cost | Clicuri | CTR |
|---|---|---:|---:|---:|
| 1 | montaj camere supraveghere | **82,10 RON** | 20 | 9,62% |
| 2 | proiectare sisteme securitate | 7,38 RON | 1 | 25,00% |
| 3 | camere supraveghere Brașov | 5,02 RON | 1 | 14,29% |
| 4 | pret camere de supraveghere exterior | 3,76 RON | 3 | 10,71% |
| 5 | camere supraveghere pret | 1,77 RON | 1 | 100,00% |

**Top 5 termeni de căutare (după afișări):** supraveghere · camere supraveghere brasov · camera supraveghere · camere supraveghere · montaj camere supraveghere brasov.

**Comentariu pe anomalii:**
- **Concentrare risc de buget:** „montaj camere supraveghere" = **82,10 din 113,75 RON (72%)** din cost, dar CTR doar **9,62%** → trage CTR-ul contului în jos spre 10,54%. Este motivul principal al ratării țintei de CTR.
- **Volum în scădere a 2-a perioadă consecutiv** (afișări ~313 vs 386). Bottleneck = volum, nu calitate; contul tot pe nișa Camere.
- **Candidați negative keywords noi (termeni de competiție / brand / EN):** `spy shop`, `spyshop camera`, `do security brasov`, `d o security`, `ecamere`, `e camera ro`, `hik connect`, `house alarm`. Merită revizuiți — dacă nu vizezi intenția de comparație, sunt risipă de buget.
- Restul termenilor de căutare sunt relevanți (camere supraveghere + variații locale).

---

## 2. Google Analytics 4 — CSSI.ro
⚠️ **BLOCAT: GA4 — necesită reautentificare/permisiuni manuale.** Property p525787706 returnează „Lipsesc permisiuni. Nu aveți acces la cont sau la proprietate" cu contul cssirobv@gmail.com (testat authuser=0 și authuser=3). La raportul din 23 iun. accesul mergea pe authuser=3 — deci e o regresie de sesiune/permisiuni apărută între timp.
*Acțiune necesară (MIHAI):* deschide manual GA4, confirmă că ești logat cu contul care are acces la property și reacordă accesul dacă a fost revocat.

---

## 3. Google Search Console — cssi.ro
⚠️ **BLOCAT: GSC — necesită reautentificare/permisiuni manuale.** Proprietatea `sc-domain:cssi.ro` redirecționează la „Hopa, nu ai acces la această proprietate" (not-verified) cu contul curent (authuser=0 și 3).
*Acțiune necesară (MIHAI):* verifică sub ce cont Google e verificată proprietatea și loghează-te cu acela, sau readaugă cssirobv@gmail.com ca utilizator în Search Console.

---

## 🚨 Alerte
1. **2/3 platforme inaccesibile (GA4 + GSC).** Raportul de azi acoperă doar Google Ads. Regresie față de 23 iun. — prioritate #1 de remediat.
2. **CTR sub țintă și în scădere** (10,54% vs 12,18%; țintă 15–18%). Cauză directă: „montaj camere supraveghere" la 9,62% CTR consumând 72% din buget.
3. **Volum descendent a 2-a săptămână** (afișări ~313, clicuri 33, conversii 2 — toate în jos vs. săptămâna anterioară).
4. **CPA în creștere** (~57 RON vs 43,69 RON) din cauza scăderii conversiilor + creșterii CPC.
5. **Candidați negative keywords** (competiție/brand): spyshop, do security, ecamere, hik connect, house alarm — de revizuit.
6. Niciun semnal de problemă de difuzare în Ads (scor optimizare 96,8%).

---

## ✅ Acțiuni propuse azi
1. **Reconectează GA4 + GSC** *(context: ambele blocate, pierdem 2/3 din raport; efort: 10 min; impact: critic — fără ele nu putem monitoriza SEO organic și conversiile pe site).* Loghează-te cu contul corect / reacordă permisiuni.
2. **Optimizează „montaj camere supraveghere"** *(context: 72% din buget la CTR 9,62%, trage contul sub țintă; efort: 20–30 min; impact: ridicarea CTR-ului de cont spre 13–15% + buget eliberat pentru alte grupuri).* Revizuiește textul anunțului (titlu cu „Brașov + montaj profesional + garanție") și landing-ul; testează ca frază/exact match.
3. **Adaugă negative keywords de competiție** *(context: spyshop, do security, ecamere, hik connect, house alarm apar în termeni; efort: 5 min; impact: reduce risipa de buget, crește relevanța → CTR).* Doar dacă nu vizezi intenționat comparațiile.

---
*Raport generat automat — monitorizare zilnică CSSI. Astăzi e MARȚI → fără raport strategic săptămânal (acela se generează lunea).*
