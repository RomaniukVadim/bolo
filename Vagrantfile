# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.

#required_plugins = %w(vagrant-vbguest vagrant-winnfsd vagrant-hostsupdater)
#plugins_to_install = required_plugins.select { |plugin| not Vagrant.has_plugin? plugin }
#if not plugins_to_install.empty?
#  puts "Installing plugins: #{plugins_to_install.join(' ')}"
#  if system "vagrant plugin install #{plugins_to_install.join(' ')}"
#    exec "vagrant #{ARGV.join(' ')}"
#  else
#    abort "Installation of one or more plugins has failed. Aborting."
#  end
#end

name = "bolo"

Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "bento/ubuntu-16.04"
  config.vm.box_version = "= 2.2.9"
  
  config.vm.define name do |vapro|
  end
  
  #config.vbguest.auto_update = false
  #config.vbguest.no_remote = true

  config.vm.hostname = name + ".dev"
  
  if Vagrant.has_plugin? 'vagrant-hostsupdater'
	config.hostsupdater.remove_on_suspend = false
  end
  
  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  
  #config.vm.network "forwarded_port", guest: 3306, host: 3380

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.56.125"
  #config.vm.network :forwarded_port, guest: 80, host: 5000
  

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder ".", "/vagrant", disabled: true
  
  #if Vagrant.has_plugin? 'vagrant-winnfsd'
	#config.winnfsd.map_uid = Process.uid
	#config.winnfsd.map_gid = Process.gid
	#config.vm.synced_folder "./app", "/opt/reversea", nfs: true,
	#	mount_options: [
	#	  'nfsvers=3',
	#	  'vers=3',
	#	  'actimeo=1',
	#	  'rsize=8192',
	#	  'wsize=8192',
	#	  'timeo=14',
	#	  :nolock,
	#	  :tcp,
	#	  :intr,
	#	  :user,
	#	  :auto,
	#	  :exec,
	#	  :rw
	#	]
  #else
  config.vm.synced_folder "./app", "/opt/bolo", 
	owner: "vagrant",
	group: "www-data",
	mount_options: ["dmode=775,fmode=664"]
  #end

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |vb|
	vb.name = name
  
    # Set the timesync threshold to 10 seconds, instead of the default 20 minutes.
    # If the clock gets more than 15 minutes out of sync (due to your laptop going
    # to sleep for instance, then some 3rd party services will reject requests.
    #vb.customize ["guestproperty", "set", :id, "/VirtualBox/GuestAdd/VBoxService/--timesync-set-threshold", 10000]

    vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
	#vb.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/vagrant", "1"]
  
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
	vb.memory = "2048"
	vb.cpus = 2
  end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  
  # fix annoyance, http://foo-o-rama.com/vagrant--stdin-is-not-a-tty--fix.html
  config.vm.provision "fix-no-tty", type: "shell" do |s|
    s.privileged = false
    s.inline = "sudo sed -i '/tty/!s/mesg n/tty -s \\&\\& mesg n/' /root/.profile"
  end
  
  config.vm.provision "shell", privileged: false, path: "./provision.sh"
  
  config.vm.provision "shell", inline: 'sudo service nginx restart', run: 'always'
end
