# Ghid Integrare Tawk.to — Chat Live CSSI.ro

## De ce Tawk.to?
- **Gratuit** — chat nelimitat, agenți nelimitați
- **Aplicație mobilă** — primești notificări instant pe telefon
- **Mesaje offline** — vizitatorii lasă mesaj când nu ești disponibil
- **Răspunsuri rapide** — configurezi scurtături pre-definite
- **Tracking GA4** — scriptul nostru trimite automat evenimente la Analytics

---

## Pas 1: Creează cont Tawk.to (5 minute)

1. Accesează **https://www.tawk.to/** → click **Sign Up Free**
2. Completează:
   - Email: cssirobv@gmail.com
   - Parolă: (alege una sigură)
   - Nume: Mihai / CSSI
3. Confirmă email-ul primit

## Pas 2: Configurează proprietatea (10 minute)

1. La **Add Property**, completează:
   - **Site Name**: CSSI Brașov
   - **Site URL**: https://cssi.ro
2. **Widget Appearance** (Aspect widget):
   - Culoare: `#DC2626` (roșu CSSI)
   - Poziție: Bottom Right
   - Titlu: "CSSI Securitate"
   - Subtitlu: "Cum vă putem ajuta?"
   - Mesaj bun-venit: "Bună! Suntem aici să vă ajutăm cu orice întrebare despre securitate."
3. **Operating Hours** (Program):
   - Luni-Vineri: 08:00 — 18:00 (ora României)
   - Sâmbătă: 09:00 — 13:00
   - Duminică: Închis
   - Mesaj offline: "Nu suntem disponibili acum. Lăsați un mesaj și vă contactăm în maxim 24h."

## Pas 3: Copiază Property ID

1. Mergi la **Administration** → **Channels** → **Chat Widget**
2. Vei vedea un cod ca acesta:
   ```
   https://embed.tawk.to/6837a1b2f0e4a814c5b3d2e1/default
   ```
3. Copiază **doar partea din mijloc** (Property ID):
   ```
   6837a1b2f0e4a814c5b3d2e1
   ```

## Pas 4: Actualizează tawk-chat.js (2 minute)

1. Intră în cPanel → **File Manager** → deschide `tawk-chat.js` din rădăcina site-ului
2. Găsește linia:
   ```javascript
   var TAWK_PROPERTY_ID = 'XXXXXXXXXXXXXXXXXXXXXXXXXX';
   ```
3. Înlocuiește cu ID-ul tău real:
   ```javascript
   var TAWK_PROPERTY_ID = '6837a1b2f0e4a814c5b3d2e1';
   ```
   (folosește ID-ul TĂU, nu exemplul de mai sus)
4. Salvează fișierul

## Pas 5: Adaugă script-ul pe pagini (15-20 minute)

Adaugă această linie **înainte de `</body>`** pe fiecare pagină HTML:

```html
<script src="/tawk-chat.js" defer></script>
```

### Mod rapid cu cPanel File Manager:
1. Deschide File Manager → selectează fiecare `.html`
2. Caută `</body>` (Ctrl+F)
3. Adaugă linia de mai sus fix deasupra
4. Salvează

### Paginile principale (fă-le primele):
- index.html
- servicii.html
- contact.html
- camere-supraveghere.html
- control-acces.html
- alarma-antiefractie.html
- automatizari-porti.html
- pontaj-electronic.html
- calculator-pret.html
- pentru-firme.html
- despre-noi.html

Apoi restul paginilor de servicii și landing pages locale.

## Pas 6: Instalează aplicația mobilă

1. Descarcă **Tawk.to** din App Store / Google Play
2. Autentifică-te cu contul creat
3. Activează notificările push
4. Acum primești alertă pe telefon de fiecare dată când un vizitator scrie

## Pas 7: Configurează răspunsuri rapide (Shortcuts)

În Tawk.to Dashboard → **Administration** → **Shortcuts**, adaugă:

| Scurtătură | Mesaj |
|-----------|-------|
| /salut | Bună ziua! Cu ce vă putem ajuta? Suntem specialiști în sisteme de securitate cu peste 20 de ani de experiență. |
| /camere | Oferim sisteme complete de supraveghere video (HikVision, Dahua) cu montaj profesional. Prețurile pornesc de la 2.500 RON pentru un sistem de 4 camere. Doriți o ofertă personalizată? |
| /control | Sisteme control acces biometric, card RFID, și pontaj electronic pentru firme. Include montaj, configurare și garanție 3 ani. Vă pot trimite o ofertă? |
| /alarma | Sisteme de alarmă antiefracție cu monitorizare 24/7. Include senzori, sirenă, tastatură și telecomandă. Montaj gratuit la achiziție. |
| /porti | Automatizări pentru porți culisante și batante — motoare Nice, BFT, Came. Montaj în 1-2 zile lucrătoare. |
| /oferta | Pentru o ofertă personalizată, avem nevoie de: 1) Ce sistem vă interesează 2) Dimensiunea spațiului 3) Locația (oraș). Putem programa și o vizită gratuită de evaluare! |
| /program | Suntem disponibili Luni-Vineri 08:00-18:00, Sâmbătă 09:00-13:00. Ne puteți contacta și la 0722.214.521 sau pe WhatsApp. |
| /zona | Deservim Brașov și împrejurimi: Codlea, Râșnov, Predeal, Ghimbav, Sânpetru, Hărman, Bran. De asemenea, lucrăm și în Sibiu și Ploiești. |

## Ce face scriptul tawk-chat.js automat:

1. **Ascunde chat-ul vechi** — widget-ul custom verde dispare
2. **Respectă cookie consent** — nu se încarcă dacă utilizatorul a refuzat
3. **Tracking GA4** — trimite event la Google Analytics când:
   - Vizitator începe conversație (event: `tawk_chat_started`, valoare: 100 RON)
   - Vizitator trimite mesaj offline (event: `tawk_offline_message`, valoare: 200 RON)
4. **Conversie Google Ads** — mesajele offline declanșează și conversie Ads

---

## Estimare impact:
- **+3-5 lead-uri/săptămână** din chat-ul live (pe baza traficului actual de ~25 users/zi)
- **Rata de conversie** pe chat e de obicei 2-5x mai mare decât pe formular
- **Cost**: 0 RON (Tawk.to e complet gratuit)
