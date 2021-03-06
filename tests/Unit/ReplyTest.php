<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    use DatabaseMigrations;
    protected $reply;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->reply = factory(\App\Reply::class)->create();
    }

    /**
     * @test
     * @return void
     */
    public function it_has_a_owner()
    {
        $this->assertInstanceOf('App\User', $this->reply->owner);
    }
}
