<?php
/**
 * Part of the Phing tasks collection by Ryan Chouinard.
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2010 Ryan Chouinard
 * @license New BSD License
 */

require_once 'phing/BuildFileTest.php';

/**
 * Simple testing of the Less compiler task. This test doesn't go too in-depth,
 * as it is assumed the upstream library is tested already.
 */
class LessCompilerTaskTest extends BuildFileTest
{

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->configureProject(PHING_TEST_BASE . '/files/lesscompiler.xml');
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->executeTarget('cleanup');
    }

    /**
     * @test
     */
    public function taskProducesCssFiles()
    {
        $this->executeTarget('main');
        $this->assertInLogs('Processing functions.less');
        $this->assertInLogs('Processing mixins.less');
        $this->assertInLogs('Processing nested_rules.less');
        $this->assertInLogs('Processing variables.less');

        $this->assertFileExists(PHING_TEST_BASE . '/files/tmp/functions.css');
        $this->assertFileExists(PHING_TEST_BASE . '/files/tmp/mixins.css');
        $this->assertFileExists(PHING_TEST_BASE . '/files/tmp/nested_rules.css');
        $this->assertFileExists(PHING_TEST_BASE . '/files/tmp/variables.css');
    }

}