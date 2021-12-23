Vagrant.configure("2") do |config|
  ## Ensure required plugins are installed
  required_plugins = %w(vagrant-vbguest)
  plugin_installed = false
  required_plugins.each do |plugin|
    unless Vagrant.has_plugin? plugin
      system "vagrant plugin install #{plugin}"
      plugin_installed = true
    end
  end
  if plugin_installed == true
    exec "vagrant #{ARGV.join(' ')}"
  end

  config.vagrant.plugins = ['vagrant-vbguest']
  config.vm.box = "ubuntu/focal64"
  config.vm.network "private_network", ip: "192.168.56.20"
  config.vm.provider "virtualbox" do |vb|
    vb.gui = false
    vb.name = "my_little_wallpaper"
    vb.memory = "4096"
    vb.cpus = 1
  end
  config.vm.provision "prepare", type: "shell", path: "vagrant/provision.sh", privileged: false
end
