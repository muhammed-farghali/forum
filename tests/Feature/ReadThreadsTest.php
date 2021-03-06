<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ReadThreadsTest extends TestCase
{
    use DatabaseMigrations;
    protected $thread;

    /**
     * @test
     * @return void
     */
    public function a_user_can_view_all_threads ()
    {
        $this->get( '/threads' )
             ->assertSee( $this->thread->path() );
    }

    /**
     * @test
     * @return void
     */
    public function a_user_can_view_a_single_thread ()
    {
        $this->get( $this->thread->path() )
             ->assertSee( $this->thread->title );
    }

    /**
     * @test
     * @return void
     */
    public function a_user_can_view_replies_related_to_thread ()
    {
        $reply = factory( 'App\Reply' )->create( [ 'thread_id' => $this->thread->id ] );
        $this->get( $this->thread->path() )
             ->assertSee( $reply->body );
    }

    /**
     * @test
     */
    public function a_user_can_filter_threads_according_a_channel ()
    {
        $channel = create( 'App\Channel' );
        $threadInChannel = create( 'App\Thread', [ 'channel_id' => $channel->id ] );
        $threadNotInChannel = create( 'App\Thread' );

        $this->get( 'threads/' . $channel->slug )
             ->assertSee( $threadInChannel->path() )
             ->assertDontSee( $threadNotInChannel->path() );
    }

    /**
     * @test
     */
    public function a_user_can_filter_threads_by_any_username ()
    {
        $this->signIn( create( 'App\User', [ 'name' => 'alaaAli' ] ) );
        $threadByAlaa = create( 'App\Thread', [ 'user_id' => auth()->id() ] );
        $threadNotByAlaa = create( 'App\Thread' );

        $this->get( 'threads?by=alaaAli' )
             ->assertSee( $threadByAlaa->path() )
             ->assertDontSee( $threadNotByAlaa->path() );
    }

    /**
     * @test
     */
    public function a_user_can_filter_threads_by_popularity ()
    {
        $threadWithTwoReplies = create( 'App\Thread' );
        create( 'App\Reply', [ 'thread_id' => $threadWithTwoReplies->id ], 2 );
        $threadWithThreeReplies = create( 'App\Thread' );
        create( 'App\Reply', [ 'thread_id' => $threadWithThreeReplies->id ], 3 );
        $response = $this->getJson( 'threads?popular=1' )->json();
        $this->assertEquals( [ 3, 2, 0 ], array_column( $response, 'replies_count' ) );

    }

    /**
     * @test
     */
    public function an_authorized_user_may_delete_his_thread ()
    {
        $user = create( 'App\User' );
        $this->signIn( $user );
        $thread = create( 'App\Thread', [ 'user_id' => $user->id ] );
        $reply = create( 'App\Reply', [ 'thread_id' => $thread->id ] );
        $this->delete( $thread->path() );
        $this->assertDatabaseMissing( 'threads', [ 'id' => $thread->id ] );
        $this->assertDatabaseMissing( 'replies', [ 'id' => $reply->id ] );

    }

    protected function setUp (): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->thread = factory( 'App\Thread' )->create();
    }


}
