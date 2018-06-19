#!/bin/bash

# copy this content in the project-dir/.git/hooks/post-receive

# getting the git directory of the project
export GIT_DIR="$(cd $(dirname $(dirname $0));pwd)"

LOG_FILE=/home/theapolis/deployment-logs/$(date +%s).log

# Loop, because it is possible to push more than one branch at a time. (git push --all)
while read oldrev newrev refname
do
    # getting the necessary information from the push
    export DEPLOY_BRANCH=$(git rev-parse --symbolic --abbrev-ref $refname)
    export DEPLOY_OLDREV="$oldrev"
    export DEPLOY_NEWREV="$newrev"
    export DEPLOY_REFNAME="$refname"


    if [ "$DEPLOY_NEWREV" = "0000000000000000000000000000000000000000" ]; then
        echo "theapolis-deployment: This ref has been deleted"
        exit 1
    fi

     # checking if the branch has been set
     if [ -n "$DEPLOY_BRANCH" ]; then

        echo "// switching to the project root directory"
        # change directory to the project-dir in order to run the commands
        cd ..

       # set it to ignore so we can do operations in non-bare git repo
       # git config --local receive.denyCurrentBranch ignore

        # switch to the pushed branch
        git checkout -f "${DEPLOY_BRANCH}" || exit 1
        # switch to latest ref hash
        git reset --hard "$DEPLOY_NEWREV" || exit 1

        echo "// Cleaning cache dev|prod"

        rm -rf var/cache/
        rm -rf /tmp/symfony/cache/

        echo "// running composer..."

        composer install --no-dev --optimize-autoloader

        php app/console doctrine:cache:clear-metadata --env=prod

        php app/console doctrine:cache:clear-query --env=prod

        php app/console doctrine:cache:clear-result --env=prod

        php app/console d:s:u --force

        php app/console fos:js-routing:dump --env=prod

        php app/console ass:d --env=prod

        php app/console cache:warmup --env=prod

        echo "Deploy Finished" >> $LOG_FILE

        echo "   /================================/"
        echo "     THEAPOLIS DEPLOYMENT COMPLETED  "
        echo "     Target branch: $DEPLOY_BRANCH  "
        echo "   \================================/"
    fi
done

exit 0