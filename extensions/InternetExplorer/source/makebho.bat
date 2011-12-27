C:\WINDOWS\Microsoft.NET\Framework\v3.5\Csc.exe /noconfig /nowarn:1701,1702 /errorreport:prompt /warn:4 /define:TRACE /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\Accessibility.dll /reference:BHO\Jayrock.Json.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Data.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Windows.Forms.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Xml.dll /reference:BHO\Microsoft.mshtml.dll /reference:obj\Release\Interop.SHDocVw.dll /debug:pdbonly /filealign:512 /optimize+ /out:obj\Release\KBXie-a8x15.dll /target:library BHO\BHO.cs BHO\IObjectWithSite.cs Properties\AssemblyInfo.cs
if %ERRORLEVEL% NEQ 0 goto end

REM C:\Program Files\Microsoft SDKs\Windows\v6.0A\bin\sgen.exe /assembly:"C:\Documents and Settings\Administrator\My Documents\Visual Studio 2008\Projects\KBXie\KBXie\obj\Release\KBXie.dll" /proxytypes /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\Accessibility.dll /reference:BHO\Jayrock.Json.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Data.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Windows.Forms.dll /reference:C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727\System.Xml.dll /reference:C:\WINDOWS\assembly\GAC\Microsoft.mshtml\7.0.3300.0__b03f5f7f11d50a3a\Microsoft.mshtml.dll /reference:obj\Release\Interop.SHDocVw.dll 

copy obj\Release\KBXie-a8x15.dll bin\Release\KBXie-a8x15.dll
if %ERRORLEVEL% NEQ 0 goto end

cd bin\Release
C:\"Program Files"\"Inno Setup 5"\iscc.exe SetupScript.iss"
if %ERRORLEVEL% NEQ 0 goto end
cd ..\..
goto done

:end

exit 5

:done