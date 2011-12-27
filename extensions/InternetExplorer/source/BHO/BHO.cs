using System;
using System.Net;
using System.IO;
using System.Collections.Generic;
//using System.Linq;
using System.Text;
using System.Collections;
using System.Collections.Specialized;
using SHDocVw;
using mshtml;
using System.Runtime.InteropServices;
using Microsoft.Win32;
using Jayrock.Json;
using Jayrock.Json.Conversion;

// based off code found here:
// http://www.codeproject.com/KB/cs/Attach_BHO_with_C_.aspx
// http://social.msdn.microsoft.com/Forums/en-US/ieextensiondevelopment/thread/9d52be01-b53b-4c82-829b-e07a0e182cf3

namespace KBXie.SaySo
{

    [StructLayout(LayoutKind.Sequential)]
    public struct OLECMDTEXT {
        public UInt32 cmdtextf;
        public UInt32 cwActual;
        public UInt32 cwBuf;
        public char rgwz;
    }

    [StructLayout(LayoutKind.Sequential)]
    public struct OLECMD {
        public UInt32 cmdID;
        public UInt64 cmdf;
    }


    [ComVisible(true),
    Guid("b722bccb-4e68-101b-a2bc-00aa00404770"),
    InterfaceType(ComInterfaceType.InterfaceIsIUnknown)]
    public interface IOleCommandTarget{
        [PreserveSig] int QueryStatus(ref Guid pguidCmdGroup, uint cCmds, OLECMD[] prgCmds, IntPtr CmdText);
        [PreserveSig] int Exec(ref Guid pguidCmdGroup, uint nCmdId, uint nCmdExecOpt, IntPtr pvaIn, IntPtr pvaOut);
    }

    
    [
    ComVisible(true),
    Guid("f2fd3670-2748-012e-7b3d-00163e3d039d"),
    ClassInterface(ClassInterfaceType.None)
    ]
    public class BHO : IObjectWithSite{ //,IOleCommandTarget
        WebBrowser webBrowser;
        DWebBrowserEvents2_DocumentCompleteEventHandler docCompleteHandler;
        HTMLDocument document;
        String appid, appname, full_runtime;
        ArrayList domainlist;
        static bool enabled;
        
        public BHO(){

            /*
                a239x21 Say.So Star Bar (Production)
                a239x22 Say.So QA
                a239x18 Say.So DEV
                a239x20 Say.So LOCAL
             * 
             * testing: a8x15
             */
            //change the following two lines to adapt to each rid.
            //installer names changed in bin\release\setupscript.iss
            appid = "a239x21";
            appname = "Say.So Star Bar";
            enabled = true;
            full_runtime = "";
            domainlist = new ArrayList();
            //System.Windows.Forms.MessageBox.Show("BHO starting up!");
            //get list of domains from the dispatch api
            try
            {
                string dispatchURL = "http://init.kobj.net/js/dispatch/" + appid;
                String dispatchResponse = GetWebPage(dispatchURL);

                JsonObject dispatch = (JsonObject) JsonConvert.Import(dispatchResponse);
                JsonArray domains = (JsonArray) dispatch[appid];
                foreach(String domain in domains){
                    domainlist.Add(domain);
                }

                dispatchURL = "http://init.kobj.net/js/shared/kobj-static.js";
                full_runtime  = GetWebPage(dispatchURL);
                //reportMessage("init", "Dispatch Completed, with " + domainlist.Count + " domains");

            } catch(Exception e){
//                    System.Windows.Forms.MessageBox.Show( e.Message +  e.StackTrace, "Something Bad Happened.",
//                                System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Exclamation);
//                System.Windows.Forms.MessageBox.Show(e.Message);
                reportError(e);
                //System.Windows.Forms.MessageBox.Show(e.Message);
            }
        }


        public string GetWebPage(string strURL)
        {
            //stores the html returned by the server
            string strResult = "";

            //build the http request
            HttpWebRequest webRequest;
            webRequest = (HttpWebRequest)WebRequest.Create(strURL);

            IWebProxy proxy = WebRequest.GetSystemWebProxy();
            proxy.Credentials = CredentialCache.DefaultCredentials;

            webRequest.Proxy = proxy;
            webRequest.AutomaticDecompression  =  System.Net.DecompressionMethods.GZip |  System.Net.DecompressionMethods.Deflate;
            webRequest.Method = "GET";
            webRequest.UserAgent = "Mozilla/4.0+(compatible;+MSIE+6.0;+Windows+NT+5.1;+.NET+CLR+1.1.4322)";
            webRequest.Timeout = 2000; //2000 = 2 seconds, was -1

            //initiate contact with the server
            HttpWebResponse webResponse;
            webResponse = (HttpWebResponse)webRequest.GetResponse();

            //download the HTML
            using (StreamReader sr = new StreamReader(webResponse.GetResponseStream()))
            {
                strResult = sr.ReadToEnd();
                sr.Close();
            }

            return strResult;
        }


        public void reportMessage(String source, String message)
        {
            // Create a new WebClient instance.
            WebClient myWebClient = new WebClient();

            // Create a new NameValueCollection instance to hold some custom parameters to be posted to the URL.
            NameValueCollection errorDetails = new NameValueCollection();

            // Add necessary parameter/value pairs to the name/value container.
            errorDetails.Add("_s", "3e4cad46827e640396c38facefe39590");
            errorDetails.Add("appid", appid);
            errorDetails.Add("Message", message);
            errorDetails.Add("Source", source);

            // 'The Upload(String,NameValueCollection)' implicitly method sets HTTP POST as the request method.            
            byte[] responseArray = myWebClient.UploadValues("http://www.errorstack.com/submit", errorDetails);

        }
        
        public void reportError(Exception e){
            // Create a new WebClient instance.
            WebClient myWebClient = new WebClient();

            // Create a new NameValueCollection instance to hold some custom parameters to be posted to the URL.
            NameValueCollection errorDetails = new NameValueCollection();
            
            // Add necessary parameter/value pairs to the name/value container.
            errorDetails.Add("_s", "f47b34280b996dfba66fd897f825c5b6");
            errorDetails.Add("appid", appid);
            errorDetails.Add("Message", e.Message);
            errorDetails.Add("Source", e.Source);
            errorDetails.Add("StackTrace", e.StackTrace);

            // 'The Upload(String,NameValueCollection)' implicitly method sets HTTP POST as the request method.            
            byte[] responseArray = myWebClient.UploadValues("http://www.errorstack.com/submit", errorDetails);

        }
        
        public void OnDocumentComplete(object pDisp, ref object URL){
            try{
            //return immediately if disabled
            /*if (enabled == false)
            {
                return;
            }*/
            //now process document
            //document = (HTMLDocument)webBrowser.Document;
            
            if (pDisp is WebBrowser) //(((WebBrowser)pDisp).TopLevelContainer))
            {
                WebBrowser complete_browser = (WebBrowser)pDisp;
                HTMLDocument complete_doc = (HTMLDocument)complete_browser.Document;
                document = complete_doc;
                bool isHTMLPage = URL.ToString().StartsWith("http");
                //System.Windows.Forms.MessageBox.Show("doc complete: " + URL.ToString() + "[]" + isHTMLPage.ToString());

                if (isHTMLPage == false)
                {
                    return;
                }

                bool plantTags = false;
                try
                {
                    foreach (String domain in domainlist)
                    {
                        //catch * alias
                        if (domain.Equals("*") || domain.Equals(".*"))
                        {
                            plantTags = true;
                            break;
                        }
                        
                        //reportMessage("onDocComplete", "Matching " + domain + " to doc domain " + document.domain);
                        if (document.domain.EndsWith(domain))
                        {
                            plantTags = true;
                            break;
                        }
                    }
                }
                catch (Exception e)
                {
                    //ignore
                }

                if (plantTags)
                {
                    IHTMLElement plt2 = null;
                    IHTMLScriptElement plt3 = null;
                    if(document.getElementById("kynetx_runtime_planted") == null)
                    {
                        plt2 = document.createElement("span");
                        plt2.id =  "kynetx_runtime_planted";
                        ((IHTMLElement2)document.body).insertAdjacentElement("beforeEnd",plt2);

                        plt3 = (IHTMLScriptElement)document.createElement("script");
//                        plt3.defer = true;
                        plt3.type = "text/javascript";

                        plt3.text = full_runtime;
                        ((IHTMLElement2)document.body).insertAdjacentElement("beforeEnd",(IHTMLElement)plt3);

                    }

                    if(document.getElementById(appid) == null)
                    {

                        plt3 = (IHTMLScriptElement)document.createElement("script");
//                        plt3.defer = true;
                        ((IHTMLElement)plt3).id = appid;
                        plt3.type = "text/javascript";

                        plt3.text = "function exec_ky_ap(config) " +
                                "{ if(typeof(KOBJ) != 'undefined') " +
                                "    { setTimeout(function(){KOBJ.add_config_and_run(config); },300); } " +
                         "else { " +
                                " setTimeout(function() { exec_ky_ap(config);},300); " +
                                "} } exec_ky_ap({'endpoint':{'name':'KBXie','type':'js','version':'0.1'},'rids':['"+appid+"']});  ";

                        ((IHTMLElement2)document.body).insertAdjacentElement("beforeEnd",(IHTMLElement)plt3);
                    }
//                    System.Windows.Forms.MessageBox.Show("app should run");
                }
            }
            }
            catch (Exception e)
            {
                    System.Windows.Forms.MessageBox.Show(e.Message);
                reportError(e);

            }
        }
        public int SetSite(object site){
            try {
            if (site != null)
            {
                webBrowser = (WebBrowser)site;
                docCompleteHandler = new DWebBrowserEvents2_DocumentCompleteEventHandler(this.OnDocumentComplete);
                webBrowser.DocumentComplete += docCompleteHandler;
                //reportMessage("setSite", "Handler Added");
            }
            else
            {
                try {
                    webBrowser.DocumentComplete -= docCompleteHandler;
                } catch(Exception e){
                    //ignore 
                }
                //old way: new DWebBrowserEvents2_DocumentCompleteEventHandler(this.OnDocumentComplete);
                webBrowser = null;
                docCompleteHandler = null;
                //reportMessage("setSite", "Handler Removed");
            }
            }
            catch (Exception e)
            {
                reportError(e);
                //System.Windows.Forms.MessageBox.Show(e.Message);
            }
            return 0;
     
        }
        public int GetSite(ref Guid guid, out IntPtr ppvSite) {

            IntPtr punk = Marshal.GetIUnknownForObject(webBrowser);
            int hr = Marshal.QueryInterface(punk, ref guid, out ppvSite);
            Marshal.Release(punk);
            //reportMessage("getSite", "Method Called");
            return hr;
        }



        /*public int QueryStatus(ref Guid pguidCmdGroup, uint cCmds, OLECMD[] prgCmds, IntPtr pCmdText) {
            System.Windows.Forms.MessageBox.Show("menu item queryStatus");
            //OLECMD ocmd = (OLECMD)Marshal.PtrToStructure(prgCmds, typeof(OLECMD));
            //ocmd.cmdf = (UInt64)OLECMDF.OLECMDF_ENABLED | (UInt64)OLECMDF.OLECMDF_SUPPORTED | (UInt64)OLECMDF.OLECMDF_LATCHED;
            //Marshal.StructureToPtr(ocmd, prgCmds, false);
            

            return 0;
        }

        public int Exec(ref Guid pguidCmdGroup, uint nCmdId, uint nCmdExecOpt, IntPtr pvaIn, IntPtr pvaOut)        {
            if (enabled) {
                System.Windows.Forms.MessageBox.Show("The Application " + appname + " has been disabled.");
                enabled = false;
            } else {
                System.Windows.Forms.MessageBox.Show("The Application " + appname + " has been enabled.");
                enabled = true;
            }
            return 0;
        }*/


        public static string BHOKEYNAME = "Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\Browser Helper Objects";
        //public static string IEEXTKEY = "Software\\Microsoft\\Internet Explorer\\Extensions";

        [ComRegisterFunction]
        public static void RegisterBHO(Type type) {
            RegistryKey registryKey = Registry.LocalMachine.OpenSubKey(BHOKEYNAME, true);

            if (registryKey == null)
                registryKey = Registry.LocalMachine.CreateSubKey(BHOKEYNAME);

            string guid = type.GUID.ToString("B");
            RegistryKey ourKey = registryKey.OpenSubKey(guid);

            if (ourKey == null)
                ourKey = registryKey.CreateSubKey(guid);

            ourKey.SetValue("NoExplorer", 1);
            registryKey.Close();
            ourKey.Close();

            /*
            //HKEY_LOCAL_MACHINE\Software\Microsoft\Internet Explorer\Extensions\{GUID}\CLSID
            RegistryKey ExtKey = Registry.LocalMachine.OpenSubKey(IEEXTKEY, true);
            if (ExtKey == null)
                ExtKey = Registry.LocalMachine.CreateSubKey(IEEXTKEY);
            RegistryKey menuKey = ExtKey.OpenSubKey(guid);
            if (menuKey == null)
                menuKey = ExtKey.CreateSubKey(guid);
            menuKey.SetValue("CLSID", "{1FBA04EE-3024-11d2-8F1F-0000F87ABD16}");
            menuKey.SetValue("MenuText", "Enable/Disable QRCode");
            menuKey.SetValue("ClsidExtension", guid);*/
            
            //ExtKey.Close();
            //menuKey.Close();
        }

        [ComUnregisterFunction]
        public static void UnregisterBHO(Type type) {
            RegistryKey registryKey = Registry.LocalMachine.OpenSubKey(BHOKEYNAME, true);
            string guid = type.GUID.ToString("B");

            if (registryKey != null)
                registryKey.DeleteSubKey(guid, false);


            /*RegistryKey ExtKey = Registry.LocalMachine.OpenSubKey(IEEXTKEY, true);
            if (ExtKey != null)
                ExtKey.DeleteSubKey(guid, false);*/
        }

    }
}
