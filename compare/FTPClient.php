<?php
/**
 * Dump helper. Functions to dump variables to the screen, in a nicely formatted manner.
 * @author Junaid Ahmed
 * @version 1.0
 */

class FTPClient
{
    // *** Class variables

    private $connectionId;
    private $loginOk = false;
    private $messageArray = array();

    public function __construct()
    {

    }

    private function logMessage($message)
    {
        $this->messageArray[] = $message;
    }

    public function getMessages()
    {
        return $this->messageArray;
    }

    public function connect($server, $ftpUser, $ftpPassword, $isPassive = false)
    {
        // *** Set up basic connection
        $this->connectionId = ftp_connect($server);

        // *** Login with username and password
        $loginResult = ftp_login($this->connectionId, $ftpUser, $ftpPassword);

        // *** Sets passive mode on/off (default off)
        ftp_pasv($this->connectionId, $isPassive);

        // *** Check connection
        if ((!$this->connectionId) || (!$loginResult)) {
            $this->logMessage('FTP connection has failed!');
            $this->logMessage('Attempted to connect to ' . $server . ' for user ' . $ftpUser, true);
            return false;
        } else {
            $this->logMessage('Connected to ' . $server . ', for user ' . $ftpUser);
            $this->loginOk = true;
            return true;
        }
    }

    public function makeDir($directory)
    {
        if (!is_dir($directory)) {
            // *** If creating a directory is successful...
            if (@ftp_mkdir($this->connectionId, $directory)) {
                $this->logMessage('Directory "' . $directory . '" created successfully');
                return true;
            } else {
                // *** ...Else, FAIL.
                $this->logMessage('Failed creating directory "' . $directory . '"');
                return false;
            }
        } else {
            $this->logMessage('Failed creating directory "' . $directory . '" already exist.');
        }

    }

    public function uploadFile($fileFrom, $fileTo)
    {
        // *** Set the transfer mode
        $asciiArray = array('txt', 'csv');
        $value = explode('.', $fileFrom);
        $extension = end($value);
        if (in_array($extension, $asciiArray)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }

        // *** Upload the file
        $upload = ftp_put($this->connectionId, $fileTo, $fileFrom, $mode);

        // *** Check upload status
        if (!$upload) {

            $this->logMessage('FTP upload has failed!');
            return false;

        } else {
            $this->logMessage('Uploaded "' . $fileFrom . '" as "' . $fileTo);
            return true;
        }
    }

    public function changeDir($directory)
    {
        if (ftp_chdir($this->connectionId, $directory)) {
            $this->logMessage('Current directory is now: ' . ftp_pwd($this->connectionId));
            return true;
        } else {
            $this->logMessage('Couldn\'t change directory');
            return false;
        }
    }

    public function getDirListing($directory = '.', $parameters = '-la')
    {
        // get contents of the current directory
        // $contentsArray = ftp_nlist($this->connectionId, $parameters . ' ' . $directory);
        $contentsArray = ftp_nlist($this->connectionId, $directory);

        return $contentsArray;
    }

    public function downloadFile($fileFrom, $fileTo)
    {

        // *** Set the transfer mode
        $asciiArray = array('txt', 'csv');

        $value = explode('.', $fileFrom);
        $extension = end($value);
        //$extension  = end(explode('.', $fileFrom));
        if (in_array($extension, $asciiArray)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }

        // try to download $remote_file and save it to $handle
        if (ftp_get($this->connectionId, $fileTo, $fileFrom, $mode, 0)) {
            $this->logMessage(' file "' . $fileTo . '" successfully downloaded');
            return true;
        } else {
            $this->logMessage('There was an error downloading file "' . $fileFrom . '" to "' . $fileTo . '"');
            return false;
        }

    }

    public function __deconstruct()
    {
        if ($this->connectionId) {
            ftp_close($this->connectionId);
        }
    }

}