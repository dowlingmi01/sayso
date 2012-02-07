using System;
using SHDocVw;
using mshtml;
using System.Runtime.InteropServices;
using Microsoft.Win32;

// based off code found here:
// http://www.codeproject.com/KB/cs/Attach_BHO_with_C_.aspx
// http://social.msdn.microsoft.com/Forums/en-US/ieextensiondevelopment/thread/9d52be01-b53b-4c82-829b-e07a0e182cf3

namespace SaySo.Bar
{
	[
	ComVisible(true),
	Guid("f2fd3670-2748-012e-7b3d-00163e3d039d"),
	ClassInterface(ClassInterfaceType.None)
	]
	public class BHO : IObjectWithSite{
		WebBrowser webBrowser;
		DWebBrowserEvents2_DocumentCompleteEventHandler docCompleteHandler;
		HTMLDocument document;
		String baseDomain;

		public BHO(){

			baseDomain = "app.saysollc.com";

		}

		public void OnDocumentComplete(object pDisp, ref object URL){
			try{
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

					IHTMLScriptElement saysoGlobal = null;
					IHTMLScriptElement saysoInit = null;

					saysoGlobal = (IHTMLScriptElement)document.createElement("script");
	//			  saysoGlobal.defer = true;
					saysoGlobal.type = "text/javascript";

					saysoGlobal.text = "window.$SaySoExtension = { base_domain: '" + baseDomain + "' }";
					((IHTMLElement2)document.body).insertAdjacentElement("beforeEnd",(IHTMLElement)saysoGlobal);

					saysoInit = (IHTMLScriptElement)document.createElement("script");
	//			  saysoInit.defer = true;
					saysoInit.type = "text/javascript";

					saysoInit.src = "//" + baseDomain + "/js/starbar/sayso-init.js";
					((IHTMLElement2)document.body).insertAdjacentElement("beforeEnd",(IHTMLElement)saysoInit);

				}
			}
			catch (Exception e)
			{
				System.Windows.Forms.MessageBox.Show(e.Message);
			}
		}

		public int SetSite(object site){
			try {
			if (site != null)
			{
				webBrowser = (WebBrowser)site;
				docCompleteHandler = new DWebBrowserEvents2_DocumentCompleteEventHandler(this.OnDocumentComplete);
				webBrowser.DocumentComplete += docCompleteHandler;
			}
			else
			{
				try {
					webBrowser.DocumentComplete -= docCompleteHandler;
				} catch(Exception){
					//ignore
				}
				//old way: new DWebBrowserEvents2_DocumentCompleteEventHandler(this.OnDocumentComplete);
				webBrowser = null;
				docCompleteHandler = null;
			}
			}
			catch (Exception)
			{
				//System.Windows.Forms.MessageBox.Show(e.Message);
			}
			return 0;

		}

		public int GetSite(ref Guid guid, out IntPtr ppvSite) {
			IntPtr punk = Marshal.GetIUnknownForObject(webBrowser);
			int hr = Marshal.QueryInterface(punk, ref guid, out ppvSite);
			Marshal.Release(punk);
			return hr;
		}

		public static string BHOKEYNAME = "Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\Browser Helper Objects";

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
		}

		[ComUnregisterFunction]
		public static void UnregisterBHO(Type type) {
			RegistryKey registryKey = Registry.LocalMachine.OpenSubKey(BHOKEYNAME, true);
			string guid = type.GUID.ToString("B");

			if (registryKey != null)
				registryKey.DeleteSubKey(guid, false);
		}
	}
}
