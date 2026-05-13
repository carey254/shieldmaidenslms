/**
 * Brand logo URL for `<img :src="PUBLIC_BRAND_LOGO" />`.
 *
 * The asset on disk is: `theshieldmaidens-lms-frontend/public/logo.png`
 * (e.g. `D:\LMS\theshieldmaidens-lms-frontend\public\logo.png` on your machine).
 * Never put a Windows path in `src` — browsers cannot load it. Vite serves `public/`
 * at the app root, so the runtime URL is BASE_URL + "logo.png".
 */
function publicAssetUrl(file: string): string {
  const base = import.meta.env.BASE_URL || '/';
  const normalized = base.endsWith('/') ? base : `${base}/`;
  return `${normalized}${file.replace(/^\//, '')}`;
}

export const PUBLIC_BRAND_LOGO = publicAssetUrl('logo.png');
