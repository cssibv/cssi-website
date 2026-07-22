# Proiect „Camere Live Brașov" — plan de implementare

**Inițiat:** 21 iulie 2026 · **Owner:** MIHAI · **Orizont:** 12 luni
**Scop:** construirea unui activ de link building pasiv + demonstrație de produs, prin camere de supraveghere publice live pe locații recognoscibile din Brașov.

---

## 0. Rezumat executiv

**Problema pe care o rezolvă.** cssi.ro are **6 domenii de referință** și Authority Score **2**. Din cele 26 de linkuri externe, 20 vin de la conca-verde.ro (site propriu, devalorizat de Google). Practic: ~4 linkuri externe reale, niciunul din domeniul securității. Trendul e plat de 12 luni.

**De ce camerele live.** Competitorul LINX operează camere publice în centrul Brașovului de ani de zile. Webcam-urile pe puncte recognoscibile atrag linkuri **pasiv și permanent** — portaluri de turism, site-uri meteo, agregatoare, presă locală — fără outreach. Este singurul tip de activ SEO care lucrează singur după instalare.

**Beneficiu secundar, poate mai valoros:** demonstrație live de calitate a imaginii, 24/7, pentru orice potențial client.

| | |
|---|---|
| Investiție per locație | **2.500–4.000 RON** (cash) + ~50 RON/lună |
| Efort intern | ~20–25 h prima cameră, 8–10 h următoarele |
| Primul link | **luna 3** |
| Estimare 12 luni | **5–15 domenii noi** per cameră bine plasată |

**⚠️ Condiție blocantă:** Faza 0 (validare juridică) trebuie încheisă cu GO înainte de orice achiziție. Un incident de confidențialitate la o firmă de securitate este o problemă reputațională, nu una de amendă.

---

## FAZA 0 — Validare juridică `BLOCANTĂ`

**Durată:** 1–2 săptămâni · **Cost:** consultanță juridică · **Gate:** GO / NO-GO

Nimic nu se cumpără și nu se montează până nu trece faza asta.

### Principiul pe care se sprijină tot proiectul

Dacă persoanele din imagine **nu sunt identificabile**, nu se prelucrează date cu caracter personal (Considerentul 26 GDPR) și cea mai mare parte a obligațiilor dispare. Toată arhitectura tehnică din Faza 2 este construită ca să susțină exact această afirmație.

### Întrebări concrete pentru jurist

- [ ] Camera fixă, unghi larg, montaj înalt, fără zoom, **fără înregistrare** — se califică drept prelucrare de date cu caracter personal?
- [ ] Instalația intră sub incidența **Legii 333/2003 / HG 301/2012**, dat fiind că scopul este informativ-promoțional, nu de pază?
- [ ] Ce **temei juridic** invocăm — interes legitim? Este necesară o evaluare a interesului legitim (LIA) documentată?
- [ ] Ce trebuie să conțină **nota de informare** de pe pagină? (draft în Anexa B)
- [ ] Este necesară **semnalizare fizică** la locație?
- [ ] Filmarea domeniului public de pe proprietate privată necesită **aviz de la Primăria Brașov**?
- [ ] Ce prevede **contractul cu gazda** privind răspunderea în caz de sesizare ANSPDCP?
- [ ] Există obligații suplimentare pentru că CSSI este deja operator de date cu caracter personal?

### Precedent util

**LINX operează camere publice în Brașov, public, de ani de zile** — Piața Sfatului, Strada Mureșenilor, Calea București, Bd. Saturn, Michael Weiss, Piața Sf. Ioan. Nu este teritoriu neexplorat juridic. Merită arătat juristului ca punct de referință.

### Criteriu de ieșire

✅ Aviz scris de la jurist + listă de condiții tehnice obligatorii → **se trece la Faza 1**
❌ Restricții care fac proiectul neviabil → se documentează motivul și se închide

---

## FAZA 1 — Alegerea locației și acordul gazdei

**Durată:** 2 săptămâni · **Depinde de:** Faza 0 = GO

### Regula de aur: nu duplica LINX

Ei acoperă centrul istoric. O cameră pe Piața Sfatului te face al doilea rezultat pentru ceva ce există deja și nu va fi listată nicăieri.

### Grilă de evaluare

| Locație | Valoare linkuri | Profil GDPR | Acces / fezabilitate | Cost | Total |
|---|---|---|---|---|---|
| **Poiana Brașov — pârtie / panoramă** | 10 | 9 | 5 | 6 | **30** |
| **Panoramă urbană spre Tâmpa** | 8 | 10 | 7 | 8 | **33** |
| Aeroport Brașov-Ghimbav | 6 | 8 | 3 | 5 | 22 |
| Centru istoric | 7 | 5 | 7 | 8 | 27 — *duplicat LINX* |

**Recomandare:** începe cu **panorama urbană** (fezabilitate mare, profil GDPR impecabil — de la distanța aia nimeni nu e identificabil) și adaugă **Poiana** ca a doua cameră, unde valoarea de linkuri e maximă dar accesul e mai greu.

### Cerință tehnică impusă de Windy

Camera trebuie să arate **suficient cer** în cadru (regulă din noiembrie 2024) și să fie orientată meteo. Camerele de interior nu sunt acceptate. Asta favorizează panoramele și defavorizează unghiurile stradale.

### Acțiuni

- [ ] **Scanează portofoliul de clienți** — ai deja clienți cu clădiri înalte în Brașov sau pensiuni/hoteluri în Poiana? Acela e primul telefon, nu un prospect rece.
- [ ] Verifică la fața locului: vizibilitate cer, curent, internet, punct de montaj sigur
- [ ] Contactează gazda cu pitch-ul din **Anexa A**
- [ ] Semnează acordul (clauze validate în Faza 0)

### Criteriu de ieșire

✅ Locație confirmată + acord scris de la gazdă + acces la curent și internet

---

## FAZA 2 — Implementare tehnică

**Durată:** 2–3 săptămâni

### Specificații echipament — dictate de GDPR, nu de marketing

| Componentă | Cerință | De ce |
|---|---|---|
| Tip cameră | **Fixă, NU PTZ** | O cameră care *poate* fi apropiată e tratată juridic diferit de una care nu poate |
| Rezoluție | **Full HD suficient** | 4K crește identificabilitatea — exact ce nu vrem |
| Unghi | Larg, montaj înalt | Siluete, nu fețe; numere de înmatriculare ilizibile |
| Înregistrare | **ZERO. Doar flux live** | Fără arhivă = fără date de securizat, de furnizat la solicitare sau de compromis |
| Carcasă | IP66/67 + încălzire | Obligatoriu la Poiana; util oriunde iarna |

### Lanțul de streaming

```
Cameră IP → encoder/RTMP → YouTube Live 24/7 → embed pe pagina cssi.ro
```

**De ce YouTube Live:** găzduire gratuită și nelimitată, uptime excelent, iar canalul devine el însuși un activ. Atenție: camerele Hikvision/Dahua nu fac de obicei RTMP nativ — e nevoie de un encoder sau un mini-PC / Raspberry Pi cu `ffmpeg`.

### Pagina de pe site

- [ ] URL dedicat, ex. `/camere-live-brasov` sau `/webcam-poiana-brasov`
- [ ] Embed YouTube + descriere a locației (context turistic, nu doar tehnic)
- [ ] **Notă de informare GDPR** vizibilă (Anexa B)
- [ ] Link contextual către `/camere-supraveghere-brasov` — transferă autoritatea acolo unde ai nevoie
- [ ] Text scurt: „Camera este instalată și întreținută de CSSI" + CTA discret
- [ ] Adăugare în `sitemap.xml` și în meniul principal

### Acțiuni

- [ ] Achiziție echipament (după GO juridic)
- [ ] Montaj + configurare stream
- [ ] Test 7 zile continuu — **uptime minim 98% înainte de a trece mai departe**
- [ ] Publicare pagină
- [ ] Monitorizare stream în sistemul propriu (alertă la cădere)

---

## FAZA 3 — Listare și promovare

**Durată:** 2 săptămâni + activitate continuă

### Windy.com — prioritatea absolută

Cel mai valoros link din tot proiectul. Acceptă și fluxuri YouTube.

**Condiții de acceptare:**
- [ ] Cameră de **exterior**, orientată meteo
- [ ] **Cer vizibil** suficient în cadru
- [ ] **Publicată deja** pe site-ul propriu
- [ ] Operată de tine (nu se pot adăuga camere ale altora)
- [ ] Flux funcțional și actualizat

**Aprobare:** de la 20 de minute până la 2–4 săptămâni, în funcție de coadă.

### Restul listărilor

| Țintă | Unghi |
|---|---|
| Agregatoare de webcam-uri | Listare directă |
| Portaluri de schi (pentru Poiana) | Condiții pe pârtie, live |
| Site-uri de turism Brașov | „Vezi Brașovul live" |
| Presă locală — `bizbrasov.ro`, `mytex.ro`, `monitorulexpres.ro` | Comunicat, **Anexa C** |
| Site-uri meteo | Condiții live |
| Wikipedia — Brașov / Poiana Brașov | Secțiunea linkuri externe (reguli stricte, șanse moderate) |

### Acțiuni

- [ ] Submit Windy
- [ ] Submit agregatoare (minim 5)
- [ ] Trimite comunicat către 3 publicații locale
- [ ] Postare pe canalele proprii + grupuri Facebook locale

---

## FAZA 4 — Operare și scalare

**Continuu**

- [ ] **Monitorizare uptime** — o cameră care cade des e scoasă din Windy și din agregatoare, iar linkurile se pierd. Alertă automată la cădere.
- [ ] Verificare lunară: câte domenii noi linkuiesc pagina camerei (Search Console → Linkuri)
- [ ] **Camera #2 la luna 6**, dacă prima a generat minim 4 domenii noi
- [ ] Camera #3 la luna 12, cu aceeași condiție

---

## Buget

### Per locație

| Element | Cost piață | Cost real CSSI |
|---|---|---|
| Cameră IP exterior fixă | 1.200–2.500 RON | 800–1.700 RON |
| Carcasă + încălzire | 300–600 RON | 200–400 RON |
| Router 4G/5G + antenă *(dacă nu folosim internetul gazdei)* | 400–800 RON | 300–600 RON |
| Encoder / mini-PC | 600–1.200 RON | 500–900 RON |
| Montaj | manoperă internă | 4–6 ore |
| **Total cash** | **2.500–5.100 RON** | **~1.800–3.600 RON** |
| Recurent | 30–60 RON/lună date | idem |

### Efort intern

| | Ore |
|---|---|
| Prima cameră (inclusiv învățare) | 20–25 h |
| Fiecare cameră următoare | 8–10 h |
| Operare lunară | 1–2 h |

Se încadrează în bugetul de 10+ h/lună asumat pentru SEO.

---

## Calendar

| Lună | Fază | Livrabil |
|---|---|---|
| **1** | Faza 0 + 1 | Aviz juridic · locație confirmată · acord gazdă |
| **2** | Faza 2 | Cameră live · pagină publicată · uptime testat |
| **3** | Faza 3 | Listare Windy + agregatoare · comunicat presă |
| **4–6** | Faza 4 | Linkuri se acumulează · **decizie camera #2** |
| **7–12** | Faza 4 | Scalare · camera #2 operațională |

---

## KPI

| Indicator | Țintă 6 luni | Țintă 12 luni |
|---|---|---|
| Uptime stream | > 98% | > 98% |
| Domenii noi către pagina camerei | 4–6 | **8–15** |
| Camere operaționale | 1 | 2 |
| Contribuție la total domenii site | 6 → 12 | 6 → **20+** |

**Cum se măsoară:** Search Console → Linkuri → *Cele mai populare pagini către care se face trimitere* → filtrează pagina camerei. Verificare lunară.

---

## Riscuri

| Risc | Impact | Mitigare |
|---|---|---|
| **Incident GDPR** | **Catastrofal** — reputațional, la o firmă de securitate | Faza 0 blocantă · cameră fixă · fără înregistrare · notă de informare |
| Windy respinge camera | Mediu — se pierde cel mai valoros link | Verifică cerința de cer vizibil **înainte** de montaj |
| Cameră offline frecvent | Mediu — delistare, linkuri pierdute | Monitorizare + alertă; alimentare cu backup |
| Gazda retrage acordul | Mediu — se pierde locația și linkurile | Contract pe minim 3 ani |
| Nu vin linkurile | Mic — costul e recuperabil | Camera rămâne demonstrație de produs, deci nu e pierdere totală |
| Timp mai lung decât estimat | Mic | Nu e proiect critic; nu blochează altceva |

---

# ANEXE

## Anexa A — Pitch către gazdă

> **Subiect: Cameră de supraveghere gratuită pentru [LOCAȚIE]**
>
> Bună ziua [NUME],
>
> Vă contactez cu o propunere din care câștigăm amândoi.
>
> CSSI dorește să instaleze o cameră de supraveghere care să transmită live panorama de la [LOCAȚIE]. Camera ar fi vizibilă public pe site-ul nostru și pe platforme internaționale de webcam-uri.
>
> **Ce primiți:**
> - Cameră profesională montată gratuit, rămâne a dumneavoastră
> - Supraveghere reală a propriei locații, cu acces din telefon
> - Mentenanță gratuită pe toată durata contractului
> - Expunere: locația apare pe platforme cu trafic internațional
>
> **Ce ne oferiți:**
> - Permisiunea de montaj pe clădire
> - Curent electric și conexiune internet (consum neglijabil)
>
> Camera este fixă, cu unghi larg, **nu înregistrează nimic** și nu permite apropierea pe persoane — este orientată pe peisaj, nu pe oameni. Toată documentația de conformitate o pregătim noi.
>
> Putem discuta 15 minute săptămâna asta?
>
> [SEMNĂTURA]

## Anexa B — Notă de informare GDPR `DRAFT — necesită validare juridică`

> **Informare privind camera live**
>
> Această pagină afișează în timp real imagini de la [LOCAȚIE].
>
> **Operator:** CSSI — [denumire completă], [adresă], [contact DPO]
>
> **Scopul prelucrării:** informare publică și prezentarea serviciilor CSSI.
>
> **Caracteristici tehnice relevante:** camera este **fixă**, cu unghi larg și montaj înalt. **Nu permite apropierea (zoom) pe persoane** și **nu înregistrează și nu stochează** imaginile. Fluxul este exclusiv live. Datorită distanței și unghiului, persoanele aflate în câmpul vizual **nu sunt identificabile**.
>
> **Temei juridic:** [DE COMPLETAT DE JURIST]
>
> **Drepturile dumneavoastră:** [DE COMPLETAT DE JURIST]
>
> Pentru orice solicitare: [contact].

⚠️ Acest text este un **schelet**, nu un document juridic. Trebuie completat și validat în Faza 0.

## Anexa C — Comunicat de presă locală `DRAFT`

> **Brașovul poate fi văzut live de oriunde din lume**
>
> Firma brașoveană CSSI a instalat o cameră de supraveghere publică ce transmite non-stop panorama de la [LOCAȚIE]. Imaginile pot fi urmărite gratuit pe [URL] și pe platforma internațională Windy.com, folosită de milioane de utilizatori pentru condiții meteo live.
>
> „[CITAT MIHAI — de ce ați făcut-o, ce înseamnă pentru brașoveni și pentru turiști]"
>
> Camera este fixă, cu unghi larg, și nu înregistrează imaginile — transmite exclusiv live. Este orientată către peisaj, iar persoanele aflate în câmpul vizual nu sunt identificabile.
>
> CSSI activează de peste 20 de ani în Brașov, cu peste 8.700 de proiecte de sisteme de securitate, supraveghere video și automatizări.
>
> **Contact presă:** [nume, telefon, email]

## Anexa D — Checklist submisie Windy

- [ ] Camera este de **exterior**
- [ ] **Cer vizibil** suficient în cadru
- [ ] Flux live funcțional, fără întreruperi în ultimele 7 zile
- [ ] Pagina publică pe cssi.ro este **live și indexabilă**
- [ ] Coordonate GPS exacte pregătite
- [ ] Denumire descriptivă: „Brașov — [locație]"
- [ ] Camera este operată de CSSI (cerință Windy)
- [ ] Formular trimis
- [ ] *Așteptare 20 min – 4 săptămâni*
- [ ] Confirmare aprobare + verificare link către cssi.ro

---

## Surse

- [How to add your webcam to Windy](https://www.windy.com/articles/10572)
- [Windy Community — Submit or Add Webcams](https://community.windy.com/topic/20362/submit-or-add-webcams)
- [Windy Community — actualizări formular submisie](https://community.windy.com/topic/37509/windy-webcams-submission-form-updates-and-how-long-will-it-take-to-be-checked)

---

*Plan generat 21.07.2026 · de revizuit la finalul Fazei 0 și la luna 6*
