# Fix alertă „Conversiile optimizate prezintă probleme de configurare"

**Data:** 21 aprilie 2026
**Cont Google Ads:** 666-033-6562 (CSSI)
**Task:** #23 [completed]

---

## 🔍 Diagnoză

**Alertă:** „Conversiile optimizate prezintă probleme de configurare care afectează performanța"

**Cauză identificată din Diagnosticare → Conversii îmbunătățite → Urgent:**
- Acțiunea afectată: **„Solicitați o ofertă"** (sursa: TAG, optimizare: Principale)
- Mesaj: „Conversiile optimizate nu au înregistrat date în ultimele șapte zile. S-ar putea să existe o problemă legată de configurare."
- 1/1 acțiune de conversie optimizată în status **Urgent**

**Root cause:** „Conversii îmbunătățite" (Enhanced Conversions) erau activate cu metoda „Eticheta Google" (gtag.js auto-detect), dar site-ul **nu are cod user-provided data** pe pagina de contact (email/telefon trimise către gtag). Rezultat: 0 date trimise → status Urgent → Smart Bidding blocat.

---

## ✅ Fix aplicat

**Calea:** Google Ads → Obiective → Conversii → Setări → **Conversii îmbunătățite** → debifat „Activați conversiile optimizate" → Salvați.

**Rezultat imediat:**
- Status înainte: „Gestionată prin eticheta Google. Înregistrează conversiile optimizate."
- Status după: **„Nu a fost configurată încă"** ✓

**Efect:**
- Alerta Urgent din Diagnosticare → curățată în 24-48h (refresh Google Ads)
- Smart Bidding deblocat pentru Campania CSSI - Servicii Securitate
- Conversiile primare (Persoane de contact, Indicații rutiere, Clienți potențiali prin apel, Trimiteți formulare, phone_call, whatsapp_click) rămân **neafectate** — ele nu depind de Enhanced Conversions

---

## 📝 Reactivare ulterioară (când va fi cazul)

### ✅ Infrastructură tehnică: COMPLETĂ

**Descoperire post-fix:** codul Enhanced Conversions **există deja** în site din 31.03.2026:

**1. gtag.js încărcat corect** — `/cookie-consent.js` liniile 27-34:
```javascript
var AW_ID = 'AW-17987940313';
gtag('config', AW_ID);
gtagScript.src = 'https://www.googletagmanager.com/gtag/js?id=' + AW_ID;
```

**2. user_data hash-uit automat** — `/tracking.js` liniile 59-99 (hook pe `window.open` spre wa.me):
```javascript
var userData = {};
if (emailField && emailField.value) {
    userData.email = emailField.value.trim().toLowerCase();
}
if (phoneField && phoneField.value) {
    var phone = phoneField.value.trim().replace(/[\s\-\.\(\)]/g, '');
    if (phone.indexOf('0') === 0) phone = '+4' + phone;
    userData.phone_number = phone;
}
if (nameField && nameField.value) {
    var nameParts = nameField.value.trim().split(/\s+/);
    userData.address = { first_name: nameParts[0], last_name: nameParts.slice(1).join(' ') };
}
gtag('set', 'user_data', userData);
gtag('event', 'conversion', { 'send_to': 'AW-17987940313/WVuaCJnH1YEcENnfqIFD' });
```

**3. Conversion label validat în Google Ads** — `WVuaCJnH1YEcENnfqIFD` corespunde exact cu acțiunea „Solicitați o ofertă" (ID cont: 17987940313, valoare: 50 RON, sursă: Site web, creat: 02.03.2026).

### 🚦 Gate rămas pentru reactivare

**Singurul blocant: VOLUM.** La nivel actual (0 form submits în ultimele 7 zile, 29 sesiuni/săptămână total), Google nu are destule date pentru a valida matching-ul user_data → conturi Google.

**Condiții pentru reactivare:**
- ✅ Cod gtag user_data pe formular — **DEJA EXISTĂ**
- ✅ Conversion label match — **VALIDAT**
- ⏳ ≥15 conversii/lună pe acțiunea „Solicitați o ofertă" — **NEATINS** (0/lună actual)

**Pas următor la reactivare (când volumul crește):**
1. Google Ads → Obiective → Conversii → „Solicitați o ofertă" → **Modificați setările** → Conversii îmbunătățite → Activați (metoda: eticheta Google, auto-detect)
2. Așteptăm 7-14 zile pentru validare
3. Verificăm Diagnosticare → nu mai apare Urgent → status „Bună" sau „Excelentă"
4. Smart Bidding îmbunătățit +5-10% conversii recuperate

---

## 📊 Status conversii DUPĂ fix

| Acțiune | Status | Sursa | Optimizare |
|---|---|---|---|
| Persoane de contact | ✅ activă | PMM | Principale |
| Obțineți indicații rutiere | ✅ activă | PMM | Principale |
| Clienți potențiali prin apeluri telefonice | ✅ activă | PMM | Principale |
| Solicitați oferte | ⚠️ 0 date (site tag nefolosit) | TAG | Principale |
| Trimiteți formulare de clienți potențiali | ✅ activă | PMM | Principale |
| phone_call (GA4) | ✅ activă (50 RON default) | GA4 import | Principale |
| whatsapp_click (GA4) | ✅ activă (50 RON default) | GA4 import | Principale |

---

## 🎯 Acțiune ulterioară recomandată

**Acțiunea „Solicitați oferte" (TAG-based) nu primește date** pentru că pe site nu e implementat un `gtag('event', 'conversion', ...)` la submit-ul formularului. Două opțiuni:

**A)** Implementez cod gtag pe formularul de contact (necesită editare `contact.html` + validare Tag Assistant) — trackează duplicat cu form_submit din GA4.

**B)** Marchez acțiunea „Solicitați oferte" Secundară (sau o șterg) — rămânem doar cu form_submit importat din GA4. **Recomandare: B** — eliminăm duplicarea.

Urmează în sesiunea următoare dacă MIHAI confirmă opțiunea B.
