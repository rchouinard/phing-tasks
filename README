# My Custom Phing Tasks

This project contains a collection of custom tasks and additions to the [Phing](http://phing.info) build tool for PHP.

## LESS Compiler Task

This custom task utilizes the [lessphp](https://github.com/leafo/lessphp) library by [leafo](https://github.com/leafo) to compile source files written in [LESS](http://lesscss.org) syntax into valid CSS. Usage is very easy. Assuming the task files are unpackaged to `rych/tasks`:

    <taskdef name="lessc" classname="rych.tasks.LessCompilerTask" />
    <target name="compile-less" description="Compile LESS to CSS">
        <lessc targetdir="path/to/published/css">
            <fileset dir="path/to/less/sources">
                <include name="*.less" />
            </fileset>
        </lessc>
    </target>

This block will define the lessc task using the custom task definition, and then define a target to compile all .less files in `path/to/less/sources` to .css files in `path/to/published/css`.

Because this task uses a bundled PHP port of the LESS engine, there is no need to have the LESS Ruby gem, or even Ruby, installed on the build machine. More information abot the bundled library, including potential differences between the PHP and Ruby versions, can be found here: http://leafo.net/lessphp/docs/