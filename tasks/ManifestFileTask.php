<?php
/**
 * Part of the Phing tasks collection by Ryan Chouinard.
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2011 Ryan Chouinard
 * @license New BSD License
 */

/**
 * Defines a Phing task to create or verify a manifest file. This file simply
 * contains a listing of files in the specified FileSet along with a hash sum
 * value. This file can be used at a later time to verify the integrity of the
 * files.
 *
 * To use this task, include it with a taskdef tag in your build.xml file:
 *
 *     <taskdef name="manifestfile" classname="my.tasks.ManifestFileTask" />
 *
 * The task is now ready to be used:
 *
 *     <target name="create-manifest" description="Generate a Manifest file">
 *         <manifestfile file="Manifest">
 *             <fileset dir="path/to/source">
 *                 <include name="*.php" />
 *             </fileset>
 *         </manifestfile>
 *     </target>
 *
 *     <target name="verify-manifest" description="Verify the Manifest">
 *         <manifestfile file="Manifest" mode="verify">
 *             <fileset dir="path/to/source">
 *                 <include name="*.php" />
 *             </fileset>
 *         </manifestfile>
 *     </target>
 *
 * It is possible to specify the hashing algorithm to use by passing the name
 * through the algo attribute. Any algorithm supported by the PHP hash extension
 * may be used. By default, this task uses SHA256.
 */
class ManifestFileTask extends Task
{

    const MODE_CREATE = 'create';
    const MODE_VERIFY = 'verify';

    /**
     * @var string
     */
    protected $_algo;

    /**
     * @var PhingFile
     */
    protected $_file;

    /**
     * @var array
     */
    protected $_fileSets;

    /**
     * @var array
     */
    protected $_hashes;

    /**
     * @var string
     */
    protected $_mode;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_algo = 'sha256';
        $this->_fileSets = array ();
        $this->_hashes = array ();
        $this->_mode = self::MODE_CREATE;
        $this->_verify = false;
    }

    /**
     * @return boolean
     */
    public function init()
    {
        return true;
    }

    /**
     * @return void
     */
    public function main()
    {
        if (!extension_loaded('hash')) {
            throw new BuildException(
                'The hash extension must be loaded to use this task',
                $this->location
            );
        }

        $this->_checkAlgo();
        $this->_checkFile();

        /* @var $fileSet FileSet */
        foreach ($this->_fileSets as $fileSet) {

            $files = $fileSet->getDirectoryScanner($this->project)
                ->getIncludedFiles();
            $projBase = $this->project->getBasedir();

            foreach ($files as $file) {
                $file = new PhingFile($fileSet->getDir($this->project), $file);

                $path = realpath($file->getAbsolutePath());
                $hash = hash_file($this->_algo, $path);

                if (substr($path, 0, strlen($projBase)) == $projBase) {
                    $path = ltrim(
                        substr($path, strlen($projBase)),
                        DIRECTORY_SEPARATOR
                    );
                }

                $this->_hashes[$path] = $hash;
            }
            unset ($file, $path, $hash);
        }
        unset ($fileSet, $files, $projBase);

        ksort($this->_hashes);

        if (strtolower($this->_mode) == self::MODE_VERIFY) {
            $this->_verifyManifest();
        } else {
            $this->_writeManifest();
        }
    }

    /**
     * @return void
     */
    protected function _verifyManifest()
    {
        if (!$this->_file->isFile() || !$this->_file->canRead()) {
            throw new BuildException(
                'Failed reading from manifest file',
                $this->location
            );
        }

        $manifest = array ();
        $fp = fopen($this->_file, 'r');
        while (true == ($line = trim(fgets($fp)))) {
            list ($path, $hash) = explode("\t", $line);
            $manifest[$path] = $hash;
        }
        fclose($fp);

        $verified = true;

        // Check for files present which are not in the manifest
        $filesNotInManifest = array_keys(array_diff_key($manifest, $this->_hashes));
        if (!empty ($filesNotInManifest)) {
            $verified = false;
            $this->log(
                'There are ' . count($filesNotInManifest) . ' files present which are not listed in the manifest',
                PROJECT::MSG_WARN
            );
            foreach ($filesNotInManifest as $path) {
                $this->log(
                    'Extra file: ' . $path,
                    PROJECT::MSG_WARN
                );
            }
            unset ($path);
        }
        unset ($filesNotInManifest);

        // Check for files listed in the manifest which are not present
        $filesNotPresent = array_keys(array_diff_key($this->_hashes, $manifest));
        if (!empty ($filesNotPresent)) {
            $verified = false;
            $this->log(
                'There are ' . count($filesNotPresent) . ' files listed in the manifest which are not present',
                PROJECT::MSG_WARN
            );
            foreach ($filesNotPresent as $path) {
                $this->log(
                    'Missing file: ' . $path,
                    PROJECT::MSG_WARN
                );
            }
            unset ($path);
        }
        unset ($filesNotPresent);

        // Compare manifest hashes with the computed hashes
        $filesPresent = array_keys(array_intersect_key($manifest, $this->_hashes));
        foreach ($filesPresent as $path) {
            if ($manifest[$path] != $this->_hashes[$path]) {
                $verified = false;
                $this->log(
                    'Hashes do not match: ' . $path,
                    PROJECT::MSG_WARN
                );
            }
        }
        unset ($filesPresent);

        if (!$verified) {
            throw new BuildException(
                'Manifest verification failed'
            );
        }

        $this->log('Manifest verification successful');
    }

    /**
     * @return void
     */
    protected function _writeManifest()
    {
        $manifest = '';
        foreach ($this->_hashes as $path => $hash) {
            $manifest .= "${path}\t${hash}\n";
        }

        if (file_put_contents($this->_file, $manifest, LOCK_EX) === false) {
            throw new BuildException(
                'Failed writing to manifest file',
                $this->location
            );
        }

        $this->log('Wrote ' . filesize($this->_file) . ' bytes to ' . $this->_file);
    }

    /**
     * @return FileSet
     */
    public function createFileSet()
    {
        $num = array_push($this->_fileSets, new FileSet);
        return $this->_fileSets[$num - 1];
    }

    /**
     * @param string $algo
     * @return void
     */
    public function setAlgo($algo)
    {
        $this->_algo = $algo;
    }

    /**
     * @param PhingFile $manifestFile
     * @return void
     */
    public function setFile(PhingFile $manifestFile)
    {
        $this->_file = $manifestFile;
    }

    /**
     * @param string $mode
     * @return void
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
    }

    /**
     * @return void
     */
    protected function _checkAlgo()
    {
        $this->_algo = strtolower($this->_algo);

        if (!in_array($this->_algo, hash_algos())) {
            throw new BuildException(
                'An invalid hashing algorithm has been specified',
                $this->location
            );
        }
    }

    /**
     * @return void
     */
    protected function _checkFile()
    {
        if ($this->_file === null) {
            throw new BuildException(
                'Path to manifest file must be specified',
                $this->location
            );
        }
    }

}