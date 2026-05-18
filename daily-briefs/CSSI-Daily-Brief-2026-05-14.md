# CSSI — Raport zilnic monitorizare
**Data:** 2026-05-14 (Joi, săptămâna 20)
**Cont Google Ads:** 666-033-6562 | **Buget:** 60 EUR/lună (~300 RON)
**Generat de:** scheduled task `cssi-daily-monitoring` (autonom, fără user prezent)
**Date live capturate:** ⚠️ Google Ads BLOCAT | ⚠️ GA4 BLOCAT | ⚠️ Search Console BLOCAT

> **⚠️ NOTĂ CRITICĂ — RUN AUTONOM BLOCAT PE BROWSER**
>
> Task-ul rulează scheduled fără user prezent. Sistemul de Chrome MCP a detectat **2 browsere conectate** (`laptop` 75c174a1…, `Browser 2` cbdbcd5c…) și aplică o regulă de securitate care **interzice selectarea automată** — necesită confirmarea explicită a userului în chat. Pentru că MIHAI nu este la tastatură, **nu pot deschide Google Ads, GA4 sau Search Console** și nu pot captura date live azi.
>
> **🔧 SOLUȚIE PE LUNG TERMEN:** Decuplează `Browser 2` din extensia Chrome (sau redenumește-l explicit „archive") pentru ca task-ul autonom să poată folosi `laptop` direct. Sau, înainte de fiecare execuție programată, asigură-te că o singură extensie e activă.
>
> Acest raport conține: (a) recapitulare ieri (13-mai) cu deltas estimate; (b) verificări care nu necesită browser; (c) acțiuni propuse bazate pe ultimul snapshot live disponibil.

---

## TL;DR (3 linii)
- **Ce merge bine (din ultimul snapshot — 13-mai):** Trendul de eficientizare Ads continuă (cost -12% WoW, proiecție lunară 547 RON în scădere de la 622). Pool GSC crește (38 → 45 queries). Pagini funcționale (Detecție Incendiu, Servicii) ies din zona 0-trafic.
- **Ce nu merge:** 🔴 **Capturarea live blocată azi** (2 browsere conectate → confirmare manuală necesară). 🔴 Conversii GA4 încă sub semnul întrebării (evenimente `phone_call` / `form_submit` / `generate_lead` lipseau din lista evenimente la ultimul snapshot). 🔴 Buget Ads tot peste pace (~547/300 RON proiecție lunară).
- **Acțiune recomandată azi:** **Verifică manual cele 2 lucruri critice deschise din raportul de ieri** — (1) GA4 Admin → Evenimente: sunt `phone_call` / `form_submit` / `generate_lead` marcate cheie?; (2) Meta-titles + descriptions pe `/camere-supraveghere.html` și `/sistem-pontaj-electronic.html` (queries cu 0 CTR la 50+20 afișări).

---

## ⚠️ Status capturare live

| Platformă | Status | Cauză | Acțiune utilizator |
|---|---|---|---|
| Google Ads | 🔴 BLOCAT | 2 browsere conectate, confirmare necesară | Vezi „Soluție" mai sus |
| GA4 | 🔴 BLOCAT | idem | idem |
| Search Console | 🔴 BLOCAT | idem | idem |
| Gmail draft | 🟢 disponibil | API direct, nu necesită browser | — |
| Fișier raport local | 🟢 generat | scriere directă în workspace | — |

---

## 1. Google Ads — recapitulare ultimul snapshot (6–12 mai 2026, din raport 13-mai)

> Datele de mai jos nu sunt live azi. Sunt reproduse din raportul precedent ca referință. Pentru cifre actualizate, deschide `https://ads.google.com/aw/overview?ocid=8059575551&euid=6445769730&authuser=0` manual.

| Metric | Ultim snapshot (6–12 mai) | Δ vs 7d anterior | Țintă | Stare |
|--------|---------------------------|-------------------|-------|-------|
| Afișări | 483 | -11,2% | n/a | 🟡 |
| Clicuri | 45 | -11,8% | n/a | 🟡 |
| **CTR campanie** | **9,31%** | -0,07pp | 15-18% | 🔴 Sub țintă |
| CPC mediu | 2,84 RON | -0,4% | <4 RON | 🟢 |
| Cost total 7 zile | 127,65 RON | -12,1% | ~70 RON/săpt | 🟡 |
| Proiecție lunară | ~547 RON | de la 622 | 300 RON | 🟡 ~182% |
| Diagnostic Ads | „Nu există suficiente cuv. cheie relevante" (warning Segmente de public) | — | — | 🟡 verifică |

### Estimare pentru data de 14-mai (nu live)
Pe trendul observat, costul zilnic ar trebui să se așeze pe ~16-18 RON/zi (până ieri ~18,2 RON/zi medie). Dacă proiecția lunară coboară sub 500 RON la următoarea captură, e semnal că ajustările bid funcționează. Dacă urcă peste 600 RON, regres.

### Task-uri pending Google Ads (recap)
- **#15** Cleanup keywords „Low search volume" — pending
- **#16** Restructurare în 3 Ad Groups tematice (Camere / Alarme+Acces+Pontaj / Detecție Incendiu) — pending
- **#17** Creare 3 RSA-uri noi (15 titluri + 4 descrieri fiecare) — pending
- **Criteriu de deblocare:** 15+ conversii cumulate stabile + 30 zile date. **Decizie reluată luni 18-mai** (raport săptămânal).

---

## 2. GA4 — recapitulare ultimul snapshot (6–12 mai 2026, din raport 13-mai)

> Nu live azi.

| Metric | Ultim snapshot | Δ vs 7d prior | Δ vs raport luni 11-mai |
|---|---|---|---|
| Utilizatori activi | 20 | +33,3% | -4,8% |
| Utilizatori noi | 16 | +6,7% | -5,9% |
| **Evenimente importante** | **5** | **-16,7%** | **-44,4%** (9 → 5) |
| Număr evenimente | 273 | +131,4% | — |

### 🚨 Alertă critică nerezolvată
Evenimentele `phone_call`, `form_submit`, `generate_lead` lipseau din lista evenimente vizibile la captura de ieri. **Nu există certitudine că au fost verificate de atunci.** Aceasta este #1 prioritate manuală pentru MIHAI azi.

**Pași verificare (5-10 min în browser):**
1. Deschide [GA4 Events Admin](https://analytics.google.com/analytics/web/#/a385388640p525787706/admin/events)
2. Filtrează lista pe nume eveniment
3. Confirmă status „marcat ca eveniment cheie" pentru `phone_call`, `form_submit`, `generate_lead`, `whatsapp_click`
4. Dacă oricare e dezactivat → reactivează
5. Test live: deschide cssi.ro, click „Solicită ofertă" → check GA4 DebugView (Configure → DebugView)

---

## 3. Google Search Console — recapitulare (4–10 mai 2026, din raport 13-mai)

> Nu live azi.

| Metric | Ultim snapshot 7d | Δ vs raport precedent |
|---|---|---|
| Total clicuri | 6 | +20% (5→6) |
| Total afișări | 393 | — |
| CTR mediu | 1,5% | +0,23pp |
| **Poziție medie** | **17,8** | -0,5 (ameliorare) |

### Top queries cu CTR 0% (acțiune meta-tag urgentă)
- `camere supraveghere` — 50 af / 0 cli — meta-snippet de optimizat
- `pontaj electronic` — 20 af / 0 cli — schema + meta agresivă
- `camere de supraveghere` — 10 af / 0 cli
- `sisteme de pontaj` — 7 af / 0 cli
- `camere supraveghere brasov` — 6 af / 0 cli (query principal!)

---

## 🚨 Alerte (priority)

### 🔴 CRITIC — moștenite din raportul de ieri (nu pot fi reverificate azi)
1. **Conversii GA4 -44% WoW (9 → 5).** Verificare GA4 Events Admin nu a fost confirmată ca rezolvată. **Acțiune manuală P0 MIHAI: verifică status evenimente.**
2. **CTR organic 0% pe top 5 queries (~91 afișări).** Meta-tags pentru paginile `/camere-supraveghere.html`, `/sistem-pontaj-electronic.html`, `/camere-supraveghere-brasov.html` nu au fost re-optimizate. **Acțiune manuală P1 MIHAI: re-scrie meta.**

### 🟡 ATENȚIE
3. **Run scheduled BLOCAT pe browser selection** — 2 extensii Chrome conectate. Necesită cleanup la nivel de extensie.
4. **Buget Ads peste pace** — proiecție 547 RON / țintă 300 RON. Tendința scade (622 → 547) dar nu suficient.
5. **Diagnostic Ads — Segmente de public:** warning persistent „Nu există suficiente cuv. cheie relevante".

### 🟢 SEMNALE POZITIVE (din ultimul snapshot)
- Cost Ads -12% WoW.
- Pool GSC 38 → 45 queries.
- Pagini funcționale (Detecție Incendiu +500%, Servicii +400%) cresc.

---

## ✅ Acțiuni propuse azi

### Acțiune 1 (P0, manuală) — Cleanup Chrome extension (2 → 1)
- **Context:** Task scheduled azi a fost blocat fiindcă extensia Chrome are 2 browsere asociate (`laptop` + `Browser 2`). Sistemul de securitate refuză să aleagă unul fără confirmarea ta în chat.
- **Pași:**
  1. Deschide Chrome → extensie Claude → Setări
  2. Deconectează „Browser 2" (cbdbcd5c-5ab6-4ceb-a9da-a46cf50142a2) dacă nu mai e folosit
  3. Sau redenumește unul explicit cu prefix „archive-" ca să fie clar care-i activ
  4. La următorul run scheduled, sistemul va găsi un singur browser și va proceda automat
- **Efort:** 3-5 min
- **Impact:** Deblochează capturarea live zilnică pentru întreg ciclul

### Acțiune 2 (P0, moștenită) — Verificare evenimente cheie GA4
- **Context:** -44% WoW pe Evenimente importante este alerta critică deschisă de luni 11-mai.
- **Pași:** vezi „Pași verificare" din secțiunea 2 de mai sus.
- **Efort:** 10 min
- **Impact așteptat:** Recuperează 4 conversii/săpt = ~200 RON/lună valoare conversie

### Acțiune 3 (P1, moștenită) — Re-optimizare meta pentru queries cu 0 CTR
- **Context:** 91 afișări pe primele 5 queries → 0 clickuri. Snippet-urile dezavantajează.
- **Pași:**
  1. Editează meta-title și meta-description pe `/camere-supraveghere.html`, `/sistem-pontaj-electronic.html`, `/camere-supraveghere-brasov.html`
  2. Pattern title: `{Topic} {Brașov} | {Valoare} | CSSI` (≤60 char)
  3. Pattern description: include CTA + dovadă socială („8.700+ proiecte", „WhatsApp 24/7") (≤155 char)
  4. Deploy + Trimitere URL în GSC pentru re-indexare rapidă
- **Efort:** 45 min
- **Impact așteptat:** CTR 1,5% → 3-4% în 2 săpt = ~10-15 clickuri extra/săpt

---

## 📊 Progres către țintele 60 zile (din ultimul snapshot)
| Țintă | Curent | Progres |
|---|---|---|
| CTR Ads 15-18% | 9,31% | 🔴 ~52% |
| Conversii primare/lună | ~22/lună proiecție (5/săpt) — sub semn întrebare | 🟡 |
| Rang „pontaj electronic" GSC | poz medie site 17,8 | 🟡 nu am breakdown per query azi |
| Valoare conversii / lună | ~250 RON | 🟡 ~42% țintă inferioară 600 RON |

---

**Browser folosit pentru capturare:** ⚠️ NICIUNUL (blocaj — vezi secțiunea status)
**Generat:** 2026-05-14 (Joi)
**Următorul raport săptămânal:** Luni, 18-mai-2026

---

📁 **Arhivă raport:** `computer://C:\Users\Diaconu Mihai\Documents\Website\cssi-website\daily-briefs\CSSI-Daily-Brief-2026-05-14.md`
