# 📊 Ghid Marketing CSSI — postare multi-platformă cu aprobare

**URL:** https://cssi.ro/admin/marketing

> ⚡ **ACTUALIZARE iunie 2026 — pagină unică.** Cele 4 pagini de marketing (Hub, Calendar Social,
> Planificator, Recenzii) au fost unificate într-o **singură pagină** `admin/marketing.html`, cu **2 taburi**:
> **📋 Postări** (creare/calendar/kanban + aprobare) și **⭐ Recenzii**. Vechile URL-uri redirecționează automat.
>
> ✨ **NOU — „Generează săptămâna cu AI".** Butonul din dreapta-sus scrie automat o săptămână de postări
> COMPLETE (calendar editorial pe servicii, fără paranteze de completat), le pune ca **Draft** programate pe
> orele optime, fiecare marcată **„📷 lipsește poza"**. Tu doar verifici textul, adaugi poza și aprobi.
> Necesită o **cheie Claude** în `secrets.php`:
> ```php
> 'ANTHROPIC_KEY' => 'sk-ant-...',   // de la console.anthropic.com → API Keys
> // opțional: 'CLAUDE_MODEL' => 'claude-sonnet-4-6',
> ```
> Fără cheie, butonul afișează un mesaj clar (nu strică nimic). Generarea imaginilor (Pollinations) rămâne gratis.

Pentru **publicarea** pe rețele ai nevoie de **o cheie Zernio** în `secrets.php`.

---

## 1. CE EXISTĂ ACUM (gata făcut)

| Componentă | Fișier | Ce face |
|---|---|---|
| **Marketing Hub** | `admin/marketing.html` | Dashboard campanii + resurse + idei conținut + buton ✅ Aprobare postări |
| **Social Media Manager** | `admin/calendar-social.html` | Creare postări + calendar + kanban + **modal de aprobare cu preview pe fiecare platformă** |
| **API publicare** | `admin/api.php` → `publishSocialPost` | Trimite postarea la Zernio care o postează pe toate rețelele |
| **Cron postări programate** | `admin/cron-social.php` | Publică automat postările programate la ora setată |
| **Status workflow** | DB `social_posts` | Idee → Draft → Programat → Publicat (sau Eroare) |

**Platforme suportate:** Facebook · Instagram · LinkedIn · YouTube · TikTok · X (Twitter)

---

## 2. FLUXUL DE LUCRU (după setup)

```
[Tu scrii postare]
       ↓
[Salvezi ca Draft sau apeși "Publică Acum"]
       ↓
[Apare MODAL DE APROBARE cu preview pe fiecare rețea]
   • Vezi cum arată pe FB, IG, LinkedIn, etc.
   • Vezi caractere/limită per platformă
   • Vezi avertismente (ex: IG fără poză, TikTok fără video)
       ↓
[Apeși "✅ Aprob și publică pe toate"]
       ↓
[Zernio API publică simultan pe toate platformele selectate]
       ↓
[Status devine "Publicat" și apare în calendar]
```

**Nimic nu se publică fără aprobarea ta explicită prin acel buton verde.**

---

## 3. CE TREBUIE SĂ FACI PENTRU A ACTIVA PUBLICAREA

### Pas 1 — Cont Zernio
1. Du-te la **https://zernio.com** și creează un cont (sau loghează-te dacă ai deja).
2. Conectează rețelele sociale CSSI (Facebook Page, Instagram Business, LinkedIn Company, YouTube, TikTok).
   - Pentru fiecare → buton "Connect Account" → OAuth → autorizezi din contul CSSI corespondent.
3. Mergi la **Settings → API Keys** → generează o cheie API → o copiezi.

### Pas 2 — Pui cheia în secrets.php
Pe server, fișierul `/secrets.php` (un nivel deasupra de `/admin/`) trebuie să conțină:

```php
<?php
return [
    // ...alte chei existente...
    'ZERNIO_KEY' => 'pune_aici_cheia_copiată_de_la_zernio',
    'CRON_SECRET' => 'token_lung_aleatoriu_pentru_cron_jobs',
];
```

Sau dacă folosești constante:
```php
define('ZERNIO_KEY', 'pune_aici_cheia_copiată_de_la_zernio');
```

### Pas 3 — Cron job pentru postări programate (opțional dar recomandat)
La hosting (cPanel → Cron Jobs), adaugă:
```
*/5 * * * * curl -s "https://cssi.ro/admin/cron-social.php?key=CRON_SECRET_VALUE" > /dev/null
```
Asta verifică la fiecare 5 minute dacă ai postări care trebuie publicate.

---

## 4. CUM FOLOSEȘTI (după ce ai setat cheia)

### A. Creezi o postare nouă
1. **https://cssi.ro/admin/marketing** → buton "+ Campanie Nouă" sau "📅 Calendar"
2. La Calendar → **"+ Postare Nouă"**
3. Completezi:
   - **Brand:** CSSI sau Conca-Verde
   - **Tip:** Foto / Video / Carusel / Story / Reel
   - **Platforme:** bifezi FB, IG, LinkedIn, YT, TikTok (ce vrei)
   - **Conținut:** textul postării + hashtag-uri
   - **Media:** upload poză/video sau apeși **"🎨 Generează imagine AI"**
   - **Data + Ora:** când să fie publicată

### B. Alegi acțiunea
- **💾 Salvează Draft** — rămâne în "Aprobare postări", nu publică
- **⏰ Programează** — apare modalul de aprobare; după aprobare se va publica la ora setată
- **🚀 Publică Acum** — apare modalul de aprobare; după aprobare se publică imediat

### C. Modalul de aprobare (pasul cheie)
Vezi un preview vizual cum arată postarea pe fiecare rețea:
- 📘 Facebook — limită 63.206 caractere
- 📸 Instagram — limită 2.200 caractere, **necesită media**
- 💼 LinkedIn — limită 3.000 caractere
- ▶️ YouTube — limită 5.000 caractere, **necesită video**
- 🎵 TikTok — **necesită video**
- 𝕏 Twitter — limită 280 caractere

Pentru fiecare platformă:
- ✅ caractere folosite vs. limită
- ⚠️ avertismente (text prea lung, lipsește media, format greșit)
- Preview mock-up cu avatarul brandului, data și conținutul

**Un singur buton verde "✅ Aprob și publică pe toate"** — apeși el, gata. Sau "✏️ Mai am de modificat".

---

## 5. UNDE GĂSEȘTI POSTĂRILE CARE AȘTEAPTĂ APROBAREA

În **Marketing Hub**, butonul galben **"✅ Aprobare postări"** sus dreapta.

Te duce direct în Social Manager filtrat pe statusul **Draft** — toate postările pe care le-ai salvat ca draft dar nu le-ai publicat încă.

Apeși 🚀 pe oricare → se deschide modalul de aprobare → preview → aprob → publicat.

---

## 6. KPI-URI / STATUS ÎN CALENDAR

Vezi la top:
- 💡 **Idei** — schițe brute, nu sunt postări încă
- 📝 **Drafturi** — așteaptă aprobarea ta (vezi "Aprobare postări")
- ⏰ **Programate** — aprobate, vor fi publicate automat la oră
- ✅ **Publicate** — au plecat pe rețele
- ❌ **Erori** — Zernio a întors eroare (vezi în "external_ids" detalii)

Click pe un KPI = filtrezi automat lista pe statusul respectiv.

---

## 7. TROUBLESHOOTING

**Eroare: "ZERNIO_KEY nesetat în secrets.php"**
→ Vezi Pas 2 de mai sus, pune cheia în `/secrets.php`.

**Eroare: "Niciun cont Zernio conectat pentru platformele selectate"**
→ Du-te la zernio.com → Accounts → conectează cont pentru platforma respectivă.

**Postarea apare cu status "Eroare"**
→ Click pe ea → vezi în detalii câmpul `external_ids` ce a întors Zernio. De obicei: token expirat, media prea mare (>10MB pentru FB/IG), text peste limită.

**Programarea nu se publică automat**
→ Verifică cron-ul: trebuie să ruleze `cron-social.php` la fiecare 5 minute. Testează manual: `curl https://cssi.ro/admin/cron-social.php?key=...`

---

## 8. ALTERNATIVĂ FĂRĂ ZERNIO (dacă vrei mai târziu)

Dacă vrei să eviți Zernio (cost recurent ~30€/lună), pot conecta direct fiecare platformă prin Graph API (FB+IG), LinkedIn API, YouTube Data API, TikTok Content API, X API. **Dezavantaj:** trebuie aplicat la Meta Developer pentru aprobare aplicație + tokenele expiră la 60 zile. Zernio se ocupă singur de refresh + un singur endpoint.

Recomand să rămâi pe Zernio până când volumul de postări justifică efortul de migrare.

---

**Status implementare:** ✅ Cod gata · ⏳ Așteaptă cheie Zernio + conectare conturi

---

## 9. UPGRADE SEO 2026 — ce am adăugat după Auditul 19 mai 2026

### A. Marketing Hub (`admin/marketing.html`) — 6 taburi în loc de 3:

| Tab | Conținut |
|---|---|
| 🎯 **Campanii** | 10 campanii prioritizate **P0/P1/P2** direct din audit (indexare Google, GBP, Ads B2B 510 RON/lună, 16 landing pages localizate, LinkedIn, blog conformitate, etc.) |
| 🔍 **SEO & Meta** | 14 pagini cu meta titles (max 60 char) + descriptions (150-160 char) + H1 + cuvinte cheie țintă — direct din **Anexa A** a auditului. Click pe orice câmp → copiezi în clipboard. |
| 💡 **Idei Conținut** | 22 idei strategice (LinkedIn carusele PDF 6.6% engagement, IG Reels hook 3 sec, FB studii caz, GBP, blog SEO). Filtre per platformă. Click pe idee → pre-completează postare nouă. |
| 🏷️ **Hashtag-uri** | Categorisate pe serviciu + brand + local + B2B + industry. Combinații recomandate per post serviciu. Click → copiezi setul. |
| 📁 **Resurse & Brand** | Link-uri directe la GBP, Search Console, Google Ads, LinkedIn, audit complet. |
| 📊 **KPI** | 12 țintă luna 6 din auditul secțiunea 6 (lead-uri, CPL, recenzii GBP, ranking, etc.) + estimare pierdere oportunitate actuală (~65.700 RON/lună). |

### B. Social Manager (`admin/calendar-social.html`) — feature noi:

**📚 Buton „Șabloane SEO 2026"** în modalul de creare postare:
- 11 șabloane pre-optimizate pentru algoritmii 2026
- Categorisat: LinkedIn B2B · Instagram Reel · TikTok · Facebook · GBP · Blog SEO · YouTube · Conca Verde
- Fiecare cu **„💡 Algoritm 2026"** explicat (Depth Score, sends/saves, trending audio, etc.)
- Click → pre-completează conținut + brand + platforme

**Char count per platformă în timp real:**
- FB 63.206 · IG 2.200 · LinkedIn 3.000 · YT 5.000 · TikTok 2.200 · X 280
- Vizibil sub textarea, culoare roșu dacă depășești

**Filtru URL `?filter=Draft`:**
- Marketing Hub → buton 🟡 „Aprobare postări" → direct la Drafts
- Vizualizare automată comutată pe Lista

### C. Best practices 2026 încorporate

| Platformă | Insight cheie 2026 | Aplicat unde |
|---|---|---|
| **LinkedIn** | Depth Score > likes · Saves = 5x weight · PDF carousels 6.6% engagement (top) · Personal profile 8x company page · Hook 210 char · Marți-joi dim. | Șabloane LinkedIn + idei + tips |
| **Instagram** | Sends/reach = #1 signal · Saves = #2 · Hook 3 sec (50% drop-off) · Trending audio +42% · Carusele 0.55% engagement | Șabloane Reel + filtru idei |
| **Google SEO** | E-E-A-T critic · AI Overview citează surse cu trust signals · GBP = 32% local ranking · Reviews velocity > volum | Meta titles + idei blog + KPI |
| **Google Business** | 100% completeness = +50% ranking · Răspuns 80%+ recenzii = boost · 1 post/săpt obligatoriu | Campanii P0 + șabloane GBP |
| **TikTok** | Pattern interrupt 3 sec · Text overlay frame 1 · 7-15 sec optim · Native video > static 5x | Șabloane Reel/TikTok |

### D. Fluxul recomandat săptămânal

```
LUNI (15 min) — verificare KPI dashboard
   ↓ deschid tab KPI în Marketing Hub
   ↓ compar față de săptămâna trecută

MARȚI dim. — LinkedIn (cel mai bun moment 2026)
   ↓ deschid Marketing Hub → tab Idei
   ↓ aleg idee LinkedIn → click → pre-completat
   ↓ modific [text între paranteze] cu detalii reale
   ↓ Aprobare → publicare

MIERCURI — Instagram Reel + GBP post
   ↓ Șabloane → Instagram Reel
   ↓ înregistrez video, încarc, public

JOI — LinkedIn al doilea post
   ↓ format diferit: PDF carousel sau studiu caz

VINERI — Facebook (long-form sau behind-the-scenes)
   ↓ Șabloane → Facebook studiu caz

CONTINUU — răspuns recenzii GBP (max 24h)
   ↓ /admin/recenzii.html
```
