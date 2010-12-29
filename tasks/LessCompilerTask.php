<?php
/**
 * Part of the Phing tasks collection by Ryan Chouinard.
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2010 Ryan Chouinard
 * @license New BSD License
 */

/**
 * Defines a Phing task to compile {@link http://lesscss.org LESS} syntax to
 * valid CSS.
 *
 * To use this task, include it with a taskdef tag in your build.xml file:
 *
 *     <taskdef name="lessc" classname="my.tasks.LessCompilerTask" />
 *
 * The task is now ready to be used:
 *
 *     <target name="compile-less" description="Compile LESS to CSS">
 *         <lessc targetdir="path/to/published/css">
 *             <fileset dir="path/to/less/sources">
 *                 <include name="*.less" />
 *             </fileset>
 *         </lessc>
 *     </target>
 *
 * This task makes use of {@link https://github.com/leafo/lessphp lessphp} by
 * GitHub user {@link https://github.com/leafo leafo}. The provided lessphp
 * library may differ slightly from the original Ruby version. See
 * {@link http://leafo.net/lessphp/docs/#differences the documentation} for
 * details.
 */
class LessCompilerTask extends Task
{

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
        $this->_fileSets = array ();
    }

    /**
     * @return boolean
     */
    public function init()
    {
        $reqPath = realpath(dirname(__FILE__))
            . DIRECTORY_SEPARATOR . 'includes';
        require_once $reqPath . DIRECTORY_SEPARATOR . 'lessc.inc.php';

        return true;
    }

    /**
     * @return void
     */
    public function main()
    {
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

                $source = new PhingFile(
                    $fileSet->getDir($this->project),
                    $file
                );
                $target = new PhingFile(
                    $this->_targetDir,
                    str_replace('.less', '.css', $file)
                );

                $this->log("Processing ${file}");
                try {
                    $lessc = new lessc($source->getAbsolutePath());
                    file_put_contents(
                        $target->getAbsolutePath(),
                        $lessc->parse()
                    );
                } catch (Exception $e) {
                    $this->log("Failed processing ${file}!", Project::MSG_ERR);
                    $this->log($e->getMessage(), Project::MSG_DEBUG);
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
    public function setTargetDir(PhingFile $path)
    {
        $this->_targetDir = $path;
    }

}