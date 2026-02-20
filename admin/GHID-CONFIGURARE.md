# 📋 GHID CONFIGURARE — Portal CSSI v2.0
## Pașii pe care îi faci TU (30 minute)

---

## ✅ PAS 1: Share Google Sheets (5 minute)

Deschide pe rând fiecare Sheet și fă-l public:

### Sheet 1: CRM Clienți
1. Deschide: https://docs.google.com/spreadsheets/d/1DQlmxJWMQh9NpwzKNwti2YN47u2vKIRG2n9Q1h2rVHk/edit
2. Click **Share** (butonul verde, dreapta sus)
3. Sub "General access" → schimbă în **"Anyone with the link"**
4. Rol: **Viewer** (sau Editor dacă vrei ca echipa să editeze direct)
5. Click **Done**

### Sheet 2: Calendar Social
→ https://docs.google.com/spreadsheets/d/15gVARAiR_MRFJsxPJ-d4QfY4lRlsafTBaCIlw8AzPL8/edit
→ Repetă pașii 2-5

### Sheet 3: Checklist Montaj (Istoric)
→ https://docs.google.com/spreadsheets/d/1JoxHEsk3FZ1I_sUKbWC0NoipS8RziWKZeNmRiLCc-0g/edit
→ Repetă pașii 2-5

### Sheet 4: Necesar Materiale
→ https://docs.google.com/spreadsheets/d/11QVsJJxocDYZ_nAMbPKqU_ZNZbUM4gdAGgX9iVbDUpI/edit
→ Repetă pașii 2-5

### Sheet 5: Calculator Preț
→ https://docs.google.com/spreadsheets/d/18Wkkvdg7hpYHCkufvCNkxbGWv0BS6epzNelD2IWGWfw/edit
→ Repetă pașii 2-5

---

## ✅ PAS 2: Fă Formularele publice (3 minute)

### Form Checklist Montaj:
1. Deschide formularul în modul editare (din Google Drive)
2. Click ⚙️ **Settings** (roată dințată)
3. Debifează: "Restrict to users in [organizație] and its trusted organizations"
4. Debifează: "Limit to 1 response" (dacă e bifat)
5. **Save**

### Form Necesar Materiale:
→ Repetă pașii 1-5 pentru acest formular

### Form Calendar Social:
→ Verifică să fie deja public (a funcționat la audit)

---

## ✅ PAS 3: Creează Folder Drive pentru Resurse Media (3 minute)

1. Deschide Google Drive: https://drive.google.com
2. Click **+ New** → **New folder**
3. Numește-l: **CSSI-Resurse-Media**
4. Creează sub-foldere:
   - 📁 Logo-uri
   - 📁 Poze-Lucrari
   - 📁 Templates
   - 📁 Social-Media
5. Click dreapta pe folder → **Share**
6. "Anyone with the link" → **Viewer**
7. **IMPORTANT**: Copiază ID-ul folderului din URL
   - URL arată: `https://drive.google.com/drive/folders/ABC123xyz`
   - ID-ul e: `ABC123xyz` (textul după /folders/)
8. Trimite-mi ID-ul și îl pun în portal

---

## ✅ PAS 4: Instalează Google Apps Script (10 minute)

Acesta activează: notificări email, raport zilnic, dashboard KPI.

### 4.1 — Deschide Sheet-ul CRM:
https://docs.google.com/spreadsheets/d/1DQlmxJWMQh9NpwzKNwti2YN47u2vKIRG2n9Q1h2rVHk/edit

### 4.2 — Deschide Apps Script:
- Click **Extensions** → **Apps Script**
- Se deschide o fereastră nouă

### 4.3 — Copiază scriptul:
- Șterge tot ce e în editor (selectează tot, delete)
- Deschide fișierul `admin/CSSI-Apps-Script.js` din ZIP
- Copiază TOT conținutul
- Dă paste în editor

### 4.4 — Salvează:
- Click 💾 (sau Ctrl+S)

### 4.5 — Instalează trigger-urile:
- În dropdown-ul de funcții (sus), selectează **installTriggers**
- Click ▶️ **Run**
- Va apărea "Authorization required" → Click **Review Permissions**
- Selectează contul cssirobv@gmail.com
- Click **Advanced** → **Go to CSSI Portal (unsafe)** → **Allow**

### 4.6 — Testează:
- Selectează funcția **testEmailNotification** → Click ▶️ Run
- Verifică inbox-ul — trebuie să primești email de test
- Selectează **checkAccess** → Click ▶️ Run
- Verifică Logs (View → Logs) — toate Sheet-urile trebuie să arate ✅

### 4.7 — Repetă pentru celelalte Sheet-uri:
- Deschide fiecare Sheet → Extensions → Apps Script
- Copiază același script
- Rulează **installTriggers** pe fiecare

---

## ✅ PAS 5: Activează notificări pe Forms (2 minute)

### Pentru fiecare formular:
1. Deschide formularul în edit mode
2. Click tab **Responses**
3. Click ⋮ (3 puncte, dreapta sus)
4. Bifează **"Get email notifications for new responses"**

---

## ✅ PAS 6: Upload pe GitHub (5 minute)

1. Deschide: https://github.com/cssibv/cssi-website
2. Descarcă ZIP-ul nou de la mine
3. Upload toate fișierele (inclusiv folderul `admin/`)
4. Asigură-te că upload-ezi și:
   - `manifest.json` (pentru PWA)
   - `sw.js` (pentru offline)
5. Verifică: Settings → Pages → Branch: main → Save

---

## ✅ PAS 7: Testare finală (5 minute)

### Verifică pe telefon:
1. Deschide: `https://cssibv.github.io/cssi-website/admin.html`
2. Loghează-te cu fiecare parolă
3. Verifică fiecare modul
4. Pe Android: Menu → "Add to Home Screen" (instalare PWA)
5. Pe iPhone: Share → "Add to Home Screen"

### Verifică notificări:
1. Completează formularul de Checklist
2. Verifică dacă primești email
3. Completează formularul de Materiale
4. Verifică email

---

## 🔴 PROBLEMĂ? CE FACI?

| Problemă | Soluție |
|----------|---------|
| Sheet nu se afișează | Share → "Anyone with the link" |
| Form cere login | Settings → Debifează "Restrict" |
| Apps Script nu merge | Review Permissions → Allow |
| Email nu vine | Verifică Spam + rulează testEmailNotification |
| Portal nu se încarcă | Verifică GitHub Pages e activat |
| PWA nu se instalează | Verifică manifest.json e uploadat |

---

*Ghid creat: 20 Februarie 2026*
*Timp estimat: 30 minute*
