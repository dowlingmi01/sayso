@echo off
setlocal

set domains=staging qa dev demo prod local

for %%d in (%domains%) do ( 
   call build.bat %%d || goto end
   call patch.bat || goto end
   call pack.bat %%d || goto end
)

:end
