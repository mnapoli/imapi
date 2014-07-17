Vagrant.configure("2") do |config|

    config.vm.box = "ubuntu/trusty64"

    config.vm.network :private_network, ip: "192.168.56.103"
    config.ssh.forward_agent = true

    config.vm.provider "virtualbox" do |v|
        v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
        v.customize ["modifyvm", :id, "--name", "imapi"]
    end

    config.vm.provision :shell, :path => "vagrant/bootstrap.sh"

end
