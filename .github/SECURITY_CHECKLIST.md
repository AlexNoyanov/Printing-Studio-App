# Security Checklist Before Committing

## ⚠️ CRITICAL: Always Check Before Pushing to GitHub

### Pre-Commit Security Checklist

- [ ] **No API keys or tokens** - Check for: `api_key`, `token`, `secret`, `password`
- [ ] **No database credentials** - Check for: `DB_PASS`, `DB_PASSWORD`, `mysql://`, connection strings
- [ ] **No authentication tokens** - Check for: JWT secrets, OAuth tokens, session keys
- [ ] **No private keys** - Check for: `.pem`, `.key`, SSH keys, certificates
- [ ] **No service account files** - Check for: `service-account.json`, Firebase keys
- [ ] **No environment files** - Check for: `.env`, `.env.local` (should be in `.gitignore`)
- [ ] **No hardcoded credentials** - Check all config files for real passwords/tokens

### Files to Always Review

- `backend/config.php` - Should use `config.example.php` as template
- `.env` files - Should never be committed
- Documentation files - Should use placeholders, not real values
- Workflow files - Should use secrets, not hardcoded values
- Test files - Should not contain real credentials

### Common Patterns to Search For

```bash
# Before committing, search for:
grep -r "password.*=" --include="*.php" --include="*.js" --include="*.md"
grep -r "token.*=" --include="*.php" --include="*.js" --include="*.md"
grep -r "api.*key" --include="*.php" --include="*.js" --include="*.md" -i
grep -r "secret" --include="*.php" --include="*.js" --include="*.md" -i
```

### If You Find Sensitive Data

1. **DO NOT COMMIT** - Remove the sensitive data first
2. **Use placeholders** - Replace with `YOUR_TOKEN`, `YOUR_PASSWORD`, etc.
3. **Use GitHub Secrets** - For CI/CD, use repository secrets
4. **Use environment variables** - For local development, use `.env` files
5. **Update .gitignore** - Ensure sensitive files are ignored

### Git History Cleanup

If sensitive data was already committed:

1. **Revoke/Regenerate** the exposed credentials immediately
2. **Remove from current files** - Update all files with placeholders
3. **Consider history rewrite** - Use `git filter-branch` or `git filter-repo` (⚠️ coordinate with team)
4. **Force push** - Only if you're sure (⚠️ rewrites history)

### Best Practices

- ✅ Always use `config.example.php` with placeholders
- ✅ Store secrets in GitHub Secrets for workflows
- ✅ Use `.env` files for local development (gitignored)
- ✅ Review `git diff` before committing
- ✅ Use `git status` to see what will be committed
- ✅ Never commit files with real credentials "just for testing"

### Remember

**Once pushed to a public repository, consider all exposed credentials compromised. Always revoke and regenerate.**

