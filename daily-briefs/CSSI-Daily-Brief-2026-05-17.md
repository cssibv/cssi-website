# CSSI — Raport zilnic de monitorizare

**Data:** 17 mai 2026 (Duminică)
**Perioadă analizată:** 10 – 16 mai 2026 (ultimele 7 zile)
**Sursă date:** Google Ads, GA4 (p525787706), GSC (sc-domain:cssi.ro)

---

## TL;DR

- **Ce merge bine:** Trafic GA4 în creștere consistentă (+28,6 % utilizatori activi, +35,3 % utilizatori noi, organic search dominant cu 60 % din canale). Cuvântul cheie principal `montaj camere supraveghere` produce CTR excelent de 10,44 % și consumă 75 % din buget.
- **Ce nu merge:** GSC arată 0 clickuri pe top 10 interogări (poziție medie 17,4 — sub fold). Pagina `/pontaj-electronic` a pierdut 51 % din afișări. 7 cuvinte cheie Ads nu mai produc conversii recente. Pagina `/servicii` și paginile noi (`/automatizari-porti`, `/bariere-auto`) au crescut spectaculos dar de la o bază mică.
- **Acțiune recomandată azi:** Investighează degradarea `/pontaj-electronic` în GSC (verifică conținut, title, posibile modificări recente) — este o pagină critică pentru obiectivul „rang 12-15 pe pontaj electronic" la 60 zile.

---

## 1. Google Ads (10 – 16 mai 2026)

### Performanță campanie unică „CSSI - Servicii Securitate"

| Metric | Valoare | WoW vs. săpt. anterioară |
|---|---|---|
| Cost total 7z | 124,90 RON | **-20,33 RON (-14,0 %)** |
| CTR mediu | **7,97 %** | (sub țintă 15-18 %) |
| Distribuție device (cost) | Mobile 91,4 % / Desktop 8,6 % / Tablet 0 % | — |
| Distribuție device (clickuri) | Mobile 92,9 % / Desktop 7,1 % | — |
| Buget rezidual cont | 297,90 RON | Următoarea plată 1 iun. 2026 |
| Scor de optimizare | 70,4 % | — |

### Top 5 cuvinte cheie (după cost)

| Cuvânt cheie | Cost 7z | CTR | Comentariu |
|---|---|---|---|
| `montaj camere supraveghere` | 93,49 RON | **10,44 %** | Workhorse — consumă 75 % din buget cu cel mai bun CTR |
| `proiectare sisteme securitate` | 7,40 RON | 5,26 % | OK |
| `pret camere de supraveghere exterior` | 2,25 RON | 1,64 % | ⚠️ CTR <5 % — candidat optimizare |
| `kit camere supraveghere wireless exterior pret` | 1,86 RON | 3,70 % | ⚠️ CTR <5 % — candidat optimizare |
| `control acces` | 0,00 RON | 0,00 % | Fără afișări — investighează |

### Top 5 căutări care au declanșat anunțuri (sortate după afișări)

1. `camere de supraveghere` (generic)
2. `camere supraveghere` (generic)
3. `camere supraveghere brasov` ✅ (geo-relevant)
4. `supraveghere video` (generic)
5. `camera supraveghere` (generic)

**Candidați negative keywords detectați:**
- `altex camere supraveghere` — competitor retailer, nu suntem distribuitor
- `hikvision interfon`, `dahua camera`, `hdcvi digital video recorder`, `dsc pk5501` — căutări pe modele/branduri specifice, nu pe servicii de instalare
- `controlos de acesso` (portugheză), `home security cameras`, `home security` (EN) — limbi/regiuni greșite

### Status urmărire conversii

- ✅ **4 cuvinte cheie** înregistrează conversii activ
- ⚠️ **7 cuvinte cheie** nu au conversii recente (>30 zile) — verifică dacă merită păstrate
- Lead funnel total: **4 interacțiuni** în 7 zile

### Tendință de căutare (sectorial RO)

- Volum căutări `+12 %` față de perioada anterioară
- Clickurile noastre `+29 %` — captăm cota suplimentară din creșterea sectorială

### Recomandare Google (atenție)
Google Ads recomandă migrare la **„Maximize Conversion Value"** (+17,4 % scor optimizare). **Nu aplica încă** — strategia ta actuală cere 15+ conversii înainte de migrare, iar contul tocmai a trecut prin 4 interacțiuni în 7 zile (insuficient).

---

## 2. GA4 — Ultimele 7 zile (10 – 16 mai 2026)

| Metric | Valoare | vs. perioadă anterioară |
|---|---|---|
| Evenimente totale | 310 | +6,9 % |
| Utilizatori activi | 27 | **+28,6 %** |
| Sesiuni | 38 | +2,7 % |
| Utilizatori noi | 23 | **+35,3 %** |
| Activi 30z | 83 | +5,1 % |
| Activi 1z | 5 | +150,0 % |

### Top pagini (după afișări — creștere WoW)

| Pagină | Δ WoW |
|---|---|
| Servicii CSSI (`/servicii`) | **+180 %** 🚀 |
| Automatizări Porți | **+200 %** 🚀 |
| Bariere Auto | **+200 %** 🚀 |
| Detecție Incendiu ISU | +50 % |
| Homepage CSSI | +37 % |
| Camere Supraveghere Brașov | +31 % |
| Despre CSSI | 0 % |

### Top surse trafic (sesiuni)

| Sursă | Pondere / Δ |
|---|---|
| **Organic Search** | dominantă (60 % share) |
| **Paid Search** (Google Ads) | 25 % |
| Direct | 14,3 % |
| Cross-network | crescut +500 % (volum mic) |

### Evenimente cheie

| Eveniment | Volum | Δ |
|---|---|---|
| page_view | 122 | +1,7 % |
| user_engagement | 102 | +10,9 % |
| first_visit | (n/a) | +35,3 % |
| click | (n/a) | +100 % |
| form_start | (n/a) | +50 % |
| session_start | (n/a) | +2,7 % |
| scroll | (n/a) | 0 % |

⚠️ **Lipsesc din vizualizare evenimentele `phone_call`, `whatsapp_click`, `form_submit`** — verifică în Reports → Engagement → Events dacă acestea sunt configurate și raportate corect.

### Țări

- Romania: leader (+20 %)
- United States (+100 %) și Italy — probabil bot/referral, verifică
- Platform: 100 % Web (-22,2 % — posibil scădere mobile app inexistentă, ignorabil)

---

## 3. Google Search Console — Ultimele 7 zile (8 – 14 mai 2026)

| Metric | Valoare |
|---|---|
| Total clickuri | **9** |
| Total afișări | **394** |
| CTR mediu | 2,3 % |
| Poziție medie | **17,4** (sub fold) |
| Total clickuri 30z (din overview) | 56 |
| Pagini indexate | 40 |
| Pagini neindexate | 38 ⚠️ |

### Top interogări (toate cu 0 clickuri în 7z)

| Interogare | Afișări | Status țintă 60z |
|---|---|---|
| camere supraveghere | 51 | — |
| **pontaj electronic** | **15** | 🎯 Țintă: rang 12-15 |
| camere de supraveghere | 8 | — |
| sistem pontaj | 8 | — |
| sisteme de securitate brasov | 7 | — |
| sistem pontaj electronic | 7 | 🎯 Țintă: rang 12-15 |
| sisteme de pontaj | 6 | — |
| camere supraveghere brasov | 5 | — |
| sisteme de pontaj electronic | 5 | — |
| camere de supraveghere brasov | 4 | — |

### Insights GSC

- ✅ `/blog/camere-supraveghere-gdpr-ghid-complet` — afișări **+843 %** (oportunitate de promovat intern)
- ⚠️ `/pontaj-electronic` — afișări **-51 %** (degradare critică pe pagină prioritară)
- ⚠️ **38 pagini neindexate** vs. 40 indexate — necesită audit Coverage
- ⚠️ **15 erori de date structurate** care nu pot fi analizate

---

## 🚨 Alerte

1. **CRITICĂ — `/pontaj-electronic` în declin (-51 % afișări).** Pagina este țintă strategică pentru obiectivul 60z (rang 12-15). Verifică imediat: title, meta description, conținut, schimbări recente în deploy.
2. **CTR mediu Ads 7,97 % este sub țintă** (15-18 %). Cauza: 4 din top 5 cuvinte cheie au CTR <6 %. Doar `montaj camere supraveghere` performează (10,44 %).
3. **0 clickuri pe top 10 interogări GSC** — toate la poziții 11-30, sub primul ecran. Necesită on-page optimization pentru a urca în pozițiile 1-10.
4. **38 pagini neindexate Google** — posibilă pierdere de potențial organic semnificativă.
5. **15 erori de date structurate (Schema.org)** — afectează rich snippets în SERP.
6. **Conversii GA4 nu sunt vizibile clar** — verifică dacă `phone_call`, `whatsapp_click`, `form_submit` au volum > 0 (cele 4 conv. înregistrate de Ads sugerează că da).
7. **7 cuvinte cheie Ads fără conversii recente** — candidat de oprire/refactor.

---

## ✅ Acțiuni propuse pentru azi (3 sugestii prioritare)

### 🔴 ACȚIUNE 1 — Audit `/pontaj-electronic` (efort 30 min, impact MARE)
**Context:** Pagina a pierdut 51 % din afișări în GSC; este pivot pentru obiectivul „rang 12-15 pe `pontaj electronic`" la 60 zile. Cu 15 afișări în 7z pe interogarea principală, suntem departe de țintă.
**Pași:**
1. Verifică în GSC → URL Inspection: starea de indexare a `https://cssi.ro/pontaj-electronic`
2. Reverifică title tag (target: include „Pontaj Electronic Brașov | CSSI") și meta description
3. Verifică dacă există deploy recent care a modificat structura H1/H2 sau conținutul
4. Verifică Schema.org: pagina trebuie să aibă Product/Service markup
**Impact așteptat:** Refacere poziție medie pe pontaj electronic spre rang 15-20 în 14-21 zile.

### 🟡 ACȚIUNE 2 — Adaugă 8 negative keywords noi (efort 10 min, impact MEDIU)
**Context:** Bugetul curent este consumat de `montaj camere supraveghere` (75 %), dar avem zgomot de la căutări irelevante.
**Pași:** Adaugă în lista de negative keywords:
- `altex` (retailer competitor)
- `hikvision` (brand pe care nu îl vindem direct)
- `dahua` (brand pe care nu îl vindem direct)
- `hdcvi`, `dsc pk5501`, `7100 series` (modele specifice)
- `home security` (limbă EN, audiență greșită)
- `controlos de acesso` (portugheză)
**Impact așteptat:** Reducere cost wasted ~5-10 RON/săptămână, CTR campanie +1-2 puncte.

### 🟢 ACȚIUNE 3 — Promovează intern articolul GDPR (efort 15 min, impact MEDIU)
**Context:** `/blog/camere-supraveghere-gdpr-ghid-complet` are afișări +843 % — semnal puternic de interes search.
**Pași:**
1. Adaugă link intern din pagina `/camere-supraveghere-brasov` către articol
2. Adaugă CTA în articol către pagina de servicii camere
3. Distribuie pe social media (LinkedIn CSSI) cu titlu „Ghid GDPR camere supraveghere"
**Impact așteptat:** Capturare trafic organic în creștere către pagini de conversie.

---

## 📋 Verificare task-uri pending (status check)

- **#15 Cleanup keywords Low search volume** — **NU încă.** Așteptăm date solide pe minim 30 zile / 15+ conversii înainte de cleanup.
- **#16 Restructurare în 3 Ad Groups tematice** — **NU încă.** Bugetul actual de 60 EUR/lună este prea mic pentru 3 grupuri separate; ar dilua semnalul de învățare.
- **#17 Creare 3 RSA-uri noi** — **NU încă.** Dependent de #16.

**Recomandare bidding strategy:** **NU migra la Maximize Conversions** încă. Contul are doar 4 conversii în 7 zile; pragul minim pentru tranziție este 15+ conversii acumulate pe ultimii 30 zile.

---

*Raport generat automat pe 17 mai 2026 (Duminică). Următorul raport: 18 mai 2026 + raport săptămânal strategic (Luni).*
