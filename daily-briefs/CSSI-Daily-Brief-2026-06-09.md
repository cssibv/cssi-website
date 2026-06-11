# CSSI Daily Brief — 2026-06-09 (marți)

> **Săptămâna W24 · 8-14 iunie 2026** · Ziua 2 din 7

## 🎯 TL;DR
- ✅ **Acces Google Ads + GSC live** — date confirmate pentru 2 din 3 platforme (vs. ieri 0/3 blocate). GA4 rămâne BLOCAT (cont browser breaksistems@gmail.com fără permisiune pe proprietate a385388640).
- ⚠️ **CTR Ads 8,90% (42/472)** — în scădere de la 9,36% (acum o săpt.) și sub țintă 15-18% cu ~7 pp. CPC mediu 3,24 RON (în creștere de la 3,14). Cost 7z = 136 RON ≈ 19,4 RON/zi → epuizare buget lunar ~22 iun la ritm curent.
- **1 acțiune azi (20 min):** Publică RSA pentru grupul Alarme/Acces/Pontaj (Task #17, restanță 13 zile, recomandarea „1 ad group has no ads" persistă) — singur deblocaj care mută optim. score peste 75% și deschide afișările pierdute zilnic.

---

## 📊 Google Ads — ultimele 7 zile (3-9 iun)

| Metric | Valoare | Săpt. anterioară | Δ | Țintă | Gap |
|---|---:|---:|---:|---:|---|
| Clicuri | 42 | 41 | +1 | — | — |
| Afișări | 472 | 438 | +7,8% | — | — |
| **CTR** | **8,90%** | 9,36% | **-0,46 pp** | 15-18% | -7 pp |
| CPC mediu | 3,24 RON | 3,14 RON | +3,2% | <2,50 RON | +30% |
| Cost total | 136 RON | 129 RON | +5,4% | — | — |
| Cost/zi mediu | 19,4 RON | 18,4 RON | +5,4% | — | la ritm: ~582 RON/lună |
| Stare difuzare | OK | OK | — | OK | ✅ |

**Comentariu:** Volum stabil (+1 click), dar costul crește mai repede decât performanța — CPC +3% și CTR -0,5pp. Diagnostic cont: „Campania dvs. nu prezintă probleme de difuzare" + „A cheltuit cea mai mare parte a bugetului zilnic mediu, primind valoarea conversiilor în ultima săptămână" — cont sănătos la nivel macro, dar nu reflectă alerta CTR sub țintă.

**Anomalii observate:** afișările cresc (+7,8%) fără ca clicurile să țină pasul → semnal că textele anunțurilor nu sunt aliniate cu intenția queries-urilor. Aceasta întărește urgența pentru restructurarea în 3 Ad Groups tematice (Task #16).

---

## 📈 GA4 — ieri (8 iun)
⚠️ **BLOCAT: GA4 — necesită reautentificare manuală.** Browser-ul Chrome conectat (Browser 1) e logat ca `breaksistems@gmail.com`, care nu are permisiune pe proprietatea a385388640.

**Fix:** logare manuală în Chrome cu `cssirobv@gmail.com` și revocare/dezactivare cont breaksistems din profilul Chrome activ, SAU adăugare permisiune „Reader" pentru breaksistems la GA4 property.

**Workaround până la fix:** date GA4 lipsă din raportul zilnic — semnalele de conversie (form_submit, phone_call, whatsapp_click, cta_click) nu pot fi verificate live. Risc: nu se observă în timp real un eventual drop tracking (vezi alerta -61,5% din 6 iun, încă neverificată).

---

## 🔍 Google Search Console — ultimele 7 zile (1-7 iun)

| Metric | Valoare | Săpt. anterioară | Δ |
|---|---:|---:|---:|
| Clicuri totale | 14 | 15 | -1 |
| Afișări totale | 593 | 687 | -13,7% |
| CTR mediu | 2,4% | 2,2% | +0,2 pp |
| Poziție medie | 14,4 | 15,2 | +0,8 (îmbunătățire) |

**Top 10 interogări (cele mai multe afișări):**

| # | Interogare | Clicuri | Afișări | CTR |
|---:|---|---:|---:|---:|
| 1 | sistem supraveghere brasov | 1 | 5 | 20,0% ⭐ |
| 2 | camere supraveghere | 0 | 62 | 0% |
| 3 | camere de supraveghere | 0 | 21 | 0% |
| 4 | sisteme de securitate | 0 | 14 | 0% |
| 5 | camere supraveghere brasov | 0 | 12 | 0% |
| 6 | sistem pontaj electronic | 0 | 11 | 0% |
| 7 | sistem pontaj | 0 | 8 | 0% |
| 8 | condică alectronică | 0 | 8 | 0% |
| 9 | pontaj electronic | 0 | 7 | 0% |
| 10 | instalare camere de supraveghere brasov | 0 | 7 | 0% |

**Recomandare GSC (Search Console alert):** pagina `https://cssi.ro/blog/pontaj-electronic-ghid-complet` a înregistrat **-66% afișări** vs. obișnuit — investigare urgentă (verifică content, internal links, dacă a fost dez-indexată; este pagina pivot pentru queries „pontaj electronic" care apar de 3 ori în top 10).

**Pagini indexate:** 71 indexate / 83 ne-indexate — verificare ne-indexate recomandată săptămânal.

**Observații:**
- „sistem supraveghere brasov" e singura interogare cu click (CTR 20%) — confirmă oportunitate Brașov-local.
- „camere supraveghere" generează 62 afișări dar 0 clicuri → poziție prea joasă (probabil 15+); content gap evident.
- „condică alectronică" (typo „a" în loc de „e") = oportunitate misspelling — pagină dedicată?

---

## 🚨 Alerte cumulate

1. **🔴 Task #17 RSA grup Alarme/Acces/Pontaj — neexecutat de 13 zile** (recomandarea „1 ad group has no ads" persistă). Optim. score blocat sub 75%, afișări pierdute zilnic. **DEBLOCANT #1 al săptămânii.**
2. **🔴 Buget Google Ads pe traiectorie depășire** — la 19,4 RON/zi → 582 RON/lună vs. buget 60 EUR (~300 RON). Necesar top-up SAU coborâre CPC.
3. **🟠 CTR Ads 8,90% (-0,46 pp WoW)** — divergență afișări/clicuri tot mai mare. Restructurarea în 3 Ad Groups (#16) + RSA-uri noi (#17) sunt blocante.
4. **🟠 GA4 fără acces** — tracking conversii primare nemonitorizat live; riscă să rateze pierderi semnal.
5. **🟠 Pagina pontaj-electronic-ghid-complet -66% afișări GSC** — alertă activă; cuvinte cheie pontaj reprezintă 3 din top 10 queries.
6. **🟡 GSC afișări totale -13,7% WoW** — tendință descrescătoare; verifică algoritm Google sau drop de indexare pe alte pagini.
7. **🟡 „condică alectronică" 8 afișări** — typo recurent, oportunitate pagină separată pentru misspelling.

---

## ✅ Acțiuni propuse azi (marți 9 iun)

### Acțiunea 1 — Publică RSA grupul Alarme/Acces/Pontaj (Task #17)
- **Context:** Recomandarea „1 ad group has no ads" persistă de 13+ zile; afișările cresc (+7,8% WoW) dar nu sunt convertite în clicuri pentru că grupul tematic nu rulează încă.
- **Efort:** ~20 min (RSA cu 15 titluri + 4 descrieri folosind keyword-uri „alarma antiefractie brasov", „control acces brasov", „sistem pontaj electronic brasov").
- **Impact așteptat:** +5-8 pp CTR pe grupul respectiv, deblochează ~16,5% optim. score, oprește pierderea de ~50-80 afișări/zi.

### Acțiunea 2 — Investighează drop -66% afișări pagină „pontaj-electronic-ghid-complet"
- **Context:** Alerta GSC activă; queries pontaj reprezintă 30% din top 10 GSC; dacă pagina pierde rang, întregul pillar SEO „pontaj electronic" suferă.
- **Efort:** ~15 min (verificare: a) pagina răspunde 200, b) e indexată, c) internal links din pagini conexe, d) GSC „URL Inspection" pentru status real).
- **Impact așteptat:** recuperare 50-100 afișări/săpt + protecție rang viitor.

### Acțiunea 3 — Fix permisiuni GA4 (problemă de continuitate, nu de urgență)
- **Context:** Browser Chrome conectat e logat cu breaksistems@gmail.com (cont fără acces GA4); rapoartele zilnice rămân semi-blind față de conversii live.
- **Efort:** ~10 min (sign-in cu cssirobv@gmail.com SAU adaugă breaksistems ca „Viewer" în GA4 property a385388640).
- **Impact așteptat:** rapoartele revin la 100% complete; tracking-ul conversiei poate fi monitorizat zilnic.

---

## 🔄 Restanțe de la rapoarte anterioare (recap)

| Task | Status | Restanță |
|---|---|---:|
| #15 Cleanup keywords „Low search volume" | Pending — așteaptă 15+ conversii | — |
| #16 Restructurare în 3 Ad Groups tematice | Pending — depinde de #17 + 30 zile date | — |
| #17 Creare 3 RSA-uri noi | Pending — **DEBLOCANT** | **13 zile** |
| Fix `defaultBrowserDeviceId` în SKILL.md | Pending | 9 zile |
| Verifică drop -61,5% evenimente conversie (6 iun) | Pending — necesită acces GA4 | 3 zile |

---

*Raport generat automat — sursă: Google Ads (overview live), GSC (performance live), GA4 (blocked: permisiuni).*
