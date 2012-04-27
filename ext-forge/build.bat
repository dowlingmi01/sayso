@echo off
setlocal

call conf%1.bat || goto end

pushd forge-tools
set FORGE_ROOT=%CD%
call scripts\activate.bat
popd

pushd app
echo var sayso = { baseDomain: '%basedomain%', version: '2.0.0' }; > src\js\config.js
if %ERRORLEVEL% NEQ 0 goto end
call forge build || goto end
popd

goto done
:end
exit /b 1

:done
