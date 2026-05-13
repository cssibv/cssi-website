# CSSI — Raport zilnic monitorizare
**Data:** 2026-05-13 (Miercuri, săptămâna 20)
**Cont Google Ads:** 666-033-6562 | **Buget:** 60 EUR/lună (~300 RON)
**Generat de:** scheduled task `cssi-daily-monitoring`
**Date live capturate:** ✅ Google Ads (7 zile, 6–12 mai) | ✅ GA4 (7 zile, 6–12 mai) | ✅ Search Console (7 zile, 4–10 mai)

> **Notă metodologică:** Browser-ul activ a fost `laptop` (selectat automat — task scheduled, fără confirmare interactivă). Datele de tip „pe rând tabel" (keyword level din Google Ads) sunt stocate într-un format DOM virtualizat care nu permite extracție prin scraping; metricii la nivel de cont și diagnosticarea contului au fost capturate complet. Comparațiile WoW folosesc raportul precedent (11-mai, fereastra 3–9 mai) ca punct de referință.

---

## TL;DR (3 linii)
- **Ce merge bine:** Costul Google Ads s-a calmat clar săptămâna asta — 127,65 RON / 7 zile (-12,1% față de 145,23 RON precedent), proiecție lunară 547 RON (în scădere de la 622 → 547 RON, dar tot peste targetul de 300 RON). Pe organic, poziția medie GSC continuă ușoara ameliorare (18,3 → 17,8, -0,5 poziții). Sesiunile Paid Search GA4 cresc la 21 (+40% vs prior), Organic Search explodează 1 → 7 (+600%).
- **Ce nu merge:** 🔴 **Evenimente importante GA4 7d = 5** (vs 9 raportate luni pe fereastra 3–9 mai → **-44% WoW**). Doar `whatsapp_click` (2) și `click` (1) sunt vizibile în lista evenimente — `phone_call`, `form_submit`, `generate_lead` lipsesc (verifică tagging și asigură-te că nu au fost dezactivate accidental). 🔴 Queries cu trafic GSC `camere supraveghere` (50 af), `pontaj electronic` (20 af) — 0 clickuri (poziție medie 17,8). 🔴 Diagnostic Google Ads: „Nu există suficiente cuvinte cheie relevante" — warning Segmente de public.
- **Acțiune recomandată azi:** Verifică în GA4 dacă evenimentele `phone_call`, `form_submit`, `generate_lead` sunt încă active (Admin → Evenimente). Dacă sunt active dar nu se declanșează, problemă în Apps Script sau site-ul a regresat. Dacă sunt marcate „dezactivat" — reactivează. Este #1 acțiune deoarece o scădere -44% WoW pe conversii primare e cea mai gravă alertă a ciclului.

---

## 1. Google Ads — captură 7 zile (6–12 mai 2026)

### Metrice cont
| Metric | Valoare 7 zile | Δ vs 7d anterior (3–9 mai) | Țintă | Stare |
|--------|---------------|-----------------------------|-------|-------|
| Afișări | 483 | -11,2% (544 → 483) | n/a | 🟡 Scade ușor |
| Clicuri | 45 | -11,8% (51 → 45) | n/a | 🟡 Scade ușor |
| **CTR campanie** | **9,31%** | -0,07pp (9,38 → 9,31) | 15-18% | 🔴 Sub țintă |
| CPC mediu | 2,84 RON | -0,4% (2,85 → 2,84) | <4 RON | 🟢 OK |
| Cost total | 127,65 RON | **-12,1%** (145,23 → 127,65) | ~70 RON/săpt | 🟡 Peste pace, dar îmbunătățire vizibilă |
| Proiecție lunară | ~547 RON | de la 622 prev. | 300 RON | 🟡 Tot ~82% peste buget |
| Status difuzare | „A primit conversii, dar încă are o problemă care poate limita difuzarea" | — | — | 🟡 Diagnostică deschisă |
| Diagnosticarea Segmente de public | „Nu există suficiente cuvinte cheie relevante" (warning) | nou semnalat azi | — | 🟡 Verifică |

> **Notă buget (7 zile):** Costul a scăzut 17,58 RON față de săptămâna anterioară — semnalul că restrângerea bid-ului sau ajustările locație/ore intră în vigoare. Proiecția lunară 547 RON încă peste 300 RON, dar tendința e clar bună (de la 880 → 622 → 547 RON pe ultimele 3 vederi 30z/7d).

### Istoricul modificărilor (ultimele 90 zile filtrate)
- 6 modificări detectate în fereastra evaluată (text complet de inspectat manual în interfața UI — date capturate la nivel de meta).

### Recomandare Google bidding strategy
Nu re-evaluată live azi (necesită navigare în pagina Recomandări — păstrăm semnalul din raportul de luni: **„Maximizați valoarea conversiilor" cu +17,4% potențial**, aplicabil când contul depășește ~15 conversii înregistrate consistent).

### Notă tehnică — extragere keywords
🟡 **Limitare extracție:** Tabel `ess-table` Google Ads folosește renderare virtualizată cu date encodate JSON heavy escape — scraping rând cu rând la nivel keyword nu reușește din DOM tree. Cifrele agregate (Cost / Click / Impr / CTR / CPC) sunt corecte. Topul de keyword nu a putut fi re-capturat azi — folosește valorile din raportul de luni (11-mai) ca aproximare; tendința la nivel de cont indică o stabilizare, nu o reașezare majoră a portfoliului.

---

## 2. GA4 — trafic 7 zile (6–12 mai 2026)

| Metric | 7 zile | Δ vs perioada anterioară | Δ vs raport luni 11-mai (3–9 mai) |
|---|---|---|---|
| Utilizatori activi | 20 | 🟢 +33,3% | 🟡 -4,8% (21 → 20) |
| Utilizatori noi | 16 | 🟢 +6,7% | 🟡 -5,9% (17 → 16) |
| **Evenimente importante** | **5** | 🔴 **-16,7%** | 🔴 **-44,4% (9 → 5)** |
| Număr de evenimente | 273 | 🟢 +131,4% | n/a |
| Utilizatori activi live (acum) | 1 (Brașov) | — | — |

### 🚨 Alertă critică conversii
Evenimentele importante au scăzut la 5 (de la 9 luni). În lista de evenimente vizibile azi:
- `whatsapp_click` — 2 (vs 3 luni)
- `click` — 1
- `page_view` — 116, `user_engagement` — 88, `session_start` — 38, `first_visit` — 16, `scroll` — 9

**Evenimente lipsă față de săptămâna trecută:** `phone_call`, `form_submit`, `generate_lead`. Verifică:
1. **GA4 Admin → Evenimente:** sunt marcate „înregistrare" / „conversion" activă?
2. **Apps Script CSSI:** trigger-ele se execută corect? (ultima modificare 23-apr)
3. **Site live:** butoanele „solicita ofertă" / form-ul contact trimit eventul?

### Sesiuni după sursă (7 zile)
| Sursă | Sesiuni | Δ vs prior |
|---|---|---|
| **Paid Search** | 21 | 🟢 +40,0% |
| Direct | 10 | 🟢 (de la 0) |
| **Organic Search** | 7 | 🟢🟢 +600,0% |
| Cross-network | 0 | 🔴 -100% (de la 1) |

### Top 5 surse atribuire primar utilizator
| Sursă/Modalitate | Utilizatori activi | Δ |
|---|---|---|
| google / cpc | 13 | 0,0% (stabil) |
| (direct) / (none) | 4 | — |
| google / organic | 3 | 🟢 +200% |
| (data not available) | 0 | -100% |

### Top 7 pagini afișări (Δ vs perioada anterioară)
| Pagină | Afișări | Δ |
|---|---|---|
| Homepage CSSI | 52 | 🟢 +147,6% |
| Camere Supraveghere Brașov | 14 | 🟢 +133,3% |
| Detecție Incendiu ISU | 6 | 🟢 +500% |
| Portofoliu CSSI | 7 | (stabil) |
| Servicii CSSI | 5 | 🟢 +400% |
| Alarmă Antiefracție | 4 | 🟢 +300% |
| Contact CSSI | 4 | 🟢 +300% |

> 🟢 Distribuția traficului pe pagini funcționale crește vizibil — Detecție Incendiu (+500%) și Servicii (+400%) ies din zona „0 trafic" pentru prima dată. Continuă pe direcția asta (linkuri interne, schema markup, conținut).

### Țări
| Țară | Utilizatori activi | Δ |
|---|---|---|
| Romania | 17 | 🟢 +13,3% |
| United States | 3 | — (probabil bot/crawler — verifică în Tehnologie) |

---

## 3. Google Search Console — 7 zile (4–10 mai 2026)

| Metric | Valoare 7 zile | Δ vs 7d anterior (raport 11-mai) |
|---|---|---|
| Total clicuri | 6 | 🟢 +20% (5 → 6) |
| Total afișări | 393 | ⚪ (n/a, prior nu numeric) |
| CTR mediu | 1,5% | 🟢 +0,23pp (1,27 → 1,5) |
| **Poziție medie** | **17,8** | 🟢 -0,5 poziții (18,3 → 17,8, ameliorare) |

### Top 10 interogări (Clicuri / Afișări)
| # | Query | Cli | Af | Notă |
|---|---|---|---|---|
| 1 | camere supraveghere | 0 | 50 | 🔴 0 clic la 50 af — meta-snippet de optimizat URGENT |
| 2 | pontaj electronic | 0 | 20 | 🔴 0 clic la 20 af — pagina pontaj are nevoie de schema + meta agresivă |
| 3 | camere de supraveghere | 0 | 10 | 🔴 idem |
| 4 | sisteme de pontaj | 0 | 7 | 🟡 |
| 5 | camere supraveghere brasov | 0 | 6 | 🔴 Query principal — meta-title CTR la 0%? Re-verifică |
| 6 | securitate smart home | 0 | 6 | 🟡 Topic nou semnalat — content gap? |
| 7 | smart home integrare securitate | 0 | 5 | 🟡 idem |
| 8 | sisteme securitate | 0 | 5 | 🟡 |
| 9 | sisteme de pontaj electronic | 0 | 5 | 🟡 |
| 10 | sisteme de securitate brasov | 0 | 4 | 🟡 |

**Total 45 queries** în fereastra 4–10 mai (vs 38 la raport precedent — pool query crește).

---

## 🚨 Alerte (priority)

### 🔴 CRITIC
1. **Conversii GA4 -44% WoW (9 → 5)** — `phone_call`, `form_submit`, `generate_lead` lipsesc complet din lista evenimente azi. Acțiune: deschide [GA4 Events Admin](https://analytics.google.com/analytics/web/#/a385388640p525787706/admin/events) → verifică status; verifică Apps Script `CSSI-Apps-Script-v3-MARKETING.gs` să nu fi fost dezactivat.
2. **CTR organic la 0% pe top 5 queries de mare volum (camere supraveghere, pontaj electronic, camere supraveghere brasov)** — meta-descriptions nu au fost încă re-optimizate per acțiunea #1 din raportul de luni.

### 🟡 ATENȚIE
3. **CTR Ads 9,31%** rămâne sub țintă 15-18% (delta -6pp până -9pp) — depinde de execuția task-urilor #16 și #17 (3 Ad Groups tematice + 3 RSA-uri noi).
4. **Buget peste pace** — proiecție 547 RON / 300 RON țintă lunară (=182%). Trendul scade (de la 622 prev) dar nu suficient. Considerează capare zilnic 12-15 RON dacă vrei să rămâi în budget.
5. **Diagnostic Ads — Segmente de public**: warning „Nu există suficiente cuvinte cheie relevante" — verifică în pagina Diagnosticarea contului ce sugerează Google.

### 🟢 SEMNALE POZITIVE
- Cost Ads -12% WoW (eficientizare reală).
- GSC pool 38 → 45 queries (rang mai larg de relevanță).
- Pagini funcționale (Detecție Incendiu, Servicii) ies din 0-trafic.

---

## ✅ Acțiuni propuse azi

### Acțiune 1 (P0) — Investigare conversii GA4
- **Context:** -44% WoW pe Evenimente importante. Cel mai grav semnal al ciclului.
- **Pași:**
  1. Deschide GA4 → Admin → Date Display → Evenimente
  2. Verifică status `phone_call`, `form_submit`, `generate_lead`, `whatsapp_click` (toate „marcat ca eveniment cheie")
  3. Dacă oricare e dezactivat → activează-l înapoi
  4. Test live: deschide site, click pe „Solicită ofertă" → vezi în GA4 DebugView dacă evenimentul vine
- **Efort:** 15 min
- **Impact așteptat:** Recuperează 4 conversii lipsă/săpt = 200 RON/lună valoare conversie

### Acțiune 2 (P1) — Re-optimizare meta pentru queries cu 0 click
- **Context:** 91 afișări (50+20+10+6+5) pe primele 5 queries → 0 clickuri. Poziție medie ~18 — în page 2; chiar și pe page 2 ar trebui să avem CTR 1-3%. Snippet-ul ne dezavantajează.
- **Pași:**
  1. Verifică meta-title și meta-description pentru `/camere-supraveghere.html`, `/sistem-pontaj-electronic.html`, `/camere-supraveghere-brasov.html`
  2. Re-scrie cu pattern: `{Topic} {Brașov} | {Valoare} | CSSI` (max 60 char)
  3. Description: include „call now / WhatsApp 24/7 / 8.700+ proiecte" (max 155 char)
  4. Deploy și retrimite pentru indexare în GSC
- **Efort:** 45 min
- **Impact așteptat:** CTR organic 1,5% → 3-4% în 2 săptămâni = ~10-15 clickuri/săpt extra

### Acțiune 3 (P2) — Deblochează task #16 + #17
- **Context:** Cu 14 zile de date noi (3-9 mai 9 conv, 6-12 mai 5 conv), nu am atins încă pragul stabil de 15+ conv pe setări noi. Aștept încă o săptămână de stabilitate.
- **Decizie:** Amână până la raportul de luni 18-mai (raport săptămânal). Dacă conversiile revin >7/săpt după fixarea evenimentelor (Acțiune 1), DEBLOCHEZ task-uri #16 și #17.
- **Efort:** 0 azi (doar amânare decizie)
- **Impact:** Sincronizat cu logica strategică pentru a evita refactor pe date instabile.

---

## 📊 Progres către țintele 60 zile (revizuit)
| Țintă | Curent | Progres |
|---|---|---|
| CTR Ads 15-18% | 9,31% | 🔴 ~52% |
| Conversii primare/lună | ~22/lună (proiecție din 5/săpt) | 🟡 ~110% țintă inferioară 12-20 — DAR cifra e sub semnul întrebării (event tracking) |
| Rang „pontaj electronic" GSC | poz 17,8 (overall site) | 🟡 nu am breakdown per query azi |
| Valoare conversii / lună | ~250 RON (whatsapp 50×5) | 🟡 ~42% țintă inferioară 600 |

---

**Browser folosit pentru capturare:** `laptop` (deviceId 75c174a1...)
**Generat:** 2026-05-13 (Miercuri)
**Următorul raport săptămânal:** Luni, 18-mai-2026

---

📁 **Arhivă raport:** `computer://C:\Users\Diaconu Mihai\Documents\Website\cssi-website\daily-briefs\CSSI-Daily-Brief-2026-05-13.md`
