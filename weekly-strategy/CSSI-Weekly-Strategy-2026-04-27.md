# CSSI — Raport strategic săptămânal
**Data emiterii:** 2026-04-27 (Luni)
**Săptămâna acoperită:** W17 (20-26 aprilie 2026)
**Cont Google Ads:** 666-033-6562 | **Buget:** 60 EUR/lună (~300 RON)
**Generat de:** scheduled task `cssi-daily-monitoring`

---

## ⚠️ Avertisment de date
Acest raport săptămânal este generat fără acces live la Google Ads / GA4 / GSC (Claude in Chrome neautentificat). Numerele de mai jos folosesc baseline-ul cunoscut din sesiunea 21 aprilie 2026. Pentru un raport săptămânal complet cu trend WoW real, reautentifică extensia Claude in Chrome și re-rulează task-ul.

---

## 1. Sumar săptămâna trecută (W17, 20-26 aprilie)

| Indicator | Valoare aproximată | Sursă |
|-----------|--------------------|-------|
| Trafic GA4 sesiuni / 7 zile | ⚠️ N/A — necesită GA4 live | — |
| Conversii primare totale | ⚠️ N/A — necesită Ads/GA4 live | — |
| Cost total Google Ads | ~70 RON estimat (60 EUR / 4,3 săpt) | Buget setat |
| Cost / conversie | ⚠️ N/A | — |
| Top câștig al săptămânii | Optimizările din 21 aprilie au intrat în efect (PMax oprit, sitelinks +3, JSON-LD pontaj) | Sumar sesiune |
| Top pierdere | Conversia „Solicitați o ofertă" rămâne în „Necesită atenție" | Sumar sesiune |

---

## 2. Comparație vs. săptămâna anterioară (W16 vs. W17)

⚠️ Imposibil de calculat azi — fără date live.
Recomandare: la următorul rulaj cu Chrome conectat, capturează snapshot W17 complet pentru a putea face WoW corect începând cu W18.

---

## 3. Progres către țintele 60 zile

| Țintă (60 zile, scadent ~21 iunie) | Curent | Progres | Bară |
|------------------------------------|--------|---------|------|
| CTR Search 15-18% | 14,29% | 95% spre minim 15% | ████████████████████░ |
| Conversii primare 12-20/lună | ~3/lună | 25% spre minim 12 | █████░░░░░░░░░░░░░░░ |
| Rang „pontaj electronic" 12-15 | 26 | 0% (încă în afara intervalului) | ░░░░░░░░░░░░░░░░░░░░ |
| Valoare conversii 600-1000 RON/lună | ~35 RON | 6% spre minim 600 | █░░░░░░░░░░░░░░░░░░░ |

**Comentariu pe progres:**
- CTR este aproape de țintă — RSA-urile noi (task #17) ar putea împinge peste 15%.
- Conversii primare = principalul punct slab. Cauza probabilă: tracking incomplet pe „Solicitați o ofertă" + volum trafic mic pe pagini de conversie.
- Rang „pontaj electronic" — efectul JSON-LD + H1 nou (aplicate 21 aprilie) se va vedea în GSC în 4-6 săptămâni; verificare W19-W21.
- Valoare conversii — depinde direct de creșterea conversiilor primare; e un derivat.

---

## 4. Analiză pe 3 axe

### 4.1 Google Ads
**Ce performează (din baseline cunoscut):**
- Sitelinks: CTR extensii 18,18% — peste media campaniei.
- Search după ștergere PMax: 100% buget redirecționat unde convertește.

**Ce necesită ajustare:**
- Ad group unic actual prea generic — task #16 (split în 3) așteaptă execuție.
- Match types — trecere la *phrase* pe toate keywords rămase (parte din task #15).

**Recomandare buget:** Menține 60 EUR/lună. Nu crește încă — întâi finalizează task #15-#17 și colectează 30 zile de date pe noua structură. Apoi evaluare creștere la 90-100 EUR/lună dacă cost/conv < 30 RON.

### 4.2 SEO organic (GSC)
⚠️ Date live indisponibile — analiza de mai jos este bazată pe planul SEO cunoscut.

**Pagini cu oportunitate (poziții 11-20 estimat):**
- `/pontaj-electronic.html` — poziție 26 → țintă 12-15 (în lucru)
- `/control-acces.html` — verificare necesară
- `/detectie-incendiu-isu.html` — verificare necesară

**Content gaps (din calendarul editorial 2026):**
- Articole comparative („Pontaj RFID vs. biometric: ce alegi pentru firmă mică")
- Studii de caz cu clienți (creștere autoritate locală + linkuri interne)
- Pagini „pentru domeniu" (pontaj pentru construcții, magazine, depozite)

### 4.3 Conversii — funnel leak detection
⚠️ Date live indisponibile.

**Ipoteze de verificat săptămâna asta:**
- Pagini cu trafic mare dar 0 conversii → suspect button placement / form friction
- Heatmap pe pagini țintă (dacă există Hotjar/Clarity) — verificare scroll depth.
- WhatsApp click rate vs. phone click rate — care convertește mai bine în client real?

---

## 5. Top 5 acțiuni prioritizate W18 (28 aprilie - 4 mai)

| # | Acțiune | Owner | Efort | Impact |
|---|---------|-------|-------|--------|
| 1 | Reconectează Claude in Chrome și fă primul pull complet de date Ads/GA4/GSC | MIHAI | 5 min | 🔥 Critic — deblochează tot raportul |
| 2 | Execută task-uri #15 + #16 + #17 din checklist (cleanup + restructurare + 3 RSA-uri) | MIHAI | 90-100 min | 🔥 Mare — fundație pentru luna mai |
| 3 | Diagnostichează conversia „Solicitați o ofertă" și repară tracking | MIHAI | 15-20 min | 🟡 Mediu — recuperează 50 RON/conv |
| 4 | Verifică în GSC pozițiile pe cele 5 queries țintă; notează baseline pentru WoW viitor | MIHAI | 10 min | 🟡 Mediu — măsurătoare |
| 5 | Publică 1 articol nou pe blog (din calendarul editorial 2026) sau update pe pagina pontaj | MIHAI | 60-90 min | 🟡 Mediu — semnal SEO + conținut nou |

---

## 6. Verificare task-uri pending #15, #16, #17

**Criteriu deblocare originar:** 15+ conversii acumulate, >30 zile date pe setările noi.

**Status azi:**
- Conversii acumulate de la 21 aprilie încoace: **necunoscut fără date live**, dar probabil sub prag (eram la ~3/lună primare).
- Zile de la setările noi: **6 zile** (21 → 27 aprilie) — sub pragul de 30 zile.

**Recomandare:**
- Task #15 (cleanup Low search volume keywords) — **DEBLOCHEAZĂ ACUM**. Nu depinde de volum de conversii, e curățenie pură. Câștigi raport mai curat și economisești quota de impresii pe kw inactive.
- Task #16 (restructurare în 3 Ad Groups) — **DEBLOCHEAZĂ după task #15** (același sesion). Nu necesită volume mari de conversii, e structură.
- Task #17 (3 RSA-uri noi) — **DEBLOCHEAZĂ împreună cu #16**. RSA-urile au nevoie de 15-30 zile pentru a-și calibra. Cu cât pleacă mai devreme, cu atât ai date mai repede.

**Cu alte cuvinte: nu mai aștepta cele 30 de zile pentru aceste 3 task-uri** — sunt task-uri de structură, nu de optimizare bazată pe conversii. Pragul de 15+ conversii rămâne valabil doar pentru migrarea la Maximize Conversions (vezi punctul 7).

---

## 7. Recomandare bidding strategy

**Stare actuală:** Maximize Clicks (default după ștergerea PMax).

**Migrare către Maximize Conversions?** **NU încă.**

Motive:
- Acum: ~3 conversii primare/lună. Pragul minim Google pentru smart bidding = 15 conversii/30 zile.
- Estimat W18-W21: dacă task-urile #15-#17 se execută săptămâna asta și RSA-urile încep să convertească → posibil 8-12 conversii/lună până la mijlocul lui mai. Încă insuficient.
- Cel mai devreme moment realist pentru migrare: **mijlocul lui iunie 2026** (după 30+ zile pe noua structură + 15+ conversii acumulate).

**Acțiune până atunci:** păstrează Maximize Clicks, monitorizează săptămânal CTR și CPC mediu. Dacă CPC mediu sare peste 3 RON, comută punctual la *Manual CPC* pe ad group-ul afectat.

---

## 8. Priorități strategice pe luna mai 2026

1. **Conversii primare la 10+/lună** prin tracking corect + RSA-uri noi.
2. **Rang „pontaj electronic" sub 20** — combinație SEO on-page + 1 articol blog suport + linkuri interne din /control-acces și /sistem-pontaj.
3. **Dublare baseline trafic GA4** pe paginile de servicii (camere, alarme, control acces) prin RSA-uri tematice + sitelinks dedicate per ad group.

---

*Raport săptămânal generat automat luni dimineață. Următorul raport săptămânal: 2026-05-04 (W19).*
