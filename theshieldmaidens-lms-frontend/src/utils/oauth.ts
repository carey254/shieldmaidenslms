/** Laravel backend origin (no trailing /api). Used for browser OAuth redirects. */
export function getBackendOrigin(): string {
  const api =
    import.meta.env.VITE_API_BASE_URL ||
    (typeof localStorage !== 'undefined' ? localStorage.getItem('apiBaseUrl') : null) ||
    'http://127.0.0.1:8000/api';
  const trimmed = String(api).replace(/\/?api\/?$/i, '');
  return trimmed || 'http://127.0.0.1:8000';
}

/** Full URL to start Google OAuth (web session on backend); return_url receives the SPA callback with token. */
export function buildGoogleOAuthUrl(frontendReturnUrl: string): string {
  const base = getBackendOrigin();
  const q = new URLSearchParams({ return_url: frontendReturnUrl });
  return `${base}/auth/google?${q.toString()}`;
}
