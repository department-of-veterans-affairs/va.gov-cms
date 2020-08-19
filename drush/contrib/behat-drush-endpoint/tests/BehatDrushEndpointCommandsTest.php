<?php
namespace ExampleDrushExtension;

use PHPUnit\Framework\TestCase;
use Drush\TestTraits\DrushTestTrait;
use TestUtils\FixturesTrait;

/**
 * Some simple tests of the various operations of the 'behat' command.
 */
class BehatDrushEndpointCommandsTest extends TestCase
{
    use FixturesTrait;

    public function setUp()
    {
        $this->fixtures()->createSut();
    }

    public function tearDown()
    {
        $this->fixtures()->tearDown();
    }

    /**
     * Test to see if the 'behat' command can be found.
     */
    public function testBehatHelpParam()
    {
        $this->drush('behat', [], ['help' => true]);
        $output = $this->getOutput();
        $this->assertContains('Behat Drush endpoint. Serves as an entrypoint for Behat', $output);
    }

    /**
     * Test 'create-node'
     */
    public function testBehatCreateNode()
    {
        $this->drush('behat', ['create-node', '{"title":"Example page","type":"page"}']);
        $data = $this->getOutputFromJSON();
        $this->assertEquals('Example page', $data['title']);
        $this->assertEquals('page', $data['type']);
    }

    /**
     * Test 'create-term'
     */
    public function testBehatCreateTerm()
    {
        $this->drush('behat', ['create-term', '{"name":"Example term","vocabulary_machine_name": "tags"}']);
        $data = $this->getOutputFromJSON();
        $this->assertEquals('Example term', $data['name']);
        $this->assertEquals('tags', $data['vocabulary_machine_name']);
    }

}
