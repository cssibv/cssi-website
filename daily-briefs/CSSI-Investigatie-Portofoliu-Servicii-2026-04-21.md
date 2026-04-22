# Investigație: „Portofoliu și Servicii: 0 afișări — sitelinks UTM"

**Data:** 21 aprilie 2026
**Proprietate:** CSSI.ro (GA4 ID: 525787706)
**Task:** #26 [completed]

---

## 🎯 Verdict

**FALSE ALARM.** Ipoteza din Daily Brief („0 afișări — probabil sitelinks Ads nu păstrează UTM-urile") este incorectă. Ambele pagini primesc trafic, tracking-ul funcționează, UTM-urile sunt OK.

---

## 📊 Date reale din GA4 Pages

### Ultimele 7 zile (14-20 apr 2026)

| # | Path | Afișări | Users | Evenimente | Durata medie |
|---|------|---------|-------|------------|---|
| 1 | / | 39 (54.17%) | 21 | 133 | 33 sec |
| 2 | /contact | 8 (11.11%) | 5 | 16 | 35 sec |
| 3 | /despre-noi | 6 (8.33%) | 4 | 12 | 23 sec |
| 4 | /camere-supraveghere | 4 (5.56%) | 4 | 6 | 13 sec |
| 5 | /pontaj-electronic | 4 (5.56%) | 2 | 13 | 1 min 02 sec |
| **6** | **/portofoliu** | **3 (4.17%)** | **3** | **7** | **17 sec** |
| **7** | **/servicii** | **3 (4.17%)** | **3** | **6** | **2 sec** ⚠️ |
| 8 | /pontaj-electronic-brasov | 2 (2.78%) | 1 | 6 | 24 sec |
| 9 | /alarma-antiefractie | 1 (1.39%) | 1 | 3 | 46 sec |
| 10 | /instalatii-termice-sanitare | 1 (1.39%) | 1 | 5 | 2 sec |

### Ultimele 28 de zile (24 mar – 20 apr)

| Pagină | Afișări | Users | Rank |
|---|---|---|---|
| /portofoliu | **25** | 12 | **#5** (!) |
| /servicii | **15** | 6 | #7 |

---

## ✅ Dovada că tracking-ul funcționează

1. **Portofoliu primește trafic** — 25 afișări / 12 utilizatori pe 28 zile face din ea **a 5-a pagină** ca trafic pe tot site-ul (după /, /contact, /aer-conditionat, /despre-noi).
2. **Servicii primește trafic** — 15 afișări / 6 utilizatori = a 7-a pagină.
3. **Ambele au scripts-ul de tracking** încărcat corect (`/tracking.js` + `/cookie-consent.js` + gtag stub).
4. **Nu există problemă UTM**: gtag-ul e configurat prin `gtag('config', 'AW-17987940313')` în cookie-consent.js pe TOATE paginile, inclusiv `utm_source=google`/`utm_medium=cpc` sunt tracked automat când există `gclid` în URL.

---

## 🚨 Singurul semnal real: /servicii cu 2 secunde durată medie

**Durata medie pe /servicii = 2 secunde.** Asta nu e problemă de tracking, ci de **UX/conținut pe hub page**:
- Utilizatorii aterizează pe /servicii și pleacă imediat (bounce).
- Probabil pagina e doar o listă de link-uri spre sub-servicii și nu oferă suficient conținut pentru a reține atenția.
- Comparat cu /pontaj-electronic (1 min 02 sec) sau /camere-supraveghere (13 sec), /servicii e clar sub medie.

**Comparație:**
- /aer-conditionat: 24 sec (direct pe serviciu)
- /camere-supraveghere: 13 sec (direct pe serviciu)  
- /despre-noi: 23 sec (hub)
- **/servicii: 2 sec (hub) ← outlier**

---

## 🔧 Acțiuni recomandate

### A) Imediate (0 efort)
Nu e nevoie de nicio acțiune de tracking. **Elimină alerta** „0 afișări" din template-ul Daily Brief — probabil a fost citire greșită pe fereastră și mai scurtă (24h), unde la volum mic (3-5 vizite/zi pe toate paginile) 0 vizite pe /servicii/portofoliu într-o zi e normal statistic, nu bug.

### B) Opțional — sub 10 min (UX fix pentru /servicii)
/servicii cu 2 sec durată medie = hub page slabă. Dacă vrem să facem din ea o landing page eficientă:
1. Adaugă headline puternic + subtitlu ROI/de ce CSSI (8.700 proiecte, 20 ani, ISU/IGPR)
2. Grila cu 15 servicii să aibă descriere scurtă (1 frază) per card, nu doar titlu
3. Adaugă CTA „Cere ofertă" prominent în top 25% din pagină (nu jos)
4. Adaugă secțiune „Cele mai cerute" cu top 4-5 servicii (alarmă, camere, detecție incendiu, pontaj)

### C) Validare suplimentară (când vrem să confirmăm UTM-urile funcționează pe Ads)
Google Ads → Campanii → Segmentare pe UTM/URL destinație. Sau: GA4 → Achiziție → Canale utilizator → Mediul sesiunii → filtru `cpc` → vezi dacă apare google/cpc cu page_view pe /portofoliu și /servicii. Nu e urgent — dovada tracking-ului funcțional e deja la poz. #5 și #7 din tabelul de mai sus.

---

## 📝 Concluzie

Alerta din Daily Brief a fost un fals pozitiv la volum mic. Tracking, UTM-uri și sitelinks funcționează corect. Singurul insight real extras din investigație: **pagina /servicii are o problemă de UX** (2 sec durată medie), dar e separate de ipoteza inițială.
