# Prerequisite
In order to get a clean working copy there are some steps to do on your own.

## Drop existing tables in your database
If you have set up a database already your are adviced to drop the tables first.
There is a command-line command that performs this action for you:
```Shell
PHPINTERPRETER app/console theaterjobs:drop-tables
```

Next update your database with the latest schema:
```Shell
PHPINTERPRETER app/console doctrine:schema:update --force
```

Now you can load the initial fixtures into your new generated database tables:
```Shell
PHPINTERPRETER app/console doctrine:fixtures:load
```

## Put organization logos into the upload folder
The organizations formerly known as partners have there own logos. If you have done the steps under [Drop existing tables in your database](#Drop-existing-tables-in-your-database) the filenames
are attached to the organization but the files are still missing. So grab a copy of the current logos and perform the following command:
```Shell
PHPINTERPRETER app/console theaterjobs:move-organization-logos <PATH-TO-THE-ORGANIZATION-LOGOS>
```

**have fun!**