# Ghid Import Campanii Google Ads — via Google Ads Editor

## Ce am pregătit:
4 fișiere CSV cu campanii noi, optimizate pe servicii separate:

| Fișier | Conținut |
|--------|---------|
| `google-ads-campanii.csv` | 3 campanii noi (Control Acces, Camere, Porți) |
| `google-ads-keywords.csv` | 22 cuvinte cheie targetate |
| `google-ads-negative-keywords.csv` | 90 cuvinte negative (blochează trafic irelevant) |
| `google-ads-anunturi-rsa.csv` | 6 anunțuri responsive (2 per campanie) |

Toate campaniile sunt setate pe **Paused** — nu se activează automat.

---

## Pas 1: Descarcă Google Ads Editor (5 minute)

1. Accesează: **https://ads.google.com/intl/ro_ro/home/tools/ads-editor/**
2. Click **Descarcă** → instalează aplicația
3. Deschide Google Ads Editor
4. La prima deschidere: **Adaugă cont** → autentifică-te cu `cssirobv@gmail.com`
5. Click **Descarcă cont** (descarcă campania existentă)

## Pas 2: Importă campaniile (10 minute)

### 2.1 Import campanii:
1. Meniu: **Cont** → **Importă** → **Importă din fișier CSV**
2. Selectează: `google-ads-campanii.csv`
3. Verifică maparea coloanelor (ar trebui să se potrivească automat)
4. Click **Procesează** → **Termină și examinează modificările**

### 2.2 Import cuvinte cheie:
1. Din nou: **Cont** → **Importă** → **Importă din fișier CSV**
2. Selectează: `google-ads-keywords.csv`
3. Procesează → Termină

### 2.3 Import cuvinte negative:
1. **Cont** → **Importă** → **Importă din fișier CSV**
2. Selectează: `google-ads-negative-keywords.csv`
3. Procesează → Termină

### 2.4 Import anunțuri:
1. **Cont** → **Importă** → **Importă din fișier CSV**
2. Selectează: `google-ads-anunturi-rsa.csv`
3. Procesează → Termină

## Pas 3: Verifică totul (5 minute)

În panoul din stânga, verifică:

1. **Campanii** → ar trebui să vezi 3 campanii noi (pe lângă cea existentă):
   - CSSI - Control Acces + Pontaj (7 RON/zi)
   - CSSI - Camere Supraveghere B2B (6 RON/zi)
   - CSSI - Automatizari Porti + Bariere (4 RON/zi)

2. **Grupuri de anunțuri** → 6 grupuri (2 per campanie):
   - Control Acces Brasov, Pontaj Electronic
   - Camere Supraveghere Brasov, Camere Localitati
   - Automatizari Porti, Bariere Auto

3. **Cuvinte cheie** → 22 cuvinte cheie cu tip potrivire Exact sau Expresie

4. **Cuvinte cheie negative** → 30 cuvinte negative per campanie

5. **Anunțuri** → 6 anunțuri responsive cu titluri și descrieri

## Pas 4: Publică modificările (2 minute)

1. Click butonul **Publică** (stânga-sus, buton albastru)
2. Examinează rezumatul modificărilor
3. Click **Publică**
4. Așteaptă confirmarea

## Pas 5: Dezactivează campania veche (după 2-3 zile)

**NU dezactiva imediat!** Așteaptă 2-3 zile ca noile campanii să fie aprobate de Google.

1. În Google Ads Editor, selectează campania veche: **"CSSI - Servicii Securitate"**
2. Schimbă statusul din **Activă** → **Întreruptă** (Paused)
3. Publică modificarea

## Pas 6: Activează campaniile noi

1. Selectează fiecare campanie nouă
2. Schimbă statusul din **Întreruptă** → **Activă**
3. Publică
4. Activează pe rând (una pe zi) pentru a monitoriza performanța

---

## Structura noilor campanii vs. cea veche:

### Înainte (1 campanie):
- 1 campanie generică → 1 grup → toate cuvintele cheie amestecate
- Budget: 19 RON/zi
- Fără cuvinte negative specifice

### După (3 campanii specializate):
- **Control Acces + Pontaj**: 7 RON/zi → pagini dedicate
- **Camere Supraveghere B2B**: 6 RON/zi → target firme
- **Automatizări Porți + Bariere**: 4 RON/zi → rezidențial + comercial
- Budget total: 17 RON/zi (se poate ajusta)
- 30 cuvinte negative per campanie (blochează "gratuit", "DIY", "angajare", alte orașe)

### De ce e mai bine:
- Fiecare campanie trimite la **pagina relevantă** (nu la homepage)
- Anunțuri specifice pe serviciu (nu generice)
- Cuvinte negative blochează clicks inutile (economie ~20-30%)
- Poți ajusta bugetul per serviciu (mai mult pe ce convertește)

---

## Bugete recomandate pentru primele 2 săptămâni:

| Campanie | Budget/zi | Observații |
|----------|-----------|-----------|
| Control Acces + Pontaj | 7 RON | Cel mai profitabil serviciu |
| Camere Supraveghere | 6 RON | Volume mare de căutări |
| Automatizări Porți | 4 RON | Mai sezonier, ajustează după rezultate |
| **Total** | **17 RON** | Sub bugetul vechi de 19 RON |

După 2 săptămâni, mută budget de la campaniile cu cost/conversie mare spre cele cu cost/conversie mic.
