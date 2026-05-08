# CSSI — Raport zilnic monitorizare
**Data:** 2026-04-27 (Luni, săptămâna 18)
**Cont Google Ads:** 666-033-6562 | **Buget:** 60 EUR/lună (~300 RON)
**Generat de:** scheduled task `cssi-daily-monitoring`

---

## TL;DR (3 linii)
- **Ce merge bine:** Infrastructura optimizărilor din 21 aprilie (PMax șters, 31 negative keywords, 10 sitelinks, JSON-LD pe `/pontaj-electronic.html`) este în continuare activă — fundamentul pe Search rămâne sănătos pe baza ultimei capturi de date (CTR 14,29%).
- **Ce nu merge:** Nu pot accesa date live azi — niciun browser Chrome conectat la sesiune, deci Google Ads / GA4 / Search Console nu pot fi citite în timp real.
- **Acțiune recomandată azi:** Reautentifică Claude in Chrome (extensia + login Google) astfel încât raportul de mâine să capteze date reale; în paralel, deblochează task-urile pending #15-#17 (curățare Low search volume + restructurare în 3 Ad Groups + 3 RSA-uri noi) — sunt pre-pregătite în checklist.

---

## ⚠️ BLOCAJ ACCES DATE LIVE

| Platform | Status | Necesar |
|----------|--------|---------|
| Google Ads | ⚠️ BLOCAT | Reautentificare Claude in Chrome (no connected browsers) |
| Google Analytics 4 | ⚠️ BLOCAT | Reautentificare Claude in Chrome |
| Google Search Console | ⚠️ BLOCAT | Reautentificare Claude in Chrome |

> **Cum se rezolvă:** Deschide Chrome, asigură-te că extensia Claude in Chrome este activă și conectată la cssirobv@gmail.com, apoi rulează manual scheduled task-ul `cssi-daily-monitoring` pentru un raport complet cu date live.

---

## 1. Google Ads — captură 7 zile (referință din ultimele date cunoscute, 21 aprilie)

⚠️ **Date live indisponibile azi.** Tabelul de mai jos reprezintă baseline-ul cunoscut din sesiunea de optimizare 21 aprilie 2026 — nu metrics curente.

| Metric | Baseline (21 aprilie) | Țintă 60 zile | Stare |
|--------|----------------------|---------------|-------|
| CTR campanie Search | 14,29% | 15-18% | În progres |
| Conversii primare /lună | ~3 | 12-20 | Sub țintă |
| Conversii local actions /lună | 29 (Other engagements) | n/a | OK |
| Buget consumat eficient | ~70% (după ștergere PMax) | 100% | În progres |
| Negative keywords active | 31 | menținut | OK |
| Sitelinks active | 10 (CTR ext. 18,18%) | menținut | OK |

**Top keywords cunoscute (impact istoric):**
- `[pontaj electronic]` — Quality Score bun, dar volum mic (necesită expansiune)
- `[camere supraveghere brasov]` — performant
- `[alarma antiefractie brasov]` — performant
- `[control acces brasov]` — volum scăzut

**Comentariu pe anomalii:** N/A azi — fără date live nu pot detecta scăderi >20% sau creșteri >30%.

---

## 2. GA4 — trafic și conversii (ieri vs. medie 7 zile)

⚠️ **Date live indisponibile azi.**

Evenimente urmărite (configurare existentă):
- `phone_call` (50 RON, import GA4)
- `whatsapp_click` (50 RON, import GA4)
- `form_submit` (50 RON, default GA4)
- `cta_click` (custom event)

Top pagini istorice (din configurarea SEO 2026):
1. `/` (homepage)
2. `/camere-supraveghere.html`
3. `/alarma-antiefractie.html`
4. `/pontaj-electronic.html` (priority page #1)
5. `/control-acces.html`

---

## 3. Google Search Console — impresii / clickuri / queries

⚠️ **Date live indisponibile azi.**

Poziții medii țintite (baseline cunoscut):
- `sistem pontaj electronic` — poziție curentă **26**, țintă **12-15** (page-level optimization aplicată 21 aprilie)
- `camere supraveghere brasov` — verificare necesară
- `alarma antiefractie brasov` — verificare necesară
- `control acces brasov` — verificare necesară
- `detectie incendiu brasov` — verificare necesară

---

## 🚨 Alerte

| # | Alerta | Severitate | Detalii |
|---|--------|------------|---------|
| 1 | Niciun browser Chrome conectat | 🔴 Înaltă | Blochează colectarea zilnică de date — necesită intervenție MIHAI |
| 2 | Conversie „Solicitați o ofertă" în stare *Necesită atenție* | 🟡 Medie | Verificare manuală necesară în Google Ads → Conversions; posibil tag tracking lipsă |
| 3 | Task-uri #15-#17 încă pending de la 21 aprilie | 🟡 Medie | Așteaptă fereastră de execuție 90-100 min; checklist gata |
| 4 | Migrare Maximize Conversions amânată | 🟢 Scăzută | Criteriu: 15+ conversii acumulate înainte de migrare |

---

## ✅ Acțiuni propuse azi

### Acțiune 1 — Reconectează Claude in Chrome (PRIORITATE 1)
- **Context:** Fără sesiune Chrome activă, raportul zilnic devine cosmetic. Toate cele 3 platforme (Ads/GA4/GSC) necesită browser logat la cssirobv@gmail.com.
- **Efort:** 2-3 minute (deschide Chrome → verifică extensie → login Google).
- **Impact:** Raportul de mâine include date reale (CTR live, conversii ieri, queries noi GSC).

### Acțiune 2 — Verifică conversia „Solicitați o ofertă" în Google Ads
- **Context:** Stare „Necesită atenție" persistă din 21 aprilie. Probabil tag-ul de pe site nu transmite hit-ul corect, sau filtrul de URL nu prinde toate variantele paginii.
- **Efort:** 10 minute (Ads → Tools & Settings → Conversions → click pe acțiune → Diagnostics).
- **Impact:** Recuperare ~50 RON valoare per submit ne-tracked + curățarea atribuirii pentru bidding viitor.

### Acțiune 3 — Începe Task #15 (curățare keywords „Low search volume")
- **Context:** ~15+ keywords în stare Low volume nu mai aduc impresii, doar fac balast în structură. Pas 2 din checklist (10 min).
- **Efort:** 10 minute.
- **Impact:** Structura curată permite RSA-uri noi (Pas 3-4) să respire mai bine + bid manual mai ușor de citit.

---

## Note pentru raportul săptămânal (Lunea)

Astăzi este Luni — vezi fișier separat:
`C:\Users\Diaconu Mihai\Documents\Website\cssi-website\weekly-strategy\CSSI-Weekly-Strategy-2026-04-27.md`

---

*Raport generat automat. Sursă date azi: contextul cunoscut din sesiunea 21 aprilie 2026 (Google Ads 666-033-6562). Pentru date live, reautentifică Claude in Chrome.*
