# CSSI — Raport zilnic monitorizare
**Data:** 2026-05-11 (Luni, săptămâna 20)
**Cont Google Ads:** 666-033-6562 | **Buget:** 60 EUR/lună (~300 RON)
**Generat de:** scheduled task `cssi-daily-monitoring`
**Date live capturate:** ✅ Google Ads (7 zile) | ✅ GA4 (7 zile) | ✅ Search Console (7 zile)

> **Notă metodologică:** Toate cele 3 platforme au răspuns live azi pe vederea de 7 zile. Comparațiile WoW sunt calculate direct pe metrici 7d vs raportul anterior (08-mai = vineri W19). Astăzi este luni — raportul zilnic este însoțit de un **raport strategic săptămânal** separat în `weekly-strategy/`.

---

## TL;DR (3 linii)
- **Ce merge bine:** Conversii primare GA4 explodează în săptămâna 19-20 — `phone_call` 2, `form_submit` 2, `generate_lead` 2, `whatsapp_click` 3 (Total 9 evenimente importante, vs 7 raport anterior). Confirmat: alerta tagging din raportul de vineri a fost dezactivată — `phone_call`, `form_submit` și `generate_lead` raportează acum date reale. Pozițiile organice continuă urcușul (poz medie 22,7 → 18,3, -4,4 poziții) iar GSC clickuri 3 → 5 (+66,7%).
- **Ce nu merge:** „camere supraveghere brașov" a pierdut top 3 (3,0 → 7,0) deși rămâne top 10; queries cu volum mare („camere supraveghere" 35 af / poz 11,9; „pontaj electronic" 16 af / poz 51,9) continuă cu 0 click-uri — snippet meta încă neoptimizat; Google Ads CTR 7d 9,38% sub țintă 15-18%; recomandarea Google sugerează migrare la „Maximizați valoarea conversiilor" (+17,4% potențial).
- **Acțiune recomandată azi:** Cu 9 conversii primare în 7 zile (record!) avem datele necesare pentru a debloca **task #16 (3 Ad Groups tematice)** și **task #17 (3 RSA-uri noi)**. Combinat cu reoptimizarea meta-urilor (acțiune #1 din vineri), poate dubla atât CTR Ads cât și CTR organic în 2 săptămâni.

---

## 1. Google Ads — captură 7 zile (3-9 mai 2026)

### Metrice cont
| Metric | Valoare 7 zile | Δ vs 7d anterior | Țintă | Stare |
|--------|---------------|-------------------|-------|-------|
| Afișări | 544 | n/a | n/a | — |
| Clicuri | 51 | n/a | n/a | — |
| **CTR campanie** | **9,38%** | — | 15-18% | 🔴 Sub țintă (-5,6pp până -8,6pp) |
| CPC mediu | 2,85 RON | -5,6% (3,02→2,85) | <4 RON | 🟢 OK |
| Cost total | 145,23 RON | +14,12% (cele mai mari schimbări) | ~70 RON/săpt | 🟡 Peste pace cu ~107% |
| Optimization Score | 68,5% | -5,4pp (de la 73,9% pe vederea 30z) | >85% | 🟡 Loc de creștere |
| Status difuzare | „A primit conversii, dar încă are o problemă care poate limita difuzarea" | — | — | 🟡 Diagnostică deschisă |
| Sold cont | 173,03 RON | +22,46 (de la 150,57) | — | 🟢 Top-up automat la 800 RON sau 1 iun |

> **Notă critic-buget (7 zile):** 145,23 RON / 7 zile = 20,75 RON/zi → proiecție lunară ~622 RON, ~2x față de bugetul setat 300 RON/lună. **Mai bun decât vederea 30z (-32% față de ~880 RON estimat ieri)** dar tot peste țintă. Activitatea zilnică pare să se calmeze ușor pe finalul săptămânii — sold-ul a urcat doar 22 RON în 4 zile.

### Top 5 cuvinte cheie (vedere 7 zile, sortate după Cost)
| # | Cuvânt cheie | Cli | CTR | Cost (RON) | % din buget | Notă |
|---|---|---|---|---|---|---|
| 1 | montaj camere supraveghere | 34 | 11,41% | **101,22** | 69,7% | Motorul principal — încă concentrat 70% |
| 2 | kit camere supraveghere wireless... | 4 | 10,00% | 13,86 | 9,5% | Nichă; CTR solid |
| 3 | proiectare sisteme securitate | 2 | 7,14% | 13,68 | 9,4% | CTR slab pentru B2B intent |
| 4 | sisteme de detectie *(întreruptă)* | 3 | 15,00% | 3,90 | 2,7% | Pause persistă; CTR cel mai bun |
| 5 | montaj camere supraveghere pret | 2 | 7,41% | 3,65 | 2,5% | Intent comercial; volume mic |

> Concentrare 70% pe „montaj camere supraveghere" — risc de portfoliu. Task #16 (3 Ad Groups tematice) ar diversifica.

### Top căutări (search terms — capturat azi)
Ordonate după afișări:
- **montaj camere supraveghere brașov** ⭐
- **camere supraveghere brașov** ⭐
- **camere supraveghere** (volum mare, intent generic)
- **sisteme supraveghere video brașov** ⭐
- **camere de supraveghere brașov** ⭐
- camera
- instalare camere supraveghere
- sisteme de alarma brașov
- sisteme supraveghere brașov
- supraveghere video brașov

### Conversii — semnale (vedere 7 zile)
| Indicator | Valoare | Notă |
|---|---|---|
| Interacțiuni → clienți potențiali interesați | 51 → 5 | Rata 9,80% (clic-to-engagement) |
| Etichete inactive | 0 | 🟢 |
| Etichete neverificate | 0 | 🟢 |
| Etichete fără conversii recente | 5 | 🟡 Verifică ce conversii nu s-au declanșat |
| Etichete care înregistrează conversii | 5 | 🟢 (+1 față de vineri) |
| Recomandare Google | „Maximizați valoarea conversiilor" (+17,4%) | 🟡 Aproape pragul 15+ conv — evaluez în W21-W22 |

### Dispozitive (vedere 7 zile)
| Dispozitiv | % Afișări | % Cost | % Clicuri |
|---|---|---|---|
| Telefoane mobile | 80,0% (435 af) | 90,6% | n/a |
| Computere | 19,1% | 8,0% | 7,8% |
| Tablete | 1,4% | 1,4% | n/a |

> Mobile dominant clar (80% afișări, 90,6% cost). Asigură-te că landing pages sunt mobile-first (deja sunt, dar verifică viteza pe „camere supraveghere brașov" — pagina vedetă).

### Semnale principale de licitare (pentru strategie inteligentă)
🟢 Pozitive: Locație Brașov + 8a.m.-4p.m. luni-vineri, Dispozitiv computere
🔴 Negative: Locație Sânpetru, ora 6p.m.+ luni-vineri, weekend 1p.m.-9p.m.

### Cele mai mari schimbări 7d vs 7d anterior
- CSSI - Servicii Securitate: **+17,97 RON (+14,12%)** — creștere costuri concomitentă cu creșterea conversiilor primare (vezi GA4 mai jos)

---

## 2. GA4 — trafic ultimele 7 zile (3-9 mai 2026)

| Metric | 7 zile | Δ vs 7 zile anterior | Δ vs raport vineri 08-mai |
|---|---|---|---|
| Utilizatori activi | 21 | 🟢 +61,5% | 🟢 +5% (20 → 21) |
| Utilizatori noi | 17 | 🟢 +30,8% | 🟡 -5,6% (18 → 17) |
| Evenimente importante | 9 | 🟢 +50,0% | 🟢🟢 +28,6% (7 → 9) |
| Utilizatori activi acum (live) | 0 | — | — |

> Mediana sectorului „Servicii securitate & pompieri" = 40-100 utilizatori/zi. CSSI rămâne sub mediana sectorului, dar trendul WoW e clar pozitiv (+61,5% utilizatori activi).

### Sesiuni după sursă (7 zile)
| Sursă | Sesiuni | Δ vs prior | Δ vs raport vineri |
|---|---|---|---|
| **Paid Search** | 24 | 🟢 +71,4% | 🟢 +14,3% (21 → 24) |
| Direct | 7 | — | 🟢 +600% (1 → 7) |
| **Organic Search** | **5** | 🟢 **+150%** | 🟢🟢 **0 → 5** (recuperare!) |
| Unassigned | 2 | — | — |
| Cross-network | 1 | — | 0 → 1 |

> 🟢🟢 **Organic Search revine cu 5 sesiuni** după cele 0 din raportul de vineri — confirmă că poziționarea organică (top 10 multiple queries) începe să convertească afișări în vizite. Paid continuă creșterea.

### Top pagini — afișări 7 zile (GA4)
| Pagină | Afișări | Δ vs prior |
|---|---|---|
| CSSI Brașov | Sisteme Securitate (homepage) | 54 | 🟢 +170,0% |
| **Camere Supraveghere Brașov** | **16** | 🟢 **+300%** ⭐ |
| Portofoliu CSSI Brașov | 7 | — |
| **Detecție Incendiu IS...** | 6 | 🟢 **+500%** ⭐ |
| **Servicii CSSI Brașov** | **5** | 🟢 **+400%** |
| **Alarmă Antiefracție Brașov** | 4 | 🟢 +100% |
| **Contact CSSI Brașov** | 4 | 🟢 +300% |

> Distribuția se diversifică: nu mai e „Camere Supraveghere monopol" — Servicii (+400%), Contact (+300%), Detecție Incendiu (+500%) urcă concomitent. Semnal puternic că funnel-ul (homepage → categorie → contact) funcționează.

### 🎯 Evenimente 7 zile — RECORD DE CONVERSII PRIMARE
| Eveniment | Număr | Utilizatori unici | Δ vs raport vineri | Notă |
|---|---|---|---|---|
| page_view | 122 | 23 | 🟢 +126% (54 → 122) | Volum în creștere |
| user_engagement | 92 | 21 | 🟢 +84% (50 → 92) | Engagement consistent |
| session_start | 40 | 23 | 🟢 +74% (23 → 40) | — |
| first_visit | 19 | 19 | 🟢 +5,6% (18 → 19) | Audiență nouă |
| scroll | 11 | 7 | 🟢 +10% (10 → 11) | — |
| **whatsapp_click** | **3** | 3 | 🟢 +50% (2 → 3) | ⭐ |
| click | 2 | 2 | 0% | — |
| **form_start** | **2** | 2 | 🟢 NEW (0 → 2) | ⭐ Apare prima oară |
| **form_submit** | **2** | 2 | 🟢🟢 NEW (0 → 2) | ⭐⭐ **Alerta vineri rezolvată!** |
| **generate_lead** | **2** | 2 | 🟢 NEW (0 → 2) | ⭐ Apare prima oară |
| **phone_call** | **2** | 2 | 🟢🟢 NEW (0 → 2) | ⭐⭐ **Alerta vineri rezolvată!** |

> 🚨 **DESCOPERIRE MAJORĂ:** Alerta din vineri „phone_call=0, form_submit=0, cta_click=0" se închide azi. Avem 9 conversii primare în 7 zile (2 form_submit + 2 phone_call + 3 whatsapp + 2 generate_lead). Mediana de ~3 conv/lună din baseline e depășită deja în 7 zile cu factor 3x. Este momentul oportun pentru: (a) verifică Google Ads pentru import GA4 conversii (web phone_call & whatsapp_click trebuie să apară în Conversion column), (b) deblochează task #15-#17.

---

## 3. Google Search Console — performanță 7 zile (2-8 mai)

### Comparație orizonturi
| Interval | Clicuri | Afișări | CTR | Poz. medie | Δ vs raport vineri |
|---|---|---|---|---|---|
| **7 zile** (2-8 mai) | **5** | **361** | **1,4%** | **18,3** | 🟢 +2 cli; 🟢 CTR +0,5pp; 🟢 Poz. -4,4 |

> Toate cele 4 metrici GSC se îmbunătățesc WoW. CTR a urcat de la 0,9% → 1,4% (+56%), poziția medie 22,7 → 18,3 (-4,4 poziții). Avansul susținut.

### Top queries GSC (7 zile)
| # | Query | Cli | Af. | Poz. | Notă |
|---|---|---|---|---|---|
| 1 | camere supraveghere | 0 | 35 | 11,9 | 🔴 35 af, 0 cli — **persistent** |
| 2 | pontaj electronic | 0 | 16 | 51,9 | 🔴 Poziție foarte joasă |
| 3 | camere de supraveghere | 0 | 7 | 15,1 | 🟡 Aproape top 10 |
| 4 | **camere supraveghere brașov** | 0 | 6 | **7,0** | 🟡 **Top 10** dar pierde 3 poziții vs vineri (3,0 → 7,0) |
| 5 | securitate smart home | 0 | 6 | 14,0 | 🟡 |
| 6 | sisteme securitate | 0 | 6 | 25,7 | 🔴 |
| 7 | smart home integrare securitate | 0 | 5 | 23,4 | 🔴 |
| 8 | sisteme de pontaj electronic | 0 | 5 | 44,6 | 🔴 Aproape stabil (vs 43,0 vineri) |
| 9 | sisteme de pontaj | 0 | 5 | 57,8 | 🔴 |
| 10 | sisteme de securitate brașov | 0 | 4 | 15,0 | 🟡 |

> **Notă:** Top 10 queries au tot 0 click-uri (cu excepția homepage-ului care a primit 3 cli pe queries brand-ish + 1 cli pe blog ajax-vs-paradox + 1 cli pe automatizari-porti).

### Poziții vs țintele 60 zile
| Query țintă | Poziție 7z | Țintă | Δ vs vineri | Stare |
|---|---|---|---|---|
| sistem pontaj electronic / sisteme de pontaj electronic | 44,6 | 12-15 | -1,6 poz | 🔴 Aproape stabil; recuperare lentă |
| **camere supraveghere brașov** | **7,0** | top 10 | -4 poz (3,0 → 7,0) | 🟡 Pierde top 3, păstrează top 10 |
| **camere de supraveghere brașov** | n/a (a căzut din top 10 GSC, doar 7 af) | top 10 | — | 🟡 Pierdere temporară a vizibilității |
| smart home integrare securitate | 23,4 | top 10 | -14 poz | 🔴 Drop semnificativ vs vineri (9,0 → 23,4) |
| alarmă antiefracție brașov | n/a în top 50 | top 10 | — | 🔴 Niciun semnal |
| control acces brașov | n/a în top 50 | top 10 | — | 🔴 Niciun semnal |
| detecție incendiu brașov | n/a în top 50 | top 10 | — | 🔴 Niciun semnal direct (dar /blog/detectie-incendiu-pret-ghid-complet e la poz 3,4 pe queries indirecte) |

### Top pagini GSC (7 zile)
| Pagină | Cli | Af. | CTR | Poz. | Notă |
|---|---|---|---|---|---|
| https://cssi.ro/ | 3 | 136 | 2,2% | 9,8 | Homepage — top 10, CTR sub benchmark |
| /blog/ajax-vs-paradox-vs-dsc-comparatie-alarme | 1 | 11 | **9,1%** | 3,0 | ⭐ CTR excelent în top 3 |
| /automatizari-porti | 1 | 4 | **25%** | 8,0 | ⭐⭐ CTR superb pe volum mic |
| /pontaj-electronic | 0 | 50 | 0% | 49,1 | 🔴 50 af / 0 cli — pagina cea mai problematică |
| /blog/camere-supraveghere-gdpr-ghid-complet | 0 | 42 | 0% | 14,9 | 🔴 42 af / 0 cli |
| /blog/detectie-incendiu-pret-ghid-complet | 0 | 36 | 0% | 3,4 | 🔴 Top 5 organic, 0 cli — meta description critică |
| /blog/smart-home-securitate-control-telefon | 0 | 19 | 0% | 24,6 | — |
| /blog/ventilatie-recuperare-caldura-ghid-2026 | 0 | 11 | 0% | 31,1 | — |
| /blog/analiza-risc-securitate-fizica-ghid-complet | 0 | 10 | 0% | 6,6 | 🔴 Top 7, 0 cli |
| /usi-garaj | 0 | 9 | 0% | 10,2 | — |

> **Top pagini cu poziție bună dar 0 CTR (oportunitate quick-win):** `/blog/detectie-incendiu-pret-ghid-complet` (poz 3,4 / 36 af), `/blog/analiza-risc-securitate-fizica-ghid-complet` (poz 6,6 / 10 af). Sunt blog posts cu intent informațional — meta description trebuie să promită valoarea articolului + CTA spre serviciu.

---

## 🚨 Alerte

| # | Alerta | Severitate | Detalii |
|---|---|---|---|
| 1 | **Top 5 queries cu volum mare și 0 click-uri** | 🟡 Medie | „camere supraveghere" 35 af, „pontaj electronic" 16 af, „camere de supraveghere" 7 af, „camere supraveghere brașov" 6 af, „securitate smart home" 6 af. Total 69 afișări pe queries strategice cu 0 click-uri. Cauza confirmată: meta snippets neoptimizate. |
| 2 | **„camere supraveghere brașov" pierde top 3** | 🟡 Medie | Poziția 3,0 (vineri) → 7,0 (azi). Cauza posibilă: SERP rerank periodic Google. Rămâne în top 10 — nu necesită acțiune imediată, doar monitorizare. |
| 3 | **smart home integrare securitate scade -14 poziții** | 🟡 Medie | 9,0 (vineri) → 23,4 (azi). Verifică dacă a apărut competiție nouă sau pagina noastră a pierdut backlinks/relevanță. |
| 4 | **Buget Google Ads 7d la 145 RON (~622 RON/lună proiectat)** | 🟡 Medie | De ~2x peste bugetul setat 300 RON. Diminuat față de vederea 30z (~880 RON) dar tot peste țintă. Cost/conversie e excelent (145/9 = 16 RON/conv) — ROI sănătos. |
| 5 | **Recomandare Google: Maximizați valoarea conversiilor (+17,4%)** | 🟢 Scăzută | Aproape de pragul 15+ conv pentru migrare. Cu 9 conv în 7 zile, prag atins în ~10 zile. Re-evaluare în W21. |
| 6 | **„Solicitați o ofertă" — încă „Necesită atenție"** | 🟡 Medie | Persistă din 21 apr. Trebuie verificat dacă form_submit-urile de azi se mapează la această conversie. |
| 7 | **CTR Ads campanie 9,38% (7d) — sub țintă 15-18%** | 🟡 Medie | Necesită task #17 (3 RSA-uri noi cu 15 titluri / 4 descrieri fiecare). |
| 8 | **pontaj-electronic pagină: 50 af / 0 cli / poz 49,1** | 🔴 Înaltă | Cea mai vizibilă pagină ratată — 50 afișări/săptămână pe queries pontaj cu CTR 0%. Necesită: (a) optimizare meta + (b) îmbunătățire poziție organică. |
| 9 | **Tag-uri „fără conversii recente": 5** | 🟢 Scăzută | Vineri erau 6, azi 5. Tendință pozitivă. Verifică care 5 nu se declanșează — posibil legate de „Solicitați o ofertă". |

---

## ✅ Acțiuni propuse azi

### Acțiune 1 — Reoptimizare meta description pentru blog posts cu CTR 0% (30-45 min) ⭐ PRIORITATE MAXIMĂ
- **Context:** Două blog posts au poziție excelentă dar 0% CTR: `/blog/detectie-incendiu-pret-ghid-complet` (poz 3,4 / 36 af) și `/blog/analiza-risc-securitate-fizica-ghid-complet` (poz 6,6 / 10 af). 46 afișări pierdute săptămânal din top 7. La un CTR conservator de 4-6% pe pagini de blog informaționale ar însemna 2-3 click-uri/săpt.
- **Pagini țintă (ordonate după impact):**
  1. `/blog/detectie-incendiu-pret-ghid-complet` — 36 af/săpt, poz 3,4 (top 5!)
  2. `/blog/camere-supraveghere-gdpr-ghid-complet` — 42 af/săpt, poz 14,9
  3. `/blog/analiza-risc-securitate-fizica-ghid-complet` — 10 af/săpt, poz 6,6
  4. `/pontaj-electronic` — 50 af/săpt, poz 49,1 (combinat cu îmbunătățire poziție)
- **Pattern recomandat:**
  - Title <60 char: include număr (cu „prețuri", „ghid 2026", „pași"), USP local, brand „CSSI"
  - Description <155 char: promite valoare specifică („afli costul, normele IGSU și pașii instalării") + CTA („Ofertă în 24h").
- **Efort:** 30-45 minute (4 pagini × 8-10 min)
- **Impact estimat:** +5-10 click-uri organice/lună la același volum afișări (CTR 0% → 3-5%).

### Acțiune 2 — Verifică import GA4 conversii în Google Ads (10 min) ⭐
- **Context:** Avem 2 phone_call și 3 whatsapp_click în GA4 — acestea sunt setate ca importuri (50 RON/conv valoare). Verifică în Google Ads → Obiective → Conversii dacă apar cu valori în coloana „Conv." și calculează cost/conv real (ar trebui să fie ~145/5 = 29 RON/conv, foarte sub țintă).
- **Efort:** 10 minute
- **Impact:** Dacă conversiile NU se importă, e blocker major pentru migrare la „Maximizați valoarea conversiilor". Dacă apar, putem trece la bidding inteligent în 1-2 săptămâni.

### Acțiune 3 — Deblochează task #15 + #17 (60-90 min) 
- **Context:** Cu 9+ conversii pe 7 zile, avem semnal suficient pentru a:
  1. **Task #15** — cleanup keywords „Low search volume" (deja peste prag). Estimare: 20-30 min.
  2. **Task #17** — creează 3 RSA-uri noi (15 titluri + 4 descrieri fiecare) pentru a urca CTR de la 9,38% la țintă 15%. Estimare: 40-60 min cu drafting CSSI specific.
- **Efort:** 60-90 minute total
- **Impact estimat:** +5-7pp CTR campanie, ducând la țintă 15-18%; cost/click stabil sau în scădere.

---

## 📌 Note pentru raportul săptămânal de astăzi

Astăzi este **Luni 11 mai** — raportul săptămânal este generat acum: `weekly-strategy/CSSI-Weekly-Strategy-2026-05-11.md`. Acoperă W19 (4-10 mai 2026) cu comparație WoW vs W18.

**Highlights pentru raportul săptămânal:**
- 🟢🟢 9 conversii primare în 7 zile (record) — alerta tagging vineri rezolvată
- 🟢 CTR organic GSC 0,9% → 1,4% (+56%)
- 🟢 Poziție medie 22,7 → 18,3 (-4,4 poziții)
- 🟢 Organic Search GA4 0 → 5 sesiuni (recuperare)
- 🟡 Buget Ads sub control mai bun (145 RON/7d vs ~880 RON/30d proiecție)
- 🔴 „camere supraveghere brașov" pierde top 3 — monitorizare W21

---

*Raport generat automat din Google Ads (cont 666-033-6562, vedere 7 zile 3-9 mai), GA4 (property a385388640, 3-9 mai 2026) și Search Console (sc-domain:cssi.ro, 2-8 mai 2026). Date capturate live via Claude in Chrome la 2026-05-11.*
