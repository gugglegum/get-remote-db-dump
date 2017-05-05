# Get Remote MySQL Dump
*Command-line utility to retrieve MySQL dump from remote system by SSH*

This is very simple utility written on PHP7 which uses PuTTY package to execute `mysqldump` command on remote server by SSH, gzip and save output to temporary file, download it and delete it on remote. This utility uses `plink.exe` and `pscp.exe` from PuTTY package. These executables must be available as system commands (their path must be added in %PATH% environment variable). Remote system must be UNIX-like system. Actually it's only because temporary file with DB dump creates in `/tmp` folder which usually isn't available on Windows systems. Since this utility uses PuTTY, it will work only on Windows. But it can be easily ported to Linux, just need to use linux analogs for `plink` and `pscp`. Thanks to MIT license you can port it on Linux and if you will share it with me I will add it to this project to make it cross-platform.

The usage of this utility is very simple:

```
php get_remote_mysqldump.php ssh-user@example.com ssh-password database-name db-user db-password [destination-path]
```

`destination-path` is the local path where downloaded MySQL dump will be saved. If omitted current dir (`.`) is used. File name of dump file has following format: `database_YYYYMMDD_hhmmss.sql.gz`.

Feel free to contact me if you have any questions or suggestions.
