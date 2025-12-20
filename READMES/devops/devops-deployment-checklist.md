# DevOps Deployment Test Checklist

This checklist documents the standardized test scripts for CMS DevOps team members to validate Drupal upgrades, EKS migrations, and other infrastructure changes.

## Overview

All tests in this checklist must pass before deployment to production. Tests are organized into two categories:

1. **Automated CI/CD Tests** - Run automatically on all pull requests via GitHub Actions and Tugboat
2. **Manual Validation Tests** - Run manually post-deployment to verify critical functionality

---

## Automated CI/CD Tests

These tests run automatically when a PR is created and must all pass before merging.

### Code Quality Tests

| Test | Purpose | Command | Runs On |
|------|---------|---------|---------|
| **PHP Lint** | Verify PHP syntax | `composer va:test:php-lint` | GitHub Actions |
| **PHP_CodeSniffer** | Check coding standards | `composer va:test:phpcs` | GitHub Actions |
| **PHPStan** | Static analysis for type safety | `composer va:test:phpstan` | GitHub Actions |
| **ESLint** | JavaScript linting | `composer va:test:eslint` | GitHub Actions |
| **Stylelint** | CSS/SCSS linting | `composer va:test:stylelint` | GitHub Actions |
| **CodeQL** | Security vulnerability scanning | Auto-configured | GitHub Actions |
| **Composer Validate** | Validate composer.json/lock | `composer validate` | GitHub Actions |
| **Check Fields** | Validate field configurations | `composer va:test:check-fields` | GitHub Actions |

### Functional Tests

| Test | Purpose | Command | Runs On |
|------|---------|---------|---------|
| **Cypress** | Accessibility and behavioral tests | `composer va:test:cypress-parallel` | Tugboat |
| **PHPUnit Functional** | Drupal functionality tests | `composer va:test:phpunit-functional` | Tugboat |
| **Status-Error** | System status and configuration checks | `composer va:test:status-error-ci` | Tugboat |
| **Content-Build GraphQL** | GraphQL query validation | `composer va:test:content-build-gql` | Tugboat |

For detailed information about these tests, see [Testing](testing.md).

---

## Manual Validation Tests

These tests must be performed manually after deployment to staging or production environments.

### 1. SSO Testing

**Purpose:** Verify Single Sign-On functionality  
**Priority:** HIGH - Users cannot access CMS if SSO fails

**Test Steps:**

1. Navigate to login page: `https://[environment].cms.va.gov/user/login`
2. Click SSO login button
3. Enter valid PIV/CAC or other configured credentials
4. Verify successful authentication and redirect to dashboard
5. Check user profile loads with correct name and email
6. Test user permissions and roles (create content, admin access, etc.)
7. Test logout functionality
8. Verify session timeout behavior (default: 30 minutes)

**Expected Result:** All SSO operations complete without errors

**Troubleshooting:**
```bash
# Check SSO configuration
drush config:get samlauth.authentication

# Check for authentication errors
drush watchdog:show --severity=Error --filter="saml"

# Verify SSO certificates are valid
drush config:get samlauth.authentication idp_cert_folder
```

### 2. Smoke Test Environment

**Purpose:** Quick validation of critical system functionality

**Test Commands:**

```bash
# System status check
drush status
# Expected: Database = Connected, Drupal bootstrap = Successful

# Check PHP version and memory
php -v
php -i | grep memory_limit
# Expected: PHP 8.1+ (or 8.3+ for Drupal 11), memory >= 256M

# Check for recent errors
drush watchdog:show --severity=Error --count=20
# Expected: No critical errors

# Clear cache
drush cache:rebuild
# Expected: Cache rebuild successful

# Run cron
drush cron
# Expected: Cron run completed

# Test database connectivity
drush sql:query "SELECT COUNT(*) FROM node;"
# Expected: Returns node count without error

# Test HTTP responses
curl -I https://[environment].cms.va.gov/
curl -I https://[environment].cms.va.gov/admin
# Expected: HTTP 200 or 302 responses
```

**Expected Result:** All commands execute without errors

**Troubleshooting:**
- Check web server logs: `/var/log/httpd/` or `/var/log/nginx/`
- Check PHP-FPM logs: `/var/log/php-fpm/`
- Verify database connection: `drush sql:cli` to connect directly

### 3. Content Management Validation

**Purpose:** Verify CMS editing and publishing functionality

**Test Steps:**

1. Login as content editor
2. Create new content:
   - Navigate to Content → Add Content
   - Create a test node (any content type)
   - Add required fields (title, body, etc.)
   - Save as draft
3. Edit existing content:
   - Find published content
   - Make minor edit
   - Save new revision
4. Media management:
   - Upload an image
   - Upload a document
   - Verify files appear in Media Library
5. Publish content:
   - Change draft to published state
   - Verify moderation workflow (if applicable)
6. Preview content:
   - Use content preview feature
   - Verify preview renders correctly
7. Delete test content:
   - Delete created test nodes and media

**Expected Result:** All content operations complete successfully without errors

**Troubleshooting:**
```bash
# Check for content-related errors
drush watchdog:show --severity=Error --filter="node"

# Verify file permissions
ls -la /var/www/html/docroot/sites/default/files/

# Check moderation workflow configuration
drush config:get workflows.workflow.editorial
```

### 4. Content Release Testing

**Purpose:** Verify content can be released to frontend

**Test Steps:**

1. Navigate to Content Release dashboard: `/admin/content/deploy`
2. Trigger a content release:
   - Click "Release Content" button
   - Monitor build progress
   - Wait for completion (typically 45-60 minutes)
3. Verify release success:
   - Check build logs for errors
   - Verify "Last successful build" timestamp updated
4. Test frontend:
   - Visit corresponding web URL (staging.va.gov or www.va.gov)
   - Verify recent content changes appear
   - Check for broken links or images

**Expected Result:** Content release completes successfully, changes visible on frontend

**Troubleshooting:**
```bash
# Check content-build status
drush content-release:status

# View recent build logs
drush content-release:logs --limit=50

# Check GraphQL queries
drush content-release:graphql-test
```

### 5. Performance Validation

**Purpose:** Ensure acceptable system performance

**Test Commands:**

```bash
# Check page load time (should be < 2 seconds for admin pages)
time curl -I https://[environment].cms.va.gov/admin/content

# Monitor slow queries
drush watchdog:show --severity=Warning --filter="slow query"
# Expected: No slow queries or very few

# Check system resources
top -bn1 | head -20
# Expected: CPU and memory usage within normal ranges

# Check cache hit rate (if using Memcache/Redis)
drush cache:stats
```

**Expected Result:**
- Page loads under 2 seconds
- No slow query warnings
- Memory and CPU usage under 80%
- Cache hit rate above 80% (if applicable)

**Troubleshooting:**
- Review slow query log: `/var/log/mysql/slow.log`
- Check Memcache/Redis status: `drush cache:stats`
- Profile with Blackfire (see [Profiling with Blackfire](blackfire.md))

---

## Deployment Checklist

Use this checklist for all deployments:

### Pre-Deployment

- [ ] All automated CI/CD tests pass on PR
- [ ] Code review completed and approved
- [ ] Database backup completed (if applicable)
- [ ] Configuration backup completed (if applicable)
- [ ] Deployment plan documented and reviewed
- [ ] Rollback procedure confirmed and tested

### Post-Deployment to Staging

- [ ] SSO functionality verified
- [ ] Smoke tests completed successfully
- [ ] Content management tested
- [ ] Content release tested
- [ ] Performance acceptable
- [ ] No critical errors in logs
- [ ] Stakeholders notified of staging deployment

### Post-Deployment to Production

- [ ] SSO functionality verified
- [ ] Smoke tests completed successfully
- [ ] Content management tested
- [ ] Performance acceptable
- [ ] No critical errors in logs
- [ ] Monitor for 15-30 minutes post-deployment
- [ ] Stakeholders notified of production deployment
- [ ] Deployment documentation updated

### If Issues Found

- [ ] Stop deployment if critical issue detected
- [ ] Execute rollback procedure (see below)
- [ ] Notify team via Slack (#cms-engineering or #cms-support)
- [ ] Document failure details in GitHub issue
- [ ] Schedule post-mortem review

---

## Rollback Procedure

If critical issues are found during or after deployment:

### BRD Environments (Staging/Production)

1. Access Jenkins: [jenkins.vfs.va.gov](http://jenkins.vfs.va.gov/)
2. Navigate to appropriate deployment job
3. Select "Rollback to Previous Release" option
4. Confirm rollback execution
5. Monitor rollback progress (typically 10-15 minutes)
6. Run smoke tests to verify rollback success
7. Notify team and stakeholders

### Tugboat Environments (CI/Demo)

1. Access Tugboat: [tugboat.vfs.va.gov](https://tugboat.vfs.va.gov/)
2. Navigate to affected environment
3. Click "Rebuild" to recreate from base preview
4. Or delete and recreate environment if necessary

---

## Common Issues and Solutions

| Issue | Symptoms | Solution |
|-------|----------|----------|
| **SSO Login Fails** | Users cannot authenticate | Check certificate expiration: `drush config:get samlauth.authentication idp_cert_folder` and verify cert validity |
| **Slow Page Loads** | Pages take >5 seconds to load | Clear cache: `drush cache:rebuild` and check for slow queries |
| **Content Not Publishing** | Content changes not visible on frontend | Check moderation workflow state and trigger content release |
| **Database Connection Errors** | "Database connection failed" messages | Verify database credentials in settings.php and check database server status |
| **Memory Exhaustion** | PHP fatal error: memory exhausted | Increase memory_limit in php.ini or check for memory leaks |
| **Permission Denied Errors** | Cannot write to files directory | Check file permissions: `chmod -R 775 sites/default/files/` |

---