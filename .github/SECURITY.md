# Security Policy

## Supported Versions

We actively support the following versions of the Printing Studio App:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability, please **do not** open a public issue**. Instead, please report it via one of the following methods:

1. **Email**: Contact the repository maintainer directly
2. **Private Security Advisory**: Use GitHub's private vulnerability reporting feature

## Security Best Practices

### For Developers

1. **Never commit sensitive information:**
   - Database credentials
   - API keys
   - Passwords
   - Private keys

2. **Use environment variables:**
   - Store sensitive configuration in environment variables
   - Use `.env` files (which are gitignored)
   - Never commit `.env` files

3. **Configuration files:**
   - Always use `config.example.php` as a template
   - Copy to `config.php` locally (which is gitignored)
   - Never commit actual `config.php` files

4. **Database security:**
   - Use strong passwords
   - Limit database user permissions
   - Use prepared statements (already implemented)
   - Enable SSL/TLS for database connections in production

5. **API security:**
   - Implement rate limiting in production
   - Use HTTPS in production
   - Validate and sanitize all inputs
   - Use CORS appropriately

## Known Security Considerations

- **Password Storage**: Currently passwords are stored in plain text. For production use, implement password hashing (bcrypt, Argon2, etc.)
- **CORS**: Currently set to allow all origins (`*`). Restrict to specific domains in production
- **Session Management**: Consider implementing proper session management and JWT tokens for production

## Security Updates

Security updates will be released as needed. Please keep your dependencies up to date:

```bash
npm audit
npm audit fix
```

