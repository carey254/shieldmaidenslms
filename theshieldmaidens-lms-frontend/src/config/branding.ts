/**
 * Brand logo — must match the file shipped at:
 *   public/logo.png  →  repo: theshieldmaidens-lms-frontend/public/logo.png
 *
 * Vite serves `public/` at the app root, so the runtime URL is BASE_URL + "logo.png".
 */
function publicAssetUrl(file: string): string {
  const base = import.meta.env.BASE_URL || '/';
  const normalized = base.endsWith('/') ? base : `${base}/`;
  return `${normalized}${file.replace(/^\//, '')}`;
}

export const PUBLIC_BRAND_LOGO = publicAssetUrl('logo.png');
