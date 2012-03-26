del /q build\*
if %ERRORLEVEL% NEQ 0 goto end

for /d %%i in (build\*) do rmdir /q /s %%i
if %ERRORLEVEL% NEQ 0 goto end

if exist build.xpi del build.xpi
if %ERRORLEVEL% NEQ 0 goto end

xcopy src build /i /e
if %ERRORLEVEL% NEQ 0 goto end

del /s build\*.xxx
if %ERRORLEVEL% NEQ 0 goto end

cd build
zip ../build.xpi * -r
if %ERRORLEVEL% NEQ 0 goto end
cd ..

move build.xpi "..\..\public\install\firefox\Say.So Starbar.xpi"
if %ERRORLEVEL% NEQ 0 goto end

goto done

:end
exit /b 1

:done