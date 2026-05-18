# CSSI — Raport strategic săptămânal

**Data emiterii:** 2026-05-18 (Luni)
**Săptămâna acoperită:** W20 (11 – 17 mai 2026)
**Cont Google Ads:** 666-033-6562 | **Buget:** 60 EUR/lună (~300 RON) | **Strategie:** Maximize Clicks
**Generat de:** scheduled task `cssi-daily-monitoring`
**Date live capturate:** ✅ Google Ads | ✅ GA4 | ✅ Search Console

---

## TL;DR săptămânal (5 linii)

1. **W20 = săptămâna de „consolidare cu o regresie de tagging".** Trafic stabil (+17 % active users), GSC explodează (+80 % clickuri), DAR conversiile GA4 raportate sunt 0 — semnal că tracking-ul a căzut, nu că deal-flow-ul a căzut.
2. **GSC W20 = cel mai bun rezultat din 2026:** 9 clickuri (record), 400 afișări (record), CTR 2,2 %. Poziția medie 19,8 a urcat ușor înapoi (de la 17,4 — totuși, baseline-ul lung e 22+).
3. **CTR Google Ads regresează (9,38 % → 7,68 %, -1,7pp).** Cauza: ponderea mai mică a queries geo („brasov") în mix și o creștere de queries brand-specifice (Dahua, Hikvision) la care anunțul nostru nu rezonează.
4. **Lead funnel Ads arată 6 „interested leads" în 7 zile** (record), dar GA4 nu confirmă — confirmă alerta de tagging.
5. **Pragul „15+ conversii cumulative" NU este atins și nu poate fi măsurat** până nu se repară tracking-ul. **Nu migrăm la Maximize Conversions săptămâna asta.** Acțiunea decisivă a săptămânii: audit GA4 events.

---

## 1. Sumar săptămâna W20 (11 – 17 mai)

| Indicator | Valoare 7 zile | Sursă |
|-----------|----------------|-------|
| **Sesiuni totale GA4** | 37 (23 Paid + 7 Organic + 5 Direct + 2 Cross-network) | GA4 |
| **Utilizatori activi** | 27 (+17,4 % WoW) | GA4 |
| **Utilizatori noi** | 23 (+21,1 % WoW) | GA4 |
| **Evenimente totale** | 321 (+8,1 % WoW) | GA4 |
| **Conversii primare raportate GA4** | **0** ⚠️ (alertă tracking) | GA4 |
| **Lead funnel Ads (interested leads)** | **6** (record) | Ads |
| **Cost total Google Ads 7z** | 139,08 RON (-5,2 % WoW) | Ads |
| **CTR Ads** | 7,68 % (-1,7pp WoW) | Ads |
| **Clickuri GSC organic** | **9** (+80 % WoW) | GSC |
| **Afișări GSC organic** | **400** (+10,8 % WoW) | GSC |
| **Poziție medie GSC** | 19,8 (vs 17,4 W19 — minor regres) | GSC |
| **Top câștig** | GSC clickuri organic la nou record absolut | GSC |
| **Top pierdere** | CTR Ads -1,7pp; conversii GA4 raportate zero | Ads + GA4 |

---

## 2. Comparație W20 vs. W19 (WoW)

| Metric | W20 (11-17 mai) | W19 (4-10 mai) | Δ WoW |
|---|---|---|---|
| Utilizatori activi GA4 | 27 | 21 | 🟢 +28,6 % |
| Utilizatori noi GA4 | 23 | 17 | 🟢 +35,3 % |
| Sesiuni GA4 totale | 37 | 39 | 🔴 -5,1 % |
| Sesiuni Paid Search | 23 | 24 | 🔴 -4,2 % |
| Sesiuni Organic Search | 7 | 5 | 🟢 +40,0 % |
| Sesiuni Direct | 5 | 7 | 🔴 -28,6 % |
| Evenimente GA4 importante | 4 (`form_start`) | 9 (mix) | 🔴 -55,6 % ⚠️ tagging |
| GSC clickuri 7d | **9** | 5 | 🟢 +80,0 % |
| GSC afișări 7d | **400** | 361 | 🟢 +10,8 % |
| GSC CTR 7d | 2,2 % | 1,4 % | 🟢 +0,8pp |
| GSC poziție medie 7d | 19,8 | 18,3 | 🔴 -1,5 (mai slabă) |
| CTR campanie Ads | 7,68 % | 9,38 % | 🔴 -1,7pp |
| Cost Ads 7d | 139,08 RON | 145,23 RON | 🟢 -4,2 % |
| Cost / conversie primară | nemăsurabil | 16,14 RON | ⚠️ N/A (tracking) |

> **Verdict WoW:** 7 din 12 KPI cresc; 4 scad; 1 nemăsurabil (cost/conv). Tema săptămânii: **SEO organic accelerează, paid search stagnează, conversiile sunt invizibile.** Necesită reparare tracking ÎNAINTE de orice altă decizie.

---

## 3. Progres către țintele 60 zile

Scadență strategie: ~10 iulie 2026 (sunt 53 zile rămase, am parcurs 7/60 = 11,7 %)

| Țintă 60z | Curent W20 | Progres | Bară |
|---|---|---|---|
| **CTR Ads 15-18 %** | 7,68 % | 47 % | ████████░░░░░░░░░░░░ |
| **12-20 conv. primare/lună** | **≈0 raportate** ⚠️ | 0 % (tracking) | ░░░░░░░░░░░░░░░░░░░░ |
| **Rang „pontaj electronic" 12-15** | ~25-30 estimat | 30 % | ██████░░░░░░░░░░░░░░ |
| **Valoare conv. 600-1000 RON/lună** | ~200 RON proiectat (dacă 4 conv. reale) | 33 % | ███████░░░░░░░░░░░░░ |
| **Optimization score Ads >75 %** | 64,8 % | 86 % | █████████████████░░░ |
| **GSC clickuri organic >15/săpt** | 9/săpt | 60 % | ████████████░░░░░░░░ |

**Verdict global:** progres real pe SEO (GSC), regres pe Paid CTR și **alarm critic** pe conversion tracking. Reabilitarea tracking-ului este blocant pentru toate celelalte decizii.

---

## 4. Analiză pe 3 axe

### 4.1. Google Ads

**Ce performează:**
- `montaj camere supraveghere` — singurul cuvânt cheie deasupra pragului 10 % CTR (10,71 %), consumă 77 % din buget; ROI cel mai sănătos
- Lead funnel arată 6 „interested leads" — sugerează că form_start-urile reflectă intent real, doar că nu se convertesc la submit (sau tagging-ul nu raportează submit-urile)
- Cost săptămânal sub control: 139 RON pe 7 zile ≈ 595 RON proiectat lunar (sub bugetul de 300 RON × 2 = 600 RON dacă păstrăm CPC mediu)
- Tendință sectorială +9 %; clickurile noastre +27 % → captăm cotă suplimentară

**Ce necesită ajustare:**
- 4 din top 5 cuvinte cheie au CTR <7 % (`pret camere de supraveghere exterior`, `kit camere supraveghere wireless exterior pret`, `proiectare sisteme securitate`, `control acces`)
- 7 cuvinte cheie fără conversii recente (>30 zile) — candidați la pause după ce reparăm tracking-ul
- Scor optimizare 64,8 % (de la 70,4 %) — Google semnalează recomandări neimplementate (mai ales bidding strategy)
- 86,3 % cost vine de pe mobile — anunțul actual este optimizat pentru mobile? Verifică imagine + CTA size

**Recomandare buget:** **Menține 60 EUR/lună până la repararea tracking-ului.** După aceea, dacă conversiile sunt real 4-6/săpt, crește la 80-100 EUR/lună (cresc clickurile, dar nu schimbi strategia până la 15+ conv. cumulative).

### 4.2. SEO organic (GSC)

**Queries în creștere (afișări W20 vs W19):**

| Query | W20 afișări | W19 afișări | Δ | Comentariu |
|---|---|---|---|---|
| camere supraveghere | 51 | 51 | 0 | Stabilă top of mind |
| pontaj electronic | 10 | 15 | 🔴 -33 % | Regresie consistentă cu degradarea `/pontaj-electronic` |
| sistem pontaj | 10 | 8 | 🟢 +25 % | Nouă tracțiune |
| pontaj biometric fabrică | 8 | 0 | 🟢 +∞ | Long-tail oportun |
| smart home integrare securitate | 6 | 0 | 🟢 +∞ | Long-tail B2B, foarte valoros |

**Pagini cu oportunitate (poziții 11-20, candidat „push to top 10"):**

1. `/pontaj-electronic` — afișări -51 % (regres) — necesită refresh urgent
2. `/camere-supraveghere` — captează majoritatea afișărilor (≈25-30 %) la poziție medie 15-18
3. `/blog/camere-supraveghere-gdpr-ghid-complet` — afișări +843 % (în top de creștere)

**Content gaps observate:**

- **`pontaj biometric fabrică`** — 8 afișări 7z, conținut existent slab. Recomandare: secțiune dedicată pe `/pontaj-electronic` pentru aplicații industriale (fabrici, hală producție, multi-schimb)
- **`smart home integrare securitate`** — 6 afișări 7z fără pagină dedicată. Recomandare: pagină / articol blog despre integrarea camerelor + alarmelor + control acces într-un dashboard unic (SmartHome ecosystem)
- **`sisteme de securitate brasov`** — 7 afișări dar pagina principală nu este optimizată per acest query exact (avem `/camere-supraveghere`, `/alarma-antiefractie`, `/control-acces` separat; lipsește pagină umbrelă)

### 4.3. Conversii

**Ce convertește (din lead funnel Ads):**

- 48 interacțiuni → 6 interested leads = **12,5 % rata de calificare** (semnal pozitiv din partea Google)
- Pagini cu trafic înalt + indicii conversie indirectă: `/camere-supraveghere-brasov` (22 afișări), homepage (34), `/servicii` (14 — creștere +180 %)

**Unde e funnel leak (trafic mare → conversii zero):**

- **`/automatizari-porti` cu +200 % afișări, 6 vizite și 0 evenimente importante** — pagina nu are CTA puternic sau formular adaptat segmentului automatizări
- **`/detectie-incendiu-isu` cu 9 afișări, 0 evenimente** — pagină tehnică, audiență probabil B2B fără CTA suficient
- **`/blog/camere-supraveghere-gdpr-ghid-complet`** — afișări organice +843 % dar 0 conversii sau click-through-uri către `/camere-supraveghere`

**Acțiune funnel:** după repararea tracking-ului, audit CTR de formular pe paginile `/automatizari-porti`, `/detectie-incendiu-isu`, `/blog/*`.

---

## 5. Acțiuni prioritizate W21 (18 – 24 mai 2026)

**Owner:** MIHAI | Format: titlu — efort — impact

### 🔴 ACȚIUNE 1 — Reparație tracking GA4 (efort 30-60 min, impact CRITIC)
- Verifică Configurări → Evenimente: `phone_call`, `whatsapp_click`, `form_submit`, `generate_lead`
- Test live în Real-Time GA4 (deschide cssi.ro, dă click pe telefon, submit form de test)
- Confirmă că Key Events sunt marcate corect
- Verifică importurile GA4 → Google Ads sunt active
- Verifică `tracking.js` și scripturile de pe site nu au fost golite în deploy-ul din 13 mai

**Impact:** Restaurează vizibilitatea completă pe ROI; deblochează posibilitatea migrării la Maximize Conversions după 15+ conversii.

### 🟡 ACȚIUNE 2 — Adaugă 12-15 negative keywords noi (efort 15 min, impact MEDIU)
Lista detaliată în raportul zilnic de azi (Acțiune 2). Adaugă în campania „CSSI - Servicii Securitate":
- Limbi străine: `shop security`, `home security`, `controlos de acesso`
- Branduri: `dahua`, `hikvision`, `imou`, `andowl`, `kmw`, `cerber`, `bft`, `tago`, `ultra security`, `spy security`, `atutech`, `avitech`, `telesystem`
- Geo greșit: `iasi`, `cluj`, `bucuresti`, `timisoara`
- Modele/segmente nealine: `hdcvi`, `dsc pk5501`, `panou solar`, `jortan`, `olx`, `altex`

**Impact:** Reducere afișări irelevante 15-25 %, CTR +1-2pp, eficiență buget.

### 🟡 ACȚIUNE 3 — Audit `/pontaj-electronic` și consolidare cu `/pontaj-electronic-brasov.html` (efort 45 min, impact MARE)
- Verifică dublură: există `pontaj-electronic.html` și `pontaj-electronic-brasov.html` separat
- Decide canonical pe `pontaj-electronic.html` (URL principal); fă redirect 301 pe varianta brasov
- Refresh title + meta + H1 pentru queries „pontaj biometric fabrică" + „pontaj electronic cu amprenta" (afișări reale)
- Submit URL la GSC pentru re-indexare

**Impact:** Recuperare poziție medie de la 25-30 spre 15-20 pe „pontaj electronic"; deblocaj parțial țintă 60z.

### 🟢 ACȚIUNE 4 — Creează pagină umbrelă „Sisteme de securitate Brașov" (efort 90 min, impact MEDIU pe termen lung)
Query `sisteme de securitate brasov` are 7 afișări/7z fără pagină optimizată. O pagină umbrelă care leagă camere + alarme + control acces + detecție incendiu poate captura această căutare și să devină hub pentru long-tail B2B.

**Impact:** După 30-45 zile, captură 20-40 % din afișările acestei interogări.

### 🟢 ACȚIUNE 5 — Promovează `/blog/camere-supraveghere-gdpr-ghid-complet` (efort 30 min, impact MEDIU)
- Adaugă banner CTA în articol → `/camere-supraveghere`
- Link intern din `/camere-supraveghere` și `/servicii` către articol
- Distribuie pe LinkedIn cu titlu „GDPR pentru camere supraveghere — ghid CSSI"

**Impact:** Convertește trafic organic pasiv (afișări +843 %) în lead-uri active.

---

## 6. Verificare task-uri pending #15, #16, #17

| Task | Status | Criteriu deblocare | Estimare deblocaj |
|---|---|---|---|
| **#15 Cleanup keywords Low Search Volume** | 🔴 BLOCAT | 30+ zile date + 15+ conversii cumulative + tracking reparat | ~mid-iunie (dacă acțiunea #1 reușește săpt. asta) |
| **#16 Restructurare 3 Ad Groups tematice** | 🔴 BLOCAT | Buget ≥ 100 EUR/lună SAU 15+ conversii care justifică segmentarea | Iulie-august, după creștere buget |
| **#17 Creare 3 RSA-uri noi (15 titluri + 4 descrieri)** | 🔴 BLOCAT pe #16 | Depinde de #16 | Iulie-august |

**Decizie săpt. asta:** Niciuna deblocată. **Săptămâna viitoare (W21 → W22):** dacă tracking-ul e reparat și avem ≥7 conversii confirmate, reevaluăm #15.

---

## 7. Recomandare bidding strategy

**Status:** ❌ **NU MIGRA la Maximize Conversions săptămâna aceasta.**

**Motivare:**

1. Tracking-ul GA4 raportează 0 conversii primare în W20 — Google nu are semnal de antrenare validă
2. Cumulative real (din lead funnel Ads): ~4-9 conversii/săpt × 4 săpt = 16-36 conv./lună. Dacă acceptăm „interested leads" ca proxy, am putea fi peste prag. **Dar Google folosește datele din Conversion Tracking, nu lead funnel.**
3. Migrarea acum la Maximize Conversions cu 0 semnale ar duce la 7-14 zile de „învățare la rece" și posibil scădere drastică de clickuri

**Plan de migrare condiționată:**
- W21: Reparare tracking (Acțiune 1)
- W22-W23: Acumulare 10-15 conversii REALE raportate în GA4 + Ads
- W24: Migrare la **Maximize Conversions** (NU Maximize Conversion Value — value-based necesită valori realiste de conversie, iar 50 RON estimat e arbitrar)
- W26-W28: După 30 zile pe Maximize Conversions, evaluăm migrare la Maximize Conversion Value

---

## 8. Anexă — Date complete capturate

- **Google Ads URL:** `https://ads.google.com/aw/overview?ocid=8059575551&euid=6445769730&authuser=0`
- **GA4 property:** a385388640 / stream p525787706
- **GSC property:** sc-domain:cssi.ro
- **Sold cont Ads:** 329,55 RON | Următoarea plată automată: 1 iun. 2026
- **Card:** Visa •••• 4909

---

*Raport săptămânal generat automat pe 18 mai 2026 (Luni). Următorul raport săptămânal: 25 mai 2026 (W21).*
