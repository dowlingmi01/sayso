[CustomMessages]

dotnetfx35lp_title=.NET Framework 3.5

;http://www.microsoft.com/globaldev/reference/lcid-all.mspx
en.dotnetfx35lp_lcid=1033

[Run]
Filename: {ini:{tmp}{\}dep.ini,install,dotnetfx35lp}; Description: {cm:dotnetfx35lp_title}; StatusMsg: {cm:depinstall_status,{cm:dotnetfx35lp_title}}; Parameters: /lang:enu /quiet /norestart; Flags: skipifdoesntexist

[Code]
procedure dotnetfx35lp();
var
	version: cardinal;
begin
	RegQueryDWordValue(HKLM, 'Software\Microsoft\NET Framework Setup\NDP\v3.5\' + CustomMessage('dotnetfx35lp_lcid'), 'Install', version);
	if IntToStr(version) <> '1' then
		InstallPackage('dotnetfx35lp', 'dotnetfx35lp.exe', CustomMessage('dotnetfx35lp_title'), CustomMessage('dotnetfx35lp_size'), CustomMessage('dotnetfx35lp_url'));
end;
