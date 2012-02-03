[CustomMessages]

;http://www.microsoft.com/globaldev/reference/lcid-all.mspx
en.dotnetfx35sp1lp_lcid=1033

[Run]
Filename: {ini:{tmp}{\}dep.ini,install,dotnetfx35sp1lp}; Description: {cm:dotnetfx35sp1lp_title}; StatusMsg: {cm:depinstall_status,{cm:dotnetfx35sp1lp_title}}; Parameters: /lang:enu /quiet /norestart; Flags: skipifdoesntexist

[Code]
procedure dotnetfx35sp1lp();
var
	version: cardinal;
begin
	RegQueryDWordValue(HKLM, 'Software\Microsoft\NET Framework Setup\NDP\v3.5\' + CustomMessage('dotnetfx35sp1lp_lcid'), 'SP', version);
	if IntToStr(version) < '1' then
		InstallPackage('dotnetfx35sp1lp', 'dotnetfx35sp1lp.exe', CustomMessage('dotnetfx35sp1lp_title'), CustomMessage('dotnetfx35sp1lp_size'), CustomMessage('dotnetfx35sp1lp_url'));
end;
