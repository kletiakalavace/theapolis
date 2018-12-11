#!/bin/bash
LOG_FILE=/home/theapolis/deployment-logs/$(date +%s).log

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
    echo "   | THEAPOLIS DEPLOYMENT COMPLETED |"
    echo "   \================================/"

exit 0
