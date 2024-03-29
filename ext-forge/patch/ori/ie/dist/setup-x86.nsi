Name "Say.So"

# Included files
!addplugindir "plugins"
!include "Library.nsh"
!include "MUI.nsh"
!include "helpers.nsh"
!include "LogicLib.nsh"
!include "FileFunc.nsh"

# Paths
!define BUILD_DIR "..\build\Win32\Release"

# Defines
!define PRODUCT_EXECUTE_FILE "bho32.dll"
!define FORGE_EXECUTE_FILE "forge32.dll"

# MUI defines 

!define MUI_ICON   "noarch\install.ico"
!define MUI_UNICON "noarch\uninstall.ico"
!define MUI_FINISHPAGE_NOAUTOCLOSE

# Installer pages
Page directory
Page instfiles

# Installer languages
!insertmacro MUI_LANGUAGE English

# Installer attributes
RequestExecutionLevel admin
OutFile "Say.So-2.0.6-x86.exe"
InstallDir "$PROGRAMFILES\Say.So"
DirText "This will install Say.So on your computer. Choose a directory"
CRCCheck on
XPStyle on

ShowInstDetails show
!define VERSION_SHORT "2.0.6"
${VersionCompleteXXXX} ${VERSION_SHORT} VIPV
VIProductVersion ${VIPV}
VIAddVersionKey ProductName "Say.So"
VIAddVersionKey ProductVersion "2.0.6"
VIAddVersionKey CompanyName "Say.So LLC"
VIAddVersionKey LegalCopyright "(c) Say.So LLC"
VIAddVersionKey CompanyWebsite "http://www.say.so/"
VIAddVersionKey FileVersion "2.0.6"
VIAddVersionKey FileDescription "Say.So"

# Installer 
Section 
    ; Check that installer is being run as Administrator
    UserInfo::GetAccountType
    pop $0  
    ${If} $0 != "admin" 
        MessageBox mb_iconstop "Administrator rights required"
        SetErrorLevel 740 ; ERROR_ELEVATION_REQUIRED
        Quit
    ${EndIf}

    ; Close IE
    Push "IEFrame"
    Exch $1
    Push $0
    FindWindow $0 $1
    IntCmp $0 0 installation
    MessageBox MB_OKCANCEL|MB_ICONEXCLAMATION "Internet Explorer is currently running. Please save your work and close all windows before proceeding." IDCANCEL cancel
     
    loop:
        FindWindow $0 $1
        IntCmp $0 0 installation
        SendMessage $0 ${WM_CLOSE} 0 0
        sleep 1000
    Goto loop
    
    installation:       
        ; install files
        CreateDirectory "$INSTDIR"
        SetOutPath "$INSTDIR"
        SetOverwrite on
        File "noarch\defaults"
        File "noarch\forge.ico"
        File "..\manifest.json"
        File "..\forge.html"
        File /r /x .hg /x certificates "..\src"
        SetOutPath "$INSTDIR\forge"
        File /r "..\forge\*.*"
        SetOutPath "$INSTDIR"   

        ; frame injection
        File "${BUILD_DIR}\forge32.exe"
        File "${BUILD_DIR}\frame32.dll"

        ; bho
        ; TODO - don't install if dll >= version already exists
        !insertmacro InstallLib REGDLL SHARED NOREBOOT_NOTPROTECTED             "${BUILD_DIR}\${FORGE_EXECUTE_FILE}"             "$INSTDIR\${FORGE_EXECUTE_FILE}"             "$INSTDIR"
        !insertmacro InstallLib REGDLL SHARED NOREBOOT_NOTPROTECTED             "${BUILD_DIR}\${PRODUCT_EXECUTE_FILE}"             "$INSTDIR\${PRODUCT_EXECUTE_FILE}"             "$INSTDIR"

        ; estimate size
        ${GetSize} "$INSTDIR" "/S=0K" $0 $1 $2
        ; IntFmt $0 "0x%08X" $0
        !define ESTIMATED_SIZE $0

        ; Enable BHO
        WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Policies\Ext\CLSID"                         "{E017A723-53B3-4952-895D-ED7C3377C885}" "1"
        ; Optional: Enable App (side-effect: removes user option to disable plugin)
        ;WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Policies\Ext\CLSID"         ;                 "{12830336-3A64-4672-0FE0-9C18A0AFA2BD}" "1" 

        ; Create uninstaller
        !define ARP "Software\Microsoft\Windows\CurrentVersion\Uninstall\Say.So"
        WriteUninstaller "$INSTDIR\Uninstall.exe"
        WriteRegStr   HKLM "${ARP}" "Path"             "$INSTDIR\defaults"
        WriteRegStr   HKLM "${ARP}" "DisplayName"      "Say.So (remove only)"
        WriteRegStr   HKLM "${ARP}" "UninstallString"  "$INSTDIR\Uninstall.exe" 
        WriteRegStr   HKLM "${ARP}" "Publisher"        "Say.So LLC"
        WriteRegStr   HKLM "${ARP}" "DisplayVersion"   "2.0.6"
        WriteRegDWORD HKLM "${ARP}" "EstimatedSize"    "${ESTIMATED_SIZE}"
        

        ; Post installation
        Call GetIEVersion
        Pop $R0
        StrCmp $R0 "6" ie6
        StrCmp $R0 "7" ie7
        StrCmp $R0 "8" ie8
        StrCmp $R0 "9" ie9
        Goto done
    ie6: 
        Goto done
    ie7: 
        Goto done
    ie8: 
        Goto done
    ie9: 
        Goto done
    done:
        ; Using explorer.exe to launch a new process from an elevated process 
        ; ensures that the new process will run on the user account and not 
        ; as Administrator.
        
        CreateShortCut "$TEMP\Launcher.lnk" "$PROGRAMFILES\Internet Explorer\iexplore.exe" "http://www.say.so/"
        Exec '"$WINDIR\explorer.exe" "$TEMP\Launcher.lnk"'
        
        Quit
    cancel:    
SectionEnd

Section "Uninstall"
    ; Unregister Forge - TODO only delete if there are no other extensions installed
    GetDLLVersion "$INSTDIR\FORGE_EXECUTE_FILE" $R0 $R1
    !insertmacro UninstallLib REGDLL SHARED NOREBOOT_NOTPROTECTED         "$INSTDIR\${FORGE_EXECUTE_FILE}"

    ; Unregister BHO
    !insertmacro UninstallLib REGDLL SHARED NOREBOOT_NOTPROTECTED         "$INSTDIR\${PRODUCT_EXECUTE_FILE}"
    DeleteRegValue HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Policies\Ext\CLSID"                         "{E017A723-53B3-4952-895D-ED7C3377C885}"

    ; Remove application settings
    DeleteRegValue HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Policies\Ext\CLSID"                         "{12830336-3A64-4672-0FE0-9C18A0AFA2BD}" 
    DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\Say.So"
    DeleteRegKey HKCU "Software\Say.So"

    ; Remove application preferences
    DeleteRegKey HKCU "Software\AppDataLow\trigger.io\4093d42274ec11e1a41a12313d1adcbe"

    ; Being for the benefit of Mr AV Vendor there will be a twiddling of bits.
    Nop
    Sleep 10
    Nop
    ; Remove files

    RMDir /r "$INSTDIR" 
SectionEnd



