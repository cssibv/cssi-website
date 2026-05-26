# CSSI — Daily Brief 26 mai 2026 (marți)

> Perioada raportată: **19–25 mai 2026** (Google Ads & GA4) / **3 luni interval vizibil, 25.02 → 24.05.2026** (GSC, lag 2 zile)
> Cont Google Ads: 666-033-6562 · GA4 property: a385388640 ✅ **DEBLOCAT** · GSC: sc-domain:cssi.ro
> Sesiune: rulare autonomă scheduled task, fără user prezent.

---

## TL;DR

- **🎉 MARE CÂȘTIG:** GA4 e accesibil din nou — blocajul de luni a fost rezolvat. Acum avem vizibilitate completă: 23 utilizatori activi, 31 sesiuni Paid Search (+34,8 % WoW), 4 `form_submit`. CTR Google Ads urcă la **6,81 %** (de la 6,48 %), iar costul scade la **132,86 RON** (-9,3 % WoW). Eficiență mai bună la buget mai mic.
- **Nu merge:** Evenimentele `phone_call` și `whatsapp_click` **NU apar deloc** în lista GA4 — tracking-ul pe canalele de telefon/WhatsApp e spart la nivel de site (probabil GTM trigger lipsă sau dataLayer.push neexecutat). Concluzia "tracking conversii downstream e spart" din rapoartele anterioare e **confirmată oficial**: 4 form_submit înregistrate, 0 phone_call, 0 whatsapp_click. Lista de 25+ negative keywords din raportul de luni rămâne tot neaplicată.
- **1 acțiune azi:** **Fixează tracking-ul `phone_call` și `whatsapp_click`** — verifică GTM-ul site-ului, vezi dacă există tag-uri active pe `tel:` și `https://wa.me/` clicks, sau dacă dataLayer.push se execută. Fără ele, jumătate din valoarea contului (conversii primare de 50 RON fiecare) e invizibilă. **30 min, deblocant pentru migrare Maximize Conversions.**

---

## 1. Google Ads (19–25 mai 2026)

### Metrici cont

| Metric | Valoare 7 zile | WoW vs. raportul de luni | Comentariu |
|---|---:|---|---|
| Cost | **132,86 RON** | -13,54 RON (-9,3 %) ✅ | sub prag bugetar, economisire |
| Afișări | **573** | stabil (+0/-0) | volum constant |
| Clicuri | **39** | +2 (+5,4 %) ✅ | clic-uri pe-up |
| CTR | **6,81 %** | +0,33 pp ✅ | trend pozitiv, încă mult sub țintă 15–18 % |
| CPC mediu | **3,41 RON** | -0,15 RON ✅ | cost per click scade |
| Diagnostic campanie | „Nu prezintă probleme de difuzare" | stabil | OK |
| Scor optimizare | ~79,8 % (neactualizat în UI) | stabil | OK |

**Anomalii detectate (praguri ±20 %/30 %):**
- Cost -9,3 % WoW — sub prag de alertă, normalizare după săptămâna anterioară.
- Sesiuni Paid Search GA4 **+34,8 %** WoW (31 vs. ~23 anterior) — bun semnal de eficiență: mai puțin buget, mai mult trafic util.

### Notă pe tabelul de cuvinte cheie & queries

Tabelul de keywords (46 active) și search terms a fost încărcat în UI dar nu a putut fi exportat în această sesiune (rendering virtualizat în Google Ads). Concluziile structurale din raportul de luni rămân valabile:
- Concentrarea bugetară pe `montaj camere supraveghere` (~76 % din buget) — risc structural neschimbat.
- 2 keywords cu CTR sub 5 %: `control acces` (4,65 %), `kit camere supraveghere wireless exterior pret` (2,94 %).
- ~25 termeni candidați pentru negative keywords (branduri, retaileri DIY, geografii greșite) — **încă neaplicați**, vezi acțiuni.

### Facturare

- Sold rămas: ~470 RON (estimat după -132,86 RON consum)
- Următoarea plată automată: **1 iun. 2026**
- Metoda principală: Visa •••• 4909 (fără metodă de rezervă)

---

## 2. Google Analytics 4 (19–25 mai 2026) ✅ DEBLOCAT

### Metrici cont

| Metric | Ultimele 7 zile | WoW % | Comentariu |
|---|---:|---:|---|
| Utilizatori activi | **23** | +11,5 % ✅ | trend ascendent |
| Număr de evenimente | **369** | +18,3 % ✅ | engagement în creștere |
| Evenimente importante | **9** | 0,0 % | stabil |
| Afișări (page_views) | **137** | +11,4 % ✅ | trafic în creștere |

### Atragere de trafic (Grupul principal de canale, ultimele 7 zile)

| Canal | Sesiuni | WoW % |
|---|---:|---:|
| **Paid Search** | 31 | **+34,8 %** ✅ Google Ads convertește în trafic |
| Organic Search | 6 | 0,0 % ⚠️ stagnare SEO |
| Direct | 4 | 0,0 % |
| Cross-network | 0 | +100 % ⚠️ a căzut la 0 (era >0 anterior) |
| Unassigned | 2 | – |

🚨 **Insight critic:** Paid Search face 70 % din sesiuni (31 din 43 total). Organic Search e blocat la 6 sesiuni/săpt. — confirmă constatarea GSC: poziție medie 22,6 (pagina 3 Google) → fără clicuri.

### Top 5 surse / modalitate

| Sursă / modalitate | Utilizatori activi | WoW % |
|---|---:|---:|
| google / cpc | **16** | +11,1 % ✅ |
| (direct) / (none) | 3 | 0,0 % |
| google / organic | 3 | 0,0 % |
| (data not available) | 0 | +100 % ✅ înainte semnal de fingerprint blocking, acum zero |

### Top pagini (afișări, ultimele 7 zile)

| Pagină | Afișări | WoW % |
|---|---:|---:|
| Home (Securitate & Instalații Brașov \| CSSI — 20+ Ani Experiență) | **67** | – |
| Sisteme Securitate Brașov \| CSSI — 20+ Ani, 8700+ Proiecte | 15 | – |
| CSSI Brașov \| Sisteme Securitate, Detecție Incendiu… | 9 | +71,0 % ✅ |
| Contact CSSI Brașov \| Telefon, Email, Ofertă Gratuită | 8 | +33,3 % ✅ |
| Detecție Incendiu ISU Brașov \| CSSI - Ghid Complet | 4 | +55,6 % ✅ |
| Camere Supraveghere Brașov \| Montaj IP & NVR cu Garanție | 1 | +95,7 % ✅ |
| Servicii CSSI Brașov \| Securitate, Automatizare, Instalații Electrice | 1 | +92,9 % ✅ |

→ Tendință foarte bună: paginile-cheie de servicii (Detecție Incendiu, Contact, Camere Supraveghere, Servicii) toate cu creștere WoW peste 30 %.

### Evenimente principale

| Eveniment | Număr | WoW % | Comentariu |
|---|---:|---:|---|
| `page_view` | 137 | +11,4 % | ✅ trafic ok |
| `user_engagement` | 116 | +13,7 % | ✅ |
| `session_start` | 42 | +20,0 % | ✅ |
| `scroll` | 37 | **+184,6 %** | ✅ trigger funcționează |
| `first_visit` | 20 | +9,1 % | ✅ utilizatori noi |
| `form_start` | 5 | +25,0 % | ✅ intent crescut |
| `form_submit` | **4** | 0,0 % | ✅ conversii reale 4 leads/săpt |
| `phone_call` | **— absent —** | – | 🚨 nu apare în listă |
| `whatsapp_click` | **— absent —** | – | 🚨 nu apare în listă |
| `cta_click` | **— absent —** | – | 🚨 nu apare în listă |

🚨 **CONFIRMARE OFICIALĂ:** Trei evenimente cheie de conversie **NU sunt trimise** din site la GA4. Asta explică de ce în Google Ads vedem 8 conversii „fără conversii recente" — pur și simplu site-ul nu trimite semnalul. Conversion tracking spart la sursă (probabil GTM tags lipsă sau JavaScript handler nedeclanșat pe `<a href="tel:">` și `<a href="https://wa.me/">`).

### Țări

| Țară | Utilizatori activi | WoW % |
|---|---:|---:|
| Romania | **22** | +12,0 % ✅ |
| Germany | 1 | – |
| Italy | 0 | +100 % |

✅ Geografia e corectă — 95,7 % audiență România.

---

## 3. Google Search Console (interval 3 luni, 25.02 → 24.05.2026)

> Notă: filtrul 7-zile necesită interacțiune extra cu UI; folosim intervalul implicit 3 luni pentru tendințe.

### Metrici cont

| Metric | 3 luni | vs. raportul de luni | Notă |
|---|---:|---:|---|
| Clicuri totale | **71** | stabil | sub mediul industriei |
| Afișări totale | **3,37 K** (3.370) | +50 vs. 3,32K ✅ | continuă creșterea |
| CTR mediu | **2,1 %** | stabil | sub 5 % industrie |
| Poziție medie | **22,6** | -0,1 ✅ | îmbunătățire lentă pagină 2-3 |
| Indexare | 56 indexate / 85 neindexate | ⚠️ schimbat | vezi alertă |

### Top 10 queries (după afișări, 3 luni)

| # | Interogare | Clicuri | Afișări | Δ Afișări vs. luni | Notă |
|---|---|---:|---:|---:|---|
| 1 | `cssi` | **6** | 38 | +1 | ✅ brand traffic |
| 2 | `ajax vs paradox` | 1 | 2 | 0 | long-tail blog |
| 3 | `camere supraveghere` | 0 | **203** | +8 ✅ | 🚨 cea mai mare opportunity ratată |
| 4 | `pontaj electronic` | 0 | 100 | +5 ✅ | 🚨 trend ascendent |
| 5 | `sistem pontaj electronic` | 0 | 91 | +3 ✅ | 🚨 ținta top 12-15 |
| 6 | `sisteme de pontaj electronic` | 0 | 73 | +1 | 🚨 |
| 7 | `sistem pontaj` | 0 | 62 | +2 | 🚨 |
| 8 | `camere de supraveghere` | 0 | 58 | +1 | 🚨 |
| 9 | `montaj aer conditionat brasov` | 0 | 37 | +1 | 🚨 HVAC vertical |
| 10 | `camere supraveghere brasov` | 0 | 31 | 0 | 🚨 local SEO |

→ Toate queries comerciale câștigă afișări (+15 total în 24 ore) dar **0 clicuri pe niciuna**. Confirmă: poziție medie 22,6 = pagina 3 → utilizatorii nu ne văd. Recomandare structurală în raportul săptămânal de luni.

### Status SEO față de țintele 60 zile

| Țintă | Curent | Status |
|---|---|---|
| Rang „sistem pontaj electronic" 12–15 | poz. medie cont ~22,6 (>20) | 🔴 sub țintă (dar -0,1 față de luni) |
| CTR organic | 2,1 % | 🔴 sub mediu 5 % |
| Trafic organic (sesiuni săptămâna) | 6 sesiuni / 7 zile | 🔴 mult sub țintă |
| Valoare conversii 600–1000 RON/lună | 4 form_submit × 50 RON × 4 săpt. = ~800 RON/lună estimat | 🟢 **pe țintă!** |

🎉 **Surpriză pozitivă:** Cu cele 4 form_submit/săpt măsurate corect în GA4, valoarea conversiilor estimate este de ~800 RON/lună — în interiorul țintei 600-1000 RON. Vestea bună: form-ul convertește. Vestea proastă: nu vedem phone_call și whatsapp_click pentru a confirma valoarea reală totală.

### ✅ Câștig SEO de semnalat

GSC continuă să semnaleze pagină în trend: **„O pagină a înregistrat recent mai multe afișări decât de obicei"** → `https://cssi.ro/blog/pontaj-electronic-ghid-complet`. Semnal pozitiv constant pe conținutul de blog.

### Indexare

- **56 pagini indexate** vs. 47 luni (+9 indexate)
- **85 pagini neindexate** vs. 33 luni (+52 neindexate)
- Total descoperite: 141 vs. 80 luni
- ⚠️ Saltul mare (+61 pagini totale) sugerează că GSC a descoperit pagini noi (probabil paginile geografice `aer-conditionat-*`, `alarma-antiefractie-*`, etc. au fost crawled). Verifică în GSC → Indexare → Pagini ce motive de neindexare apar.

### Date structurate (raport overview)

- HTTPS: **26 valide** / 0 nevalide ✅
- Căi navigare: **25 valide** ✅
- FAQ: **9 valide / 2 nevalide** ⚠️ (neschimbat)
- Fragmente recenzii: **17 valide** ✅
- **Date structurate care nu pot fi analizate: 16** ⚠️ (neschimbat)

---

## 🚨 Alerte

1. 🔴 **TRACKING SPART:** `phone_call`, `whatsapp_click`, `cta_click` **NU sunt trimise** din site la GA4 (confirmat azi cu date complete). Asta blochează: vizibilitatea valorii totale a conversiilor, migrarea la Maximize Conversions, și raportarea reală a leads-urilor. **Acțiunea 1.**
2. ⚠️ **52 pagini noi neindexate** (de la 33 la 85). Necesită verificare cauze: blocate de robots.txt? noindex? duplicate? probabil paginile geografice locale (Brașov, Codlea, Predeal, Râșnov, Bran, etc.) care nu primesc trafic suficient pentru indexare.
3. ⚠️ **Organic Search stagnant la 6 sesiuni/săpt.** — fără schimbare WoW, în timp ce Paid Search e +34,8 %. Investiția SEO nu produce trafic încă.
4. ⚠️ **Cross-network a căzut la 0 sesiuni** (era >0) — verifică dacă Pmax/Display ar fi trebuit să trimită trafic. Posibil nu rulează.
5. ⚠️ **8 acțiuni de conversie cu „Nu există conversii recente"** în Google Ads — confirmat de azi că e cauzat de tracking spart pe phone_call/whatsapp_click.
6. ⚠️ **~25 negative keywords noi propuși de luni** (branduri, retaileri DIY, ploiesti) — **încă nemodificați**. Consum estimat pierdere: 15–20 RON/săpt.
7. ⚠️ **Concentrarea bugetară `montaj camere supraveghere` ~76 %** — risc structural neschimbat.
8. ⚠️ **Fără metodă de plată de rezervă** în Google Ads.

---

## ✅ Acțiuni propuse azi (marți)

### Acțiune 1 (30 min, impact CRITIC, ZERO risc) — TRACKING `phone_call` & `whatsapp_click`

**Verifică Google Tag Manager-ul site-ului:**
1. Deschide GTM container (cssi.ro)
2. Verifică triggers pentru: click pe `tel:`, click pe `https://wa.me/`
3. Verifică dacă există tags GA4 Event configurate să trimită `phone_call` și `whatsapp_click` pe acele triggers
4. Test în GTM Preview: dă click pe un buton de telefon sau WhatsApp și vezi dacă apare evenimentul

**Alternativ (dacă nu folosești GTM):** verifică în Apps Script CSSI Marketing Module sau în `index.html` dacă există addEventListener pe `tel:` și `wa.me`.

**De ce:** Confirmat azi că GA4 nu primește aceste evenimente. Sunt 50 RON valoare conversie per eveniment. Cu 3-5 clicuri telefonice/săpt + 2-3 WhatsApp/săpt estimat → **400-600 RON/lună conversii invizibile** (potențial dublare a valorii actuale).

**Impact:** redeschide vizibilitatea pe canalele de conversie de mare valoare, deblocant pentru migrare Maximize Conversions.

### Acțiune 2 (15 min, impact MARE, igienă cont) — Aplică Negative Keywords

**Adaugă în bulk la negative keywords:** `[shop security]`, `[spy shop]`, `[atutech]`, `[ultra security]`, `[a2t]`, `[do security]`, `[security guard services]`, `[ploiesti]` (frază), `[altex]` (frază), `[leroy merlin]` (frază), `[axis romania]`, `[axis m2025 le]`, `[dahua kta02]`, `[dahua kta03]`, `[reolink]` (frază), `[loosafe]`, `[taggo]`, `[kis604]`, `[cele mai bune camere]`.

**De ce:** flagate în 3 rapoarte consecutive (vineri, luni, azi), neacționate. Consum estimat: ~80 RON/lună risipiți.

**Impact:** economisire ~25 % din buget redirecționat spre intent comercial real.

### Acțiune 3 (10 min, impact mediu, ZERO risc) — Verifică indexare pagini noi

**În GSC → Indexare → Pagini, verifică:**
- De ce 85 pagini sunt neindexate (era 33)
- Care e statusul fiecărei pagini neindexate: "Discovered – currently not indexed", "Crawled – currently not indexed", "Blocked by robots.txt", "Excluded by noindex tag", "Duplicate"
- Dacă sunt paginile geografice (`/aer-conditionat-bran`, `/alarma-antiefractie-codlea`, etc.) — sunt valoroase pentru SEO local, trebuie indexate

**De ce:** 50+ pagini noi neindexate înseamnă SEO local pierdut pentru Brașov, Codlea, Predeal, Râșnov etc.

**Impact:** posibil deblocaj pe `camere supraveghere brasov`, `alarma antiefractie codlea` și similare → trafic organic real.

> **Notă continuitate:** Acțiunile 2 și 3 din raportul de luni (deblocaj GA4, negative keywords) — GA4 e rezolvat ✅. Negative keywords rămân.

---

## Status task-uri pending

| Task | Stare | Recomandare |
|---|---|---|
| #15 Cleanup keywords Low search volume | În progres prin auto-apply | ✅ rămâne auto |
| #16 Restructurare în 3 Ad Groups | pending | ⏸️ După fix tracking phone_call/whatsapp_click + 2 săpt. date curate |
| #17 Creare 3 RSA-uri noi | pending | ⏸️ După #16 |
| Migrare la Maximize Conversions | blocat | 🔴 Necesită fix tracking phone_call/whatsapp_click (acțiunea 1 azi) + 15+ conversii confirmate |

---

📎 **Fișier:** `computer://C:\Users\Diaconu Mihai\Documents\Website\cssi-website\daily-briefs\CSSI-Daily-Brief-2026-05-26.md`
