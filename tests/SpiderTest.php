<?php

use PHPUnit\Framework\TestCase;

class SpiderTest extends TestCase
{
   protected $spider;

   protected function setUp() : void
   {
      $url = "http://www.website.org";
      $name = "website";
      $Save = $this->createMock(SaveData::class);
      $this->spider = new Spider($url, $name, $Save);
      $this->assertInstanceOf(Spider::class, $this->spider);
   }

   // public function testObjectCreationSetsProperties()
   // {
   //    $spider = Mockery::mock('Spider')
   //          ->shouldAllowMockingProtectedMethods();

   //      $brute->shouldReceive('scanFile')
   //            ->once()
   //            ->andReturn(array());
   // }

   // public function testArraysAreEmpty()
   // {
   //    $url = "http://www.website.org";
   //    $name = "website";
   //    $Save = $this->createMock(SaveData::class);
   //    $this->spider = new Spider($url, $name, $Save);
   //    $this->assertEquals(null, $this->spider->queue);
   //    $this->assertEquals(0, $this->spider->crawled);
   // }

   public function testProperties()
   {
      $this->createMock(Spider::class)
         ->shouldAllowMockingProtectedMethods();

           $spider->shouldReceive('file_to_array')
                 ->twice()
                 ->andReturn(array());
      // $this->spider = new Spider($url, $name, $Save);
      $this->assertEquals($url, $this->spider->TARGET_URL);
   }
}