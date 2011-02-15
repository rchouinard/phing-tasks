<?php
/**
 * Part of the Phing tasks collection by Ryan Chouinard.
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2010 Ryan Chouinard
 * @license New BSD License
 */

/**
 * Defines a Phing task to run the YUI compressor against a set of JavaScript
 * or CSS files.
 *
 * A java binary must be available in the environment PATH for this task to
 * work.
 *
 * To use this task, include it with a taskdef tag in your build.xml file:
 *
 *     <taskdef name="yuic" classname="my.tasks.YuiCompressorTask" />
 *
 * The task is now ready to be used:
 *
 *     <target name="yui-compressor" description="Compress CSS and JavaScript">
 *         <yuic targetdir="path/to/target">
 *             <fileset dir="path/to/source">
 *                 <include name="*.css" />
 *                 <include name="*.js" />
 *             </fileset>
 *         </yuic>
 *     </target>
 *
 * This task makes use of the
 * {@link http://developer.yahoo.com/yui/compressor/ YUI compressor}. Version
 * 2.4.2 of the compiled jar file is bundled with this task, however a different
 * jar file may be specified using the optional jarpath attribute.
 */
class YuiCompressorTask extends Task
{

    /**
     * @var string
     */
    protected $_javaPath;

    /**
     * @var PhingFile
     */
    protected $_jarPath;

    /**
     * @var PhingFile
     */
    protected $_targetDir;

    /**
     * @var array
     */
    protected $_fileSets;

    /**
     * @return void
     */
    public function __construct()
    {
        $defaultJarPath = realpath(
            dirname(__FILE__) . '/includes/yuicompressor-2.4.2.jar'
        );

        $this->_javaPath = 'java';
        $this->_jarPath = new PhingFile($defaultJarPath);
        $this->_fileSets = array ();
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
        $this->_checkJarPath();
        $this->_checkTargetDir();

        /* @var $fileSet FileSet */
        foreach ($this->_fileSets as $fileSet) {

            $files = $fileSet->getDirectoryScanner($this->project)
                ->getIncludedFiles();

            foreach ($files as $file) {

                $targetDir = new PhingFile($this->_targetDir, dirname($file));
                if (!$targetDir->exists()) {
                    $targetDir->mkdirs();
                }
                unset ($targetDir);

                $source = new PhingFile($fileSet->getDir($this->project), $file);
                $target = new PhingFile($this->_targetDir, $file);

                $this->log("Processing ${file}");
                $cmd = escapeshellcmd($this->_javaPath)
                    . ' -jar ' . escapeshellarg($this->_jarPath)
                    . ' -o ' . escapeshellarg($target->getAbsolutePath())
                    . ' ' . escapeshellarg($source->getAbsolutePath());
                $this->log('Executing: ' . $cmd, Project::MSG_DEBUG);
                @exec($cmd, $output, $return);

                if ($return !== 0) {
                    $this->log("Failed processing ${file}!", Project::MSG_ERR);
                }
            }
        }
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
     * @param PhingFile $path
     * @return void
     */
    public function setJarPath(PhingFile $path)
    {
        $this->_jarPath = $path;
    }

    /**
     * @return void
     */
    protected function _checkJarPath()
    {
        if ($this->_jarPath === null) {
            throw new BuildException(
                'Path to YUI compressor jar file must be specified',
                $this->location
            );
        } else if (!$this->_jarPath->exists()) {
            throw new BuildException(
                'Unable to locate jar file at specified path',
                $this->location
            );
        }
    }

    /**
     * @param PhingFile $path
     * @return void
     */
    public function setTargetDir(PhingFile $path)
    {
        $this->_targetDir = $path;
    }

    /**
     * @return void
     */
    protected function _checkTargetDir()
    {
        if ($this->_targetDir === null) {
            throw new BuildException(
                'Target directory must be specified',
                $this->location
            );
        }
    }

}