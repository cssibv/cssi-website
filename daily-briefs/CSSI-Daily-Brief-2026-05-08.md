# CSSI — Raport zilnic monitorizare
**Data:** 2026-05-08 (Vineri, săptămâna 19)
**Cont Google Ads:** 666-033-6562 | **Buget:** 60 EUR/lună (~300 RON)
**Generat de:** scheduled task `cssi-daily-monitoring`
**Date live capturate:** ✅ Google Ads | ✅ GA4 | ✅ Search Console

> **Notă metodologică:** Dashboard-ul Google Ads s-a deschis astăzi pe vederea implicită „Ultimele 30 de zile" (291 cli / 2,73K af / 879 RON). În tabelele de mai jos această vedere e marcată clar **(30 zile)**, în timp ce GA4 și GSC rămân pe **(7 zile)** ca de obicei. Compararea cu raportul de ieri (care era 7 zile) se face pe baza tendințelor relative, nu a valorilor absolute.

---

## TL;DR (3 linii)
- **Ce merge bine:** GSC poziție medie urcă vizibil (26,6 → 22,7 / -3,9 poziții); „camere supraveghere brașov" e acum la **poziția 3,0** (vs ~14 estimat ieri) — top 3 organic confirmat; GA4 utilizatori activi 7z 20 (+17,6% vs 17 ieri) și sesiuni Paid Search 21 (+23,5%); pagina „Camere Supraveghere" continuă creșterea (10 afișări / +900% WoW).
- **Ce nu merge:** GSC CTR scade 1,6% → 0,9% (cu poziții mai bune dar mai puține click-uri reale: 5 → 3); Organic Search pe GA4 confirmă slăbiciunea (0 sesiuni 7z, -100%); zero conversii primare detectabile în feed-ul GA4 (doar 2 whatsapp_click și 0 phone_call/form_submit înregistrate); meta description-urile rămân unfit — TOP 7 queries continuă să aibă 0% CTR.
- **Acțiune recomandată azi:** Atacă **acțiunea #1 din raportul de ieri** — reoptimizează meta title/description pentru 5 pagini cheie (45-60 min). Cu pozițiile organice care urcă (avem deja top 3 pe „camere supraveghere brașov"), CTR 0% e singurul motiv pentru care nu transformăm afișările în vizite. Quick win cu impact maxim săptămâna asta.

---

## 1. Google Ads — captură 30 zile (vedere implicită dashboard)

### Metrice cont (vedere 30 zile)
| Metric | Valoare 30 zile | Ref. țintă | Stare |
|--------|------------------|------------|-------|
| Afișări | 2.727 | n/a | — |
| Clicuri | 291 | n/a | — |
| **CTR campanie** | **10,67%** | 15-18% | 🔴 Sub țintă (-4,3pp până -7,3pp) |
| CPC mediu | 3,02 RON | <4 RON | 🟢 OK |
| Cost total | 879,56 RON | ~300 RON/lună | 🔴 Peste pace cu ~193% (vedere 30z) |
| Optimization Score | 73,9% | >85% | 🟡 Loc de creștere |
| Status difuzare | „Cheltuie cea mai mare parte a bugetului zilnic, primind conversii" | — | 🟢 OK |
| Sold rămas | 150,57 RON | — | 🟡 Următoarea plată automată: 1 iun |

> **Notă critic-buget:** 879 RON / 30 zile = ~29,3 RON/zi → proiecție lunară ~880 RON, **de ~3x peste bugetul setat 300 RON/lună**. Discrepanța e mai mare decât semnalată ieri (atunci era ~100% overspend pe 7 zile). Se recomandă verificare manuală: e bugetul ajustat? E setarea „shared budget"? E recuperare zile cu sub-spend? **Acțiune urgentă:** confirmă cu MIHAI care e bugetul efectiv intenționat. Cost/conversie rămâne sănătos (vezi mai jos), deci ROI-ul nu e problema — doar predictibilitatea spend-ului.

### Top 5 cuvinte cheie (vedere 30 zile, sortate după Cost)
| # | Cuvânt cheie | Cli | CTR | Cost (RON) | % din buget | Notă |
|---|---|---|---|---|---|---|
| 1 | montaj camere supraveghere | 187 | 13,28% | **529,92** | 60,2% | Motorul principal — CTR sănătos |
| 2 | camere supraveghere preț | 10 | 7,58% | 53,48 | 6,1% | CTR slab; informațional, nu intent |
| 3 | sisteme de detecție *(întreruptă)* | 5 | 12,36% | 40,55 | 4,6% | Pause persistă |
| 4 | kit camere supraveghere wireless exterior preț | 11 | 9,93% | 36,92 | 4,2% | Nichă specifică, păstrează |
| 5 | control acces | 10 | 8,47% | 36,05 | 4,1% | CTR sub-baseline; review în task #16 |

> 60% din buget pleacă pe „montaj camere supraveghere" — sănătos, dar și un risc de concentrare. Diversificarea în task #16 (3 Ad Groups tematice) ar reduce dependența de un singur keyword.

### Top căutări (search terms — capturat azi din overview)
Lista (ordonată după afișări) include termeni puternic relevanți precum:
- **montaj camere supraveghere brașov** ⭐
- **camere supraveghere brașov** ⭐
- **camere de supraveghere brașov** ⭐
- montare camere supraveghere la curte
- supraveghere video brașov *(produs 1 conv ieri la 6,29 RON)*
- firme autorizate montaj camere de supraveghere brașov
- sisteme securitate brașov
- sisteme de alarmă brașov

> **Termeni de adăugat ca exact match:** „supraveghere video brașov" (cuvânt convertit ieri) și „firme autorizate montaj camere de supraveghere brașov" (intent puternic local). 5 min de muncă, lock-in pe queries valoroase.

### Conversii — semnale (vedere 30 zile)
| Indicator | Valoare | Notă |
|---|---|---|
| Interacțiuni → clienți potențiali interesați | 291 → 29 | Rata 9,97% (clic-to-engagement) |
| Etichete inactive | 0 | 🟢 |
| Etichete neverificate | 0 | 🟢 |
| Etichete fără conversii recente | 6 | 🟡 Verifică ce conversii nu s-au declanșat |
| Etichete care înregistrează conversii | 4 | 🟢 |
| Recomandare Google | „Maximizați valoarea conversiilor" (+21,3%) | 🟡 De evaluat după ce strângem 15+ conv |

### Recomandare-cheie de la Google: tendințe căutare
- Volum căutări (RO): **+6%** vs perioada precedentă
- Clicurile noastre: **+9%** — creștem ușor mai repede decât piața. 🟢 Pozitiv.

---

## 2. GA4 — trafic ultimele 7 zile (1-7 mai 2026)

| Metric | 7 zile | Δ vs perioada anterioară | Δ vs raport ieri |
|---|---|---|---|
| Utilizatori activi | 20 | -25,0% | 🟢 +17,6% (17 → 20) |
| Utilizatori noi | 18 | -28,6% | 🟢 +5,9% (17 → 18) |
| Evenimente importante | 7 | 0,0% (stabil) | — |
| Număr de evenimente | 166 | -71,1% | — |
| Utilizatori 30 zile | 84 | -7,7% | — |
| Utilizatori activi acum (live) | 0 | — | — |

> Mediana sectorului „Servicii securitate & pompieri" = 40-100 utilizatori/zi. CSSI rămâne **sub mediana sectorului**, dar trendul intern WoW e pozitiv (+17,6% activi vs raportul de ieri).

### Sesiuni după sursă (7 zile)
| Sursă | Sesiuni | Δ vs prior | Δ vs raport ieri |
|---|---|---|---|
| **Paid Search** | 21 | 🟢 +40,0% | 🟢 +23,5% (17 → 21) |
| Organic Search | 0 | 🔴 -100% | 🔴 1 → 0 (-100%) |
| Cross-network | 1 | — | 0 → 1 |
| Direct | 1 | — | 0 → 1 |

> Paid traffic în creștere — semnal că reclamele recompun audiența. Organic la zero — confirmă nevoia de a deschide acțiunea de reoptimizare meta (vezi acțiuni propuse).

### Top pagini — afișări 7 zile
| Pagină | Afișări | Δ vs prior |
|---|---|---|
| CSSI Brașov \| Sisteme Securitate (homepage) | 28 | 🟡 -21,7% |
| **Camere Supraveghere Brașov** | **10** | 🟢 **+900%** ⭐ |
| Contact CSSI Brașov | 4 | 🟢 +100% |
| Despre CSSI Brașov | 3 | — |
| Portofoliu CSSI Brașov | 2 | — |
| Servicii CSSI Brașov | 2 | — |
| Alarmă Antiefracție Brașov | 1 | 0,0% |

> „Camere Supraveghere Brașov" continuă explozia (+600% ieri → +900% azi) — pagina e clar destinația preferată din anunțurile Paid. **Recomandare:** pune accent pe această pagină în text snippet și landing page optimization (formular vizibil deasupra fold).

### Evenimente 7 zile (ordonate după volum)
| Eveniment | Număr | Δ vs prior | Notă |
|---|---|---|---|
| page_view | 54 | -92,9% | 🟡 Volum în scădere |
| user_engagement | 50 | 🟢 +127,3% | Calitate sesiuni mai bună |
| session_start | 23 | +15,0% | — |
| first_visit | 18 | +28,6% | — |
| scroll | 10 | 🟢 +233,3% | Engaged scroll în creștere |
| **whatsapp_click** | **2** | -33,3% | Singurul eveniment de conversie capturat |
| click | 2 | 0,0% | — |
| **phone_call** | **0** | — | 🔴 Niciun apel telefonic înregistrat |
| **form_submit** | **0** | — | 🔴 Niciun formular trimis |
| **cta_click** | **0** | — | 🔴 Niciun CTA click |

> **Alertă conversii:** GA4 înregistrează DOAR `whatsapp_click` din evenimentele de conversie definite în SKILL. `phone_call`, `form_submit` și `cta_click` lipsesc cu desăvârșire. Posibile cauze: (a) tagurile nu se declanșează corect, (b) nu există suficient trafic pentru a genera evenimentul, (c) numele evenimentului diferă în GTM. **Acțiune lunară:** audit tagging pentru aceste 3 evenimente lipsă.

---

## 3. Google Search Console — performanță 7 zile (30 apr – 6 mai)

### Comparație orizonturi
| Interval | Clicuri | Afișări | CTR | Poz. medie | Δ vs raport ieri |
|---|---|---|---|---|---|
| **7 zile** (30 apr – 6 mai) | **3** | **321** | **0,9%** | **22,7** | 🔴 -2 cli; 🔴 CTR -0,7pp; 🟢 Poz. -3,9 |

> **Vești bune și vești rele.** Bune: poziția medie urcă cu aproape 4 poziții (26,6 → 22,7) — Google ne consideră tot mai relevanți. Rele: cu 321 afișări (vs 310 ieri) ar trebui să primim 5-6 click-uri minimum la CTR „normal" 1,6-2%; primim doar 3. **Cauza:** snippet-urile noastre nu transformă vizibilitatea în vizite — exact problema identificată ieri.

### Top queries GSC (7 zile)
| # | Query | Cli | Af. | Poz. | Notă |
|---|---|---|---|---|---|
| 1 | camere supraveghere | 0 | 26 | 11,8 | 🔴 26 af, 0 cli — **persistent** |
| 2 | sistem pontaj | 0 | 12 | 74,2 | 🔴 Poziție foarte joasă |
| 3 | pontaj electronic | 0 | 9 | 51,3 | 🔴 Poz. ușor mai bună (vs ~55) |
| 4 | camere de supraveghere | 0 | 7 | 15,7 | 🟡 La 5 poziții de top 10 |
| 5 | sisteme securitate | 0 | 6 | 25,7 | 🔴 |
| 6 | sistem pontaj electronic | 0 | 6 | **43,0** | 🟢 Drop oprit (era ~50-55) |
| 7 | pontaj electronic cu amprenta | 0 | 5 | 26,2 | 🔴 |
| 8 | montaj aer condiționat brașov | 0 | 5 | 61,2 | ⚠️ Query irelevant |
| 9 | smart home integrare securitate | 0 | 4 | 9,0 | 🟡 Aproape de top 10! |
| 10 | sisteme de securitate brașov | 0 | 4 | 15,0 | 🟡 |
| 14 | **camere supraveghere brașov** | 0 | 3 | **3,0** ⭐ | 🟢🟢 **TOP 3!** |
| 16 | **camere de supraveghere brașov** | 0 | 3 | **8,7** | 🟢 **TOP 10!** |
| 24 | cssi (brand) | 0 | 2 | 1,0 | Brand search — pos 1 |

> **Descoperire majoră astăzi:** „camere supraveghere brașov" e la poziția **3,0** (top 3 organic) și „camere de supraveghere brașov" la **8,7** (top 10). Ieri am estimat „14-15" pentru aceste queries — în realitate suntem mult mai sus. Problema NU e poziția — e CTR. Suntem în top SERP dar avem 0 click-uri = snippet neatrăgător.

### Poziții vs țintele 60 zile
| Query țintă | Poziție 7z | Țintă | Stare |
|---|---|---|---|
| sistem pontaj electronic | 43,0 | 12-15 | 🔴 Drop oprit (vs ~50 ieri); recuperare lentă |
| **camere supraveghere brașov** | **3,0** | top 10 | 🟢🟢 **DEPĂȘIT — top 3** |
| **camere de supraveghere brașov** | **8,7** | top 10 | 🟢 **ATINS** |
| smart home integrare securitate | 9,0 | top 10 | 🟢 Atins |
| alarmă antiefracție brașov | n/a în top 50 | top 10 | 🔴 Niciun semnal |
| control acces brașov | n/a în top 50 | top 10 | 🔴 Niciun semnal |
| detecție incendiu brașov | n/a în top 50 | top 10 | 🔴 Niciun semnal |

---

## 🚨 Alerte

| # | Alerta | Severitate | Detalii |
|---|---|---|---|
| 1 | **Top 3 query-uri Brașov cu 0 click-uri** | 🔴 Înaltă | „camere supraveghere brașov" pos 3 + „camere de supraveghere brașov" pos 8,7 — 6 afișări, 0 click-uri. Confirmat: snippet-ul e problema, nu poziția. **Cea mai mare oportunitate quick-win deschisă.** |
| 2 | **Buget Google Ads ~880 RON/lună (vedere 30z)** | 🔴 Înaltă | De ~3x peste bugetul intenționat 300 RON. Verifică manual setarea bugetului — posibil ajustare neobservată sau shared budget. Cost/conv rămâne sub 50 RON, deci ROI OK; problema e doar predictibilitatea. |
| 3 | **Conversii primare nedetectate în GA4 (phone_call, form_submit, cta_click)** | 🔴 Înaltă | Doar `whatsapp_click` se declanșează. Audit GTM tagging necesar pentru cele 3 evenimente lipsă. Posibil pierdere de date conversii valide. |
| 4 | **„Solicitați oferte" — încă „Necesită atenție"** | 🟡 Medie | Persistă din 21 apr (17+ zile). Verifică tagging GA4. |
| 5 | **CTR campanie 10,67% (vedere 30z) — sub țintă 15-18%** | 🟡 Medie | Cu vederea de 30 de zile, CTR e ușor peste 9,96% (7z) de ieri. Tot necesită task #17 (3 RSA-uri noi). |
| 6 | **Organic Search 0 sesiuni GA4** | 🟡 Medie | -100% WoW. Coroborat cu CTR 0% pe top queries GSC, suntem în „șoc tăcut" SEO. |
| 7 | **„montaj aer condiționat brașov" — query irelevant cu 5 af** | 🟢 Scăzută | Pagina cu AC indexată; nu e prioritate SEO. |
| 8 | **Task-uri pending #15-#17 încă în așteptare** | 🟢 Scăzută | Avem 17+ conversii cumulative — momentul e oportun. |

---

## ✅ Acțiuni propuse azi

### Acțiune 1 — Reoptimizare meta title/description pentru 5 pagini cheie (45-60 min) ⭐⭐ PRIORITATE MAXIMĂ
- **Context:** Această acțiune era #1 ieri și azi devine MAI URGENTĂ. Datele de azi confirmă că suntem în **TOP 3 organic pentru „camere supraveghere brașov"** (poziție 3,0) și **TOP 10 pentru „camere de supraveghere brașov"** (poziție 8,7), DAR primim 0 click-uri. Cu 321 afișări/săpt și CTR 0,9%, suntem la jumătate vs benchmark sector (1,8-3%).
- **Pagini țintă (ordonate după impact estimat):**
  1. `/camere-supraveghere.html` (combină queryuri brașov + generale) — 26+3+3+7+12 = ~51 af/săpt total
  2. `/sistem-pontaj-electronic.html` — 6+9+12+5 = ~32 af/săpt
  3. `/index.html` (homepage) — pentru queries brand + smart home
  4. `/alarma-antiefractie-brasov.html` — încă fără semnal SERP, nevoie de boost
  5. `/control-acces-brasov.html` — încă fără semnal SERP, nevoie de boost
- **Pattern recomandat (același ca ieri):**
  - Title <60 char: `[Serviciu] Brașov | Garanție 5 ani | CSSI · 20+ Ani`
  - Description <155 char: USP local („instalare 24/7", „firmă autorizată IGSU/IGPR", „ofertă în 15 min") + telefon + CTA puternic.
- **Efort:** 45-60 minute (5 pagini × 10 min).
- **Impact estimat:** **+15-30 click-uri organice/lună** la același volum afișări (CTR 0,9% → 3-5% conservator). Multiplicator x2-3 pe organic search în 30 zile.

### Acțiune 2 — Adaugă 2 keyword-uri exact match (5 min)
- **Context:** „supraveghere video brașov" a generat 1 conv ieri la 6,29 RON. „firme autorizate montaj camere de supraveghere brașov" e un query cu intent puternic local care apare în search terms.
- **Efort:** 5 minute (Ads → Cuvinte cheie → Adăugare → `[supraveghere video brașov]` exact + `[firme autorizate montaj camere de supraveghere brașov]` exact).
- **Impact:** Bidding precis pe queries cu intent comercial confirmat.

### Acțiune 3 — Verifică buget Google Ads (10 min)
- **Context:** Vederea de 30 de zile arată cheltuială 879 RON, ~3x bugetul intenționat 300 RON/lună. Confirmare manuală necesară: e ajustare deliberată? Shared budget? Shared limit lipsă?
- **Efort:** 10 minute (Ads → Setări campanie → Buget zilnic + Setări cont → Limită cont).
- **Impact:** Predictibilitate spend lunar; previne surprize la facturare.

---

## 📌 Note pentru raportul săptămânal

Astăzi este **Vineri 8 mai** — următorul raport săptămânal este programat pentru **Luni 11 mai 2026**. Acumulează metricile zilnice până atunci.

**Tendințe vizibile pentru raportul săptămânal (W19):**
- 📈 GA4 utilizatori activi: 17 → 20 (+17,6% în 24h) — recuperare după drop-ul de pe 6 mai
- 📈 GA4 sesiuni Paid: 17 → 21 (+23,5%) — reclamele recâștigă tracțiune
- 📈 Camere Supraveghere afișări: 7 → 10 (+42,9% în 24h, +900% WoW) — pagină vedetă
- 📈 GSC poziție medie: 26,6 → 22,7 (-3,9 poziții — îmbunătățire) — ranking în creștere
- 📉 GSC clicuri: 5 → 3 (CTR 1,6% → 0,9%) — afișările cresc, conversia scade
- 📉 GA4 phone_call/form_submit/cta_click = 0 — alertă tagging conversii
- 🔴 Buget Google Ads la 879 RON/30z (de ~3x peste 300 RON intenționat) — verificare urgentă

**Punctul-cheie săptămânal:** suntem deja în top 3-10 organic pe queries-cheie, dar pierdem 100% trafic organic din cauza CTR snippet. Un sprint de 1-2 ore pe meta-uri săptămâna asta va deconecta lacătul.

---

*Raport generat automat din Google Ads (cont 666-033-6562, vedere 30 zile), GA4 (property a385388640, 1-7 mai 2026) și Search Console (sc-domain:cssi.ro, 30 apr – 6 mai 2026). Date capturate live via Claude in Chrome la 2026-05-08.*
