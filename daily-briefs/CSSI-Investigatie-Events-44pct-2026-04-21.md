# Investigație: GA4 Events -44.6% WoW (209 vs ~377)

**Data:** 21 aprilie 2026
**Proprietate:** CSSI.ro (GA4 ID: 525787706)
**Task:** #25 [completed]

---

## 🎯 Verdict

**Scăderea NU este tracking issue. Este artefact statistic cauzat de un spike de trafic pe 7-8 aprilie.** Tracking-ul funcționează corect pe toate canalele.

---

## 🔍 Semnale contradictorii care au dezvăluit cauza

Dashboard-ul GA4 arată simultan:

| Metrică | Valoare | Delta WoW |
|---|---|---|
| Utilizatori activi | 24 | **↑ 20.0 %** |
| Utilizatori noi | 20 | **↑ 17.6 %** |
| Număr evenimente | 209 | **↓ 44.6 %** |

**Contradicția:** dacă traficul ar fi scăzut real, și utilizatorii ar fi scăzut. Aici utilizatorii CRESC cu +20%, dar evenimentele scad cu -44.6%. Pe hârtie, asta ar părea tracking issue (users up, events down = fiecare user generează mai puține evenimente). Dar chart-ul pe 28 de zile explică altfel.

---

## 📊 Trendul pe 28 de zile (24 mar – 20 apr)

Chart-ul arată **baseline stabil de ~30-50 evenimente/zi**, cu o anomalie clară:

- 25 mar – 5 apr: ~35-60 ev/zi (baseline)
- **7 apr: 170 ev (spike 3-4x)** ⚠️
- **8 apr: ~100 ev (spike continuă)** ⚠️
- 9 apr – 20 apr: revenire la baseline ~25-50 ev/zi

**Săptămâna anterioară (7-13 apr)** = a conținut spike-ul pe 7-8 apr → total umflat ~377
**Săptămâna curentă (14-20 apr)** = baseline normal → total 209

Ambele săptămâni au aceeași rată zilnică **în afară de outlier-ul 7-8 aprilie**.

---

## ✅ Validări că tracking-ul funcționează

**1. Real-time:** 1 utilizator activ în Brașov acum, evenimente LIVE pe graficul „Utilizatori activi pe minut". Dacă gtag nu trimitea, aici ar fi fost 0.

**2. Breakdown pe event name (ultimele 28 zile):**

| # | Event | Count | % | Users |
|---|---|---|---|---|
| 1 | page_view | 384 | 36.4 % | 98 |
| 2 | user_engagement | 324 | 30.7 % | 93 |
| 3 | session_start | 144 | 13.7 % | 98 |
| 4 | first_visit | 86 | 8.2 % | 86 |
| 5 | scroll | 73 | 6.9 % | 33 |
| 6 | form_start | 11 | 1.0 % | 10 |
| 7 | click | 10 | 0.9 % | 4 |
| 8 | form_submit | 5 | 0.5 % | 5 |
| 9 | phone_call | 5 | 0.5 % | 4 |
| 10 | whatsapp_click | 5 | 0.5 % | 5 |

**Niciun event la 0.** Toate event-urile custom (form_submit, phone_call, whatsapp_click) se înregistrează. Pâlnia automată (page_view → session_start → user_engagement) e intactă.

**3. Raport 3.2 evenimente/utilizator (384 pv / 98 users)** este normal pentru site cu 1-2 pagini/sesiune medie.

**4. Codul din `/tracking.js` a fost verificat anterior** — `gtag('event', ...)` pentru phone_call, whatsapp_click, form_submit, cta_click funcționează; `window.open` hook pentru form captează submit-urile WhatsApp.

---

## 🧐 Ce a fost spike-ul din 7-8 aprilie?

Chart-ul arată că spike-ul a fost driven de:
- page_view: ~80 pe 7 apr (normal: ~15)
- user_engagement: ~60 pe 7 apr (normal: ~10)
- session_start: ~20 pe 7 apr (normal: ~5)

Raportul user_engagement/page_view rămâne ~75% = caracteristic traficului uman, NU bot-like scanning. Probabil:

**A)** Distribuție organică a unui link (share pe WhatsApp/Facebook, recomandare)
**B)** Vizită extinsă a unui prospect/echipă (multi-page research)
**C)** Campanie Meta Ads/Google Ads cu click peak în zilele respective (verificabil din Google Ads → performanță pe zi)

În ambele cazuri = trafic real, nu bot sau glitch.

---

## 📉 Concluzie operațională

**Nu e nevoie de nicio acțiune.** Tracking-ul e sănătos. Scăderea WoW va dispărea din dashboard săptămâna viitoare când ambele ferestre de comparație vor exclude outlier-ul.

**Recomandare pentru viitor:** când Daily Brief-ul raportează WoW cu delta mare (>30%), adaugă automat și vizualizarea pe 28 zile pentru context — un delta 7v7 poate fi înșelător la volume mici (<500 ev/săpt).
