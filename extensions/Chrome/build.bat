del /q build\*
if %ERRORLEVEL% NEQ 0 goto end
copy src\* build
if %ERRORLEVEL% NEQ 0 goto end
del build\*.xxx
if %ERRORLEVEL% NEQ 0 goto end
%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe --pack-extension=%CD%\build --pack-extension-key=%CD%\signkey.pem --no-message-box
if %ERRORLEVEL% NEQ 0 goto end
move build.crx "..\..\public\install\chrome\Say.So Starbar.crx"
if %ERRORLEVEL% NEQ 0 goto end
goto done

:end
exit /b 1

:done
