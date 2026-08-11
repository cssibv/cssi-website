# CSSI — Plan de execuție pentru creștere în căutări
**Data:** 2026-08-10 · Pe baza datelor GSC/GA4/Ads din raportul zilnic + audit al site-ului

> Concluzie audit: site-ul e **tehnic excelent și complet** (schema, FAQ, tracking, pagini pe servicii × orașe, gestionat de smart-web.ro). Nu-ți lipsesc pagini. Creșterea substanțială de acum vine din **autoritate off-page + Google Business Profile + măsurare corectă**, plus câteva ajustări on-page de precizie. Documentul acoperă cele 3 direcții alese: (1) fix măsurare conversii, (2) linkuri interne, (3) GBP + recenzii.

---

## ✅ LEVER 1 — Linkuri interne (EXECUTAT parțial azi)

**Făcut azi (în `index.html`, backup: `index.html.bak-20260810`):**
Homepage-ul avea în footer un bloc „Servicii Brașov" care linkuia Pontaj, Alarmă, dar **nu** paginile comerciale cu cerere reală. Am adăugat 3 linkuri cu anchor exact:

- **Camere Supraveghere Brașov** — query-ul #1 din GSC care primește deja clicuri (21 afișări/săpt, poz. ~11,7) și **nu avea niciun link din homepage**.
- **Control Acces Brașov**
- **Detecție Incendiu Brașov**

*Efect:* concentrează autoritate pe paginile comerciale-locale care stau pe pagina 2. Homepage-ul (cea mai puternică pagină) trimite acum semnal direct spre pagina care poate urca cel mai ușor în top 10.

**Rămâne de făcut (recomandat, risc mic):**
1. **Linkuri contextuale în conținut** (nu doar footer) din paginile cu trafic mare spre paginile-țintă: `portofoliu.html` (108 afișări/28z) și `blog_index.html` → link „camere supraveghere Brașov" în corpul textului. Linkurile în conținut cântăresc mai mult decât cele din footer. *Efort: 20 min. Impact: mediu.*
2. **Aliniere anchor text** — folosește variații naturale („montaj camere supraveghere Brașov", „instalare camere Brașov"), nu același text peste tot.

---

## 🔧 LEVER 2 — Fix măsurare conversii (diagnostic + checklist)

**De ce contează:** Ads raportează 3 conversii, GA4 „Clienți potențiali" = 0. Fără măsurare corectă nu poți optimiza campania, nu poți dovedi ROI și nu poți migra la Maximize Conversions. Codul de tracking **există și e bun** — problemele sunt de configurare + 2 bug-uri minore în cod.

### Ce am găsit în cod (`tracking.js` + pagini)
1. **Dublă-numărare `phone_call` și `whatsapp_click`.** Fiecare pagină (≈140 fișiere) are handler inline pe `tel:` / `wa.me`, ȘI `tracking.js` leagă aceleași evenimente global pe `DOMContentLoaded`. Rezultat: fiecare click telefon/WhatsApp se numără de **2×**. (Doc-ul din `tracking.js` confirmă că dubla-numărare pe *formulare* a fost deja reparată în 20.07 — dar handlerele inline telefon/WhatsApp au rămas.)
   → **Fix:** păstrează o singură sursă. `tracking.js` e declarat „sursa unică de adevăr", deci scoate handlerele inline `phone_call`/`whatsapp_click` din pagini (se poate face scriptat, cu backup). *Pot face eu asta dacă vrei — 30–45 min.*
2. **Valori inconsistente pe același eveniment.** Inline: `phone_call value:100`; `tracking.js`: `value:150`. → standardizează pe o singură valoare (recomand 150).
3. **Inconsistență AggregateRating între pagini.** `index.html`: 127 recenzii / 4,9. `camere-supraveghere-brasov.html`: 8 recenzii / 5,0. Google poate ignora/penaliza rating-uri inconsistente. → aliniază TOATE paginile pe numărul real din Google Business Profile.

### Checklist configurare (necesită acces GA4 + Ads — MIHAI/agenție)
1. **GA4 → Admin → Key events (Evenimente-cheie):** confirmă că `generate_lead`, `phone_call`, `whatsapp_click`, `form_submit` sunt marcate ca *key events*. Dacă nu, nu apar ca „Clienți potențiali". *Efort: 5 min.*
2. **GA4 → Ads Links + Ads → Conversions:** confirmă că evenimentele-cheie GA4 sunt **importate** ca acțiuni de conversie în Ads (strategia aleasă e „GA4 → import în Ads", nu fire direct). Verifică ce sursă generează cele 3 conversii actuale (probabil „Calls from ads / Clicks to call" native).
3. **Ads → Conversions → setare „Primară/Secundară":** doar acțiunile reale de lead să fie *primare* (form, phone, whatsapp). „Clicks to call" cu valoare 1 RON să rămână secundară.
4. **Test live:** deschide `cssi.ro/?cssi_debug=1` (există debug logging în tracking.js), dă click pe telefon/WhatsApp și trimite formularul — verifică în GA4 Realtime că fiecare eveniment apare **o singură dată**.

*Impact: fundația pentru orice optimizare plătită + pentru migrarea la bidding pe conversii.*

---

## 📍 LEVER 3 — Google Business Profile + recenzii (cel mai mare ROI local)

**De ce e prioritatea #1 reală pentru creștere:** pentru un instalator local din Brașov, *map pack-ul* (pachetul local Google Maps) apare deasupra rezultatelor organice la căutări „…brasov" și „…near me". Paginile tale sunt bune, dar pe pagina 2 organic; GBP te poate pune în top 3 local mult mai repede. Datele GA4 arată că majoritatea traficului e din Brașov — exact publicul GBP.

### A. Optimizare profil (o singură dată — efort: 1–2h)
- **Categorii:** categorie principală „Security system installer / Firmă instalare sisteme de securitate" + categorii secundare (Alarm system supplier, Fire protection system supplier, Electrician). Categoriile corecte = cel mai mare factor de ranking local.
- **Zone deservite:** adaugă Brașov + toate localitățile pentru care ai pagini (Codlea, Ghimbav, Râșnov, Predeal, Săcele, Hărman, Sânpetru, Bran).
- **Servicii:** listează fiecare serviciu ca item separat (camere supraveghere, alarmă antiefracție, control acces, pontaj electronic, detecție incendiu, automatizări porți) cu descriere de 2–3 fraze fiecare.
- **Poze:** urcă 15–20 poze reale de la lucrări (ai portofoliu 8700+). Firmele cu poze primesc semnificativ mai multe cereri. Adaugă poze noi lunar.
- **Produse/Postări:** activează secțiunea.

### B. Motor de recenzii (recurent — cel mai important)
- **Aliniere număr recenzii:** decide numărul REAL de recenzii Google și pune-l identic în tot site-ul (acum: 127 vs 8 — vezi Lever 2, pct. 3).
- **Țintă:** minim **4–6 recenzii Google noi/lună**, constant. Volumul + prospețimea recenziilor sunt factor direct de ranking local.
- **Proces:** ai deja pagina `/review` și link în footer. Trimite linkul direct de recenzie (Google „Cere recenzii" → link scurt) prin SMS/WhatsApp **în ziua finalizării lucrării**, când clientul e mulțumit.
- **Răspunde la TOATE recenziile** (există deja skill „recenzii" pentru Conca Verde — putem face unul similar pentru CSSI). Răspunsurile cu cuvinte-cheie („camere supraveghere Brașov") ajută și ele.

**Șablon cerere recenzie (SMS/WhatsApp):**
> Bună ziua, [Nume]! Mulțumim că ați ales CSSI pentru [montaj camere/alarmă] în [localitate]. Dacă sunteți mulțumit de lucrare, ne-ați ajuta enorm cu o recenzie de 1 minut aici: [link Google]. Vă mulțumim! — Echipa CSSI

**Șablon răspuns la recenzie pozitivă:**
> Mulțumim, [Nume]! Ne bucurăm că sistemul de [serviciu] din [localitate] funcționează impecabil. Vă stăm la dispoziție pentru mentenanță și orice extindere. — CSSI Brașov

### C. Semnale locale conexe (mediu termen)
- **Citări NAP** (Nume-Adresă-Telefon identice) în directoare locale RO: cauta.ro, listafirme.ro, infofirme, ghidul primăriei/Camera de Comerț Brașov, hartă Waze.
- **Consistență NAP** cu ce e în schema site-ului (Strada Busuiocului 6, 500376 Brașov, +40752288400).

---

## Ordinea recomandată de execuție (următoarele 2 săptămâni)
| # | Acțiune | Owner | Efort | Impact |
|---|---|---|---|---|
| 1 | Verifică key events GA4 + import Ads + test `?cssi_debug=1` | MIHAI/agenție | 30 min | 🔴 Mare |
| 2 | Optimizare GBP (categorii, servicii, poze, zone) | MIHAI | 1–2h | 🔴 Mare |
| 3 | Pornește motorul de recenzii (4–6/lună, șablon SMS) | MIHAI | recurent | 🔴 Mare |
| 4 | Aliniază numărul de recenzii în schema tuturor paginilor | agenție/eu | 30 min | 🟠 Mediu |
| 5 | Dedup handlere inline phone/whatsapp (fix dublă-numărare) | eu | 45 min | 🟠 Mediu |
| 6 | Linkuri contextuale în conținut spre paginile Brașov | eu/agenție | 20 min | 🟠 Mediu |

*Notă: pașii 4, 5, 6 îi pot executa eu direct în fișiere (cu backup). Pașii 1–3 necesită accesul tău la GA4/Ads/GBP — îți dau pașii exacți sau te ghidez live.*
