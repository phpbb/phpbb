require 'json'
require 'yaml'

VAGRANTFILE_API_VERSION ||= "2"
confDir = $confDir ||= File.expand_path("phpBB/vendor/laravel/homestead", File.dirname(__FILE__))

homesteadYamlPath = "vagrant/bootstrap.yaml"
afterScriptPath = "vagrant/after.sh"
aliasesPath = "vagrant/aliases"

require File.expand_path(confDir + '/scripts/homestead.rb')

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    if File.exist? aliasesPath then
        config.vm.provision "file", source: aliasesPath, destination: "~/.bash_aliases"
    end

    if File.exist? homesteadYamlPath then
        Homestead.configure(config, YAML::load(File.read(homesteadYamlPath)))
    end

    if File.exist? afterScriptPath then
        config.vm.provision "shell", path: afterScriptPath
    end
end
