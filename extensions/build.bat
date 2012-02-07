@echo off
setlocal

set alldomains=staging qa dev demo prod
set allbrowsers=Chrome Firefox InternetExplorer Safari

set domains=%1
set browsers=%2

if "%domains%" == "" set domains=%alldomains%
if "%domains%" == "all" set domains=%alldomains%

if "%browsers%" == "" set browsers=%allbrowsers%
if "%browsers%" == "all" set browsers=%allbrowsers%

for %%d in (%domains%) do ( 
   perl processxxx.pl dict.txt dict-%%d.txt || goto end
   for %%b in (%browsers%) do (
     pushd %%b
     call build.bat %%d || goto end
     popd
   )
)

:end
