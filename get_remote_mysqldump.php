<?php

if ($argc < 6 || $argc > 7) {
    printHelp();
    exit(-1);
}

$sshUserHost = $argv[1];
$sshPassword = $argv[2];
$database = $argv[3];
$dbUser = $argv[4];
$dbPassword = $argv[5];
$destinationDir = isset($argv[6]) ? $argv[6] : '.';
$backupFileName = '/tmp/' . $database . date('_Ymd_His') . '.sql.gz';

echo "Creating MySQL dump file for \"{$database}\" database on remote system...\n";
// Step 1: Make DB dump on remote system
if (!executeRemoteScript("mysqldump -u" . escapeshellarg($dbUser) . " -p" . escapeshellarg($dbPassword) . " " . escapeshellarg($database) . " | gzip > " . escapeshellarg($backupFileName))) {
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
    passthru("plink -ssh -C -pw " . escapeshellarg($sshPassword) . " -m " . escapeshellarg($tempRemoteScript) . " " . escapeshellarg($sshUserHost), $retCode);
    unlink($tempRemoteScript);
    return $retCode === 0;
}

function downloadRemoteFile($remoteFile): bool
{
    global $sshUserHost, $sshPassword, $destinationDir;
    passthru("pscp -pw " . escapeshellarg($sshPassword) . " " . escapeshellarg($sshUserHost . ":" . $remoteFile) . ' ' . escapeshellarg($destinationDir), $retCode);
    return $retCode === 0;
}

function printHelp()
{
    echo "Remote DB dump retrieving tool\n\n";
    echo "Usage:\n";
    echo "\tphp " . basename(__FILE__) . " <ssh-user@host> <ssh-password> <database> <db-user> <db-password> [<destination-dir>]\n";
    echo "\nPlease note that this utility depends on the PuTTY package. It needs plink.exe & pscp.exe. And they should be in %PATH% environment variable\n";
}
