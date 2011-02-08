# My Custom Phing Tasks

This project contains a collection of custom tasks and additions to the [Phing](http://phing.info) build tool for PHP.

## LESS Compiler Task

This task utilizes the [lessphp](https://github.com/leafo/lessphp) library by [leafo](https://github.com/leafo) to compile source files written in [LESS](http://lesscss.org) syntax into valid CSS. Usage is very easy. Assuming the task files are unpackaged to `rych/tasks`:

    <taskdef name="lessc" classname="rych.tasks.LessCompilerTask" />
    <target name="compile-less" description="Compile LESS to CSS">
        <lessc targetdir="path/to/published/css">
            <fileset dir="path/to/less/sources">
                <include name="*.less" />
            </fileset>
        </lessc>
    </target>

This block will define the lessc task using the custom task definition, and then define a target to compile all .less files in `path/to/less/sources` to .css files in `path/to/published/css`.

Because this task uses a bundled PHP port of the LESS engine, there is no need to have the LESS Ruby gem, or even Ruby, installed on the build machine. More information abot the bundled library, including potential differences between the PHP and Ruby versions, can be found here: <http://leafo.net/lessphp/docs/>

### Task Attributes

#### Required
 - **targetdir** - Specifies the directory path for output files.

#### Optional
_There are no optional attributes for this task._

## Manifest File Task

A manifest file is simply a text file which contains a listing of files and their hash values. The ManifestFileTask is used to generate and/or verify such a file. This can be useful in ensuring the integrity of your project files.

Phing ships with an undocumented ManifestTask, although I was unsuccessful in getting it to work at all. To avoid conflicts, I've named this one ManifestFileTask instead.

Usage example:

    <taskdef name="manifestfile" classname="rych.tasks.ManifestFileTask" />
    <target name="generate-manifest" description="Generate a manifest file">
        <manifestfile file="Manifest">
            <fileset dir="path/to/source">
                <include name="*.php" />
            </fileset>
        </manifestfile>
    </target>
    <target name="verify-manifest" description="Verify a manifest file">
        <manifestfile file="Manifest" mode="verify">
            <fileset dir="path/to/source">
                <include name="*.php" />
            </fileset>
        </manifestfile>
    </target>

### Task Attributes

#### Required
 - **file** - Specifies path to the manifest file.

#### Optional
 - **algo** - The hashing algorithm which should be used. Any algorithm supported by PHP's [hash extension](http://php.net/manual/en/function.hash-algos.php) may be used. Defaults to sha256.
 - **mode** - One of _create_ or _verify_. Default is _create_.

## YUI Compressor Task

The [YUI compressor](http://developer.yahoo.com/yui/compressor/) is a java tool which can minimize CSS and JavaScript files, saving a few extra bytes. In order to use this task, the `java` executable **must** be available in the environment PATH.

Version 2.4.2 of the YUI compressor is included, however it is possible to specify an alternate jar file if needed.

Usage is simple:

    <taskdef name="yuic" classname="rych.tasks.YuiCompressorTask" />
    <target name="minimize-assets" description="Compress CSS and JavaScript">
        <yuic jarpath="jar/yuic.jar" targetdir="path/to/target">
            <fileset dir="path/to/source">
                <include name="*.css" />
                <include name="*.js" />
            </fileset>
        </yuic>
    </target>

### Task Attributes

#### Required
 - **targetdir** - Specifies the directory path for output files.

#### Optional
 - **jarpath** - Path to an alternate jar file.