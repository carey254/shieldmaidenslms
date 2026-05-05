# LMS Auth Structure

Use this folder as the single place for login testing pages.

## Files

- `index.html`: Entry page linking all role portals
- `admin-login.html`: Admin portal login test
- `instructor-login.html`: Instructor portal login test
- `student-login.html`: Student portal login test
- `shared-auth.js`: Shared API call + role verification + token redaction

## Security behavior

- Each page enforces its expected role (`admin`, `instructor`, `student`).
- Response tokens are redacted in UI output.
- Role mismatch is treated as denied access.
