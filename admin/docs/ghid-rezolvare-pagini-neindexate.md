# Ghid Rezolvare Pagini Neindexate — CSSI

**Data:** 26 Mai 2026  
**Situație actuală:** 56 pagini indexate, 85 neindexate din 141 total

---

## Problema principală: 53 pagini "Descoperite – nu sunt indexate"

Google a găsit aceste pagini (prin sitemap) dar NU le-a indexat. Motivul cel mai probabil: **conținut prea similar** între paginile localizate (ex: camere-supraveghere-ghimbav, camere-supraveghere-codlea, etc. au text aproape identic doar cu numele orașului schimbat).

### Ce trebuie făcut (pe termen mediu):

1. **Adaugă conținut unic pe fiecare pagină localizată** — cel puțin 2-3 paragrafe specifice orașului (distanță de la Brașov, tipuri de clienți din zonă, proiecte finalizate acolo)
2. **Adaugă imagini unice** — poze de la proiecte din zona respectivă
3. **Internal linking** — link-uri între pagini (ex: de pe camere-supraveghere-ghimbav link la alarma-antiefractie-ghimbav)

---

## 21 pagini cu redirecționare (A eșuat)

Acestea sunt probabil redirecțări vechi de la breaksistems.ro → cssi.ro. Validarea "A eșuat" înseamnă că Google a încercat să le re-verifice și redirecțarea nu mai funcționează corect.

### Acțiune: 
Verifică în GSC > Indexare > Pagini > click pe "Pagină cu redirecționare" pentru a vedea lista exactă de URL-uri. Cele care sunt redirecțări corecte (301) pot fi ignorate. Cele care dau eroare trebuie reparate în .htaccess.

---

## 8 pagini "Accesate cu crawlere – nu sunt indexate"

Google le-a citit dar a decis că nu merită indexate. Pot fi pagini cu conținut subțire sau duplicat.

### Acțiune:
Verifică lista în GSC. Dacă sunt pagini importante, adaugă mai mult conținut unic pe ele.

---

## 1 pagină 404

O pagină returnează eroare 404.

### Acțiune:
Verifică în GSC > Indexare > Pagini > click pe "Nu a fost găsită (404)" pentru a vedea URL-ul exact. Dacă pagina trebuie să existe, creează-o. Dacă nu, adaugă redirect 301 în .htaccess.

---

## 1 pagină blocată de robots.txt

Probabil o pagină din admin/ sau altă secțiune protejată.

### Acțiune:
Verifică în GSC. Dacă e admin/ sau altă pagină privată, e corect să fie blocată. Dacă e o pagină publică, scoate-o din restricția robots.txt.

---

## PAȘI IMEDIAȚI — Solicitare Indexare Manuală (top 10 pagini)

Deschide Google Search Console → Bara de sus "Inspectează orice URL" → paste fiecare URL → click "Solicită indexarea"

### Pagini prioritare pentru indexare manuală:

1. `https://cssi.ro/` (homepage)
2. `https://cssi.ro/camere-supraveghere` 
3. `https://cssi.ro/pontaj-electronic`
4. `https://cssi.ro/alarma-antiefractie`
5. `https://cssi.ro/control-acces`
6. `https://cssi.ro/automatizari-porti`
7. `https://cssi.ro/camere-supraveghere-brasov`
8. `https://cssi.ro/pontaj-electronic-brasov`
9. `https://cssi.ro/alarma-antiefractie-brasov`
10. `https://cssi.ro/pentru-firme`
11. `https://cssi.ro/servicii`
12. `https://cssi.ro/calculator-pret`

**Limită:** Google permite ~10-12 solicitări/zi. Fă câte 10 pe zi timp de o săptămână.

### După indexare manuală, continuă cu articolele de blog:

13. `https://cssi.ro/blog/pontaj-electronic-ghid-complet`
14. `https://cssi.ro/blog/camere-supraveghere-firma-ghid-complet`
15. `https://cssi.ro/blog/control-acces-firma-ghid-alegere-2026`
16. `https://cssi.ro/blog/hikvision-vs-dahua-comparatie-completa-2026`
17. `https://cssi.ro/blog/ajax-vs-paradox-vs-dsc-comparatie-alarme`
18. `https://cssi.ro/blog/cost-sistem-securitate-complet-2026`
19. `https://cssi.ro/blog/studiu-de-caz-anaf-control-acces-turnichete`
20. `https://cssi.ro/blog/studiu-de-caz-alexandrion-securitate`

---

## Ce s-a făcut deja (26 mai 2026):

- ✅ Sitemap.xml actualizat cu lastmod = 2026-05-26 (semnal freshness)
- ✅ Comentarii HTML curățate din sitemap
- ✅ Meta tags optimizate pe 8 pagini principale (title + description mai atractive)
- ✅ Eliminat "detecție incendiu" din meta tags index.html și servicii.html
- ✅ Robots.txt verificat — corect, permite totul

---

## Monitorizare

Verifică progresul indexării săptămânal în GSC > Indexare > Pagini. Obiectiv: 80+ pagini indexate în 30 de zile.
