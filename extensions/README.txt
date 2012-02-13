Usage:

build.bat [domain [browser]]

Where currently:

domain = all | staging | qa | dev | demo | prod | local

browser = all | Chrome | Firefox | InternetExplorer | Safari

For domains, the option 'all' does not include local. You need to
specify it separately.

The file xxxfiles.txt contains the list of files that will be
configured.

The file dict.txt contains global configuration options and the files
dict-xxxx.txt contain domain specific options.

Requirements:

* The main batch file expects the perl command to be on the path.

* The Chrome build batch file expects to find the Chrome browser
  executable at its standard install location:
  %LOCALAPPDATA%\Google\Chrome\Application\chrome.exe

* The Firefox build batch file expects the zip command to be on the
  path.

* The InternetExplorer build batch file requires .Net framework 3.5
  and Inno Setup 5.  It expects to find the Inno Setup exe at
  C:\"Program Files (x86)"\"Inno Setup 5"\iscc.exe

* Safari extension building can't be automated for now, but the task
  is greatly simplified after you run the main build.bat

1. You need to install on your machine the certificate with private
   key necessary to sign the extensions. (File
   extensions/Safari/safari_identity.pfx).

2. In Preferences->Advanced check "Show Develop menu in menu bar".

3. In the menu bar select Develop|Show Extension Builder

4. The first time you will have to add all the extensions to build one
   by one. For each of the subdirectories of extensions/Safari/build/
   (you should have: demo dev local prod qa staging, all of them with
   the extension .safariextension): click the '+' sign on the lower
   left corner of the Extension Builder, select "Add Extension...",
   and point it to that directory. When you add the extensions, check
   that below the folder name there is a check mark and says:
   Safari Developer: (7V6CMXYLYN) bigimp@gmail.com

5. You will have now on the left bar of the extension builder six
   entries. Select each one and click "Build Package...". Save the
   resulting file in public/install/safari over the appropiate
   file. You can know which extension you are building by looking at
   the folder name under "Say.So Bar". Use the following names:

        prod    Say.So Starbar
        demo    SaySo-DEMO
        dev     SaySo-DEV
        local   SaySo-LOCAL
        staging SaySo-STAGE
        qa      SaySo-TEST

Steps 1, 2, and 4 will have to be done only once.
Steps 3 and 5 will be required each time you want to build the
Safari extensions.

