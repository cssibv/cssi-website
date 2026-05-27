# CSSI — Audit Google Ads & Plan Task-uri #15/#16/#17 — 27 mai 2026

> Audit realizat live în contul 666-033-6562, perioada 20–26 mai 2026.
> Scop: pregătire pentru cleanup keywords (#15), restructurare ad groups (#16), RSA-uri noi (#17).

---

## Stare actuală structură cont

- **1 campanie activă:** CSSI - Servicii Securitate
- **1 singur ad group:** „Grupul de anunțuri 1" — TOATE cuvintele cheie aici
- **Tip potrivire:** toate pe **Potrivire amplă** (broad match)
- **Buget:** ~130 RON/săpt, CTR cont 6,65%

## Keywords observate (top, 7 zile)

| Keyword | Clicuri | Afișări | CTR | Cost | Rată conv | Verdict |
|---|---:|---:|---:|---:|---:|---|
| montaj camere supraveghere | 24 | 348 | 6,90% | 85,98 RON | **20,83%** | ⭐ STEA — 66% buget, convertește excelent |
| pret camere supraveghere exterior | 4 | 58 | 6,90% | 12,34 | 0% | OK trafic, fără conv încă |
| control acces | 3 | 46 | 6,52% | 13,53 | 0% | OK |
| montaj camere video | 1 | 14 | 7,14% | 7,26 | 0% | volum mic |
| montaj camere de supraveghere | 1 | 4 | 25,00% | 0,55 | 0% | CTR mare, volum f. mic |
| kit camere wireless exterior pret | 1 | 33 | **3,03%** | 3,54 | 0% | ⚠️ CTR sub 5% |
| montez camere supraveghere | 0 | 0 | — | 0 | — | candidat pauză |
| sistem detectie incendiu | 0 | 0 | — | 0 | — | **Întreruptă** (corect — breaksistems) |
| sistem acces cu cartela | 0 | 0 | — | 0 | — | candidat pauză |
| sistem control acces cu card | 0 | 0 | — | 0 | — | candidat pauză |

*(Mai sunt ~36 keywords pe paginile 2-9 — pattern similar: multe cu 0 afișări = low search volume.)*

---

## TASK #15 — Cleanup keywords „Low search volume"

**Status: GATA DE EXECUȚIE** (nu necesită prag de conversii).

**Candidați pentru pauză/eliminare** (0 afișări susținut):
- `montez camere supraveghere` (variantă slangă, 0 trafic)
- `sistem acces cu cartela`
- `sistem control acces cu card`
- + toți cei cu status „Low search volume" de pe paginile 2-9 (de filtrat live)

**Recomandare:** Aplică filtru „Afișări = 0 pe ultimele 30 zile" + „status Low search volume" → pune pe pauză (nu șterge — păstrează istoricul). NU atinge keywords cu 0 afișări pe 7 zile dar trafic pe 30 zile.
**Efort:** 20 min · **Impact:** cont mai curat, Quality Score mai concentrat. **NU influențează direct conversiile** — efort administrativ.

⚠️ **Atenție:** `sistem detectie incendiu` e deja **Întreruptă** corect (strategia breaksistems.ro) — NU o reactiva.

---

## TASK #16 — Restructurare în 3 Ad Groups tematice

**Status: AȘTEAPTĂ** — recomand DUPĂ acumularea a 15+ conversii. Restructurarea acum resetează învățarea pe Maximize Clicks.

**Structura propusă:**

### Ad Group 1 — „Camere Supraveghere" (prioritate 1, ⭐ performer)
Keywords: `montaj camere supraveghere`, `montaj camere de supraveghere`, `montaj camere video`, `camere supraveghere brasov`, `pret camere supraveghere exterior`, `kit camere supraveghere wireless` (revizuit), `camere supraveghere exterior`
→ Landing: `/camere-supraveghere-brasov`

### Ad Group 2 — „Alarme + Control Acces + Pontaj" (B2B/securitate activă)
Keywords: `control acces`, `sistem control acces cu card`, `sistem acces cu cartela`, `alarma antiefractie brasov`, `sistem pontaj electronic`, `pontaj electronic brasov`
→ Landing: pagina relevantă per sub-temă (control-acces-brasov / alarma-antiefractie-brasov / pontaj-electronic-brasov)

### Ad Group 3 — (OPȚIONAL) „Detecție Incendiu"
**NU se creează** — detection-incendiu rămâne pe breaksistems.ro per decizia ta din 27.05. Bugetul rămâne pe Camere + Securitate.

**Efort:** 1-1,5h · **Impact:** Quality Score + relevanță anunț↔keyword↔landing → CTR mai mare, CPC mai mic.

---

## TASK #17 — Creare 3 RSA-uri noi (15 titluri + 4 descrieri fiecare)

**Status: AȘTEAPTĂ** — după #16 (fiecare ad group are nevoie de RSA-ul lui) + 7-14 zile de tracking corect.

**Draft titluri RSA „Camere Supraveghere" (de pus în Ad Group 1):**
1. Camere Supraveghere Brașov
2. Montaj Camere în 24h
3. Hikvision & Dahua Originale
4. Vizualizare pe Telefon Gratuit
5. CSSI — 20+ Ani Experiență
6. 8.700+ Proiecte Finalizate
7. Garanție 3 Ani Inclusă
8. AI Detecție Persoane
9. Ofertă Gratuită în 24h
10. Echipă Locală Brașov
11. NVR PoE Profesional
12. Camere 4K & ColorVu
13. Montaj + Configurare Complet
14. Sună: 0752 288 400
15. Evaluare Gratuită la Fața Locului

**Descrieri (4):**
1. Instalare camere supraveghere în Brașov cu Hikvision/Dahua. Montaj rapid, garanție 3 ani. Sună acum!
2. Vizualizare live pe telefon, fără abonament. AI detecție persoane, zero alarme false. Ofertă gratuită.
3. 20+ ani experiență, 8.700+ proiecte. Echipă locală Brașov, intervenție în 24h.
4. De la camere casă la sisteme enterprise 4K. Consultanță și evaluare gratuită la fața locului.

*(Pentru Ad Group 2 — Alarme/Control Acces/Pontaj — se adaptează cu USP-uri specifice: Ajax/Paradox, IGPR, integrare pontaj, Revisal etc.)*

**Efort:** 1h/RSA · **Impact:** mai multe variante = Google testează → CTR mai mare. Ținta: 6,65% → 12-15%.

---

## Recomandare bidding strategy

**Status actual:** Maximize Clicks.
**Migrare la Maximize Conversions: ÎNCĂ NU.**
- Conversii reale acumulate: ~5/lună (form_submit 3 + whatsapp_click 2). phone_call la 0 în săptămâna asta.
- Prag recomandat: **15+ conversii / 30 zile** înainte de migrare.
- **Cale:** menține Maximize Clicks → optimizează CTR prin #16+#17 → când ajungi la 15 conv/lună, migrează la Maximize Conversions.

---

## Ordine de execuție recomandată

1. **ACUM:** #15 cleanup low search volume (20 min, fără risc)
2. **Săptămâna asta:** lasă contul să ruleze cu meta tags noi (deja live) + monitorizează CTR organic
3. **Când ai 15+ conv cumulate (~3-4 săpt):** #16 restructurare ad groups
4. **Imediat după #16:** #17 RSA-uri noi per ad group
5. **După 30 zile date pe structura nouă:** migrare Maximize Conversions

---

*Audit live realizat 27.05.2026. Schița campanie #3 a fost ștearsă în aceeași sesiune.*
