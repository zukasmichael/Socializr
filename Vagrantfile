Vagrant.configure("2") do |config|
  config.vm.box = "socializr"
  config.vm.box_url = "http://puppet-vagrant-boxes.puppetlabs.com/debian-607-x64-vbox4210.box"

  config.vm.network :private_network, ip: "192.168.56.110"

  config.vm.boot_timeout = 300

  config.vm.synced_folder "./", "/vagrant", id: "webroot", owner: "vagrant", group: "www-data", :nfs => false, :mount_options => ["dmode=775","fmode=775"]

  config.vm.usable_port_range = (2200..2250)
  config.vm.provider :virtualbox do |virtualbox|
    virtualbox.customize ["modifyvm", :id, "--name", "socializr-devbox"]
    virtualbox.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    virtualbox.customize ["modifyvm", :id, "--memory", "768"]
    virtualbox.customize ["setextradata", :id, "--VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]
  end

  config.puppet_install.version = "3.3.2"

  config.vm.provision :shell, :path => "shell/initial-setup.sh"
  config.vm.provision :shell, :path => "shell/librarian-puppet-vagrant.sh"
  config.vm.provision :puppet do |puppet|
    puppet.facter = {
      "ssh_username" => "vagrant"
    }

    puppet.manifests_path = "puppet/manifests"
    puppet.options = ["--verbose", "--hiera_config /vagrant/hiera.yaml", "--parser future"]
  end




  config.ssh.username = "vagrant"

  config.ssh.shell = "bash -l"

  config.ssh.keep_alive = true
  config.ssh.forward_agent = false
  config.ssh.forward_x11 = false
  config.vagrant.host = :detect
end

