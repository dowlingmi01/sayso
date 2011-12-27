[CustomMessages]

en.dotnetfx20lp_title=.NET Framework 2.0

en.dotnetfx20lp_size=1.8 MB

;http://www.microsoft.com/globaldev/reference/lcid-all.mspx
en.dotnetfx20lp_lcid=1033

[Run]
Filename: {ini:{tmp}{\}dep.ini,install,dotnetfx20lp}; Description: {cm:dotnetfx20lp_title}; StatusMsg: {cm:depinstall_status,{cm:dotnetfx20lp_title}}; Parameters: "/q:a /c:""install /q /l"""; Flags: skipifdoesntexist

[Code]
procedure dotnetfx20lp();
var
	version: cardinal;
begin
	RegQueryDWordValue(HKLM, 'Software\Microsoft\NET Framework Setup\NDP\v2.0.50727\' + CustomMessage('dotnetfx20lp_lcid'), 'Install', version);
	if IntToStr(version) <> '1' then
		InstallPackage('dotnetfx20lp', ExpandConstant('dotnetfx20_langpack_{language}.exe'), CustomMessage('dotnetfx20lp_title'), CustomMessage('dotnetfx20lp_size'), CustomMessage('dotnetfx20lp_url'));
end;
