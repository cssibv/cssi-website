# CSSI — Daily Brief 22 mai 2026 (vineri)

> Perioada raportată: **15–21 mai 2026** (Google Ads, GA4) / **14–20 mai 2026** (GSC are lag 2 zile)
> Cont Google Ads: 666-033-6562 · GA4 property: a385388640 · GSC: sc-domain:cssi.ro

---

## TL;DR

- **Merge bine:** Google Ads stabil cu CTR 6,04 % (40 clicuri / 662 afișări, cost 141 RON) și „camere supraveghere brasov" a urcat la 21,4 % CTR; cost +13,44 % WoW arată difuzare sănătoasă. Trafic Romania în creștere (+21,1 % utilizatori). „Camere supraveghere" intră în trend cu +63 % clickuri vs. luna trecută.
- **Nu merge:** 0 conversii primare înregistrate în ultimele 7 zile pe 8 din 11 acțiuni de conversie; sesiunile GA4 din Organic scad −27,3 % și afișările de pagini −40,6 %; toate top queries GSC (camere supraveghere, pontaj electronic, smart home, sisteme securitate) au **0 clicuri** la poziție medie 19,5 (pagina 2 Google).
- **1 acțiune azi:** adaugă imediat negative keywords pentru `camere de luat vederi` și `camera video de supraveghere` (irelevante, au consumat ~30 RON săptămâna asta) și pornește audit tracking conversii pentru cele 8 acțiuni inactive.

---

## 1. Google Ads (15–21 mai 2026)

### Metrici cont

| Metric | Valoare 7 zile | WoW (vs. 7 zile anter.) |
|---|---|---|
| Afișări | 662 | — |
| Clicuri | 40 | — |
| CTR | 6,04 % | sub țintă (15–18 %) |
| Cost | 141 RON (camp. 140,91 RON) | **+13,44 %** |
| CPC mediu | 3,52 RON | — |
| Scor optimizare | 79,8 % | — |
| Conversii primare 7z | **0 înregistrate** | ⚠️ |
| Diagnostic | „Campania nu prezintă probleme de difuzare" | OK |

### Top 5 cuvinte cheie (după clicuri)

| Cuvânt cheie | Stare | Clic | Af. | CTR | CPC | Cost |
|---|---|---:|---:|---:|---:|---:|
| montaj camere supraveghere | Eligibilă | 30 | 383 | 7,83 % | 3,84 RON | 115,09 RON |
| pret camere de supraveghere exterior | Eligibilă | 4 | 88 | 4,55 % | 2,73 RON | 10,92 RON |
| control acces | Eligibilă | 1 | 33 | 3,03 % | 6,51 RON | 6,51 RON |
| kit camere supraveghere wireless exterior pret | Eligibilă | 1 | 53 | 1,89 % | 3,54 RON | 3,54 RON |
| sistem detectie incendiu | **Întreruptă** | 0 | 0 | — | — | — |

Observație: cuvântul cheie principal `montaj camere supraveghere` consumă 81,6 % din buget (115/141 RON). Concentrare extremă pe un singur termen — bine pentru intent comercial, dar fragil dacă scade CTR-ul lui.

### Top 5 termeni de căutare (search terms)

| Termen | Tip pot. | Clic | Af. | CTR | CPC | Cost | Notă |
|---|---|---:|---:|---:|---:|---:|---|
| camere supraveghere brasov | amplă | 3 | 14 | 21,4 % | 1,44 RON | 4,32 RON | ✅ relevant |
| sisteme de alarma pentru apartament | AI Max | 3 | 2 | 150 % | 0,97 RON | 2,92 RON | ✅ ieftin, relevant |
| camera supraveghere wireless exterior 360 grade | amplă | 2 | 1 | 200 % | 3,68 RON | 7,36 RON | ✅ |
| camere de luat vederi | amplă | 2 | 7 | 28,6 % | 2,10 RON | 4,21 RON | ⚠️ irelevant (filmare) |
| camere video de supraveghere | amplă | 2 | 1 | 200 % | **13,27 RON** | 26,54 RON | ⚠️ CPC anormal de mare |

**Total termeni de căutare 7z:** 23 clic / 340 af / 6,76 % CTR / 80,37 RON / rata conv. 8,7 %.

### Recomandări Google Ads active

- Setați rentabilitate vizată a cheltuielilor publicitare (+8 % scor optimizare) — recomandat când avem suficiente date conversie (deocamdată nu).
- **Eliminați 12 cuvinte cheie redundante** (apare ca alertă în cont) — corespunde task-ului pending #15.

---

## 2. GA4 (15–21 mai 2026)

### Metrici cheie (vs. perioada anterioară)

| Metric | 7 zile | Variație |
|---|---:|---:|
| Utilizatori activi | 24 | **+9,1 %** ✅ |
| Sesiuni totale | 34 (21 paid + 8 org + 3 dir + 2 alt) | — |
| Număr evenimente | 314 | **−25,4 %** ⚠️ |
| Evenimente importante (key) | 7 | **−12,5 %** |
| Afișări de pagină | 111 | **−40,6 %** 🚨 |
| Utilizatori în timp real (30 min) | 0 | — |

⚠️ **Anomalie majoră:** afișări de pagină −40,6 % deși utilizatorii cresc cu +9 %. Implică sesiuni mult mai scurte (utilizatori care văd 1 pagină și pleacă) — posibil probleme de UX pe landing-uri sau trafic mai puțin calificat.

### Top surse de trafic (Sesiuni)

| Sursă | Sesiuni | WoW |
|---|---:|---:|
| Paid Search | 21 | −4,5 % |
| Organic Search | 8 | **−27,3 %** ⚠️ |
| Direct | 3 | −75,0 % |
| Cross-network | 1 | 0,0 % |
| Unassigned | 1 | — |

### Top pagini (afișări)

| Titlu pagină | Afișări | WoW |
|---|---:|---:|
| Securitate & Instalații (`/`) | 25 | — |
| CSSI Brașov \| Sisteme... | 22 | −60,7 % |
| Sisteme Securitate Brașov | 15 | — |
| Contact CSSI Brașov | 12 | **+300,0 %** ✅ |
| Camere Supraveghere... | 6 | −77,8 % ⚠️ |
| Detecție Incendiu ISI... | 4 | −73,3 % ⚠️ |
| Servicii CSSI Brașov | 1 | −94,4 % 🚨 |

**Pozitiv:** pagina Contact +300 % afișări — utilizatorii ajung la pasul de conversie.
**Negativ:** paginile de servicii (Camere, Detecție Incendiu, Servicii) toate în picaj −74 %/−94 %.

### Țări

| Țară | Utilizatori | WoW |
|---|---:|---:|
| Romania | 23 | +21,1 % |
| Italy | 1 | — |
| United States | 0 | −100 % |

---

## 3. Google Search Console (14–20 mai 2026)

### Metrici cont

| Metric | 7 zile | Notă |
|---|---:|---|
| Clicuri | 14 | scăzut |
| Afișări | 495 | OK |
| CTR | 2,8 % | sub mediul industriei (~5 %) |
| Poziție medie | **19,5** | pagina 2 Google ⚠️ |
| Total queries | 77 | — |

### Top queries (după afișări)

| Interogare | Clic | Afișări | Poz. medie |
|---|---:|---:|---:|
| camere supraveghere | 0 | 36 | **14,8** |
| smart home integrare securitate | 0 | 12 | — |
| pontaj electronic | 0 | 11 | — |
| camere supraveghere brasov | 0 | 10 | — |
| sistem pontaj electronic | 0 | 10 | — |
| sistem pontaj | 0 | 10 | — |
| sisteme de securitate brasov | 0 | 8 | — |
| aer conditionat brasov | 0 | 8 | — |
| sisteme securitate | 0 | 7 | — |

🚨 **Constatare critică:** **0 clicuri organic pentru TOATE top queries.** Cele 14 clicuri totale vin din long-tail. Cuvintele cheie principale (camere supraveghere, pontaj electronic, smart home) sunt la pagina 2 — necesită SEO push agresiv pe titluri/meta/conținut pentru a urca în top 10.

### Status față de țintele 60 zile

| Țintă | Curent | Status |
|---|---|---|
| Rang „pontaj electronic" 12–15 | ~pagina 2 (>20) | 🔴 sub țintă |
| CTR 15–18 % | 6,04 % Ads / 2,8 % organic | 🔴 sub țintă |
| 12–20 conversii primare/lună | 0 înregistrate săpt. asta | 🔴 critică |
| Valoare conv. 600–1000 RON/lună | 0 RON săpt. asta | 🔴 critică |

---

## 🚨 Alerte

1. **CONVERSII = 0 înregistrate săptămâna asta.** Conform Google Ads, 8 din 11 acțiuni de conversie nu au înregistrat conversii recente; doar 3 acțiuni sunt active. Necesită audit imediat — fie tracking-ul este rupt, fie utilizatorii chiar nu mai sună/scriu.
2. **Drop organic GA4:** Organic Search −27,3 % sesiuni; afișări pagini −40,6 % overall. Pierdere clară de vizibilitate organică.
3. **0 clicuri GSC din top queries.** „Camere supraveghere" la poziția 14,8 (pagina 2), „smart home integrare securitate" cu 12 afișări fără clicuri.
4. **Negative keywords candidați:** `camere de luat vederi` (4,21 RON cost, intent filmare nu securitate), `camera video de supraveghere` (CPC 13,27 RON anormal).
5. **Detecție Incendiu și Camere Supraveghere** — pagini-cheie cu drop −73 % / −78 % afișări săpt. asta.
6. **Concentrare buget excesivă:** 81,6 % din cost vine de la 1 keyword (`montaj camere supraveghere`). Risc.

---

## ✅ Acțiuni propuse azi (vineri)

### Acțiune 1 (10 min, impact mediu)
**Adaugă 2 negative keywords:** `[camere de luat vederi]`, `[camera video de supraveghere]`.
- **De ce:** `camere de luat vederi` este termen pentru video-filmare/cinematografie, nu securitate. `camera video de supraveghere` are CPC 13,27 RON — de 4× peste media contului — sugerând concurență din alt vertical.
- **Impact estimat:** economisire ~30 RON/săpt. (= 120 RON/lună, 40 % din buget).

### Acțiune 2 (20 min, impact MARE)
**Audit tracking conversii.** Verifică în Google Ads > Obiective dacă cele 8 acțiuni „Nu există conversii recente" încă mai funcționează. Testează manual pe site:
- click pe numărul de telefon (event `phone_call`)
- click pe WhatsApp (event `whatsapp_click`)
- trimite formular contact (`form_submit`)
- click CTA „Solicită ofertă" (`cta_click`)
- **De ce:** dacă tracking-ul nu mai trimite evenimente, toate optimizările Ads sunt oarbe (Maximize Clicks merge mai departe dar nu putem migra la Maximize Conversions).
- **Impact estimat:** **blocant** pentru atingerea țintei de 15+ conversii necesară migrației bidding.

### Acțiune 3 (15 min, impact mediu)
**Aplică recomandarea Google: elimină 12 cuvinte cheie redundante** (banner roșu în cont).
- **De ce:** corespunde task #15 pending (cleanup keywords Low search volume). Curăță structura și pregătește terenul pentru restructurarea în 3 Ad Groups (task #16).
- **Impact estimat:** simplifică analiza și prevede dublarea bid-ului pe aceeași căutare.

---

## Status task-uri pending

| Task | Stare | Recomandare |
|---|---|---|
| #15 Cleanup keywords Low search volume | pending | ✅ Poate fi deblocat azi prin alerta „12 redundante" |
| #16 Restructurare în 3 Ad Groups | pending | ⏸️ Așteaptă după #15 |
| #17 Creare 3 RSA-uri noi (15 titluri + 4 desc.) | pending | ⏸️ Așteaptă după #16 |
| Migrare la Maximize Conversions | blocat | 🔴 Necesită 15+ conversii (acum 0) — vezi Acțiunea 2 |

---

*Raport generat automat de cssi-daily-monitoring (Cowork) — date GA4/Ads/GSC accesate la 22.05.2026.*
