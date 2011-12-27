[CustomMessages]

en.dotnetfx20sp1lp_title=.NET Framework 2.0 SP1

en.dotnetfx20sp1lp_size=3.4 MB

;http://www.microsoft.com/globaldev/reference/lcid-all.mspx
en.dotnetfx20sp1lp_lcid=1033

en.dotnetfx20sp1lp_url=http://download.microsoft.com/download/0/8/c/08c19fa4-4c4f-4ffb-9d6c-150906578c9e/NetFx20SP1_x86.exe


[Run]
Filename: {ini:{tmp}{\}dep.ini,install,dotnetfx20sp1lp}; Description: {cm:dotnetfx20sp1lp_title}; StatusMsg: {cm:depinstall_status,{cm:dotnetfx20sp1lp_title}}; Parameters: "/q:a /c:""install /q /l"""; Flags: skipifdoesntexist

[Code]
procedure dotnetfx20sp1lp();
var
	version: cardinal;
begin
	RegQueryDWordValue(HKLM, 'Software\Microsoft\NET Framework Setup\NDP\v2.0.50727\' + CustomMessage('dotnetfx20sp1lp_lcid'), 'SP', version);
	if IntToStr(version) < '1' then
		InstallPackage('dotnetfx20sp1lp', ExpandConstant('dotnetfx20sp1_langpack_{language}.exe'), CustomMessage('dotnetfx20sp1lp_title'), CustomMessage('dotnetfx20sp1lp_size'), CustomMessage('dotnetfx20sp1lp_url'));
end;
