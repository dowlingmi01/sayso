@echo off
setlocal

call conf%1.bat || goto end

set productversion=2.0.7

echo BaseDomain=%basedomain%> dict.txt
echo ProductVersion=%productversion%>> dict.txt
echo FileName=%filename%>> dict.txt

perl processxxx.pl dict.txt || goto end

pushd forge-tools
set FORGE_ROOT=%CD%
call scripts\activate.bat
popd

pushd app
echo var sayso = { baseDomain: '%basedomain%', version: '%productversion%' }; > src\js\config.js
if %ERRORLEVEL% NEQ 0 goto end
call forge build firefox || goto end
rem call forge build chrome || goto end
rem call forge build safari || goto end
rem call forge build ie || goto end
popd

goto done
:end
exit /b 1

:done
