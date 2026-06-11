# CSSI Daily Brief — 2026-06-08 (luni)

> **Săptămâna W24 · 8-14 iunie 2026** — primă zi a săptămânii noi. Raport zilnic + raport strategic săptămânal W23 wrap-up (paralel).

## 🎯 TL;DR
- ⚠️ **BLOCAT acces date live — recidiv săptămânal** (3 browsere Chrome conectate: Browser 1, laptop, Browser 3; selecția automată refuzată în rulare scheduled). Pattern persistent: 1-5 iun BLOCAT, 6 iun OK (rulare manuală suspectată), 8 iun din nou BLOCAT.
- 🚨 **Recomandarea „1 ad group does not have any ads" persistă de 12+ zile** — grupul Alarme/Acces/Pontaj încă fără anunțuri. Pierdere cumulativă estimată ~150-200 afișări neutilizate săptămâna trecută.
- **Acțiune azi (15 min):** (1) Configurează `defaultBrowserDeviceId` în `uploads/SKILL.md` (fix permanent); (2) Publică RSA grup Alarme/Acces/Pontaj — restul săptămânii e blocată pe acest singur task.

---

## ⚠️ Blocaje rulare

| Platformă | Status | Cauză |
|---|---|---|
| Google Ads | ⚠️ BLOCAT | 3 browsere conectate, selecție automată refuzată (necesită confirmare user) |
| GA4 | ⚠️ BLOCAT | Selecție manuală browser + authuser=3 |
| Google Search Console | ⚠️ BLOCAT | Selecție manuală browser |
| Gmail (draft) | ✅ Disponibil | MCP Gmail activ — drafturile zilnic + săptămânal create |

**Browsere detectate (8 iun):**
- Browser 1 — `3eedd816-bd97-41cb-bff6-78a938642a81`
- laptop — `75c174a1-8357-4f80-b7b7-630102eeb65f` (recomandat ca default)
- Browser 3 — `cbdbcd5c-5ab6-4ceb-a9da-a46cf50142a2`

**Fix permanent (deschis de la 1 iun, repetat 8 ori):** modifică pasul 2 din `uploads/SKILL.md` să apeleze `mcp__Claude_in_Chrome__select_browser({deviceId: "75c174a1-8357-4f80-b7b7-630102eeb65f"})` ÎNAINTE de `navigate`. Fără acest fix, raportul rămâne semi-funcțional indefinit.

---

## 🔁 Stare context (ultim raport cu date confirmate: 2026-06-06)

### Google Ads — 7 zile (30 mai – 5 iun)
| Metric | Valoare | Țintă | Gap |
|---|---:|---:|---|
| Clicuri | 41 | — | — |
| Afișări | 438 | — | — |
| CTR | **9,36%** | 15-18% | -5,6 pp |
| CPC mediu | 3,14 RON | <2,50 RON | +25,6% |
| Cost total | 129 RON | — | (după ritm: 387 RON/lună la 18,43 RON/zi) |
| Sold cont (5 iun) | 96,40 RON | — | runway ~5-6 zile la ritm curent |
| Optimization score | 63,3% | >80% | -16,7 pp |
| Conversii primare (7z) | (sub afișaj) | 12-20/lună | risc subțintă |

**Top câștigător silențios:** „proiectare sisteme securitate" — CTR **20,00%**, cost doar 2,35 RON. Merită grup dedicat.

### GA4 — ieri (5 iun) vs. media 7 zile
| Metric | Valoare | Variație |
|---|---:|---:|
| Utilizatori activi | 36 | **+20,0%** ✅ |
| Utilizatori noi | 33 | **+26,9%** ✅ |
| Evenimente importante | 5 | **-61,5%** ⚠️ |

**Sesiuni pe canal (7 zile):** Paid Search 21 (-12,5%) · Organic Search 12 (0,0%) · Direct 3 (-66,7%) · Organic Social 1 (nou).

### GSC — 7 zile (27 mai – 2 iun)
| Metric | Valoare |
|---|---:|
| Clicuri totale | 15 |
| Afișări totale | 687 |
| CTR mediu | 2,2% |
| Poziție medie | 15,2 |

**Top win SEO:** „camere de supraveghere brasov" → CTR **25%**, poziție **7,2** ⭐
**Atenție:** pagina „Sisteme Securitate Brașov" -67,7% afișări (alerta din 6 iun, neverificată).

---

## 🚨 Alerte cumulate (de monitorizat azi)

1. **🔴 BUGET Google Ads aproape de epuizare** — sold 96,40 RON (5 iun) la consum 18,43 RON/zi = epuizare estimată ~10-11 iun. **Top-up necesar săptămâna asta** sau ajustare CPC pentru a ajunge la 30 iun.
2. **🔴 „1 ad group does not have any ads" — 12+ zile** (recomandare cu +16,5% optim. score). Grupul Alarme/Acces/Pontaj fără anunțuri. Pierdere cumulativă: ~150-200 afișări/săpt × 2 săpt = ~300-400 afișări neutilizate.
3. **⚠️ Drop evenimente conversie -61,5%** (semnal din 6 iun) — verifică form_submit / phone_call / whatsapp_click; risc tracking spart.
4. **⚠️ Pagina „Sisteme Securitate" -67,7% afișări** — pierdere semnificativă rang organic. Verifică content + internal links.
5. **⚠️ CTR Ads 9,36%** — sub țintă 15-18% cu ~6 pp. Restructurare în 3 Ad Groups (#16) și RSA-uri (#17) sunt deblocante.
6. **⚠️ CPC 3,14 RON** — peste ținta 2,50 RON. Calibrare după acumulare conversii primare.
7. **ℹ️ Queries irelevante candidate negative keywords:** „spion", „cctv cameras" (engleză), „what are", „best cctv" — adăugate în lista de propus.

---

## ✅ Acțiuni propuse azi (luni 8 iun)

### 1. [2 min · 🔴 INFRA CRITICAL] Fix `defaultBrowserDeviceId` în SKILL.md
**Context:** 6 din ultimele 8 zile rulare BLOCATĂ pe selecție browser. Pipeline-ul de monitorizare e degradat săptămânal.
**Acțiune:** Editează `uploads/SKILL.md` pasul 2 → adaugă apel `select_browser` cu `deviceId: "75c174a1-8357-4f80-b7b7-630102eeb65f"` (laptop) înainte de orice `navigate`.
**Impact:** Deblocare definitivă a tuturor rapoartelor zilnice. Fără acest fix, restul acțiunilor de mai jos nu pot fi monitorizate automat.

### 2. [5 min · 🔴 CRITIC RUNWAY] Verifică sold buget Google Ads
**Context:** Sold 96,40 RON (5 iun) – consum ~3 zile × 18 RON = ~54 RON consumat = sold estimat **~42 RON azi**. Runway 2-3 zile.
**Acțiune:** `https://ads.google.com/aw/billing/summary?ocid=8059575551` → dacă <50 RON, top-up minim 100-150 RON pentru a duce campania până la 30 iun.
**Impact:** Evită gap de difuzare 18-20 zile la jumătatea lunii.

### 3. [10 min · 🔴 MARE impact] Publică RSA grup Alarme/Acces/Pontaj
**Context:** Persistă de 12+ zile, pierdere ~300-400 afișări cumulate. Material gata în `daily-briefs/CSSI-Audit-Ads-2026-05-27.md` (15 titluri + 4 descrieri).
**Acțiune:** Google Ads → Campania CSSI → Ad Group „Alarme..." → Anunțuri → +RSA → copy-paste din audit.
**Impact:** +16,5% optimization score, restaurare difuzare pe ~50% din keyword universe, +3-5 conversii/săpt așteptate.

### 4. [15 min · MEDIU impact] Investighează drop evenimente GA4 (-61,5%)
**Context:** Alertă deschisă din 6 iun. 5 evenimente importante vs. media 13/zi. Posibile cauze: (a) form spart, (b) tracking GA4 căzut, (c) drop temporar.
**Acțiune:** GA4 → Realtime → vizitează `/contact`, completează form, click telefon/WhatsApp. Verifică evenimentele apar live.
**Impact:** Dacă tracking e spart, pierzi 30+ zile de date înainte de migrare la Maximize Conversions.

---

## 📋 Status task-uri pending

| # | Task | Status | Criteriu deblocare |
|---|---|---|---|
| #15 | Cleanup keywords „Low search volume" | ⏳ pending | 15+ conversii acumulate (curent ~1-2) |
| #16 | Restructurare în 3 Ad Groups | ⚠️ parțial | Grup Alarme/Acces/Pontaj fără anunțuri = blocaj |
| #17 | Creare 3 RSA-uri noi | 🔄 1/3 publicat | Următor: Alarme/Acces/Pontaj (acțiunea #3 de mai sus) |

---

## 📅 Calendar context

- **Azi (8 iun, LUNI):** raport zilnic + **raport strategic săptămânal W23** generat în paralel → `weekly-strategy/CSSI-Weekly-Strategy-2026-06-08.md`
- **W24 (8-14 iun):** prioritate absolută = închidere RSA + fix infra browser. Reevaluare bidding strategy la final W24.
- **Rapoarte recente:** 21, 22, 24-29, 31 mai + 1-6 iun. Lipsuri: 23, 30 mai (sâmbete), 7 iun (duminică).

---

## Note tehnice

- **6/8 zile recente rulate cu BLOCAT** — pattern persistent. Singura zi cu date live confirmate săptămâna trecută: 6 iun (sâmbătă, suspectat rulare manuală MIHAI).
- **Ultim raport cu date proaspete:** 2026-06-06.
- **Următoarea rulare:** 2026-06-09 (marți) — raport zilnic standard.

---

*Generat automat 2026-06-08 prin scheduled task `cssi-daily-monitoring`. Date live BLOCATE — raport bazat pe context 6 iun + acțiuni persistente. Vezi și raport strategic săptămânal paralel.*
