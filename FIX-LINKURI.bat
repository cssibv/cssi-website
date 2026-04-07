@echo off
chcp 65001 >nul
cd /d "C:\Users\Diaconu Mihai\Documents\Website\cssi-website"
echo.
echo ============================================
echo   CSSI - Fix linkuri .html in toate paginile
echo ============================================
echo.

set "count=0"
for %%f in (index.html camere-supraveghere.html alarma-antiefractie.html alarma-antiefractie-brasov.html detectie-incendiu-isu.html control-acces.html automatizari-porti.html bariere-auto.html pontaj-electronic.html interfoane-videointerfoane.html sonorizare.html usi-garaj.html instalatii-electrice.html instalatii-termice-sanitare.html aer-conditionat.html ventilatie.html despre-noi.html portofoliu.html contact.html servicii.html camere-supraveghere-ploiesti.html) do (
    if exist "%%f" (
        echo   Procesez: %%f
        powershell -Command "(Get-Content '%%f' -Raw -Encoding UTF8) -replace 'href=\"index\.html\"','href=\"/\"' -replace 'href=\"contact\.html\"','href=\"contact\"' -replace 'href=\"despre-noi\.html\"','href=\"despre-noi\"' -replace 'href=\"portofoliu\.html\"','href=\"portofoliu\"' -replace 'href=\"servicii\.html\"','href=\"servicii\"' -replace 'href=\"blog/index\.html\"','href=\"blog/\"' -replace 'href=\"detectie-incendiu-isu\.html\"','href=\"detectie-incendiu-isu\"' -replace 'href=\"camere-supraveghere\.html\"','href=\"camere-supraveghere\"' -replace 'href=\"alarma-antiefractie\.html\"','href=\"alarma-antiefractie\"' -replace 'href=\"control-acces\.html\"','href=\"control-acces\"' -replace 'href=\"automatizari-porti\.html\"','href=\"automatizari-porti\"' -replace 'href=\"bariere-auto\.html\"','href=\"bariere-auto\"' -replace 'href=\"interfoane-videointerfoane\.html\"','href=\"interfoane-videointerfoane\"' -replace 'href=\"sonorizare\.html\"','href=\"sonorizare\"' -replace 'href=\"usi-garaj\.html\"','href=\"usi-garaj\"' -replace 'href=\"instalatii-electrice\.html\"','href=\"instalatii-electrice\"' -replace 'href=\"instalatii-termice-sanitare\.html\"','href=\"instalatii-termice-sanitare\"' -replace 'href=\"aer-conditionat\.html\"','href=\"aer-conditionat\"' -replace 'href=\"ventilatie\.html\"','href=\"ventilatie\"' -replace 'href=\"pontaj-electronic\.html\"','href=\"pontaj-electronic\"' -replace 'cssi\.ro/servicii\.html','cssi.ro/servicii' -replace 'cssi\.ro/camere-supraveghere\.html','cssi.ro/camere-supraveghere' -replace 'cssi\.ro/alarma-antiefractie\.html','cssi.ro/alarma-antiefractie' -replace 'cssi\.ro/detectie-incendiu-isu\.html','cssi.ro/detectie-incendiu-isu' -replace 'cssi\.ro/control-acces\.html','cssi.ro/control-acces' -replace 'cssi\.ro/automatizari-porti\.html','cssi.ro/automatizari-porti' -replace 'cssi\.ro/bariere-auto\.html','cssi.ro/bariere-auto' -replace 'cssi\.ro/interfoane-videointerfoane\.html','cssi.ro/interfoane-videointerfoane' -replace 'cssi\.ro/sonorizare\.html','cssi.ro/sonorizare' -replace 'cssi\.ro/usi-garaj\.html','cssi.ro/usi-garaj' -replace 'cssi\.ro/instalatii-electrice\.html','cssi.ro/instalatii-electrice' -replace 'cssi\.ro/instalatii-termice-sanitare\.html','cssi.ro/instalatii-termice-sanitare' -replace 'cssi\.ro/aer-conditionat\.html','cssi.ro/aer-conditionat' -replace 'cssi\.ro/ventilatie\.html','cssi.ro/ventilatie' -replace 'cssi\.ro/pontaj-electronic\.html','cssi.ro/pontaj-electronic' -replace 'cssi\.ro/contact\.html','cssi.ro/contact' -replace 'cssi\.ro/despre-noi\.html','cssi.ro/despre-noi' -replace 'cssi\.ro/portofoliu\.html','cssi.ro/portofoliu' | Set-Content '%%f' -Encoding UTF8"
        set /a count+=1
    )
)

echo.
echo ============================================
echo   GATA! %count% fisiere procesate.
echo   Acum fa git commit + push pe cPanel.
echo ============================================
echo.
pause
