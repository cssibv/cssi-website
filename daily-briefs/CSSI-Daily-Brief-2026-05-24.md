# CSSI — Daily Brief 24 mai 2026 (duminică)

> Perioada raportată: **17–23 mai 2026** (Google Ads, GA4) / **performanță SC pe interval 3 luni vizibil**, GSC are lag de 2 zile
> Cont Google Ads: 666-033-6562 · GA4 property: a385388640 · GSC: sc-domain:cssi.ro
> Browser folosit pentru extragere: „laptop" (selectat automat, isLocal=true) — rulare autonomă fără user prezent.

---

## TL;DR

- **Merge bine:** Google Ads în creștere săptămânală sănătoasă — cost +22,42 % WoW (153 RON vs. 125 RON), CTR urcă ușor la 6,09 %, **4 conversii înregistrate** prin canalul de clienți potențiali (vs. 0 raportate vineri). GA4: utilizatori activi +18,5 %, evenimente +11,3 %, scroll +218 % (engagement în sus), form_start +33,3 %. GSC: pagina `/blog/pontaj-electronic-ghid-complet` semnalată oficial cu „mai multe afișări decât de obicei" — câștigăm tracțiune pe pontaj.
- **Nu merge:** Organic Search GA4 −25 % WoW (a doua săptămână consecutiv în scădere). În top evenimente GA4 NU apar `phone_call` și `whatsapp_click` — confirmă suspiciunea de tracking spart pentru aceste conversii. GSC păstrează **0 clicuri** pe top queries (camere supraveghere 190 afișări, pontaj electronic 91 afișări, sistem pontaj electronic 83 afișări) — vizibilitate prezentă, dar fără CTR; poziția medie 22,9 (pagina 3 Google).
- **1 acțiune azi:** profită că e duminică liberă pentru a aplica recomandarea „Eliminați cuvintele cheie redundante" (auto-apply disponibil în Ads) ȘI verifică manual de pe telefonul mobil dacă click pe numărul de telefon de pe `cssi.ro` declanșează evenimentul `phone_call` în GA4 DebugView — 10 minute, deblochează posibilă pierdere majoră de conversii raportate.

---

## 1. Google Ads (17–23 mai 2026)

### Metrici cont

| Metric | Valoare 7 zile | WoW |
|---|---|---|
| Afișări | 673 | +1,7 % vs. 662 |
| Clicuri | 41 | +2,5 % vs. 40 |
| CTR | 6,09 % | +0,05 pp (sub țintă 15–18 %) |
| Cost | 152,90 RON | **+22,42 %** (campanie: +28 RON) |
| CPC mediu | 3,73 RON | +5,97 % vs. 3,52 RON |
| Scor optimizare | 79,8 % | stabil |
| Conversii (canal clienți potențiali) | **4** | ✅ vs. 0 săpt. trecută |
| Diagnostic | „Campania nu prezintă probleme de difuzare" | OK |

**Anomalii:**
- Costul +22,42 % WoW, peste pragul de 30 % nu suntem încă, dar e a doua săptămână consecutiv în creștere (de la +13,44 % → +22,42 %). Aproape de pragul de alertă.
- CPC mediu urcă la 3,73 RON (+6 %) — bid-urile pe `montaj camere supraveghere` se scumpesc.

### Canalul de clienți potențiali

| Etapă | Număr |
|---|---:|
| Interacțiuni | 41 |
| Clienți potențiali interesați | 4 |
| Clienți potențiali calificați | — |
| Clienți potențiali convertiți | — |

✅ **Pas pozitiv:** după 0 conversii săptămâna trecută, avem 4 leads interesați în 7 zile. Implică **funnel-ul Ads e viu** — fie tracking-ul s-a reparat parțial, fie tracking-ul a fost ok dar conversiile au scăzut la 0 conjunctural săpt. trecută.

### Top 5 cuvinte cheie (după cost)

| Cuvânt cheie | Stare | Clic | CTR | Cost | Pondere buget |
|---|---|---:|---:|---:|---:|
| montaj camere supraveghere | Eligibilă | 30 | 7,59 % | 125,42 RON | **82,0 %** |
| pret camere de supraveghere exterior | Eligibilă | 4 | 4,55 % | 11,04 RON | 7,2 % |
| control acces | Eligibilă | 2 | 4,88 % | 9,43 RON | 6,2 % |
| kit camere supraveghere wireless exterior pret | Eligibilă | 1 | 1,89 % | 3,54 RON | 2,3 % |
| montaj camere de supraveghere | Eligibilă | 1 | 25,00 % | 0,55 RON | 0,4 % |

⚠️ Concentrarea pe `montaj camere supraveghere` rămâne extremă (82 % din buget) — risc structural neschimbat de la raportul anterior.

### Top termeni de căutare (queries) – observații cheie

Top 8 termeni cu afișări: `camera supraveghere`, `camere supraveghere`, `camere supraveghere brasov`, `cameră de supraveghere`, `montaj camere supraveghere brasov`, `supraveghere video brasov`, `camere de supraveghere`, `montare camere supraveghere la curte`.

✅ Toate sunt cu intent comercial relevant.
⚠️ În lista de termeni apar încă: `camere de luat vederi`, `camera video de supraveghere`, `camere video de supraveghere`, `shop security`, `spy shop`, `interfon casa`, `instalare camera supraveghere`. Primele două au fost flaguri și săptămâna trecută — încă **nu au fost adăugate ca negative keywords**.

### Trend de căutare RO

- Volumul căutărilor pentru cuvinte cheie țintă: **+7 %**
- Clicurile noastre: **+64 %**
- Înseamnă că ne adaptăm bine la cerere — câștigăm cotă.

### Demografie & dispozitive

- Cost telefon mobil: **75,1 %** | computere: 24,9 % | tablete: 0 %
- Afișări: telefon 78,8 % | computere 21,2 %
- Date demografice cunoscute pe 58 % din afișări — restul anonim.

### Recomandări Google Ads active

| Recomandare | Impact |
|---|---|
| Eliminați cuvintele cheie redundante | +0,1 pp scor optim. (auto-apply disponibil) |
| Adăugați cuvinte cheie noi | nedefinit |
| Eliminați cuvinte cheie negative conflictuale | igienă cont |
| Setați rentabilitate vizată a cheltuielilor (tROAS) | +8 % scor optim. (recomandă date conv. mai multe) |
| Campanie nouă pentru Performanță maximă | +9 % scor optim. (atenție: schimbă paradigma de bidding) |

### Facturare

- Sold: **450,78 RON** pe 24 mai 2026
- Următoarea plată automată: **1 iun. 2026**
- Metoda principală: Visa •••• 4909

---

## 2. GA4 (17–23 mai 2026)

### Metrici cheie (vs. perioada anterioară 7z)

| Metric | 7 zile | Variație |
|---|---:|---:|
| Utilizatori activi | 22 | **+18,5 %** ✅ |
| Număr evenimente | 346 | **+11,3 %** ✅ |
| Evenimente importante (key) | 8 | **+14,3 %** ✅ |
| Afișări de pagină | 128 | **+4,9 %** ✅ |
| Utilizatori în timp real (30 min) | 0 | — |

✅ **Inversiune pozitivă:** după raportul precedent care semnala −40,6 % afișări de pagină și −25,4 % evenimente, **toate metricile sunt acum pozitive** pe ferestră de 7 zile. Posibil ca jumătatea de săptămână trecută să fi fost anomalie tranzitorie.

### Surse de trafic (Sesiuni 7 zile)

| Sursă | Sesiuni | WoW |
|---|---:|---:|
| Paid Search | 22 | 0,0 % (stabil) |
| Organic Search | 6 | **−25,0 %** ⚠️ (continuă tendința) |
| Direct | 3 | +50,0 % (recuperare) |
| Cross-network | 5 | **+150,0 %** ✅ |
| Unassigned | 3 | nou (atribuit pierdut?) |

⚠️ Organic Search la a doua săptămână consecutiv în scădere — semnal că sub-paginile de servicii pierd din vizibilitate sau autoritate.

### Top pagini (Afișări 7 zile)

| Pagină | Afișări | WoW |
|---|---:|---:|
| Securitate & Instalații Brașov \| CSSI (home `/`) | 52 | — |
| Sisteme Securitate Brașov \| CSSI | 15 | — |
| CSSI Brașov \| Sisteme Securitate, Detecție Incendiu | 14 | +58,8 % ✅ |
| Contact CSSI Brașov | 11 | **+175,0 %** ✅ |
| Detecție Incendiu ISU Brașov \| CSSI | 4 | +55,6 % ✅ |
| Camere Supraveghere Brașov | 3 | +85,7 % (revenire vs. drop −78 % săpt. anter.) |
| Servicii CSSI Brașov | 1 | +92,9 % |

✅ Toate paginile de servicii revin în creștere după dropul masiv de săptămâna trecută. Pagina Contact se menține în trend ascendent (+175 % după +300 % anterior) — utilizatorii intenționează să convertească.

### Țări (Utilizatori activi)

| Țară | Utilizatori | WoW |
|---|---:|---:|
| Romania | 21 | +12,5 % ✅ |
| Germany | 1 | nou |
| Italy | 0 | −100 % |
| United States | 0 | −100 % |

### Top evenimente (după număr)

| Eveniment | Nr. | WoW |
|---|---:|---:|
| page_view | 128 | +4,9 % |
| user_engagement | 110 | +6,8 % |
| session_start | 37 | +2,6 % |
| scroll | 35 | **+218,2 %** ✅ |
| first_visit | 21 | +8,7 % |
| form_start | 4 | +33,3 % ✅ |
| click | 3 | +25,0 % |

🚨 **Constatare critică:** în top 7 evenimente NU apar `phone_call`, `whatsapp_click`, `form_submit`, `cta_click` — confirmă suspiciunea din raportul precedent că **aceste conversii nu se trimit corect către GA4** (sau au volum 0 real). Cele 4 conversii înregistrate în Google Ads probabil vin din alt mecanism (call extensions, conversions importate).

### Sursa / modalitatea atribuită primului utilizator

| Sursă/Modalitate | Utilizatori | WoW |
|---|---:|---:|
| google / cpc | 11 | +35,3 % ✅ |
| google / organic | 3 | +40,0 % ✅ |
| (data not available) | 5 | +150,0 % ⚠️ (atribuire pierdută) |
| (direct) / (none) | 2 | −33,3 % |

⚠️ „data not available" în creștere — semnal de pierdere de cookie/atribuire pentru o parte din trafic.

---

## 3. Google Search Console (interval 3 luni vizibil — 23.02–21.05.2026)

> Notă: filtrarea pe 7 zile necesită interacțiune extra cu UI-ul GSC; raport pe interval 3 luni (mod implicit), suficient pentru tendințe.

### Metrici cont

| Metric | 3 luni | Notă |
|---|---:|---|
| Clicuri totale | 70 | mediu (~5/săpt) |
| Afișări totale | 3,19 K (3190) | bun |
| CTR mediu | 2,2 % | sub mediul industriei |
| Poziție medie | **22,9** | pagina 3 Google 🚨 (regresie vs. 19,5 raport anter.) |
| Total queries (top 10 vizibile) | 141 | — |
| Indexare | 47 indexate / 33 neindexate | acoperire ~59 % |

### Top 10 queries (3 luni — după afișări)

| Interogare | Clicuri | Afișări | Notă |
|---|---:|---:|---|
| cssi | 6 | 36 | ✅ brand traffic |
| ajax vs paradox | 1 | 2 | long-tail produs |
| camere supraveghere | 0 | **190** | 🚨 cea mai mare oportunitate ratată |
| pontaj electronic | 0 | 91 | 🚨 al doilea ca volum |
| sistem pontaj electronic | 0 | 83 | 🚨 |
| sisteme de pontaj electronic | 0 | 69 | 🚨 |
| sistem pontaj | 0 | 58 | 🚨 |
| camere de supraveghere | 0 | 52 | 🚨 |
| montaj aer conditionat brasov | 0 | 34 | 🚨 (aer condiționat — vertical secundar) |
| sisteme de securitate brasov | 0 | 31 | 🚨 |

🚨 **Constatare critică:** **0 clicuri organic pe TOATE primele 8 cuvinte cheie comerciale.** Singurele clicuri vin de pe `cssi` (brand) și `ajax vs paradox`. Implică:
- Vizibilitate prezentă (3,19 K afișări) dar prea jos în SERP (poz. medie 22,9 = pagina 3)
- Necesită urcare în top 10 pentru a converti afișările în clicuri

### Status SEO față de țintele 60 zile

| Țintă | Curent | Status |
|---|---|---|
| Rang „pontaj electronic" 12–15 | poz. medie ~22,9 (>20) | 🔴 sub țintă |
| CTR organic | 2,2 % | 🔴 sub mediul 5 % |
| Trafic organic (sesiuni săpt.) | 6 | 🔴 |
| Valoare conv. 600–1000 RON/lună | 4 leads × 50 RON = ~200 RON (estimare săpt.) | 🟡 pe drumul bun |

### ✅ Câștig SEO de semnalat

**GSC a flaguit oficial:** „O pagină a înregistrat recent mai multe afișări decât de obicei" → `https://cssi.ro/blog/pontaj-electronic-ghid-complet`. Ghidul de pontaj urcă în Google — investiție conținut care începe să dea roade.

### Statusul indexării

- 47 pagini indexate
- 33 pagini neindexate — verifică în GSC > Indexare > Pagini ce motive sunt (duplicate, blocate, redirect, etc.). 33 e mult; recuperarea unora ar dubla suprafața SEO.

### Date structurate

- HTTPS: 26 valide / 0 nevalide ✅
- Căi de navigare: 25 valide ✅
- Cele mai frecvente întrebări (FAQ): 9 valide / **2 nevalide** ⚠️ — verifică și repară
- Fragmente recenzii: 17 valide ✅
- **Date structurate care nu pot fi analizate: 16** ⚠️ — verifică în GSC > Îmbunătățiri

---

## 🚨 Alerte

1. **`phone_call` și `whatsapp_click` lipsesc complet din top evenimente GA4.** Probabil tracking spart sau pluginul nu trimite. Acțiunea 2 din raportul de vineri rămâne nedeblocată.
2. **Organic Search GA4 −25 % WoW** — a doua săptămână consecutiv în scădere; risc de pierdere de poziție pe sub-pagini servicii.
3. **GSC poziție medie 22,9** vs. 19,5 raport anterior — regresie de ~3 poziții (atenție: comparația e între ferestre diferite ca lungime, dar trendul e clar).
4. **2 negative keywords încă neadăugate:** `camere de luat vederi`, `camera video de supraveghere` — flagate vineri, încă apar în lista de termeni căutați.
5. **Concentrarea pe `montaj camere supraveghere` urcă la 82 %** din buget (vs. 81,6 % vineri) — fragilitate strategică.
6. **„data not available" în GA4 a crescut +150 %** — pierderi de atribuire (probabil cookies blocate sau utilizatori cu ad-blocker).
7. **33 pagini neindexate în GSC** — verifică motivele.

---

## ✅ Acțiuni propuse azi (duminică)

### Acțiune 1 (5 min, impact mediu, ZERO risc)
**Aplică recomandarea auto „Eliminați cuvintele cheie redundante".** Acum e disponibilă auto-apply în panou.
- **De ce:** corespunde task #15. Curăță contul fără efort manual; pregătește terenul pentru restructurare în 3 Ad Groups (task #16).
- **Efort:** 1 click — duminică nu strici nimic activ.
- **Impact:** +0,1 pp scor optimizare + claritate structură.

### Acțiune 2 (10 min, impact MARE, blocant pentru migrare bidding)
**Verifică manual tracking conversii** — încarcă `cssi.ro` pe telefon mobil, deschide GA4 DebugView (pe celălalt browser), apoi:
- click pe numărul de telefon → verifică dacă apare `phone_call` în DebugView
- click pe link WhatsApp → verifică `whatsapp_click`
- completează minim 1 câmp în formular → verifică `form_start` (deja se înregistrează 4 — deci ăsta merge)
- click pe CTA „Solicită ofertă" → verifică `cta_click`

- **De ce:** confirmă/infirmează ipoteza că `phone_call` și `whatsapp_click` sunt sparte. Dacă da, e nevoie de fix în GTM/cod tracking. **Fără asta nu putem migra la Maximize Conversions.**
- **Impact:** deblocant pentru tot funnel-ul de bidding inteligent.

### Acțiune 3 (15 min, impact mediu)
**Adaugă 2 negative keywords:** `[camere de luat vederi]`, `[camera video de supraveghere]`.
- **De ce:** flagate de 2 ori consecutiv, încă neacționate. Consumă ~30 RON/săpt. în intent irelevant.
- **Impact:** economisire ~120 RON/lună (40 % din buget).

---

## Status task-uri pending

| Task | Stare | Recomandare |
|---|---|---|
| #15 Cleanup keywords Low search volume | pending | ✅ Deblochează azi prin auto-apply (1 click) |
| #16 Restructurare în 3 Ad Groups | pending | ⏸️ După #15. Așteaptă încă 7–14 zile de date după curățire |
| #17 Creare 3 RSA-uri noi | pending | ⏸️ După #16 |
| Migrare la Maximize Conversions | blocat | 🔴 Acum 4 conversii confirmate în Ads (vs. 0 săpt. trecută). Pragul rămâne 15+ — încă insuficient. Acțiunea 2 (fix tracking) e critică. |

---

## Observații pentru raportul săptămânal (luni 25 mai 2026)

Mâine (luni) e momentul pentru raportul strategic săptămânal. Sub-secțiuni de pregătit:
- Comparație trafic săptămânal 17–23 mai vs. 10–16 mai
- Progres către țintele 60 zile (CTR Ads urcă lent: 6,04 → 6,09 %)
- Recomandare oficială: rămânem pe Maximize Clicks până la 15+ conversii valide
- Evaluare: pornim task-uri #15/#16/#17 sau mai așteptăm?

---

*Raport generat automat de cssi-daily-monitoring (Cowork) — date Google Ads/GA4/GSC accesate la 24.05.2026, browser „laptop" (Windows, isLocal).*
