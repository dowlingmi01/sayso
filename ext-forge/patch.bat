@echo off
setlocal

echo Patching...

echo firefox-all
fc app\development\firefox\resources\f\data\forge\all.js patch\firefox-all.ori.js > nul
if %ERRORLEVEL% NEQ 0 goto end

echo firefox-main
fc app\development\firefox\resources\f\lib\main.js patch\firefox-main.ori.js > nul
if %ERRORLEVEL% NEQ 0 goto end

echo ie-setup-x86
rem fc app\development\ie\dist\setup-x86.nsi patch\ie-setup-x86.ori.nsi > nul
rem if %ERRORLEVEL% NEQ 0 goto end

echo chrome-manifest
fc app\development\chrome\manifest.json patch\chrome-manifest.ori.json > nul
if %ERRORLEVEL% NEQ 0 goto end

echo chrome-forge
fc app\development\chrome\forge.html patch\chrome-forge.ori.html > nul
if %ERRORLEVEL% NEQ 0 goto end

copy patch\firefox-all.js app\development\firefox\resources\f\data\forge\all.js
if %ERRORLEVEL% NEQ 0 goto end

copy patch\firefox-main.js app\development\firefox\resources\f\lib\main.js
if %ERRORLEVEL% NEQ 0 goto end

copy patch\ie-setup-x86.nsi app\development\ie\dist\setup-x86.nsi
if %ERRORLEVEL% NEQ 0 goto end

copy patch\chrome-manifest.json app\development\chrome\manifest.json
if %ERRORLEVEL% NEQ 0 goto end

copy patch\chrome-forge.html app\development\chrome\forge.html
if %ERRORLEVEL% NEQ 0 goto end

copy patch\sayso.ico app\development\ie\dist\noarch\forge.ico
copy patch\sayso.ico app\development\ie\dist\noarch\install.ico

goto done
:end
echo Files to patch differ
exit /b 1

:done
