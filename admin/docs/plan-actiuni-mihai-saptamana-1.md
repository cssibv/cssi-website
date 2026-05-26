# Plan Acțiuni Mihai — Săptămâna 26 Mai - 1 Iunie 2026

## Total timp estimat: ~4-5 ore (distribuit pe mai multe zile)

---

## LUNI 26 Mai — Upload fișiere (1.5h)

### 1. Upload pe server via cPanel (1h)
**Cele mai importante fișiere (fă-le primele):**
- `tawk-chat.js` → rădăcina site-ului (NU modifica Property ID încă)
- `tracking.js` → rădăcina
- `cookie-consent.js` → rădăcina
- `robots.txt` → rădăcina
- `sitemap.xml` → rădăcina
- `servicii.html` → rădăcina (PAGINA RECONSTRUITĂ)
- `camere-supraveghere-brasov.html` → rădăcina (PAGINA RECONSTRUITĂ)
- `politica-cookies.html` → rădăcina (PAGINA RECONSTRUITĂ)
- `contact.html` → rădăcina
- `index.html` → rădăcina

Apoi **restul fișierelor HTML** din rădăcina site-ului (au primit tawk-chat.js, fix HTML, link-uri interne).

Apoi folderul `blog/` complet (32 fișiere — au primit tawk-chat.js).

Apoi `admin/docs/` (ghiduri și CSV-uri).

### 2. Verificare rapidă post-upload (15 min)
- [ ] https://cssi.ro → homepage se încarcă corect?
- [ ] https://cssi.ro/servicii → pagina servicii afișează 15 servicii?
- [ ] https://cssi.ro/camere-supraveghere-brasov → pagina e completă?
- [ ] https://cssi.ro/contact → formularul funcționează?
- [ ] View source pe orice pagină → `tawk-chat.js` apare înainte de `</body>`?
- [ ] https://cssi.ro/robots.txt → arată `/admin/` în Disallow?

### 3. Resubmit sitemap în GSC (15 min)
- Intră în Google Search Console
- Mergi la Sitemaps → Adaugă `https://cssi.ro/sitemap.xml`
- Apasă Trimite

---

## MARȚI 27 Mai — Tawk.to Chat Live (30 min)

### 1. Creează cont Tawk.to (10 min)
- Accesează https://www.tawk.to/ → Sign Up Free
- Email: cssirobv@gmail.com
- Confirmă emailul

### 2. Configurează (10 min)
- Site Name: CSSI Brașov
- Site URL: https://cssi.ro
- Culoare widget: `#DC2626` (roșu CSSI)
- Poziție: Bottom Right
- Titlu: "CSSI Securitate"

### 3. Activează pe site (10 min)
- Copiază Property ID-ul (din Administration → Chat Widget)
- Intră în cPanel → File Manager → deschide `tawk-chat.js`
- Linia 29: înlocuiește `XXXXXXXXXXXXXXXXXXXXXXXXXX` cu ID-ul real
- Salvează

**Gata! Chat-ul e activ pe TOATE cele 119+ pagini.**

### 4. Instalează app mobilă
- Descarcă Tawk.to din App Store/Play Store
- Autentifică-te → activează notificări

---

## MIERCURI 28 Mai — Google Ads (45 min)

### 1. Descarcă Google Ads Editor (5 min)
- https://ads.google.com/intl/ro_ro/home/tools/ads-editor/
- Instalează, conectează contul cssirobv@gmail.com

### 2. Import campanii noi (15 min)
- Cont → Importă → CSV → `google-ads-campanii.csv`
- Cont → Importă → CSV → `google-ads-keywords.csv`
- Cont → Importă → CSV → `google-ads-negative-keywords.csv`
- Cont → Importă → CSV → `google-ads-anunturi-rsa.csv`

### 3. Verifică și publică (10 min)
- Verifică: 3 campanii, 6 grupuri, 22 keywords, 6 anunțuri
- Click Publică
- **NU activa campaniile încă** — așteaptă aprobarea Google (24-48h)

### 4. Configurează shortcuts Tawk.to (15 min)
- Din ghidul `admin/docs/ghid-integrare-tawk-to.md`, secțiunea Pas 7
- Adaugă cele 8 shortcut-uri: /salut, /camere, /control, /alarma, /porti, /oferta, /program, /zona

---

## JOI 29 Mai — Google Business Profile (1h)

### 1. Revendicare GBP (dacă nu e deja)
- Caută "CSSI Brașov" pe Google Maps
- Revendică profilul (buton "Revendică această afacere")
- Verificare poate dura 3-7 zile (poștă sau telefon)

### 2. Optimizare profil
- **Categorie principală**: Companie de securitate
- **Categorii secundare**: Sisteme de supraveghere, Control acces, Instalator electric
- **Descriere**: "CSSI — securitate, automatizare și instalații în Brașov. Camere supraveghere, control acces, automatizări porți, alarme, instalații electrice. Autorizați ANRE, ISU, IGPR. 20+ ani, 8.700+ proiecte."
- **Zonă deservită**: Brașov, Codlea, Râșnov, Predeal, Ghimbav, Sânpetru, Hărman, Bran
- **Program**: L-V 08:00-17:00, S 09:00-13:00

### 3. Upload fotografii
- Minim 5 fotografii (sediu, echipă, proiecte finalizate, mașini firmă)
- Ideal: 20 fotografii diverse

---

## VINERI 30 Mai — Google Ads activare + Prima recenzie (30 min)

### 1. Activare campanii Ads
- Dacă Google a aprobat anunțurile → activează campaniile una câte una
- Păstrează campania veche activă încă 2-3 zile (suprapunere)
- Verifică prima zi: afișări, clickuri, cost

### 2. Trimite primele SMS-uri cerere recenzie
- Alege 3-5 clienți recenți mulțumiți
- Trimite mesajul din planul de recenzii (`admin/docs/plan-activare-recenzii-google.md`)
- Obiectiv: 2-3 recenzii noi pe săptămână

---

## Bonus (dacă ai timp):

- [ ] Creează pagina LinkedIn CSSI (ghid în `admin/docs/linkedin-optimizare-cssi.md`)
- [ ] Înregistrare pe primele 5 directoare (ghid detaliat: `admin/docs/ghid-listare-directoare.md`)
- [ ] Configurează Meta Pixel ID real în `cookie-consent.js` (linia cu `XXXXXXXXXXXXXXXXX`)
- [ ] Trimite primele 2-3 email-uri outreach (template-uri: `admin/docs/template-outreach-backlinks.md`)

---

## Ghiduri disponibile în admin/docs:

| Ghid | Ce conține |
|------|-----------|
| `ghid-upload-server-mai-2026.md` | Checklist upload complet pe server |
| `ghid-integrare-tawk-to.md` | Activare chat Tawk.to |
| `ghid-import-google-ads-editor.md` | Import campanii noi Google Ads |
| `ghid-listare-directoare.md` | **NOU** — 10 directoare pas cu pas |
| `template-outreach-backlinks.md` | **NOU** — Email-uri pentru link building |
| `plan-activare-recenzii-google.md` | Proces cerere recenzii |
| `linkedin-optimizare-cssi.md` | Optimizare pagină LinkedIn |
| `linkedin-calendar-postari.md` | Calendar 16 postări LinkedIn |
