MIGRATION ARCHITECTURE
The migration system starts with a file containing the DDL (database description language) for the full database schema. If the database doesn't yet exist, the database will be created and this DDL will be run against it.

This DDL file should not contain any sort of CREATE DATABASE statement. This will be done separately before the DDL is run.

This DDL file may contain DDL to create users and grant them access to the database.

Once the database exists, the migrations are run against the database.

Each migration goes into its own directory. The directory name is the name of the migration. Migrations are applied in sorted order. If the migrations start with numbers, they are sorted by these numbers, otherwise they are sorted alphabetically.

The migration directory can contain files with SQL, Perl, or executable programs.

If a file ends in ".sql", the migration runner code will feed it to the appropriate command line utility for your database.
