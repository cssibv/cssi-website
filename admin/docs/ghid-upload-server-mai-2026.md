# Ghid Upload Fișiere pe Server — Mai 2026

## Cum se face upload-ul:
1. Deschide **cPanel** → **File Manager**
2. Navighează la rădăcina site-ului (`public_html` sau `/`)
3. Upload fișierele de mai jos, **suprascriind** cele existente

---

## PRIORITATE 1 — Fișiere critice (fă-le primele!)

### Scripturi JS (rădăcina site-ului):
- **`tawk-chat.js`** ← IMPORTANT: editează linia 29 cu Property ID-ul Tawk.to real ÎNAINTE de upload
- **`tracking.js`** ← valori monetare GA4 + Enhanced Conversions
- **`cookie-consent.js`** ← allow_enhanced_conversions=true

### Config:
- **`robots.txt`** ← blocat /admin/ și /contract.html
- **`sitemap.xml`** ← toate 119 URL-uri actualizate

---

## PRIORITATE 2 — Pagini principale (28 fișiere)

Upload în rădăcina site-ului. Acestea au primit:
- tawk-chat.js pe toate paginile
- Fix HTML trunchiat (23 pagini)
- Schema FAQPage restaurată (5 pagini)
- Internal linking (secțiuni "Zone Deservite")
- Fix link-uri broken
- Lazy loading imagini

**Fișiere:**
- `index.html`
- `servicii.html` ← **RECONSTRUIT COMPLET**
- `contact.html`
- `despre-noi.html`
- `pentru-firme.html`
- `portofoliu.html`
- `calculator-pret.html`
- `politica-cookies.html` ← **RECONSTRUIT**
- `camere-supraveghere.html`
- `camere-supraveghere-brasov.html` ← **RECONSTRUIT COMPLET**
- `alarma-antiefractie.html`
- `control-acces.html`
- `automatizari-porti.html`
- `bariere-auto.html`
- `interfoane-videointerfoane.html`
- `instalatii-electrice.html`
- `instalatii-termice-sanitare.html`
- `aer-conditionat.html`
- `ventilatie.html`
- `sonorizare.html`
- `pontaj-electronic.html`
- `usi-garaj.html`
- `detectie-incendiu-isu.html`
- `sistem-integrat-securitate-brasov.html`
- `contract.html`
- `blog_index.html`
- `404.html`
- `offline.html`

---

## PRIORITATE 3 — Landing pages locale (60 fișiere)

Upload în rădăcina site-ului. Au primit: tawk-chat.js + backlink "Vezi toate locațiile" + fix HTML.

**Fișiere:** (toate fișierele `serviciu-localitate.html`)
- camere-supraveghere-{bran,codlea,ghimbav,harman,predeal,rasnov,sanpetru,ploiesti,sibiu,targu-mures}.html
- alarma-antiefractie-{bran,brasov,codlea,ghimbav,harman,predeal,rasnov,sanpetru,ploiesti,sibiu}.html
- automatizari-porti-{bran,brasov,codlea,ghimbav,harman,predeal,rasnov,sanpetru}.html
- instalatii-electrice-{bran,brasov,codlea,ghimbav,harman,predeal,rasnov,sanpetru}.html
- aer-conditionat-{bran,brasov,codlea,ghimbav,harman,predeal,rasnov,sanpetru}.html
- usi-garaj-{bran,brasov,codlea,ghimbav,harman,predeal,rasnov,sanpetru}.html
- bariere-auto-{brasov,codlea}.html
- control-acces-{brasov,codlea}.html
- management-parcari-{brasov,codlea}.html
- pontaj-electronic-brasov.html

---

## PRIORITATE 4 — Blog articles (32 fișiere)

Upload în folderul `/blog/`. Au primit: tawk-chat.js + fix link-uri.

Tot conținutul din folderul `blog/` (31 articole + index.html).

---

## PRIORITATE 5 — Admin docs (ghiduri pentru tine)

Upload în `/admin/docs/`. Acestea sunt ghiduri de referință:
- `ghid-integrare-tawk-to.md` ← Pașii pentru activare Tawk.to
- `ghid-import-google-ads-editor.md` ← Import campanii noi Ads
- `ghid-listare-directoare.md` ← **NOU** — Pași concreți pentru 10 directoare online
- `ghid-upload-server-mai-2026.md` ← Acest ghid
- `template-outreach-backlinks.md` ← **NOU** — Template-uri email pentru link building
- `plan-actiuni-mihai-saptamana-1.md` ← Plan acțiuni săptămâna 1
- `plan-activare-recenzii-google.md` ← Proces SMS cerere recenzii
- `google-ads-campanii.csv` ← 3 campanii noi
- `google-ads-keywords.csv` ← 22 keywords
- `google-ads-negative-keywords.csv` ← 90 negative keywords
- `google-ads-anunturi-rsa.csv` ← 6 anunțuri RSA
- `google-ads-expansion-campanii.csv` ← **NOU** — 3 campanii expansion (Alarme, Electrice, AC)
- `google-ads-expansion-keywords.csv` ← **NOU** — 27 keywords expansion
- `google-ads-expansion-negative-keywords.csv` ← **NOU** — 57 negative keywords expansion
- `google-ads-expansion-anunturi-rsa.csv` ← **NOU** — 6 anunțuri RSA expansion

---

## PRIORITATE 2b — Pagină NOUĂ (rădăcina site-ului)

- **`zone-deservite.html`** ← **PAGINĂ NOUĂ** — Hub SEO local cu toate cele 11 localități deservite

---

## Mod rapid: Upload tot dintr-o dată

### Opțiunea A — cPanel File Manager:
1. Selectează toate fișierele din rădăcină → Upload → suprascrie
2. Navighează la `/blog/` → Upload toate fișierele blog
3. Navighează la `/admin/docs/` → Upload documentele

### Opțiunea B — FTP (FileZilla):
1. Conectează cu datele FTP din cPanel
2. Drag & drop tot folderul local peste cel remote
3. La conflicte, alege "Suprascrie"

---

## După upload — verificare:
1. Deschide https://cssi.ro → verifică homepage
2. Deschide https://cssi.ro/servicii → verifică pagina servicii
3. Deschide https://cssi.ro/contact → verifică chat (dacă Tawk.to e configurat)
4. Deschide https://cssi.ro/camere-supraveghere-brasov → verifică pagina refăcută
5. Deschide view-source pe orice pagină → caută `tawk-chat.js` înainte de `</body>`
