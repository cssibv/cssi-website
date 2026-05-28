# CSSI — Context Sesiune (rezumat compact) — 27 mai 2026

> Document de memorie pentru continuarea lucrului. Conține starea conturilor, ce s-a făcut, decizii și ce a mai rămas.

---

## CONSTANTE CONT (neschimbate)
- **Google Ads:** cont 666-033-6562, buget ~60 EUR/lună, strategie bid: **Maximize Clicks**
- **GA4:** property a385388640 / stream p525787706 (cssi.ro)
- **GSC:** sc-domain:cssi.ro
- **Telefoane:** 0752 288 400 (mobil/WhatsApp), 0268 414 740 (fix)
- **Site local:** C:\Users\Diaconu Mihai\Documents\Website\cssi-website (git → GitHub cssibv/cssi-website → deploy cPanel)
- **Email rapoarte:** cssirobv@gmail.com (MIHAI)

## ȚINTE 60 ZILE & PROGRES (la 27 mai)
- CTR Ads: 6,65% → țintă 15-18% (~40%)
- **Conversii/lună: ~20 → țintă 12-20 = ATINSĂ ✅**
- Valoare conv: 547,93 RON/30z → țintă 600-1000 (~91% prag min)
- Rang „sistem pontaj electronic": 25,0 → țintă 12-15 (gap 10 poz)
- CTR organic GSC: 2,2% → țintă 4-6%

---

## CE S-A FĂCUT AZI (toate LIVE dacă nu se specifică altfel)

### Site (pushate + deployate — confirmat live)
- Meta titles/descriptions optimizate (pattern `[Keyword] [Oraș] ✓ [USP] | Brand`) pe: camere-supraveghere-brasov, pontaj-electronic, pontaj-electronic-brasov, alarma-antiefractie-brasov, control-acces-brasov, detectie-incendiu-isu
- `tracking.js`: eliminat dual-fire (evită dublă numărare — GA4 import deja funcționează), păstrat debug logging `?cssi_debug=1`
- `cookie-consent.js`: gtag.js încărcat cu GA4 ID (best practice)
- Pagină nouă `detectie-incendiu-brasov.html` creată dar **NOINDEX** (detection incendiu rămâne pe breaksistems.ro per decizie business)

### Google Ads (modificări live în cont)
- **Restructurat în 2 ad groups** (din 1 generic): „Camere Supraveghere" (redenumit din Grupul 1) + „Alarme, Control Acces & Pontaj" (nou)
- Grup nou are 6 keywords potrivire-expresie + **RSA nou cu 15 titluri tematice** + 4 descrieri
- Pus pe pauză „control acces" (broad) din Grupul 1 (overlap cu grupul nou)
- Șters schița campanie #3 (abandonată din martie)

### Verificări/corecții diagnostic
- **Tracking phone_call/whatsapp_click NU e spart** (raportul de dimineață greșise). whatsapp_click: 2 conv/154 RON. phone_call: configurat corect, 0 = volum mic + consent modeling neactivat. Toate 4 evenimente-cheie marcate corect în GA4.
- Unassigned +600% = artefact Consent Mode (refuzuri cookie), NU problemă UTM
- Camere Supraveghere 0 afișări GA4 = săptămână cu trafic mic, pagina e sănătoasă
- GSC: camere-supraveghere-brasov trimis la indexare ✅

### Meta Business
- 2 portofolii duplicate găsite: „CSSI" (gol) + „CSSI 1" (real). User a **programat „CSSI" pentru ștergere** (grace period ~30 zile)
- Niciun pixel Meta nu există. Meta Pixel ID = placeholder în cookie-consent.js. **Decizie: skip Meta** (nu fac reclame FB acum)

---

## CE A MAI RĂMAS (opțional, nu urgent)
1. **GSC indexare manuală** 4 pagini (pontaj-electronic, pontaj-electronic-brasov, alarma-antiefractie-brasov, control-acces-brasov) — sau recrawl natural din sitemap. (Bara GSC nu re-declanșa inspecția prin automatizare azi.)
2. **Al 2-lea RSA dedicat camere** în grupul Camere Supraveghere — conținut pregătit în `CSSI-Audit-Ads-2026-05-27.md`. (Grupul are deja anunț funcțional. Interfața de creare anunț îngheață renderer-ul.)
3. **Monitorizare** (5-7 zile): efect meta tags pe CTR organic GSC; afișări pe grupul nou Alarme/Acces/Pontaj
4. **Bidding:** migrare la Maximize Conversions DUPĂ ~2 săptămâni (lasă restructurarea să se stabilizeze). Eligibil tehnic (20 conv/30z > prag 15).
5. **Termen mediu:** linkuri interne + conținut pentru pagini poziții 11-20 (camere supraveghere 15,7; sisteme pontaj 19,7)

---

## ÎNVĂȚĂMINTE TEHNICE (pentru sesiuni viitoare)
- **Sandbox NU poate scrie în .git** → push-ul se face de user (GitHub Desktop)
- **Edit/Write trunchiază fișiere >8KB** → pentru JS mare folosește `sed`/Python prin bash, NU Edit/Write
- **Pagini grele Google Ads (creare anunț) și GSC (URL inspection) îngheață renderer-ul** Chrome → folosește `read_page`/`find`/`form_input` (mai ușoare) în loc de screenshot; form_input cu ref-uri e cel mai fiabil pentru completat câmpuri
- **GA4 deep-links** se redirecționează la home → navighează prin UI (Admin → meniuri)
- web_fetch/curl restricționate în sandbox → verificare live doar prin Claude in Chrome

## FIȘIERE GENERATE AZI (în cssi-website/)
- daily-briefs/CSSI-Daily-Brief-2026-05-27.md (raport zilnic)
- daily-briefs/CSSI-Implementari-2026-05-27.md (implementări + acțiuni manuale)
- daily-briefs/CSSI-Audit-Ads-2026-05-27.md (audit Ads + conținut RSA camere pregătit)
- weekly-strategy/CSSI-Weekly-Strategy-2026-05-27.md (raport strategic)
- daily-briefs/CSSI-Context-Sesiune-2026-05-27.md (acest fișier)
