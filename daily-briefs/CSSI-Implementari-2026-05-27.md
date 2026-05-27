# CSSI — Raport implementări 27 mai 2026

> Pe baza problemelor identificate în `CSSI-Daily-Brief-2026-05-27.md`, am implementat fix-urile sub. Mai jos: ce am făcut, ce trebuie să faci tu manual, și deciziile pe care le-ai de luat.

---

## ✅ FĂCUT (cod & conținut)

### 1. Meta titles & descriptions optimizate pentru CTR organic

Toate titlurile au acum: ✓ semn vizual + USP local Brașov + număr de telefon / preț / număr proiecte.

| Pagină | Titlu nou |
|---|---|
| `camere-supraveghere-brasov.html` | Camere Supraveghere Brașov ✓ Montaj 24h \| 0752 288 400 CSSI |
| `pontaj-electronic.html` | Sistem Pontaj Electronic Brașov ✓ De la 1.500 lei \| CSSI |
| `pontaj-electronic-brasov.html` | Pontaj Electronic Brașov ✓ De la 1.500 lei, Montaj 48h \| CSSI |
| `alarma-antiefractie-brasov.html` | Alarmă Antiefracție Brașov ✓ Ajax/Paradox Montaj 24h \| CSSI |
| `control-acces-brasov.html` | Control Acces Brașov ✓ Card/Biometric/Facial \| CSSI 20+ Ani |

Pattern aplicat: **`[Keyword] [Oraș] ✓ [USP Concret] | [Brand]`**.

**Impact așteptat:** dublarea CTR organic (2,2 % → 4-5 %) pe paginile listate → ~10 clicuri organice/săpt în plus, fără cost de media.

### 2. Tracking dual-fire (Google Ads + GA4)

`tracking.js` actualizat:
- Adăugat `cssiSendAdsConversion()` helper care trimite și conversia Google Ads directă (`send_to: AW-.../label`) — **fallback peste import-ul GA4 → Ads**, în cazul în care GA4 nu marchează eventul ca "Key event" sau dacă import-ul GA4-Ads e întârziat.
- Adăugat debug logging: deschide orice pagină cu `?cssi_debug=1` în URL și ai în DevTools console toate evenimentele de tracking.
- Adăugat config `CSSI_ADS_CONVERSIONS` cu placeholder-uri pentru `phone_call` și `whatsapp_click` — vezi acțiunea manuală #1 mai jos.

`cookie-consent.js` actualizat:
- `gtag.js` se încarcă acum cu `G-XGSRGBQBCS` (GA4 ID) ca best-practice — Google preferă să loadezi cu GA4 ID când GA4 e principalul tracker. Config Ads se păstrează în coada dataLayer și se aplică la load.

### 3. Bug SEO descoperit & corectat (parțial)

**`detectie-incendiu-isu.html` avea `meta robots: noindex, nofollow`** — am descoperit comentariul în `sitemap.xml`: *"detectie-incendiu-isu exclus din marketing CSSI — promovat pe breaksistems.ro"*.

Am restaurat noindex-ul cu un comentariu HTML explicit care documentează decizia de business. **Vezi decizia #1 mai jos** (vrei să schimbi strategia?).

### 4. Pagina nouă `detectie-incendiu-brasov.html` (draft)

Creată după modelul `control-acces-brasov.html`, conținut adaptat pentru detecție incendiu (autorizare ISU, P3, centrale convenționale/adresabile, mentenanță obligatorie, branduri industria — Bentel, Inim, Notifier, Hochiki, Siemens, Bosch). **Setată ca `noindex` în așteptarea deciziei tale** (vezi #1).

---

## ⚙️ ACȚIUNI MANUALE NECESARE — făcute de tine

### MANUAL #1 — Creează & copiază conversion labels Google Ads pentru `phone_call` și `whatsapp_click`

**Context:** Cele 2 evenimente erau configurate doar ca GA4 events importate. Pentru a fi 100 % siguri că Google Ads le numără în timp real (independent de import-ul GA4), am adăugat în `tracking.js` un dual-fire. Acum am nevoie de conversion labels reale (placeholder-urile sunt detectate automat și ignorate, deci nu rupe nimic acum).

**Pași:**

1. Mergi în Google Ads → **Obiective** (Conversions) → **+ Conversie nouă**
2. Alege "Site-ul web" → "URL-ul site-ului" → introdu `cssi.ro`
3. Creează 2 conversii:
   - **Nume:** `phone_call (direct)` · **Categoria:** Phone Call Lead · **Valoare:** 50 RON · **Count:** One
   - **Nume:** `whatsapp_click (direct)` · **Categoria:** Contact · **Valoare:** 50 RON · **Count:** One
4. La instalare → alege **"Folosind etichete"** → copiază secțiunea `'send_to': 'AW-17987940313/XXXXXXXXXXXXXXXX'`
5. Înlocuiește în `/sessions/wizardly-dazzling-franklin/mnt/cssi-website/tracking.js`:
   ```
   phone_call:     'AW-17987940313/PHONE_CALL_LABEL_PLACEHOLDER',
   whatsapp_click: 'AW-17987940313/WHATSAPP_CLICK_LABEL_PLACEHOLDER',
   ```
   cu label-urile reale.
6. Deploy. Testează cu `?cssi_debug=1` în URL → în DevTools console ar trebui să vezi "Sent Ads conversion: phone_call ..." la click pe orice link tel:.

**Efort:** 15 min · **Impact:** Conversion tracking deblocat pentru Google Ads → bidding strategy "Maximize Conversions" devine fezabilă.

### MANUAL #2 — Marchează evenimentele ca "Key Events" în GA4

**Context:** Chiar dacă `phone_call`/`whatsapp_click` ajung în GA4, ele nu apar drept conversii decât dacă sunt marcate explicit.

**Pași:**

1. GA4 → **Administrator** → **Evenimente** (Events)
2. Caută în listă: `phone_call`, `whatsapp_click`, `form_submit`, `generate_lead`
3. La fiecare → toggle pe **"Marcați ca eveniment-cheie"** (Mark as key event)
4. GA4 → **Administrator** → **Linkuri cu produs** → **Google Ads** → verifică că import-ul evenimentelor-cheie e ON
5. Așteaptă 24-48h ca să curgă datele

**Efort:** 5 min · **Impact:** evenimentele apar ca conversii în Google Ads → import GA4-Ads funcționează corect.

### MANUAL #3 — Decide despre `detectie incendiu brasov` (strategic)

**Context:** Site-ul CSSI exclude intenționat detection-incendiu din marketing (comentariu în sitemap.xml: "promovat pe breaksistems.ro"). Dar:
- GSC arată 555 afișări/săpt cu CTR 2,2 % — există cerere pe CSSI.ro
- Pagina `detectie-incendiu-isu.html` e încă în site cu noindex
- Am pregătit `detectie-incendiu-brasov.html` (noindex, gata de lansare)

**De decis:**
- **Opțiunea A — Status quo:** detection-incendiu rămâne pe breaksistems.ro. Las paginile pe CSSI cu noindex.
- **Opțiunea B — Lansează pe CSSI:** scot noindex de pe ambele, le adaug în sitemap. CSSI captează keyword-urile, breaksistems rămâne ca site companion. **Recomandare:** dacă breaksistems.ro nu generează volum, mergi B.

**Cum activezi opțiunea B (când decizi):**
1. În `detectie-incendiu-isu.html`: schimbă `<meta name="robots" content="noindex, nofollow">` → `<meta name="robots" content="index, follow, max-image-preview:large">`
2. În `detectie-incendiu-brasov.html`: aceeași schimbare
3. Adaugă în `sitemap.xml`:
   ```xml
   <url><loc>https://cssi.ro/detectie-incendiu-isu</loc><lastmod>2026-05-27</lastmod><priority>0.9</priority></url>
   <url><loc>https://cssi.ro/detectie-incendiu-brasov</loc><lastmod>2026-05-27</lastmod><priority>0.9</priority></url>
   ```
4. GSC → trimite URL nou pentru indexare (Inspect URL → Request indexing)

**Efort:** 5 min · **Impact estimat:** +20-40 afișări/săpt suplimentare după 4-6 săpt indexare.

### MANUAL #4 — Sterge schiță Google Ads campanie nr. 3

**Context:** Schiță creată pe 6 mar 2026, fără grupuri sau cuvinte cheie, abandonată de 80+ zile.

**Pași:** Google Ads → Campanii → Toate → tab "Schițe" → Campania nr. 3 → meniul "⋮" → Eliminați.

**Efort:** 30 sec · **Impact:** curățenie cont.

### MANUAL #5 — Investighează Unassigned trafic (+600 % WoW)

**Context:** 7 sesiuni necategorizate într-o săpt în GA4. Posibil UTM lipsă pe campanii email/social.

**Pași:**
1. GA4 → Rapoarte → **Achiziția** → **Atragere de trafic**
2. Filtrează după **Group canal sesiune principală = Unassigned**
3. Vezi **Landing page** și **session_source/medium**
4. Dacă vin de pe Facebook/Instagram, adaugă UTM la postări:
   - `?utm_source=facebook&utm_medium=social&utm_campaign=organic`
   - `?utm_source=instagram&utm_medium=social&utm_campaign=story`

**Efort:** 15 min · **Impact:** atribuire corectă, posibilă descoperire canal performant.

### MANUAL #6 — Investighează "Camere Supraveghere Brașov: 0 afișări" în GA4

**Context:** Pagina a căzut la 0 afișări (-100 % WoW). Posibil să fi fost ne-indexată, redenumită, sau blocată.

**Pași:**
1. Verifică în browser: `https://cssi.ro/camere-supraveghere-brasov` se încarcă?
2. GSC → Inspect URL → vezi statusul de indexare
3. Verifică linkurile interne către această pagină (poate au fost rupte)

**Efort:** 10 min · **Impact:** dacă a fost spartă, restaurarea o aduce înapoi în top traffic.

### MANUAL #7 — Setează Meta Pixel ID

**Context:** În `cookie-consent.js` linia 40: `var META_PIXEL_ID = 'XXXXXXXXXXXXXXXXX';` — placeholder.

**Pași:** Meta Business Manager → Events Manager → copiază Pixel ID → înlocuiește placeholder-ul.

**Efort:** 5 min · **Impact:** funnel-ul Facebook/Instagram începe să tracketeze.

---

## 📋 SUMAR FIȘIERE MODIFICATE

| Fișier | Modificare |
|---|---|
| `camere-supraveghere-brasov.html` | Meta title + description optimizate |
| `pontaj-electronic.html` | Meta title + description optimizate |
| `pontaj-electronic-brasov.html` | Meta title + description optimizate |
| `alarma-antiefractie-brasov.html` | Meta title + description optimizate |
| `control-acces-brasov.html` | Meta title + description optimizate |
| `detectie-incendiu-isu.html` | Meta tags actualizate; **NOINDEX restaurat** cu comentariu HTML explicit |
| `detectie-incendiu-brasov.html` | **PAGINĂ NOUĂ** (draft, noindex pending decizie) |
| `tracking.js` | Dual-fire Ads+GA4 pentru phone_call & whatsapp_click; debug logging |
| `cookie-consent.js` | gtag.js încărcat cu GA4 ID (best practice) |

---

## 🎯 IMPACT AGREGAT AȘTEPTAT (4-6 săpt)

| Metric | Baseline | Target | Cum |
|---|---:|---:|---|
| CTR organic GSC | 2,2 % | **4-5 %** | meta tags optimizate |
| Clicuri organice/săpt | 12 | **24-30** | CTR x2 pe top queries |
| Conversii Google Ads/lună | ~3-5 | **15+** | tracking dual-fire deblocat |
| Sesiuni Organic Search/săpt | 6 | **10-15** | CTR + indexare nouă pagină |

---

*Raport generat 2026-05-27 împreună cu daily brief. După implementarea acțiunilor manuale #1, #2, vei putea reactiva strategia Maximize Conversions în Google Ads.*
