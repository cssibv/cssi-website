# CSSI — Instalare Modul MARKETING în Portal v3.0

**Data:** 22 apr 2026
**Fișier creat:** `CSSI-Apps-Script-v3-MARKETING.gs` (~540 linii)
**Referință plan:** Plan Promovare Automatizare v2 — Secțiunea 5

---

## 🎯 Ce face modulul

Extinde Portal CSSI v3.0 (Apps Script deja funcțional) cu automatizare marketing. Fără infrastructură nouă, fără costuri, fără tooluri de plătit — doar extensie.

**Adaugă:**

- **3 sheet-uri noi:** `MARKETING`, `KPI_DASHBOARD`, `MK_ALERTS`
- **7 funcții automatizate:**
  1. `syncMarketingData` — zilnic 07:30, populează MARKETING
  2. `generateKpiDashboard` — zilnic 07:45, scorecard Verde/Galben/Roșu
  3. `sapRaport` — luni 07:30, email săptămânal
  4. `lunarRaport` — ziua 1 a lunii 07:00, email lunar
  5. `checkCPL` — zilnic 11:00, alert CPL > 50 RON 3 zile
  6. `checkRankDrop` — zilnic 11:15, detectează scăderi poziție GSC (nevoie API)
  7. `keywordIdeas` — vineri 10:00, sugestii keyword noi (nevoie API)

---

## 📋 Instalare pas-cu-pas

### Pas 1 — Deschide Apps Script Portal CSSI v3.0

Google Drive → deschide spreadsheet-ul Portal CSSI v3.0 → **Extensions** → **Apps Script**.

### Pas 2 — Lipește modulul

1. În Apps Script, la sfârșitul fișierului principal (după toate funcțiile existente), adaugă un separator vizibil (o linie goală).
2. Deschide fișierul local `CSSI-Apps-Script-v3-MARKETING.gs` (din folderul `cssi-website`).
3. Copiază TOT conținutul și lipește-l la sfârșit.
4. **Save** (Ctrl+S / Cmd+S) → editorul îți va cere să dai un nume proiectului dacă nu are deja.

### Pas 3 — Rulează setupMarketingModule() manual

1. În dropdown-ul de funcții (sus lângă butonul Run), selectează **setupMarketingModule**.
2. Click **Run** (▶).
3. Prima dată Google va cere autorizare — confirmă.
4. Verifică logul: **View → Logs** (sau Ctrl+Enter) — ar trebui să vezi:

   ```
   ✓ Sheet MARKETING creat
   ✓ Sheet KPI_DASHBOARD creat
   ✓ Sheet MK_ALERTS creat
   ✓ CONFIG populat cu chei API placeholder
   ═══ Setup MARKETING complet. Rulează installMarketingTriggers() apoi. ═══
   ```

5. Verifică în spreadsheet: ar trebui să vezi 3 tab-uri noi.

### Pas 4 — Configurează email-ul pentru rapoarte

În sheet-ul **CONFIG** (deja existent), găsește rândul `EMAIL_MARKETING_REPORTS` (creat la pasul 3) și completează cu adresa ta:

| Key | Value |
|-----|-------|
| EMAIL_MARKETING_REPORTS | cssirobv@gmail.com |

Dacă nu completezi, rapoartele merg la `EMAIL_ADMIN` (din configul existent).

### Pas 5 — Test manual (OPȚIONAL dar recomandat)

În dropdown-ul de funcții selectează **testMarketingModule** → Run.

Asta rulează toate funcțiile o dată. Verifică:
- Sheet `MARKETING` are câteva rânduri (pot fi goale inițial, e OK)
- Sheet `KPI_DASHBOARD` are scorecard afișat
- Primești un email cu raportul săptămânal

### Pas 6 — Instalează trigger-ele

În dropdown selectează **installMarketingTriggers** → Run.

Log-ul ar trebui să arate: `✓ Triggere MARKETING instalate (7 funcții)`.

Poți verifica manual în Apps Script: **Triggers** (icona ceas din stânga) — vei vedea 7 triggere noi pentru funcțiile de mai sus.

---

## 🔑 Faza 2 — Conectare API (opțional, după obținere credentiale)

Modulul funcționează deja fără API-uri (pe baza sheet-ului CRM + date introduse manual). Pentru automatizare completă (populare MARKETING fără intervenție), urmează pașii de mai jos când ești gata.

### Google Ads API

1. Google Ads → **Tools & Settings** → **API Center** → Apply for API access (Basic tier gratuit).
2. După aprobare (24-48h) primești **Developer Token**.
3. În CONFIG completează:
   - `GOOGLE_ADS_DEV_TOKEN` = token-ul primit
   - `GOOGLE_ADS_CUSTOMER_ID` = `666-033-6562` (deja pre-populat)
4. În codul `syncMarketingData()`, decomentează blocul `// TODO: API` și implementează apelul (pattern în anexe).

### GA4 Data API

1. Google Cloud Console → activează **Analytics Data API v1**.
2. Creează un Service Account JSON key.
3. În Apps Script: **Services** → adaugă `AnalyticsData`.
4. În GA4 Admin → Property Settings → acces: adaugă email-ul service account-ului cu rol Viewer.

### GSC API (Search Console)

1. Google Cloud Console → activează **Search Console API**.
2. Service Account.
3. În GSC → Settings → Users → adaugă service account cu rol `Restricted`.
4. În Apps Script: **Services** → `SearchConsole`.

---

## 📊 Cum arată rezultatul

După prima zi rulare, vei primi dimineață la 07:45 dashboard-ul populat în KPI_DASHBOARD arătând:

| KPI | Valoare curentă | Țintă lună | Stare |
|-----|----|----|----|
| Lead-uri calificate (30z) | 8 | 15 | 🟡 Galben |
| CPL mediu (30z) | 47.13 RON | ≤ 25 RON | 🔴 Roșu |
| CTR Google Ads (7z) | 10.58% | ≥ 5% | 🟢 Verde |
| Utilizatori GA4 (30z) | — | ≥ 500 | — |
| Poziție medie GSC | — | ≤ 15 | — |

**Luni dimineață** primești email raport săptămânal cu:
- Lead-uri noi (week-over-week)
- Cost și conversii Google Ads
- Status KPI lunar
- Recomandări auto-generate

**Ziua 1 a lunii** primești email raport lunar cu scorecard complet + recomandări.

---

## ⚡ Ce e diferit față de plan

**Avansat în raport cu plan-ul v2:**
- Plan-ul zicea Luna 3 (iulie) pentru modul MARKETING — îl facem acum în aprilie.
- Adăugat sheet `MK_ALERTS` pentru istoric alerte (nu era în plan, dar util pentru debugging).

**Rămas pentru mai târziu (nevoie API):**
- `checkRankDrop` și `keywordIdeas` sunt placeholder — se completează după setup GSC API.
- `syncMarketingData` folosește placeholder pentru Google Ads (necesită Developer Token).

---

## 🧭 Recomandare next actions

1. **Săptămâna aceasta:** instalare + test modul (pași 1-6 de mai sus).
2. **Săptămâna viitoare:** aplică pentru Google Ads Developer Token. Între timp, introduci manual în sheet MARKETING 1 rând/zi cu datele din Google Ads (impresii, clicuri, cost, conversii din campanie).
3. **Luna următoare:** conectează GA4 + GSC API când Google Ads e stabil.

---

## 📁 Fișiere

- `/CSSI-Apps-Script-v3-MARKETING.gs` — cod complet (lipește în Apps Script)
- `/CSSI-Apps-Script-v3.gs` — fișierul existent (NU modificat)
- `/CSSI_Plan_Promovare_Automatizare_v2.docx` — planul complet (referință)

---

**Status plan execuție (apr 2026):**
- ✅ Luna 1 — Restart Google Ads + landing pages (completat)
- ✅ Luna 2 — SEO on-page + content (17 blog posts + pagină pontaj Brașov deja făcute)
- 🟡 Luna 3 — Modul MARKETING Apps Script (**fișier creat azi, pending deploy**)
- ⏳ Luna 4 — Meta organic + retargeting (buget 500+ followers)
