C:\WINDOWS\Microsoft.NET\Framework\v3.5\Csc.exe /noconfig /nowarn:1701,1702 /errorreport:prompt /warn:4 /define:TRACE /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\Accessibility.dll /reference:BHO\Jayrock.Json.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Data.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Windows.Forms.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Xml.dll /reference:BHO\Microsoft.mshtml.dll /reference:obj\Release\Interop.SHDocVw.dll /debug:pdbonly /filealign:512 /optimize+ /out:obj\Release\SaySo.dll /target:library BHO\BHO.cs BHO\IObjectWithSite.cs Properties\AssemblyInfo.cs
if %ERRORLEVEL% NEQ 0 goto end


copy obj\Release\SaySo.dll bin\Release\SaySo.dll
if %ERRORLEVEL% NEQ 0 goto end

cd bin\Release
C:\"Program Files (x86)"\"Inno Setup 5"\iscc.exe SetupScript.iss"
if %ERRORLEVEL% NEQ 0 goto end
cd ..\..
goto done

:end

REM exit 5

:done