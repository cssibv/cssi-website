# Audit de structură cssi.ro — constatări

**Data:** 21 iulie 2026 · **Context:** analiză declanșată de raportul strategic W30
**Status:** analiză, fără implementare. Deciziile rămân deschise.

---

## Rezumat

Trei constatări care nu apar în raportul săptămânal W30 și care **schimbă ordinea priorităților** stabilite acolo.

| # | Constatare | Severitate |
|---|---|---|
| 1 | 64 de pagini de localitate, 87–94% identice, ~50% din site-ul indexat | 🔴 Structurală |
| 2 | Blogul produce traficul, nu paginile de serviciu | 🟡 Strategică |
| 3 | Pagina de pontaj acoperă modalitățile, dar cu altă terminologie decât caută oamenii | 🟢 Tactică |

**Ce răstoarnă din raportul W30:** raportul recomanda investiție în paginile de serviciu și localitate. Datele arată că acelea nu produc, iar blogul da.

---

## Constatarea 1 — Duplicare masivă pe paginile de localitate

### Metodă

Am extras textul din fiecare pagină (fără script/style/taguri), am **neutralizat numele localității**, apoi am calculat similaritatea Jaccard între perechile din fiecare cluster. Neutralizarea contează: fără ea, similaritatea ar părea mai mică decât e în realitate.

### Rezultate

| Serviciu | Pagini | Similaritate medie | Cuvinte/pagină |
|---|---|---|---|
| camere-supraveghere | 11 | **94%** | ~730 |
| alarma-antiefractie | 10 | 87% | ~731 |
| aer-conditionat | 8 | **94%** | ~718 |
| usi-garaj | 8 | **94%** | ~726 |
| instalatii-electrice | 8 | **94%** | ~707 |
| automatizari-porti | 8 | — | — |
| control-acces / bariere-auto / management-parcari | 6 | — | — |
| **TOTAL** | **64** | | |

Perechile cele mai apropiate din clusterul camere:

```
97%  harman   <->  sanpetru
96%  codlea   <->  ghimbav
96%  codlea   <->  rasnov
96%  rasnov   <->  sanpetru
95%  ghimbav  <->  rasnov
```

97% similaritate înseamnă că `camere-supraveghere-harman` și `camere-supraveghere-sanpetru` sunt **aceeași pagină cu alt oraș**.

### Proporția din site

**64 din 129 URL-uri** din `sitemap.xml` sunt pagini de localitate. Aproximativ jumătate din site-ul indexat.

### Excepții

Patru pagini au conținut substanțial mai bogat și **nu** intră în tipar:
`camere-supraveghere-brasov` (979 cuvinte), `ploiesti` (811), `targu-mures` (808), `sibiu` (779).

Restul de 7 din clusterul camere sunt între 728 și 732 de cuvinte — o variație de 4 cuvinte pe 7 pagini.

### Ce e verificat vs. ce e judecată

**Verificat:** numărul de pagini, gradul de similaritate, numărul de cuvinte, proporția din sitemap, absența lor din topul de performanță.

**Judecată profesională, nu fapt dovedit pentru acest site:** că duplicarea diluează bugetul de crawl și autoritatea internă, și că reprezintă un risc latent. Politicile Google numesc explicit *doorway pages* și *scaled content abuse*, iar tiparul se potrivește. Dar **nu există dovada unei penalizări active** — site-ul rankează normal pe alți termeni.

**Nuanță importantă:** intenția e legitimă. CSSI chiar deservește localitățile acelea. Problema e execuția, nu scopul.

---

## Constatarea 2 — Blogul duce site-ul

Top 10 pagini după clicuri, Search Console, ultimele 28 de zile:

| Pagină | Clicuri | Afișări | Poziție |
|---|---|---|---|
| `blog/siguranta-sare-cauze-solutii-instalatie-electrica` | **19** | 1.599 | 7,1 |
| `/` (homepage) | 15 | 1.336 | 7,9 |
| `blog/analiza-risc-securitate-fizica-ghid-complet` | 13 | 502 | 19,9 |
| `blog/instalare-camere-supraveghere-brasov-cost-firma-2026` | 11 | 458 | 8,8 |
| `blog/camere-supraveghere-gdpr-ghid-complet` | 11 | 335 | 9,0 |
| `/automatizari-porti` | 9 | 135 | 15,5 |
| `blog/securitate-depozit-logistica-ghid-complet` | 6 | 172 | 8,2 |
| `blog/detectie-incendiu-pret-ghid-complet` | 4 | 230 | 5,1 |
| `blog/mentenanta-sisteme-securitate-de-ce-conteaza` | 3 | 168 | 8,0 |
| `/aer-conditionat` | 3 | 125 | 24,2 |

**Opt din zece sunt articole de blog. Zero pagini de localitate.**

110 pagini primesc afișări, din 129 în sitemap.

### Implicație

Efortul de conținut are randament demonstrat pe blog și randament zero pe paginile de localitate. Raportul W30 recomanda invers.

> ⚠️ Observație secundară: `blog/detectie-incendiu-pret-ghid-complet` primește 230 afișări și **nu este noindexat**, deși pagina de serviciu `detectie-incendiu-isu.html` este exclusă intenționat din marketingul CSSI. Inconsecvență de verificat — fie articolul ar trebui și el exclus, fie regula nu se aplică blogului.

---

## Constatarea 3 — Pontaj: nepotrivire de terminologie

`pontaj-electronic.html` (637 linii) **acoperă deja** modalitățile, cu subtitluri h3:

`Amprentă Digitală` · `Recunoaștere Facială` · `Card Proximitate` · `Cod PIN` · `Pontaj Mobil (App)`

**Recomandarea mea anterioară — „adaugă secțiuni pentru modalități" — era greșită. Există.**

Problema reală e alta:

| Ce caută oamenii | Afișări | Poziție | Ce scrie pe pagină |
|---|---|---|---|
| `pontaj electronic cu cartela` | 18 | 15,1 | „Card Proximitate" |
| `pontaj electronic cu amprenta` | 17 | 10,1 | „Amprentă Digitală" |
| `pontaj electronic facial` | 18 | 17,2 | „Recunoaștere Facială" |
| `dispozitive pontaj fara instalare` | 18 | 11,9 | **lipsește complet** |

Verificare: sintagma „fara instalare" apare de **0 ori** în pagină.

Ajustarea e de nivel semantic, nu de conținut nou. ~30 de minute.

---

## Opțiuni de acțiune

| Opțiune | Efort | Reversibilitate | Impact estimat |
|---|---|---|---|
| **B — Aliniere terminologie pontaj** | 30 min | Totală | 71 afișări vizate, poziții 10–17 |
| **D — Title/meta `automatizari-porti`** | 20 min | Totală | 135 afișări la poziția 15,5 |
| **C — Prioritizare blog** | continuu | — | Randament demonstrat |
| **A — Curățare duplicate** | vezi mai jos | Depinde de metodă | Cea mai mare pârghie |

### Despre opțiunea A

Trei metode, în ordinea riscului:

**1. `noindex` pe paginile fără afișări** — reversibil dintr-o linie. Paginile rămân pentru utilizatori și pentru linkuri interne. Necesită întâi extragerea din GSC a listei exacte de pagini cu zero afișări.

**2. Diferențiere reală a conținutului** — rescriere cu proiecte locale concrete, particularități, referințe. Corect, dar 64 de pagini × câteva ore = nefezabil pe termen scurt.

**3. Consolidare cu 301** — păstrezi Brașov + 3-4 localități majore, redirectezi restul. Cel mai curat rezultat, dar **ireversibil**.

**Recomandarea mea:** metoda 1 întâi, măsurare 6–8 săptămâni, apoi decizie informată între 2 și 3. Nu recomand să începi cu 301 — nu avem încă dovada că duplicarea e cauza, doar corelația.

---

## Secvențiere recomandată

| Ordine | Ce | De ce în ordinea asta |
|---|---|---|
| 1 | B + D (aliniere pontaj, meta automatizări) | Risc zero, câștig imediat, nu depind de nimic |
| 2 | Extragere listă pagini cu zero afișări din GSC | Necesară înainte de orice decizie pe A |
| 3 | A, metoda `noindex` | După ce știm exact care pagini |
| 4 | Măsurare 6–8 săptămâni | Urcă paginile rămase? |
| 5 | Decizie finală: rescriere sau consolidare 301 | Pe bază de date, nu de ipoteză |

---

## Cum se măsoară efectul

**Pentru A:** Search Console → Performanță → Pagini. Urmărește poziția medie a paginilor **rămase indexate** (`camere-supraveghere-brasov`, `alarma-antiefractie-brasov` etc.). Dacă urcă după 6–8 săptămâni, ipoteza s-a confirmat.

**Pentru B:** poziția celor patru interogări de pontaj. Ținta: `pontaj electronic cu amprenta` de la 10,1 în top 5.

**Pentru D:** `automatizari porti brasov`, acum 11,3.

**Atenție la interpretare:** orice comparație cu perioada anterioară datei de 20.07.2026 e distorsionată de corecția dublei numărări a conversiilor. Nu se aplică pozițiilor organice, dar se aplică oricărei metrici de conversie.

---

*Audit realizat 21.07.2026 · fără modificări aplicate pe site*
