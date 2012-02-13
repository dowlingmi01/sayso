rmdir /q /s build\%1.safariextension
if %ERRORLEVEL% NEQ 0 goto end
mkdir build\%1.safariextension
if %ERRORLEVEL% NEQ 0 goto end
copy src\* build\%1.safariextension
if %ERRORLEVEL% NEQ 0 goto end
del build\%1.safariextension\*.xxx
if %ERRORLEVEL% NEQ 0 goto end

goto done

:end
exit /b 1

:done
