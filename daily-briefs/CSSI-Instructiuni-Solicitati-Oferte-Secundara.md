# Instrucțiuni: marchez „Solicitați oferte" (TAG) ca Secundară

**Context:** Elimin duplicarea cu `form_submit` importat din GA4. După modificare, rămâne o singură sursă de adevăr (`form_submit` din GA4) în optimizarea Smart Bidding — acțiunea TAG devine doar "observator", nu mai influențează algoritmul.

**Durată estimată:** 30 secunde, 3 click-uri.

---

## Pașii exacți

### 1. Deschide pagina Conversii

Google Ads → **Obiective** (stânga) → **Conversii** → **Rezumat**
Sau direct: `https://ads.google.com/aw/conversions`

### 2. Scrollează până la cardurile de obiective

Sub grafice vei vedea o listă de carduri: „Trimiteți un formular de client potențial", „Client potențial prin apel telefonic", „Persoană de contact", **„Solicitați o ofertă"** (cu status ⚠️ „Necesită atenție"), „Obțineți indicații rutiere", „Interacțiune".

### 3. Click pe „Modificați obiectivul" din cardul „Solicitați o ofertă"

Se deschide panoul lateral „Editați setările pentru Solicitați oferte" cu 3 secțiuni:
- Obiectiv standard pentru cont
- Optimizarea obiectivelor specifice campaniei
- **Optimizarea acțiunilor de conversie** ← asta te interesează

### 4. Expandează „Optimizarea acțiunilor de conversie"

Click pe săgeata ⌄ din dreapta, lângă textul „1 acțiune de conversie principală".

### 5. Schimbă acțiunea din „Principală" în „Secundară"

Va apărea un tabel cu o acțiune: **Solicitați oferte** (TAG). În dreapta are un toggle/select cu valoarea „Principală". Schimbă în **„Secundară"**.

### 6. Salvează

Click pe butonul albastru **„Salvați"** din jos.

---

## Ce se schimbă

**ÎNAINTE:**
- Acțiunea „Solicitați oferte" (TAG-based, 0 date) = Principală → influențează Smart Bidding cu 0 date = status ⚠️ „Necesită atenție"
- `form_submit` (din GA4, date reale) = Principală → influențează Smart Bidding

**DUPĂ:**
- Acțiunea „Solicitați oferte" (TAG-based, 0 date) = **Secundară** → rămâne vizibilă în rapoarte, dar **NU mai influențează Smart Bidding**
- `form_submit` (din GA4, date reale) = Principală → singura sursă pentru Smart Bidding ✅

**Efect imediat:** dispare warning-ul „Necesită atenție" din cardul obiectivului în 24-48h.

---

## Cum verifici că a mers

După 24-48h:
- Card-ul „Solicitați o ofertă" → status verde **„Activ"** (nu mai „Necesită atenție")
- Diagnosticare → fără alerte Urgent pe acțiunea asta

## Alternativ (mai rapid dar puțin mai agresiv)

Dacă vrei să scapi complet de acțiunea TAG (recomand doar dacă ești sigur că nu vei implementa niciodată un `gtag('event', 'conversion', ...)` la submit pe formular):

**Ștergere:**
Rezumat → click pe „Solicitați o ofertă" → deschide detalii acțiune → **Eliminați**.

**NU recomand ștergerea** pentru că:
1. Secundara e reversibilă (revii la Principală când ai date)
2. Ștergerea îți pierde istoricul (acum e 0, dar dacă cândva vei implementa gtag pe form, reîncepi de la zero)
3. Secundara păstrează acțiunea în rapoarte ca observator

---

## Rezultat așteptat în doc master

După ce faci modificarea, status-ul real va fi:

| Acțiune | Status | Sursa | Optimizare |
|---|---|---|---|
| Persoane de contact | ✅ activă | PMM | Principală |
| Obțineți indicații rutiere | ✅ activă | PMM | Principală |
| Clienți potențiali prin apeluri telefonice | ✅ activă | PMM | Principală |
| **Solicitați oferte** | ⚠️ 0 date (site tag nefolosit) | TAG | **Secundară** ← modificat azi |
| Trimiteți formulare de clienți potențiali | ✅ activă | PMM | Principală |
| phone_call (GA4) | ✅ activă (50 RON default) | GA4 import | Principală |
| whatsapp_click (GA4) | ✅ activă (50 RON default) | GA4 import | Principală |
