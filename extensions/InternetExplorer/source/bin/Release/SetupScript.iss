#include "scripts\products.iss"

#include "scripts\products\winversion.iss"
#include "scripts\products\fileversion.iss"
//#include "scripts\products\iis.iss"
#include "scripts\products\kb835732.iss"

#include "scripts\products\msi20.iss"
#include "scripts\products\msi31.iss"
#include "scripts\products\ie6.iss"

//#include "scripts\products\dotnetfx11.iss"
//#include "scripts\products\dotnetfx11lp.iss"
//#include "scripts\products\dotnetfx11sp1.iss"

#include "scripts\products\dotnetfx20.iss"
//#include "scripts\products\dotnetfx20lp.iss"
#include "scripts\products\dotnetfx20sp1.iss"
//#include "scripts\products\dotnetfx20sp1lp.iss"

//#include "scripts\products\dotnetfx35.iss"
//#include "scripts\products\dotnetfx35lp.iss"
//#include "scripts\products\dotnetfx35sp1.iss"
//#include "scripts\products\dotnetfx35sp1lp.iss"

//#include "scripts\products\mdac28.iss"
//#include "scripts\products\jet4sp8.iss"


[Files]
Source: Interop.SHDocVw.dll; DestDir: {app}
Source: Jayrock.Json.dll; DestDir: {app}
Source: KBXie-a8x15.dll; DestDir: {app}
Source: Microsoft.mshtml.dll; DestDir: {app}

[Setup]
OutputBaseFilename=saysosetup
VersionInfoCompany=Say.So LLC
VersionInfoProductName=Say.So LLC
VersionInfoProductVersion=1.0
AppName=Say.So Star Bar
AppVerName=Say.So Star Bar 1.0
DisableDirPage=true
DisableProgramGroupPage=true
UsePreviousGroup=false
OutputDir=SetupOutput
DefaultDirName={pf}\SaySoStarBar\
ShowLanguageDialog=no

[Run]
Filename: {reg:HKLM\SOFTWARE\Microsoft\.NETFramework,InstallRoot}\{reg:HKCR\CLSID\{{61b3e12b-3586-3a58-a497-7ed7c4c794b9%7D\InprocServer32\2.0.0.0,RuntimeVersion}\RegAsm.exe; Parameters: /codebase KBXie-a8x15.dll; WorkingDir: {app}; StatusMsg: Registering controls ...; Flags: runhidden

[UninstallRun]
Filename: {reg:HKLM\SOFTWARE\Microsoft\.NETFramework,InstallRoot}\{reg:HKCR\CLSID\{{61b3e12b-3586-3a58-a497-7ed7c4c794b9%7D\InprocServer32\2.0.0.0,RuntimeVersion}\RegAsm.exe; Parameters: /unregister KBXie-a8x15.dll; WorkingDir: {app}; StatusMsg: Unregistering controls ...; Flags: runhidden


[Code]
function InitializeSetup(): Boolean;
begin
	initwinversion();

	if (not minspversion(5, 0, 3)) then begin
		MsgBox(FmtMessage(CustomMessage('depinstall_missing'), [CustomMessage('win2000sp3_title')]), mbError, MB_OK);
		exit;
	end;
	if (not minspversion(5, 1, 2)) then begin
		MsgBox(FmtMessage(CustomMessage('depinstall_missing'), [CustomMessage('winxpsp2_title')]), mbError, MB_OK);
		exit;
	end;

	//if (not iis()) then exit;

	msi20('2.0');
	msi31('3.0');
	ie6('5.0.2919');

	//dotnetfx11();
	//dotnetfx11lp();
	//dotnetfx11sp1();

	kb835732();

	if (minwinversion(5, 0) and minspversion(5, 0, 4)) then begin
		dotnetfx20sp1();
		//dotnetfx20sp1lp();
	end else begin
		dotnetfx20();
		//dotnetfx20lp();
	end;

	//dotnetfx35();
	//dotnetfx35lp();
	//dotnetfx35sp1();
	//dotnetfx35sp1lp();

	//mdac28('2.7');
	//jet4sp8('4.0.8015');

	Result := true;
end;
[Languages]
Name: en; MessagesFile: compiler:Default.isl
Name: de; MessagesFile: compiler:Default.isl
