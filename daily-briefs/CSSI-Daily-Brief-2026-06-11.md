# CSSI Daily Brief — 2026-06-11 (joi)

## TL;DR
- ✅ **Ce merge:** 3 conversii în 7 zile (valoare 450 RON), afișări +62% (546 vs ~336), CPC scăzut la 3,52 RON; conversie nouă ieri din „sisteme securitate brasov".
- ⚠️ **Ce nu merge:** alertă critică în cont — **„Grupurile de anunțuri nu conțin anunțuri"**; CTR 7,33% sub ținta 15–18% (ieri doar 2,38%); „pontaj electronic" în GSC la poziția 22–30 vs țintă 12–15.
- 🎯 **Acțiunea zilei:** deblochează task #17 — creează RSA-urile pentru grupurile fără anunțuri (pierzi difuzare chiar acum).

---

## Google Ads (4–10 iun, ultimele 7 zile)

| Metrică | Valoare | vs. perioada anterioară |
|---|---|---|
| Afișări | 546 | +210 (+62%) |
| Clickuri | 40 | — |
| CTR | 7,33% | sub țintă (15–18%) |
| CPC mediu | 3,52 RON | — |
| Cost | 140,98 RON (~20,1 RON/zi) | +8,15 RON |
| Conversii | 3,00 | +1,00 |
| Valoare conversii | 450 RON | +150 |
| CPA | ~47 RON | — |

**Ieri (10 iun):** 126 afișări, 3 clickuri, CTR 2,38%, cost 21,57 RON, **1 conversie (150 RON)**.

Campania „CSSI - Servicii Securitate": Eligibilă, buget 19 RON/zi, Optimization Score 72,3%. Cost mediu/zi 20,1 RON — ușor peste bugetul zilnic (ritm normal Google, dar de urmărit).

**Top 5 keywords (7 zile):**

| Keyword | Clickuri | Afișări | CTR | Cost |
|---|---|---|---|---|
| montaj camere supraveghere | 24 | 250 | 9,60% | 94,28 RON |
| pret camere de supraveghere exterior | 5 | 116 | 4,31% | 14,34 RON |
| proiectare sisteme securitate | 2 | 24 | 8,33% | 4,70 RON |
| kit camere supraveghere wireless exterior pret | 1 | 70 | 1,43% | 1,04 RON |
| firma montare camere supraveghere | 1 | 3 | 33,33% | 1,25 RON |

**Top queries (7 zile):** montaj camere supraveghere brasov (3 clk), cat costa o camera de supraveghere (2), montaj camere de supraveghere (1), sistem control acces cu card (1), **sisteme securitate brasov (1 clk → 1 conversie, 100% rată conv.)**.

⚠️ Search Impression Share: coloana nu era vizibilă în vizualizarea curentă — de verificat manual.

## GA4

⚠️ Notă: tab-ul GA4 a înghețat în timpul colectării; datele au fost citite pe intervalul implicit **28 zile (14 mai–10 iun)**, breakdown-ul pe evenimente (phone_call / whatsapp_click / form_submit / cta_click) nu a putut fi capturat azi.

- **Sesiuni (28 zile): 164** — Paid Search 93 (56,7%), Organic 46 (28%), Direct 18, Unassigned 4, Cross-network 2, Organic Social 1. Rata de implicare 62,2%.
- **Ieri (din grafic):** ~8 sesiuni total.
- **Top pagini de destinație:** / (103), /camere-supraveghere (12), /blog/analiza-risc-securitate-fizica-ghid-complet (5), /detectie-incendiu-isu (5), /bariere-auto (3).
- **Evenimente cheie (28 zile): 27** — 21 pe „/", 3 pe /automatizari-porti, 2 pe /camere-supraveghere. Rata evenimente cheie/sesiune: 10,37%.

## Google Search Console (2–8 iun, 7 zile)

**16 clickuri, 624 afișări, CTR 2,6%, poziție medie 14,5.**

| Query | Clickuri | Afișări | Poziție |
|---|---|---|---|
| sistem supraveghere brasov | 1 | 5 | 39,2 |
| cssi | 1 | 2 | 2,0 |
| camere supraveghere | 0 | 60 | 15,3 |
| camere de supraveghere | 0 | 26 | 13,7 |
| sisteme de securitate | 0 | 15 | 18,5 |
| camere supraveghere brasov | 0 | 14 | 14,4 |
| sistem pontaj electronic | 0 | 11 | 22,1 |
| pontaj electronic | 0 | 8 | 30,0 |

Poziții ținte: „camere supraveghere brasov" **14,4** (aproape de top 10), „sistem pontaj electronic" **22,1** / „pontaj electronic" **30,0** (țintă 12–15 — sub țintă), „alarma antiefractie brasov" / „control acces brasov" / „detectie incendiu brasov" — fără afișări în top 10 interogări.

## 🚨 Alerte

1. **CRITIC — „Grupurile de anunțuri nu conțin anunțuri"** (banner roșu în cont). Grupuri fără anunțuri = zero difuzare pe acele teme. Direct legat de task #16/#17.
2. **CTR sub țintă:** 7,33% vs 15–18%; ieri 2,38%. Cauza probabilă: aceleași grupuri fără RSA + keywords generice.
3. **Keywords cu CTR <5%:** kit camere supraveghere wireless exterior pret (1,43%), pret camere de supraveghere exterior (4,31%), montaj camere video (0%, 21 afișări).
4. **Queries irelevante — candidați negative keywords:** „emag camere de luat vederi", „tapo camera exterior", „cartela sim camera supraveghere", „a2t", „camera de luat vederi cu panou solar".
5. **Cost/zi 20,1 RON vs buget 19 RON/zi** — în limita normală de supra-livrare Google, de monitorizat.

## ✅ Acțiuni propuse azi

1. **Creează RSA-urile lipsă (task #17 / alertă critică).** Context: grupuri de anunțuri fără anunțuri = buget și impression share pierdute zilnic; aliniat cu restructurarea în 3 ad groups (#16). Efort: 1–2 h. Impact: mare — deblochează difuzarea și ar trebui să ridice CTR spre țintă.
2. **Adaugă 4–5 negative keywords** (emag, tapo, cartela sim, panou solar, a2t). Context: queries DIY/brand-uri retail consumă afișări irelevante. Efort: 10 min. Impact: mediu — CTR și calitate trafic.
3. **Optimizează pagina de pontaj pentru „sistem pontaj electronic"** (poz. 22,1; „camere supraveghere brasov" e la 14,4 și aproape de top 10). Context: 19 afișări/săpt. fără click pe tema pontaj; țintă rang 12–15. Efort: ~1 h on-page (title, H1, FAQ). Impact: mediu-mare pe organic.

---
*Generat automat — cssi-daily-monitoring, 2026-06-11. Sursă: Google Ads (666-033-6562), GA4 (p525787706), GSC (sc-domain:cssi.ro).*
