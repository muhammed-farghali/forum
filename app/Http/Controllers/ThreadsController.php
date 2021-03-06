<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Filters\ThreadFilters;
use App\Thread;
use Illuminate\Http\Request;

class ThreadsController extends Controller
{
    public function __construct ()
    {
        $this->middleware( 'auth' )->except( [ 'index', 'show' ] );
    }

    public function index ( Channel $channel, ThreadFilters $filters )
    {
        $threads = $this->getThreads( $channel, $filters );
        if ( request()->wantsJson() ) {
            return $threads;
        }
        return view( 'threads.index', compact( 'threads' ) );
    }

    /**
     * @param Channel       $channel
     * @param ThreadFilters $filters
     * @return mixed
     */
    protected function getThreads ( Channel $channel, ThreadFilters $filters )
    {
        $threads = Thread::with( 'channel' )->latest()->filter( $filters );
        if ( $channel->exists ) {
            $threads->where( 'channel_id', $channel->id );
        }
        return $threads->get();
    }

    public function show ( $channel, Thread $thread )
    {
        return view( 'threads.show', [
            'thread'  => $thread,
            'replies' => $thread->replies()->paginate( 5 ),
        ] );
    }

    public function create ()
    {
        return view( 'threads.create' );
    }

    public function destroy ( $channel, Thread $thread )
    {
        $this->authorize('update', $thread);
        $thread->replies()->delete();
        $thread->delete();
        return redirect('/threads');
    }

    public function store ( Request $request )
    {
        $this->validate( $request, [
            'title'      => 'required',
            'excerpt'    => 'required',
            'body'       => 'required',
            'channel_id' => 'required|exists:channels,id',
        ] );
        $thread = Thread::create( [
            'user_id'    => auth()->id(),
            'channel_id' => request( 'channel_id' ),
            'title'      => request( 'title' ),
            'excerpt'    => request( 'excerpt' ),
            'body'       => request( 'body' )
        ] );

        return redirect( $thread->path() );
    }
}
