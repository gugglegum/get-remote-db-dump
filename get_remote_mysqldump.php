<?php

// Expecting from 6 to 8 command line arguments
if (!($argc >= 6 && $argc <= 8)) {
    printHelp();
    exit(-1);
}

$sshUserHost = $argv[1];
$sshPassword = $argv[2];
$database = $argv[3];
$dbUser = $argv[4];
$dbPassword = $argv[5];
$destinationDir = isset($argv[6]) ? $argv[6] : '.';
$mysqlDumpArgs = isset($argv[7]) ? $argv[7] : '';
$backupFileName = '/tmp/' . $database . date('_Ymd_His') . '.sql.gz';

echo "Creating MySQL dump file for \"{$database}\" database on remote system...\n";
// Step 1: Make DB dump on remote system
if (!executeRemoteScript("mysqldump -u" . escapeshellarg($dbUser) . " -p" . escapeshellargspecial($dbPassword) . " " . escapeshellarg($database) . ($mysqlDumpArgs !== '' ? ' ' . $mysqlDumpArgs : '') . " | gzip > " . escapeshellarg($backupFileName))) {
    exit(1);
}

// Step 2: Download just created DB dump on remote system
echo "Downloading dump file from remote system...\n";
if (!downloadRemoteFile($backupFileName)) {
    exit(1);
}

echo "Deleting MySQL dump file on remote system...\n";
// Step 3: Remove already downloaded DB dump on remote system
if (!executeRemoteScript("rm " . escapeshellarg($backupFileName))) {
    exit(1);
}

function executeRemoteScript(string $script): bool
{
    global $sshUserHost, $sshPassword;
    $tempRemoteScript = tempnam(sys_get_temp_dir(), 'get_remote_db_backup');
    file_put_contents($tempRemoteScript, $script);
    passthru("plink -ssh -C -pw " . escapeshellargspecial($sshPassword) . " -m " . escapeshellarg($tempRemoteScript) . " " . escapeshellarg($sshUserHost), $retCode);
    unlink($tempRemoteScript);
    return $retCode === 0;
}

function downloadRemoteFile($remoteFile): bool
{
    global $sshUserHost, $sshPassword, $destinationDir;
    passthru("pscp -pw " . escapeshellargspecial($sshPassword) . " " . escapeshellarg($sshUserHost . ":" . $remoteFile) . ' ' . escapeshellarg($destinationDir), $retCode);
    return $retCode === 0;
}

function printHelp()
{
    echo "Remote DB dump retrieving tool\n\n";
    echo "Usage:\n";
    echo "\tphp " . basename(__FILE__) . " <ssh-user@host> <ssh-password> <database> <db-user> <db-password> [<destination-dir>] [\"<mysqldump-arguments>\"]\n\n";
    echo "<ssh-user@host>         SSH user and host\n";
    echo "<ssh-password>          SSH password\n";
    echo "<database>              Name of database (scheme) to dump, compress and download\n";
    echo "<db-user>               MySQL user on remote host\n";
    echo "<db-password>           MySQL password on remote host\n";
    echo "<destination-dir>       Optional local destination directory where downloaded compressed database dump will be saved\n";
    echo "<mysqldump-arguments>   Optional additional arguments to pass directly to mysqldump command on remote host\n";
    echo "                        Use it to pass \"--ignore-table=schema.table1 --ignore-table=schema.table2\" to exclude table1\n";
    echo "                        and table2 from dump or any other additional options you need. Pass multiple arguments in double\n";
    echo "                        quotes and escape special characters if needed since they are passed directly without any escape.\n";
    echo "\nPlease note that this utility depends on the PuTTY package. It needs plink.exe & pscp.exe. And they should be in %PATH% environment variable\n";
}

/**
 * Replacement of escapeshellarg() to deal with special characters. Some password can contain "!" symbols, original
 * escapeshellarg() replaces them by spaces.
 */
function escapeshellargspecial($arg)
{
    return '"' . str_replace("'", "'\"'\"'", $arg) . '"';
}
