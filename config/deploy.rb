# config valid only for current version of Capistrano
lock "3.8.0"

set :application, "integration"
set :repo_url, "git@github.com:jmily/integration.git"
set :branch, "master"

# Default branch is :master
# ask :branch, `git rev-parse --abbrev-ref HEAD`.chomp

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, "/home/escape/escape"

# Default value for :format is :airbrussh.
# set :format, :airbrussh

# You can configure the Airbrussh format using :format_options.
# These are the defaults.
# set :format_options, command_output: true, log_file: "log/capistrano.log", color: :auto, truncate: :auto

# Default value for :pty is false
# set :pty, true

# Default value for :linked_files is []
set :linked_files, fetch(:linked_files, []).push('app/config/parameters.yml')

# Default value for linked_dirs is []
set :linked_dirs, fetch(:linked_dirs, []).push('var')

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
# set :keep_releases, 5

namespace :symfony do
    namespace :doctrine do
        task :migrate do
            invoke 'symfony:console', 'doctrine:migrations:migrate', '--no-interaction'
        end
    end
    before 'cache:warmup', 'symfony:doctrine:migrate'
end