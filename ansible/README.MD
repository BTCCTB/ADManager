# How to deploy PasswordManager ?
For deployment we use [ansistrano-deploy](https://github.com/ansistrano/deploy)

## Add the target host
One CentOS based host by group in the hostsfile (`./ansible/hosts.ini`)

```ini
[sample]
mynewtarget

```
## Add the variables
Target-specific variables are stored as group_vars (`./ansible/group_vars/`)

Sensitive data is stored in a vault (`./ansible/group_vars/sample/sample_vault.yml`)
```yaml
vault_database_host: 'my_db_host'
vault_database_root: 'root'
vault_database_root_password: 'my_db_host_root_password'
vault_database_user: 'my_db_user'
vault_database_password: 'my_db_user_password'
```

All others are included in a vars file (`./ansible/group_vars/sample/sample_vars.yml`)
```yaml
ansible_user: "myuser"
ansible_ssh_private_key_file: "./.ssh/sample/private_key"
project_deploy_dir: "/var/www/html"
host_server_name: "mynewtarget"
application_url: "http://{{ host_server_name }}/"
application_env: "DEV"
host_server_vhost_file: "/etc/apache2/sites-enabled/000-default.conf"
database_root: "{{ vault_database_root }}"
database_root_password: "{{ vault_database_root_password }}"
git_branch: "dev"
```

## Deploy !
To deploy on your new target, you must run this simple command on your new CentOS based host:

```bash
ansible-playbook ansible/deploy.yml --limit sample
```
