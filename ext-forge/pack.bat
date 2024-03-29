@echo off
setlocal

call conf%1.bat || goto end

goto firefox

"%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" --pack-extension=%CD%\app\development\chrome --pack-extension-key=%CD%\chromebuild\%filename%.pem --no-message-box
if %ERRORLEVEL% NEQ 0 goto end
move app\development\chrome.crx "..\public\install\chrome\%filename%.crx"
if %ERRORLEVEL% NEQ 0 goto end

rem copy chromebuild\%filename%.pem app\development\chrome\key.pem
rem if %ERRORLEVEL% NEQ 0 goto end
pushd app\development\chrome
del src\*.xxx
zip ../../../build.zip * -r
if %ERRORLEVEL% NEQ 0 goto end
popd
move build.zip chromebuild\%filename%.zip
if %ERRORLEVEL% NEQ 0 goto end

:firefox
pushd app\development\firefox
del resources\f\data\src\*.xxx
zip ../../../build.xpi * -r
if %ERRORLEVEL% NEQ 0 goto end
popd
move build.xpi "..\public\install\firefox\%filename%.xpi"
if %ERRORLEVEL% NEQ 0 goto end
goto done

rmdir /q /s "safaribuild\%filename%.safariextension"
if %ERRORLEVEL% NEQ 0 goto end
xcopy /s app\development\forge.safariextension\* "safaribuild\%filename%.safariextension\"
if %ERRORLEVEL% NEQ 0 goto end
del safaribuild\%filename%.safariextension\src\*.xxx

pushd app\development\ie
del src\*.xxx
rem copy ..\..\..\launchIE\Debug\launchIE.exe dist
makensis /DSAYSO_BASE_DOMAIN=%basedomain% /V3 dist\setup-x86.nsi
if %ERRORLEVEL% NEQ 0 goto end
copy /b dist\*-x86.exe "..\..\..\..\public\install\ie\%filename%-Setup.exe"
signtool sign /t http://tsa.starfieldtech.com "..\..\..\..\public\install\ie\%filename%-Setup.exe"
popd

goto done

:end
exit /b 1

:done
