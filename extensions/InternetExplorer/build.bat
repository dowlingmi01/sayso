%SystemRoot%\Microsoft.NET\Framework\v3.5\Csc.exe /noconfig /nowarn:1701,1702 /errorreport:prompt /warn:4 /define:TRACE /reference:%SystemRoot%\Microsoft.NET\Framework\v2.0.50727\System.Windows.Forms.dll /reference:lib\Microsoft.mshtml.dll /reference:lib\Interop.SHDocVw.dll /debug:pdbonly /filealign:512 /optimize+ /out:obj\SaySo.dll /target:library src\BHO.cs src\IObjectWithSite.cs src\AssemblyInfo.cs
if %ERRORLEVEL% NEQ 0 goto end

cd setup
C:\"Program Files (x86)"\"Inno Setup 5"\iscc.exe SetupScript.iss
if %ERRORLEVEL% NEQ 0 goto end
cd ..

move build.exe "..\..\public\install\ie\Say.So Starbar-Setup.exe"
if %ERRORLEVEL% NEQ 0 goto end

goto done

:end
exit /b 1

:done