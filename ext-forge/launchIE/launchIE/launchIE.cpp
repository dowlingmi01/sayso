#include <Windows.h>
#include <ExDisp.h>
#include <Shobjidl.h>
#include <Shlwapi.h>
#include <Shlguid.h>

int CALLBACK wWinMain(
  __in  HINSTANCE hInstance,
  __in  HINSTANCE hPrevInstance,
  __in  LPWSTR lpCmdLine,
  __in  int nCmdShow
) {
	// Code based on http://brandonlive.com/2008/04/27/getting-the-shell-to-run-an-application-for-you-part-2-how/
	CoInitialize( NULL );
	IShellWindows *psw;
    HRESULT hr = CoCreateInstance(CLSID_ShellWindows, NULL, CLSCTX_LOCAL_SERVER, IID_PPV_ARGS(&psw));
    if (SUCCEEDED(hr)) {
        HWND hwnd;
        IDispatch* pdisp;
        VARIANT vEmpty = {}; // VT_EMPTY
        if (S_OK == psw->FindWindowSW(&vEmpty, &vEmpty, SWC_DESKTOP, (long*)&hwnd, SWFO_NEEDDISPATCH, &pdisp)) {
			IShellBrowser *psb;

			hr = IUnknown_QueryService(pdisp, SID_STopLevelBrowser, IID_PPV_ARGS(&psb));
			if (SUCCEEDED(hr)) {
				IShellView *psv;
				hr = psb->QueryActiveShellView(&psv);
				IDispatch *pdispBackground;
				HRESULT hr = psv->GetItemObject(SVGIO_BACKGROUND, IID_PPV_ARGS(&pdispBackground));
				if (SUCCEEDED(hr)) {
					IShellFolderViewDual *psfvd;
					hr = pdispBackground->QueryInterface(IID_PPV_ARGS(&psfvd));
					if (SUCCEEDED(hr)) {
						IDispatch *pdisp;
						hr = psfvd->get_Application(&pdisp);
						if (SUCCEEDED(hr)) {
							IShellDispatch2 *psd;
							hr = pdisp->QueryInterface(IID_PPV_ARGS(&psd));
							BSTR prog = SysAllocString(L"iexplore.exe"); 
							VARIANT par;
							VariantInit(&par);
							V_VT(&par) = VT_BSTR;
							V_BSTR(&par) = SysAllocString(lpCmdLine);

							int iRetVal = psd->ShellExecute( prog, par, vEmpty, vEmpty, vEmpty );
						}
					}
				}
			}
		}
	}

	return 0;
}
