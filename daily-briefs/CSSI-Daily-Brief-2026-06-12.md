# CSSI Daily Brief — 2026-06-12 (vineri)

## TL;DR
- ✅ **Ce merge:** Trafic în creștere puternică — sesiuni GA4 +48,5% (49), afișări Ads +47% (553), organic google +143% sesiuni. 2 conversii Ads = 300 RON valoare.
- ⚠️ **Ce nu merge:** Alertă activă în cont: **„Grupurile de anunțuri nu conțin anunțuri"** — grupurile noi (task #16) rulează fără RSA-uri. CTR Ads 7,59% (țintă 15–18%). Evenimente importante GA4 −25%.
- 🎯 **Acțiunea zilei:** Deblochează **task #17** — creează RSA-urile pentru grupurile fără anunțuri (alerta blochează direct livrarea pe Alarme/Acces/Pontaj/Incendiu).

---

## Google Ads (5–11 iun, 7 zile)

| Metrică | Valoare | vs. perioada anterioară |
|---|---|---|
| Afișări | 553 | +178 (+47%) |
| Clicuri | 42 | — |
| CTR | 7,59% | sub ținta 15–18% |
| CPC mediu | 3,42 RON | — |
| Cost | 143,47 RON | +16,53 RON (+13%) |
| Conversii | 2,00 | +0 (stagnare) |
| Valoare conversii | 300,00 RON | — |
| Cost/conversie | 71,73 RON | — |
| Rata de conversie | 4,76% | — |
| Optimization score | 72,3% | — |

- **Ieri (11 iun):** 9 clicuri, 61 afișări, CPC 2,49 RON, cost 22,44 RON (peste media zilnică de ~20 RON).
- **Buget:** 19 RON/zi → cost 7 zile 143 RON vs. ~133 RON buget teoretic — ușoară depășire, normală (pacing Google), de urmărit.
- **Bid strategy actuală: „Maximizați valoarea conversiilor"** — contul a migrat deja de la Maximize Clicks (notă: contextul intern indica încă Maximize Clicks; de actualizat referința).
- **Dispozitive:** 84,9% mobil / 11,1% desktop / 4% tablete (cost).
- **Search impression share:** necapturat în această rulare (coloana nu era expusă în vizualizările accesate).

**Top termeni de căutare (după clicuri):**

| Termen | Clicuri | Afișări | CTR | Cost |
|---|---|---|---|---|
| montaj camere supraveghere brasov | 2 | 6 | 33,3% | 2,39 RON |
| cat costa o camera de supraveghere | 2 | 2 | 100% | 3,10 RON |
| sistem control acces cu card | 1 | 1 | 100% | 2,87 RON |
| camera supraveghere | 1 | 14 | 7,1% | 1,25 RON |
| camere supraveghere brasov | 1 | 7 | 14,3% | 3,97 RON |

(Total termeni raportabili: 21 clicuri / 261 afișări / 1 conversie / 51,19 RON)

**Top cuvinte cheie (după clicuri):** montaj camere supraveghere (26), pret camere de supraveghere exterior (6), kit camere supraveghere wireless exterior pret (1), proiectare sisteme securitate (1), camere supraveghere pret (1). Toate clicurile vin din grupul **Camere Supraveghere** — celelalte grupuri tematice nu livrează (vezi alerta).

**Anomalie:** afișări +47% dar conversii stagnante la 2 → creșterea de volum nu se traduce încă în lead-uri; presiunea trebuie pusă pe RSA-uri noi + extensii.

---

## GA4 (ultimele 7 zile; comparativ cu per. anterioară)

- **Utilizatori activi:** 34 (+9,7%) · **Utilizatori noi:** 30 (+7,1%) · **Sesiuni:** 49 (+48,5%)
- **Evenimente importante:** 3 (−25,0%) 🚨
- **Canale (sesiuni):** Paid Search 22 (+22%), Organic Search 21 (+91%), Direct 3, Unassigned 2, Cross-network 1
- **Surse:** google/cpc 22, google/organic 17 (+143%), bing/organic 4, direct 3
- **Top pagini (afișări):** Camere Supraveghere & Alarme (30), Camere Supraveghere de la 1.500 RON (9), Servicii Securitate & Instalații (9), Contact (5), Portofoliu (5), Blog (4)
- **Referință 28 zile:** 114 utilizatori, 27 evenimente cheie — Paid Search 20 (74%), Organic 4, Direct 3; cost/eveniment cheie pe Paid: 26,61 RON.
- ⚠️ Breakdown pe evenimente individuale (phone_call / whatsapp_click / form_submit / cta_click) nu a putut fi extras în această rulare (raportul GA4 de evenimente nu s-a încărcat prin navigare directă); de inclus mâine.

**Funnel leak:** pagina Camere Supraveghere & Alarme are cel mai mult trafic (30 afișări, 31 utilizatori/28z) dar evenimentele importante pe site au scăzut la 3/săptămână → CTA-urile de pe paginile de servicii merită verificate.

---

## Google Search Console (4–10 iun, 7 zile)

- **Clicuri:** 17 · **Afișări:** 770 · **CTR:** 2,2% · **Poziție medie:** 14,8
- Comparația WoW exactă nu s-a randat în interfață; proxy GA4: sesiuni organice google +143%.

**Poziții keywords țintă:**

| Keyword | Poziție | Afișări | Clicuri |
|---|---|---|---|
| sistem pontaj electronic | 21,9 (țintă 12–15) | 16 | 0 |
| camere supraveghere brasov | **7,8** 🎉 | 17 | 0 ⚠️ |
| alarma antiefractie brasov | nu apare (apropiat: „alarme brasov" poz 24) | — | — |
| control acces brasov | nu apare (apropiat: „control acces biometric" poz 24) | — | — |
| detectie incendiu brasov | **nu apare deloc — content gap** | — | — |

**Top queries (afișări):** camere supraveghere 45 (poz 15,2) · camere de supraveghere 27 (13,9) · camere supraveghere brasov 17 (7,8) · sisteme de securitate 17 (18,8) · sistem pontaj electronic 16 (21,9)

**Oportunități poziții 11–20 (striking distance):** camere supraveghere (15,2), camere de supraveghere (13,9), pontaj electronic cu amprenta (13,7), aparate de pontaj electronic (14,5), camere de supraveghere brasov (15,0), aparat pontaj electronic (15,4), sisteme de securitate (18,8).

---

## 🚨 Alerte

1. **Grupuri de anunțuri fără anunțuri** (alertă activă Google Ads) — grupurile tematice noi nu livrează; blocant direct pentru task #17.
2. **„camere supraveghere brasov" poz 7,8 organic, 17 afișări, 0 clicuri** — title/meta description neatractive în SERP.
3. **Evenimente importante GA4: 3 (−25% WoW)** — conversii on-site în scădere în ciuda traficului +48%.
4. **Candidat negative keyword:** „cartela sim camera supraveghere" (CPC 5,41 RON, intent DIY/produs).
5. **„detectie incendiu brasov" — zero prezență organică** — content gap pe un serviciu core.
6. Cost 7 zile +13% WoW (143 RON) — în limite, dar de urmărit pacing-ul la depășiri repetate.

## ✅ Acțiuni propuse azi

1. **Creați RSA-urile lipsă (task #17)** — *context:* alertă activă, grupurile Alarme/Acces/Pontaj/Incendiu nu pot livra fără anunțuri, deci tot bugetul intră pe Camere; *efort:* 1–2 h (15 titluri + 4 descrieri × 3 grupuri); *impact:* mare — deblochează livrarea pe 3 linii de servicii.
2. **Adăugați negativ „cartela sim"** (expresie) — *context:* clic irelevant la 5,41 RON CPC; *efort:* 5 min; *impact:* mic dar imediat pe eficiența bugetului.
3. **Rescrieți title/meta pentru pagina Camere Supraveghere** — *context:* poz. 7,8 organic cu 0 clicuri din 17 afișări (CTR 0%); titlul actual nu câștigă clicul în SERP; *efort:* 30 min; *impact:* moderat — primele clicuri organice gratuite pe keyword-ul țintă.

---
*Generat automat de monitorizarea zilnică CSSI · date: Google Ads 666-033-6562, GA4 p525787706, GSC sc-domain:cssi.ro*
