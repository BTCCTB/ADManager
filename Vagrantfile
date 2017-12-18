Vagrant.configure("2") do |config|

  config.vm.box = "centos/7"

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "forwarded_port", guest: 3306, host: 3306
  config.vm.network "private_network", ip: "192.168.33.10"

  config.vm.synced_folder ".", "/vagrant", type: "rsync",
    rsync__exclude: [
        ".git/",
        ".db/",
        "vendor/",
        "var/logs/*",
        "var/sessions/*",
        "var/cache/*"
    ]

  config.vm.provider "virtualbox" do |vb|
    vb.customize [ "modifyvm", :id, "--uartmode1", "file", File.join(Dir.pwd, "var/logs/console.log") ]
  end

  config.vm.provision "shell", inline: <<-SHELL
     yum check-update
     yum install -y python
  SHELL

  # Run Ansible from the Vagrant Host
  config.vm.provision "ansible" do |ansible|
    ansible.limit = "dev"
    ansible.playbook = "./ansible/provision.yml"
    ansible.groups = {
      'dev' => ['default']
    }
    ansible.host_key_checking = false
    ansible.vault_password_file = "./.vagrant/vault_password_file.txt"
  end

  #config.vm.provision "ansible" do |ansible|
  #  ansible.limit = "dev"
  #  ansible.playbook = "./ansible/deploy.yml"
  #  ansible.groups = {
  #    'dev' => ['default']
  #  }
  #  ansible.host_key_checking = false
  #  ansible.vault_password_file = "./.vagrant/vault_password_file.txt"
  #end
end
