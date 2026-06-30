# 📊 CSSI — Raport Zilnic de Monitorizare
**Data:** 2026-06-18 (joi) · **Săptămâna:** W25 · **Cont Ads:** 666-033-6562

---

## TL;DR
- ✅ **Ce merge bine:** Conform ultimei capturi valide (17 iun.), trendul GA4 era pozitiv — sesiuni +22,9% (59), Organic Search motor principal (35 sesiuni), evenimente importante urcate la 3.
- ❌ **Ce nu merge:** Astăzi **nu s-au putut capta date live** — accesul la platforme prin browser necesită selecția manuală a unei extensii Chrome, imposibil într-o rulare automată nesupravegheată. Problemele structurale rămân deschise: urmărire conversii deficitară, CTR Ads 8,35% sub țintă, funnel leak (`form_start` = 0).
- 🎯 **Acțiunea zilei:** Deschide manual Google Ads/GA4/GSC într-o sesiune asistată pentru a permite captarea zilnică; între timp, rezolvă urmărirea conversiilor (etichetă inactivă + `form_start` 0) — blocaj critic pentru ROI și migrarea la Maximize Conversions.

---

## ⚠️ Stare acces platforme (18 iun.)

| Platformă | Status | Detaliu |
|---|---|---|
| Google Ads | ⚠️ **BLOCAT** | Captarea live necesită alegerea manuală a unei extensii Chrome (2 browsere conectate: „Browser 1" și „Mihai laptop"). Într-o rulare programată nesupravegheată nu pot selecta browserul în locul utilizatorului. |
| Google Analytics 4 | ⚠️ **BLOCAT** | Idem — depinde de aceeași sesiune browser. |
| Google Search Console | ⚠️ **BLOCAT** | Idem — depinde de aceeași sesiune browser. |

> **De ce:** Politica de securitate cere ca utilizatorul să confirme explicit care extensie Chrome se folosește. Acest pas nu poate fi automatizat. **Soluție:** rulează raportul într-o sesiune Cowork în care ești prezent să confirmi browserul, SAU lasă o sesiune Chrome autentificată activă și aprobă o singură dată selecția. Datele de mai jos sunt **reportate din ultima captură validă (17 iun.)** și marcate ca atare — NU sunt date noi de azi.

---

## 🟦 Google Ads — ultima captură validă (17 iun., fereastra 10–16 iun.)
*Date reportate, nu live azi.*

| Metrică | Valoare (17 iun.) | Observație |
|---|---|---|
| Afișări | 491 | trend ascendent |
| Clicuri | 41 | |
| **CTR** | **8,35%** | 🔻 sub ținta 15–18% |
| CPC mediu | 3,56 RON | |
| Cost (7 zile) | 145,79 RON | ⚠️ ~20,8 RON/zi → proiecție ~625 RON/lună (peste ținta ~300) |
| Valoare conv./Cost | 2,06 | |
| Sold cont | 342,49 RON | următoarea plată: 1 iul. 2026 |

**Concentrare buget:** 73% din cost (106 RON) pe un singur cuvânt cheie — „montaj camere supraveghere". Restul temelor (alarme / control acces / pontaj / detecție incendiu) nu generează volum în Ads.

---

## 🟧 GA4 — ultima captură validă (17 iun., 7 zile)
*Date reportate, nu live azi.*

| Metrică | Valoare | vs. anterior |
|---|---|---|
| Utilizatori activi | 38 | 🔺 +5,0% |
| Utilizatori noi | 33 | 🔺 +10,8% |
| Evenimente importante | 3 | 🔺 +25,0% |
| Sesiuni | 59 | 🔺 +22,9% |

**Surse:** Organic Search 35 (+84,2%) · Paid Search 15 (+40%) · Direct 5 · Cross-network 2 · Unassigned 2.
**Funnel leak:** `form_start` = 0; niciun `phone_call`/`whatsapp_click`/`form_submit` în top → urmărire probabil ruptă.

---

## 🟩 Google Search Console — ultima captură validă (17 iun., 09–15 iun.)
*Date reportate, nu live azi.*

| Metrică | Valoare |
|---|---|
| Clicuri | 21 |
| Afișări | ~1,02 K |
| CTR mediu | 2,1% |
| Poziție medie | 13,1 |

**Keywords țintă:** camere supraveghere brasov 7,6 ✅ · camere de supraveghere brasov 8,8 ✅ · sistem pontaj electronic 19,8 🔴 · pontaj electronic cu amprenta 13,0 ✅ · control acces / alarmă antiefracție / detecție incendiu brasov — fără ranking 🔴.
**Oportunitate (poz. 11–20, 0 clicuri):** familia „pontaj electronic" — 49+ afișări, 0 clicuri → problemă CTR/titlu în SERP.

---

## 🚨 Alerte (deschise, neschimbate față de 17 iun.)

1. **⚠️ BLOCAT: captare date live** — toate cele 3 platforme inaccesibile azi din cauza selecției manuale obligatorii a browserului. Necesită rulare asistată.
2. **⚠️ Urmărire conversii deficitară** — 1 etichetă inactivă + 7 acțiuni fără conversii recente; `form_start` = 0 în GA4. Blochează Maximize Conversions și măsurarea ROI.
3. **⚠️ Ad group fără anunțuri** — un grup nu difuzează (impact estimat +16,5% afișări).
4. **⚠️ Pacing buget** — proiecție ~625 RON/lună vs. ținta ~300 RON/lună.
5. **🔻 CTR sub țintă** — 8,35% vs. 15–18%.
6. **🗑️ Candidați negative keywords** — `oogis`, `spy shop`, `rovision`, `telesystem`, `ultra security`, `tapo c310`, `v380 camera`.

---

## ✅ Acțiuni propuse azi (18 iun.)

1. **Deblochează captarea automată a datelor** *(context: rularea programată nu poate alege browserul; efort: 5 min o singură dată — pornește/autentifică Chrome și aprobă selecția extensiei la următoarea rulare asistată, sau rulează raportul manual din Cowork; impact: restabilește monitorizarea zilnică completă).*
2. **Verifică urmărirea conversiilor** *(context: etichetă inactivă + `form_start` 0 → conversiile reale nu se înregistrează; efort: 30–45 min — testează butoanele apel/WhatsApp/formular și importul GA4→Ads; impact: CRITIC pentru optimizare și migrare la Maximize Conversions).*
3. **Exclude branduri concurente ca negative keywords** *(`oogis`, `spy shop`, `rovision`, `telesystem`, `ultra security`; efort: 10 min; impact: trafic mai curat, CTR/CPA mai bune).*

---

## 📌 Task-uri pending (status neschimbat)
- **#15** Cleanup keywords „Low search volume" — în așteptare.
- **#16** Restructurare în 3 Ad Groups tematice (Camere / Alarme+Acces+Pontaj / Detecție Incendiu) — justificat de concentrarea de 73% buget pe un keyword.
- **#17** Creare 3 RSA-uri noi (15 titluri + 4 descrieri fiecare) — necesar pentru a urca CTR-ul către țintă.

---

*Notă autonomie: raport generat automat (utilizatorul nu a fost prezent). Captarea live a fost BLOCATĂ — selecția extensiei Chrome necesită confirmare umană, imposibilă într-o rulare nesupravegheată. Toate cifrele de mai sus sunt reportate din ultima captură validă (17 iun. 2026) și marcate explicit; nu reflectă starea de azi. Următorul raport strategic săptămânal: luni 22 iun. 2026.*
