# Automated Deployment Guide

This guide documents the automated production deployment pipeline for the application using GitHub Actions, secure SSH authentication, and zero-downtime release practices.

---

## 1. Deployment Architecture & Workflow

The deployment workflow (`.github/workflows/deploy.yml`) is triggered on every push to the `main` branch or manually through `workflow_dispatch`. It packages application code, synchronizes files with the target server, executes essential database migrations, clears and caches configurations, and restarts workers.

```mermaid
sequenceDiagram
    autonumber
    actor Dev as Developer
    participant GH as GitHub Actions
    participant Srv as Production Server
    participant DB as Production Database

    Dev->>GH: Push commit to main branch
    GH->>GH: Checkout code & setup PHP runtime
    GH->>GH: Package release tarball (excluding .git, .env, tests, node_modules)
    GH->>GH: Configure SSH key & host verification
    GH->>Srv: Upload release tarball via SCP
    GH->>Srv: Execute remote deployment commands
    activate Srv
    Srv->>Srv: Put application into maintenance mode (`artisan down`)
    Srv->>Srv: Extract release tarball & set permissions (775)
    Srv->>Srv: Install production dependencies (`composer install --no-dev`)
    Srv->>DB: Execute pending migrations (`artisan migrate --force`)
    Srv->>Srv: Clear old cache & build production caches (config, route, view)
    Srv->>Srv: Ensure storage symlink & restart queue workers (`artisan queue:restart`)
    Srv->>Srv: Bring application out of maintenance mode (`artisan up`)
    deactivate Srv
    GH->>GH: Securely remove temporary SSH key and local artifacts
    GH-->>Dev: Deployment Status (Success / Failure)
```

---

## 2. GitHub Secrets & Variables Configuration

To configure continuous deployment, navigate to your GitHub repository:
**Settings > Secrets and variables > Actions**.

### Required Repository Secrets

| Secret Name | Type | Purpose | Example Placeholder |
| :--- | :--- | :--- | :--- |
| `SSH_HOST` | Secret | Domain name or IP address of the target server | `ssh.example.com` |
| `SSH_PORT` | Secret | SSH port configured on the remote server | `22` (or custom e.g., `2222`) |
| `SSH_USER` | Secret | Dedicated remote deploy username | `deployer` |
| `SSH_PRIVATE_KEY` | Secret | OpenSSH private key for passwordless authentication | `-----BEGIN OPENSSH PRIVATE KEY----- ...` |
| `SSH_KNOWN_HOSTS` | Secret | Host public key fingerprint for strict host verification | `ssh.example.com ssh-ed25519 AAAAC3NzaC1...` |

### Required Repository Variables

| Variable Name | Type | Purpose | Example Placeholder |
| :--- | :--- | :--- | :--- |
| `DEPLOY_PATH` | Variable | Absolute path to the web application root on the server | `/var/www/html` or `/home/user/public_html` |
| `PHP_VERSION` | Variable | PHP runtime version to use during workflow setup | `8.2` or `8.4` |

---

## 3. SSH Key Generation & Host Verification

### Generating a Dedicated Deploy Key Pair

Always use a dedicated, isolated key pair for CI/CD rather than personal keys:

```bash
# Generate an Ed25519 key pair with no passphrase
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy_key
```

1. Copy the contents of `~/.ssh/github_deploy_key` and add it as the **`SSH_PRIVATE_KEY`** secret in GitHub.
2. Append the public key (`~/.ssh/github_deploy_key.pub`) to the target server user's authorized keys:
   ```bash
   cat ~/.ssh/github_deploy_key.pub >> ~/.ssh/authorized_keys
   chmod 600 ~/.ssh/authorized_keys
   chmod 700 ~/.ssh
   ```

### Populating `SSH_KNOWN_HOSTS`

To protect against man-in-the-middle (MITM) attacks, the pipeline enforces strict host key verification:

```bash
# Retrieve the server public host key
ssh-keyscan -p 22 ssh.example.com
```

Save the exact output string as the **`SSH_KNOWN_HOSTS`** secret in GitHub.

---

## 4. Server-Side Permissions & Configuration

Ensure the deployment user has appropriate file ownership and permissions for Laravel's writable directories:

```bash
cd /var/www/html
sudo chown -R deployer:www-data .
sudo chmod -R 775 storage bootstrap/cache public/uploads
```

### Environment File (`.env`) Management

The deployment pipeline deliberately **excludes `.env`** to prevent overwriting production credentials. Maintain the live `.env` file directly on the production host or inject it using secret management tools (such as HashiCorp Vault, AWS Secrets Manager, or GitHub Environment Secrets).

---

## 5. Security & Governance Best Practices

1. **Dedicated Deploy User**: Run CI/CD tasks under an unprivileged user (e.g. `deployer`) with SSH access restricted to the project root directory.
2. **Key Rotation Policy**: Regularly rotate deploy SSH keys (e.g., quarterly or whenever team access changes).
3. **GitHub Environments**: Attach deployment workflows to GitHub Environments (e.g. `production`) configured with required approval reviewers and branch protection rules.
4. **Credential Cleanup**: The workflow includes an unconditional `always()` step to erase private keys from the runner filesystem.