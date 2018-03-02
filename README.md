# Get Remote MySQL Dump
*Command-line utility to retrieve MySQL dump from remote system by SSH*

This is a very simple utility written on PHP7 which uses PuTTY package for Windows to execute `mysqldump` command on remote server by SSH, gzip it and save output to the temporary file, download it and delete it on remote host. This utility uses `plink.exe` and `pscp.exe` from PuTTY package. These executables must be available as system commands (their path must be added into %PATH% environment variable).

Remote system must be UNIX-like system. Actually it's only because temporary file with DB dump created in `/tmp` folder which usually isn't available on Windows systems. Since this utility uses PuTTY, it will work only on Windows. But it can be easily ported to Linux, just need to use linux analogs for `plink` and `pscp`. Thanks to MIT license you can port it on Linux and if you will share it with me I will add it to this project to make it cross-platform.

The usage of this utility is very simple:

```
php get_remote_mysqldump.php ssh-user@example.com ssh-password database-name db-user db-password [destination-path] [mysqldump-args]
```

`destination-path` is the local path where downloaded MySQL dump will be saved. If omitted current dir (`.`) is used. File name of the dump file has following format: `database_YYYYMMDD_hhmmss.sql.gz`.

`mysqldump-args` is optional argument that allows you to pass some particular command line arguments directly to remote mysqldump. They will be added as is at the end of command. It can be used, for example, to exclude some tables from dump by passing `"--ignore-table=schema.table1 --ignore-table=schema.table2"`. You should enclose all arguments inside double quotes and escape all special characters inside since they will be passed directly without escape.

Feel free to contact me if you have any questions or suggestions.
