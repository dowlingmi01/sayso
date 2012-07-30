@echo off
setlocal

call conf%1.bat || goto end

%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe --pack-extension=%CD%\app\development\chrome --pack-extension-key=%CD%\chromesignkey.pem --no-message-box
if %ERRORLEVEL% NEQ 0 goto end
move app\development\chrome.crx "..\public\install\chrome\%filename%.crx"
if %ERRORLEVEL% NEQ 0 goto end

pushd app\development\firefox
zip ../../../build.xpi * -r
if %ERRORLEVEL% NEQ 0 goto end
popd
move build.xpi "..\public\install\firefox\%filename%.xpi"
if %ERRORLEVEL% NEQ 0 goto end

rmdir /q /s "safaribuild\%filename%.safariextension"
if %ERRORLEVEL% NEQ 0 goto end
xcopy /s app\development\forge.safariextension\* "safaribuild\%filename%.safariextension\"
if %ERRORLEVEL% NEQ 0 goto end

pushd app\development\ie
copy ..\..\..\launchIE\Debug\launchIE.exe dist
makensis /DSAYSO_BASE_DOMAIN=%basedomain% /V3 dist\setup-x86.nsi
if %ERRORLEVEL% NEQ 0 goto end
copy /b dist\*-x86.exe "..\..\..\..\public\install\ie\%filename%-Setup.exe"
popd

goto done

:end
exit /b 1

:done
