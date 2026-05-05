const AUTH_CONFIG = {
  apiBase: "http://localhost:8000",
  loginEndpoint: "/api/login",
};

function sanitizeResponse(data) {
  if (!data || typeof data !== "object") return data;
  const copy = { ...data };
  if (copy.token) copy.token = "[REDACTED]";
  if (copy.access_token) copy.access_token = "[REDACTED]";
  if (copy.refresh_token) copy.refresh_token = "[REDACTED]";
  return copy;
}

async function submitRoleLogin(expectedRole) {
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const result = document.getElementById("result");

  const email = emailInput.value.trim();
  const password = passwordInput.value;

  if (!email || !password) {
    result.textContent = "Please enter email and password.";
    return;
  }

  try {
    const response = await fetch(`${AUTH_CONFIG.apiBase}${AUTH_CONFIG.loginEndpoint}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();
    const role = data?.user?.role || data?.role || "unknown";
    const roleMatch = response.ok && role === expectedRole;

    result.textContent = JSON.stringify(
      {
        portal: expectedRole,
        http_status: response.status,
        login_success: response.ok,
        role_from_server: role,
        role_match: roleMatch,
        message: roleMatch
          ? "Login allowed for this portal."
          : "Role mismatch or login failed. Access denied.",
        response_preview: sanitizeResponse(data),
      },
      null,
      2
    );
  } catch (error) {
    result.textContent = `Request failed: ${error.message}`;
  }
}
