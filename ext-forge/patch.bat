@echo off
setlocal

echo Patching...

fc app\development\firefox\resources\f\data\forge\all.js patch\firefox-all.ori.js > nul
if %ERRORLEVEL% NEQ 0 goto end

fc app\development\firefox\resources\f\lib\main.js patch\firefox-main.ori.js > nul
if %ERRORLEVEL% NEQ 0 goto end

fc app\development\ie\dist\setup-x86.nsi patch\ie-setup-x86.ori.nsi > nul
if %ERRORLEVEL% NEQ 0 goto end

copy patch\firefox-all.js app\development\firefox\resources\f\data\forge\all.js
if %ERRORLEVEL% NEQ 0 goto end

copy patch\firefox-main.js app\development\firefox\resources\f\lib\main.js
if %ERRORLEVEL% NEQ 0 goto end

copy patch\ie-setup-x86.nsi app\development\ie\dist\setup-x86.nsi
if %ERRORLEVEL% NEQ 0 goto end

copy patch\sayso.ico app\development\ie\dist\noarch\forge.ico
copy patch\sayso.ico app\development\ie\dist\noarch\install.ico

goto done
:end
echo Files to patch differ
exit /b 1

:done
