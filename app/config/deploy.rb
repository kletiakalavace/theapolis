# ------------------------------------------------------------------------------
# 1. Servers configuration
# ------------------------------------------------------------------------------
set :domain,      "theater-leute.de"
set :serverName,   "theater-leute.de" # The server's hostname
set :application, "theapolis"
set :cache_path, "/var/cache"
set :log_path, "/var/logs"
# Roles
role :web,        domain
role :app,        domain, :primary => true

# ------------------------------------------------------------------------------
# 2. Security configuration
# ------------------------------------------------------------------------------
set :user,        "theapolis"
set :ssh_options, {:forward_agent => true}
#on :start do
#  `ssh-add`
#end

# ------------------------------------------------------------------------------
# 2.1. Database configuration
# ------------------------------------------------------------------------------
set :db_user, "theapolis"
set :db_name, "theaterjobs21"
set :db_password, "hYC9NTPlOLDt"
set :db_host, "localhost"

# ------------------------------------------------------------------------------
# 3. Github and SCM configuration
# ------------------------------------------------------------------------------
set :repository,   "git@github.com:theaterjobs/theaterjobs21.git"
set :scm,         :git
#set :user,        "janakaszas"
set :scm_passphrase, "jk0409"
set :branch, "master"
set :deploy_via,  :rsync_with_remote_cache
set :deploy_to,   "/home/theapolis/htdocs/theapolis" # Remote location where the project will be stored
set :keep_releases,  3 # The number of releases which will remain on the server

# ------------------------------------------------------------------------------
# 4. Project configuration
# ------------------------------------------------------------------------------
# Set some paths to be shared between versions
set :shared_files,    ["app/config/parameters.yml" , web_path + "/.htaccess"]
set :shared_children, [web_path + "/uploads", web_path + "/media", "vendor"]
# Update composer during the deploy
set :use_composer, true
set :update_vendors, true
# Update vendors during the deploy
#set :update_vendors, true
# Assetic dump
#set :dump_assetic_assets, true

# ------------------------------------------------------------------------------
# 5. Capifony parameters
# ------------------------------------------------------------------------------
set :use_sudo     , false     # auf domainfactory dürfen wir natürlich kein sudo benutzen.
# Set path to php
set :php_bin, "/usr/bin/php5"
set :local_rsync_bin, "/usr/bin/rsync"
logger.level = Logger::MAX_LEVEL

# ------------------------------------------------------------------------------
# 6. Logging
# ------------------------------------------------------------------------------
#require 'capistrano/log_with_awesome'
#on :exit do
#  put full_log, "#{deploy_to}/shared/logs/last_deploy.log"
#end

# ------------------------------------------------------------------------------
# 7. Tasks
# ------------------------------------------------------------------------------
namespace :deploy do

  task :drop_tables, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console theaterjobs:drop-tables"
  end

  task :update_db, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console doctrine:schema:update --force"
  end

  task :load_fixtures, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console doctrine:fixtures:load --fixtures=src/Theaterjobs/UserBundle/DataFixtures/ORM/ --fixtures=src/Theaterjobs/InserateBundle/DataFixtures/ORM/ --fixtures=src/Theaterjobs/NewsBundle/DataFixtures/ORM/ --fixtures=src/Theaterjobs/ProfileBundle/DataFixtures/ORM/"
  end

#   task :load_banknumbers, :roles => :web do
#     run "cd #{release_path} && #{php_bin} bin/console theaterjobs-shop:load-banknumbers"
#   end

  task :load_countries, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console theaterjobs:load-countries"
  end

  task :load_post_fixtures, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console theaterjobs-shop:load-post-fixtures"
  end

  task :load_organizations, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console theaterjobs:load-organizations"
  end

  task :assetic, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console --env=prod assetic:dump"
  end

  task :assets, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console --env=prod assets:install"
  end

  task :fosroutejs, :roles => :web do
    run "cd #{release_path} && #{php_bin} bin/console --env=prod fos:js-routing:dump"
  end

  task :chmod, :roles => :web do
    run "chmod 755 #{release_path} #{release_path}/web"
  end

end

# ------------------------------------------------------------------------------
# 8. Local Tasks
# ------------------------------------------------------------------------------
namespace :local do

#   task :rsync_assets do
#     run_local("#{local_rsync_bin} -avz --delete $HOME/html/theaterjobs2.1/src/Theaterjobs/MainBundle/Resources/public/components ssh-159502-svn@theaterjobs.de:theaterjobs21_test/shared/src/Theaterjobs/AssetBundle/Resources/public/components")
#   end

end

def run_local(cmd)
  system cmd
  if($?.exitstatus != 0) then
    puts 'exit code: ' + $?.exitstatus.to_s
    exit
  end
end


# before "deploy", "local:rsync_assets"

#after "deploy", "deploy:cleanup", "deploy:drop_tables", "deploy:update_db",
#    "deploy:load_fixtures", "deploy:load_countries",
#    "deploy:load_post_fixtures", "deploy:load_organizations", "deploy:chmod"

#after "deploy", "deploy:cleanup", "deploy:drop_tables", "deploy:update_db",
#      "deploy:load_fixtures", "deploy:chmod", "deploy:fosroutejs", "deploy:assets", "deploy:assetic"
#
after "deploy", "deploy:cleanup", "deploy:drop_tables", "deploy:update_db",
      "deploy:load_fixtures", "deploy:chmod", "deploy:fosroutejs", "deploy:assets", "deploy:assetic"
