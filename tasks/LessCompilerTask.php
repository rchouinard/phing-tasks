<?php
/**
 * Part of the Phing tasks collection by Ryan Chouinard.
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2010 Ryan Chouinard
 * @license New BSD License
 */

require_once 'phing/Task.php';

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
     * @var string
     */
    protected $_targetDir;

    /**
     * @var array
     */
    protected $_fileSets;

    /**
     * Task constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_fileSets = array ();
    }

    /**
     * Initialize the task.
     *
     * @return boolean
     */
    public function init()
    {
        // Require the bundled lessphp library.
        $reqPath = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'includes';
        require_once $reqPath . DIRECTORY_SEPARATOR . 'lessc.inc.php';

        // Tell the caller that everything is okay.
        return true;
    }

    /**
     * Perform the work.
     *
     * @return void
     */
    public function main()
    {
        // Make sure the target directory exists.
        if (!file_exists($this->_targetDir)) {
            $this->log('Creating target directory ' . $this->_targetDir);
            mkdir($this->_targetDir, 0755, true);
        }

        // Build the full path to the target directory.
        $targetPath = realpath($this->_targetDir);

        // Loop through the registered file sets.
        /* @var $fileSet FileSet */
        foreach ($this->_fileSets as $fileSet) {

            // Build the full path to the file set's source directory.
            $sourcePath = realpath($fileSet->getDir($this->project));

            // Get an array containing paths to files included in the set.
            $files = $fileSet->getDirectoryScanner($this->project)
                ->getIncludedFiles();

            // Loop through the files in the set.
            foreach ($files as $sourceFile) {

                // Build the target's file name.
                $targetFile = str_replace('.less', '.css', $sourceFile);

                // Build the full paths to the source and target files.
                $sourceFull = $sourcePath . DIRECTORY_SEPARATOR . $sourceFile;
                $targetFull = $targetPath . DIRECTORY_SEPARATOR . $targetFile;

                // If the target directory path does not exist, create it.
                if (!file_exists(dirname($targetFull))) {
                    $this->log(
                        'Creating target directory '
                            . $this->_targetDir
                            . DIRECTORY_SEPARATOR
                            . dirname($targetFile)
                    );
                    mkdir(dirname($targetFull), 0755, true);
                }

                // Perform the actual compilation step.
                $this->log("Compiling ${sourceFile}");
                $lessc = new lessc($sourceFull);
                file_put_contents($targetFull, $lessc->parse());

            } // End files in set loop.

        } // End registered file sets loop.
    }

    /**
     * Create a new FileSet instance in the local queue.
     *
     * This method handles embedded <fileset> tags within the task call. When
     * these are encountered, this method is called and a new FileSet inserted
     * into a local array. The instance is then returned by reference to the
     * calling code, which will populate the instance.
     *
     * @return FileSet
     */
    public function createFileSet()
    {
        $num = array_push($this->_fileSets, new FileSet);
        return $this->_fileSets[$num - 1];
    }

    /**
     * Set the desired target (output) directory path.
     *
     * @param string $path
     * @return void
     */
    public function setTargetDir($path)
    {
        $this->_targetDir = $path;
    }

}