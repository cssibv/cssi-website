# CSSI — Raport zilnic de monitorizare

**Data:** 18 mai 2026 (Luni)
**Perioadă analizată:** 11 – 17 mai 2026 (ultimele 7 zile)
**Sursă date:** Google Ads (666-033-6562), GA4 (p525787706), GSC (sc-domain:cssi.ro)
**Generat de:** scheduled task `cssi-daily-monitoring`
**Date live capturate:** ✅ Google Ads | ✅ GA4 | ✅ Search Console

---

## TL;DR (3 linii)

- **Ce merge bine:** GSC arată **+80% clickuri** WoW (9 vs 5) și **+10,8% afișări** (400 vs 361). Lead funnel Google Ads a generat **6 clienți potențiali interesați** în 7 zile — progres clar față de săptămâna trecută. Trafic Paid Search continuă să domine (62% din sesiuni).
- **Ce nu merge:** **CTR Ads a scăzut la 7,68%** (de la 9,38% săptămâna trecută, -1,7pp) — sub țintă 15-18%. GA4 arată **doar `form_start` (4) fără `form_submit`/`phone_call`/`whatsapp_click`** — funnel leak sau pierdere tagging. Poziția medie GSC s-a deteriorat (18,3 → **19,8**, -1,5).
- **Acțiune recomandată azi:** **Verifică imediat în GA4 dacă evenimentele `phone_call`, `whatsapp_click`, `form_submit`, `generate_lead` mai sunt activ raportate** (apar 0 în raportul de azi vs. 9 conversii săptămâna trecută). Posibilă regresie de tracking după ultimul deploy.

---

## 1. Google Ads (11 – 17 mai 2026)

### Performanță campanie unică „CSSI - Servicii Securitate"

| Metric | Valoare | WoW vs. W19 |
|---|---|---|
| Cost total 7z | **139,08 RON** | -7,65 RON (-5,22 %) |
| Clickuri | 48 | — |
| Afișări | 625 | — |
| CPC mediu | 2,90 RON | — |
| **CTR mediu** | **7,68 %** | 🔴 -1,7pp (de la 9,38 %) — **sub țintă 15-18 %** |
| Scor optimizare | 64,8 % | 🔴 -5,6pp (de la 70,4 %) |
| Sold cont | 329,55 RON | Următoarea plată automată: 1 iun. 2026 |
| Distribuție device (cost) | Mobile 86,3 % / Desktop 13,7 % / Tablet 0 % | — |
| Distribuție device (clickuri) | Mobile 85,4 % / Desktop 14,6 % | — |

### Top 5 cuvinte cheie (după cost)

| Cuvânt cheie | Cost 7z | Clickuri | CTR | Comentariu |
|---|---|---|---|---|
| `montaj camere supraveghere` | **107,44 RON** | 36 | **10,71 %** | 🟢 Workhorse — 77 % din buget, singurul deasupra pragului 10 % |
| `proiectare sisteme securitate` | 7,40 RON | 1 | 6,25 % | OK |
| `pret camere de supraveghere exterior` | 2,48 RON | 2 | **2,13 %** | ⚠️ CTR <5 % — candidat optimizare |
| `kit camere supraveghere wireless exterior pret` | 1,86 RON | 1 | **2,13 %** | ⚠️ CTR <5 % — candidat optimizare |
| `control acces` | 0,00 RON | 0 | 0,00 % | ⚠️ Fără afișări — verifică Low Search Volume |

### Top 10 căutări detectate (sortate după afișări)

1. `camere de supraveghere` (generic)
2. `camere supraveghere` (generic)
3. `camere supraveghere brasov` ✅ (geo-relevant)
4. `camera supraveghere` (generic)
5. `camere de supraveghere brasov` ✅ (geo-relevant)
6. `supraveghere video` (generic)
7. `camere de luat vederi` (generic)
8. `shop security` ⚠️ (EN, audiență greșită)
9. `camera ip wireless exterior dahua dh p5ae pv 1620p 5mp` ⚠️ (model brand)
10. `sistem supraveghere video` (generic)

**Candidați NOI negative keywords detectați (în plus față de cei 31 deja activi):**
- `shop security`, `home security`, `home security system` (EN)
- `controlos de acesso` (portugheză)
- `dahua`, `hikvision`, `imou`, `andowl` (branduri specifice)
- `hdcvi`, `dvr hikvision`, `7100 series`, `dsc pk5501` (modele)
- `olx camere supraveghere` (marketplace competitor)
- `montaj camere supraveghere iasi` ⚠️ (geo greșit)
- `cerber`, `kmw systems`, `telesystem`, `atutech`, `avitech`, `bft`, `tago`, `ultra security`, `spy security` (competitori sau branduri terțe)
- `instalare camera jortan pe telefon` (suport tehnic, nu vânzare)
- `cartela 4g pentru camera de supraveghere`, `ce cartela trebuie la camera de supraveghere` (DIY)
- `yala cu cod` (produs nealine cu serviciile noastre)

### Lead funnel (Google Ads)

| Etapă | 7 zile |
|---|---|
| Interacțiuni | 48 |
| Clienți potențiali interesați | **6,00** 🟢 (creștere față de săpt. trecută) |
| Clienți potențiali calificați | — |
| Clienți potențiali convertiți | — |

### Status urmărire conversii

- ✅ **4 cuvinte cheie** înregistrează conversii activ
- ⚠️ **7 cuvinte cheie** fără conversii recente (>30 zile)
- 🟢 0 etichete inactive, 0 neverificate

### Tendință de căutare (sectorial RO)

- Volum căutări: **+9 %** față de perioada anterioară
- Clickurile noastre: **+27 %** — captăm o cotă suplimentară din creșterea sectorială

### Recomandare Google (atenție)
Google Ads recomandă din nou migrarea la **„Maximize Conversion Value"** (+23 %). **NU aplica încă** — pragul minim este 15+ conversii înregistrate; suntem la ~4-9 conversii/7 zile, iar tagging-ul GA4 trebuie reconfirmat (vezi alerta GA4 mai jos).

---

## 2. GA4 — Ultimele 7 zile (11 – 17 mai 2026)

| Metric | Valoare | vs. perioadă anterioară |
|---|---|---|
| Evenimente totale | 321 | 🟢 +8,1 % |
| Utilizatori activi | 27 | 🟢 +17,4 % |
| Sesiuni | 37 | 🟢 +7,5 % |
| Utilizatori noi | 23 | 🟢 +21,1 % |
| Activi 30z | 84 | +5,0 % |
| Activi 1z | 3 | 0,0 % |

### Top pagini (după afișări — creștere WoW)

| Pagină | Afișări | WoW |
|---|---|---|
| CSSI Brașov — Homepage | 34 | +40,4 % |
| Camere Supraveghere Brașov | 22 | +46,7 % |
| Servicii CSSI Brașov | 14 | **+180 %** 🟢 |
| Automatizări Porți Brașov | 6 | **+200 %** 🟢 |
| Detecție Incendiu ISU Brașov | 9 | +50 % |
| Contact CSSI Brașov | 6 | +50 % |
| Despre CSSI Brașov | 5 | 0,0 % |

### Surse de trafic (sesiuni)

| Canal | Sesiuni | WoW |
|---|---|---|
| Paid Search | 23 | +8,0 % |
| Organic Search | 7 | +16,7 % |
| Direct | 5 | +37,5 % |
| Cross-network | 2 | +100 % |

### Evenimente (top 7)

| Eveniment | Număr | WoW |
|---|---|---|
| page_view | 125 | +2,5 % |
| user_engagement | 105 | +14,1 % |
| session_start | 37 | +7,5 % |
| first_visit | 23 | +21,1 % |
| scroll | 13 | +18,2 % |
| click | 4 | +100 % |
| **form_start** | **4** | +100 % |
| ⚠️ `form_submit` | **0** (absent din raport) | — |
| ⚠️ `phone_call` | **0** (absent din raport) | — |
| ⚠️ `whatsapp_click` | **0** (absent din raport) | — |
| ⚠️ `generate_lead` / `cta_click` | **0** (absent din raport) | — |

### Țări

- Romania: 25 utilizatori activi (+19 %)
- Italy: 1 (probabil bot/referral)
- United States: 1 (+50 % — verifică legitimitate)

---

## 3. Google Search Console — Ultimele 7 zile (10 – 16 mai 2026)

| Metric | Valoare | WoW |
|---|---|---|
| Total clickuri | **9** | 🟢 +80 % (5 → 9) |
| Total afișări | **400** | 🟢 +10,8 % (361 → 400) |
| CTR mediu | 2,2 % | 🔴 -0,2pp (de la 2,3 %) |
| Poziție medie | **19,8** | 🔴 -1,5 (18,3 → 19,8 — mai slabă) |
| Pagini indexate | 40 | — |
| Pagini neindexate | 38 ⚠️ | — |
| Erori date structurate | 15 | — |

### Top 10 interogări (toate cu 0 clickuri în 7z)

| Interogare | Afișări | Status țintă 60z |
|---|---|---|
| camere supraveghere | 51 | — |
| camere de supraveghere | 11 | — |
| **pontaj electronic** | **10** | 🎯 Țintă: rang 12-15 |
| sistem pontaj | 10 | — |
| **sistem pontaj electronic** | **8** | 🎯 Țintă: rang 12-15 |
| pontaj biometric fabrică | 8 | — |
| sisteme de securitate brasov | 7 | — |
| smart home integrare securitate | 6 | — |
| sisteme securitate | 5 | — |
| pontaj electronic cu amprenta | 5 | — |

### Insights GSC

- ✅ `/blog/camere-supraveghere-gdpr-ghid-complet` — afișări **+843 %** (consistent cu raportul de ieri — confirmă oportunitate)
- ⚠️ `/pontaj-electronic` — afișări **-51 %** (degradare persistă din raportul anterior — necesită intervenție)
- ⚠️ Poziție medie a coborât de la 18,3 la 19,8 — primul WoW regresiv după 3 săptămâni de creștere
- ⚠️ **38 pagini neindexate** vs. 40 indexate — situație stabilă dar nerezolvată
- ⚠️ **15 erori de date structurate** care nu pot fi analizate (Schema.org)

---

## 🚨 Alerte (în ordinea priorității)

1. **🔴 CRITICĂ — Posibilă pierdere tracking conversii GA4.** Săptămâna trecută am avut 9 conversii primare (form_submit 2 + phone_call 2 + whatsapp_click 3 + generate_lead 2). Astăzi raportul GA4 arată **0 evenimente importante de tip conversion**, doar 4 `form_start` + 4 `click`. Posibilă cauză: regresie tagging după deploy, configurare conversii GA4 dezactivată sau pur și simplu raport „eveniment numărat" diferit pentru weekend. **Verifică imediat în GA4 → Configurări → Evenimente.**

2. **🔴 CTR Ads scade sub trendul săptămânii trecute (9,38 % → 7,68 %, -1,7pp).** Sub țintă 15-18 %. Cauza: 4 din top 5 cuvinte cheie au CTR <7 %. Doar `montaj camere supraveghere` (10,71 %) se apropie de țintă.

3. **🟡 `/pontaj-electronic` rămâne în declin (-51 % afișări).** Acțiunea recomandată ieri rămâne deschisă — pagina e pivot pentru obiectivul 60z.

4. **🟡 Poziție medie GSC s-a deteriorat (18,3 → 19,8).** Primul WoW regresiv în 3 săptămâni — necesită monitorizare.

5. **🟡 Scor optimizare Ads a scăzut (70,4 % → 64,8 %).** Google detectează mai multe recomandări neimplementate.

6. **🟡 Lead funnel arată 6 „interested leads" în 7 zile (vs ~3-4 baseline) — semnal pozitiv, dar nu se reflectă în conversiile GA4** — încă o confirmare a alertei #1.

7. **🟢 38 pagini neindexate Google** — situație cunoscută, neagravată.

8. **🟢 15 erori de date structurate** — neagravate.

---

## ✅ Acțiuni propuse pentru azi (3 sugestii prioritare)

### 🔴 ACȚIUNE 1 — Verifică tracking conversii GA4 (efort 15-30 min, impact MARE)

**Context:** Lead funnel Google Ads arată 6 clienți potențiali interesați + 4 conversii înregistrate săpt. trecută; raportul GA4 de azi arată 0 evenimente `form_submit`, `phone_call`, `whatsapp_click`. Săptămâna trecută aveam 9 conversii primare. Posibilă regresie de tracking sau setări modificate.

**Pași:**
1. Deschide GA4 → Configurări → **Evenimente** → verifică ultima înregistrare pentru `phone_call`, `whatsapp_click`, `form_submit`, `generate_lead`, `cta_click`
2. Verifică GA4 → Configurări → **Evenimente importante (Key events)** — confirmă că cele 4 sunt marcate ca atare
3. În Real-Time, fă un test: deschide cssi.ro pe telefon, dă click pe numărul de telefon → vezi dacă apare `phone_call`
4. Verifică în GTM (dacă există) sau scriptul `tracking.js` că emiterea evenimentelor n-a fost dezactivată după deploy din 13 mai (date `.gitignore`, `.htaccess`, `sw.js` modificate)
5. Verifică Google Ads → Conversii → importurile GA4 — sunt active?

**Impact așteptat:** Restabilire vizibilitate completă pe ROI și bidding signal — fără date corecte nu putem migra la Maximize Conversions și nici nu putem optimiza bugetul rațional.

### 🟡 ACȚIUNE 2 — Adaugă 12-15 negative keywords noi (efort 15 min, impact MEDIU)

**Context:** Lista actuală de 31 negative keywords nu acoperă valul de căutări irelevante observate săptămâna asta (branduri specifice, geo greșit Iași, limbă EN/PT).

**Pași:** Adaugă negative keywords la nivelul campaniei „CSSI - Servicii Securitate":

- `shop security`, `home security`, `home security system` (limbă EN)
- `controlos de acesso` (portugheză)
- `dahua`, `hikvision`, `imou`, `andowl`, `kmw`, `cerber`, `telesystem`, `atutech`, `avitech`, `bft`, `tago`, `ultra security`, `spy security` (branduri specifice — nu suntem distribuitori autorizați)
- `hdcvi`, `7100 series`, `dsc pk5501` (modele)
- `olx`, `altex` (marketplace/retailer)
- `iasi`, `bucuresti`, `cluj`, `timisoara` (geo greșit — adaugă DOAR dacă nu există deja)
- `jortan` (brand chinezesc DIY)
- `panou solar` (segment specific, nu serviciul nostru core)

**Impact așteptat:** Reducere afișări irelevante ~15-25 %, CTR campanie +1-2 puncte, transfer buget către queries relevante.

### 🟢 ACȚIUNE 3 — Audit `/pontaj-electronic` (efort 30 min, impact MARE — restant din raportul de ieri)

**Context:** Acțiunea rămâne neexecutată; pagina pierde a doua săptămână consecutivă din afișări. Pe interogarea „pontaj electronic" avem 10 afișări/7z dar 0 clickuri (poziție medie probabil 20-30).

**Pași:**
1. GSC → URL Inspection: `https://cssi.ro/pontaj-electronic` → confirmă stare „URL is on Google" și data ultimului crawl
2. Verifică `<title>` și `<meta description>` — trebuie să conțină „Pontaj Electronic Brașov | CSSI"
3. Compară conținutul curent cu cel de pe `/pontaj-electronic-brasov.html` (există ambele variante de fișiere!) — dacă există duplicate, consolidează cu rel=canonical
4. Verifică Schema.org pe pagină (Product/Service markup activ?)
5. Submit re-indexare în GSC dacă faci modificări

**Impact așteptat:** Recuperare poziție medie pe „pontaj electronic" către rang 15-20 în 14-21 zile.

---

## 📋 Verificare task-uri pending (status check)

- **#15 Cleanup keywords Low search volume** — **NU încă.** Cuvântul `control acces` are 0 afișări 7z, dar așteptăm 30+ zile de date și 15+ conversii cumulative.
- **#16 Restructurare în 3 Ad Groups tematice** — **NU încă.** Bugetul de 60 EUR/lună rămâne prea mic pentru 3 grupuri; ar dilua semnalul de învățare al algoritmului.
- **#17 Creare 3 RSA-uri noi** — **NU încă.** Dependent de #16.

**Recomandare bidding strategy:** **NU migra la Maximize Conversions** încă. Suntem la ~4-9 conversii/săptămână (după rezolvare tracking), iar pragul minim este 15+ conversii cumulative pe 30 zile + tracking GA4 sănătos. **Blocant nou: alerta #1 (verificare tracking)**.

---

*Raport generat automat pe 18 mai 2026 (Luni). Astăzi se publică și raportul strategic săptămânal W21 — vezi `weekly-strategy/CSSI-Weekly-Strategy-2026-05-18.md`.*
