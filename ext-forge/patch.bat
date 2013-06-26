@echo off
setlocal

echo Patching...

for /f %%f in (patch.txt) do (echo %%f & fc app\development\%%f patch\ori\%%f > nul || goto end)
for /f %%f in (patch.txt) do (copy patch\new\%%f app\development\%%f || goto end)

copy patch\sayso.ico app\development\ie\dist\noarch\forge.ico
copy patch\sayso.ico app\development\ie\dist\noarch\install.ico

goto done
:end
echo Files to patch differ
exit /b 1

:done
