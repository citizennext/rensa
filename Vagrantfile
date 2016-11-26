# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-16.04"
  config.vm.network "private_network", ip: "192.168.60.10"
  config.vm.synced_folder "./", "/var/www/audioreg", create: true, group: "www-data", owner: "www-data"
  config.vm.provision "shell", path: "provision/local/setup.sh"
  config.vm.provider "virtualbox" do |vb|
    vb.name = "audioreg"
    vb.memory = "1024"
  end

end
