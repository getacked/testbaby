<?php

namespace App\Http\Controllers;

use Auth, Redirect, View, Storage, Input;
use App\Event;
use Stripe\Stripe;
use Stripe_Error;
use App\Photo;
use Carbon\Carbon;
use App\Tag;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Requests\EventRequest;
use App\Http\Requests\EventEditRequest;
use App\Http\Controllers\Controller;
use File;
use QrCode;
use App\Mailers\AppMailer;


class EventsController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth',
      ['except' => [
        'index',
        'show',
        'search',
        'searchPage'
      ]]);
    $this->middleware('user.type:normal', 
                ['only' => ['attend']]);
    $this->middleware('user.type:host',
      ['except' => [
        'show',
        'index',
        'attend',
        'search',
        'searchPage'
      ]]);
  }


  public function index()
  {
     $events = Event::orderBy('event_time')->upcoming()->get();

     return view('events.all', compact('events') );
  }

  public function show(Event $event, $id)
  { 
    $event = Event::findOrFail($id);
    return view('events.show', compact('event'));
  }

  public function edit(Event $event, $id)
  {
    $event = Event::find($id);
    return view('events.edit', compact('event'));
  }


  public function create(){
    //create a list of all most commonly used tags
    $tags = Tag::lists('name');
    return view('events.create', compact('tags'));
  }


  public function search(Request $request){
    // $client = new \AlgoliaSearch\Client("DL6Q2SNWBH", "d88cd24d64152b62f3eb02bfdc75eb6c");

    // $events = Event::orderBy('event_time')->upcoming()->get()->toJson();
    // dd($events);
    // $myIndex = $client->initIndex("dev_events");
    // $myIndex->addObject($events);
    
    $initialSearch = $request->searchTerm;
    $fromSearchbar = true;
    return View::make('events.search', compact(['initialSearch', 'fromSearchbar']));
  }

  public function searchPage(){
    $initialSearch = '';
    $fromSearchbar = false;
    return View::make('events.search', compact(['initialSearch', 'fromSearchbar']));
  }

  public function store(EventRequest $request)
  {
    $user = Auth::user();
//extract attributes from input
    $attributes = $request->only('name', 'place_name', 'event_type', 'description', 'ticket_cap', 'gmaps_id', 'ticket_price');
    $attributes['ticket_left'] = $request['ticket_cap'];
    $attributes['host_id'] = $user->id;

// parse date and time to create a carbon instance
    $dateTime = $request['event_date'] . " " . $request['event_time'];
    $attributes['event_time'] = Carbon::createFromFormat('d F, Y H:i', $dateTime);

// create an Event and associate the user as host
    $event = Event::create($attributes)->host()->associate($user);

// trim custom tags for whitespace and make array
    $trimmedTags = preg_replace('/\s+/', '', $request['customTags']);
    $tags = explode(',', $trimmedTags);

    if($request['tags'])
    {
      foreach($request['tags'] as $tag)
      {
        $event->tags()->attach(Tag::find($tag));
      }  
    }

    //
    if($tags)
    {
      foreach($tags as $tag)
      { 
        $event->tags()->attach(Tag::firstOrCreate(array('name' => strtolower($tag))));
      }  
    }

    if( $request->file('image') ){
      $imageFile = $request->file('image');

    //make timestamp and append event name for filename
      $timestamp = str_replace([' ', ':'], '-', Carbon::now()->toDateTimeString());
      $filename = $timestamp . '-' . $event->name;
    //remove 'image/' from MIME type
      $mime = "." . substr($imageFile->getMimeType(), 6);
    //move file to /public/images/
      $filename = $timestamp . '-' . $event->name;
    
      $photoData = array('fileName' => $filename, 'mime' => $mime);
      $photo = Photo::create( $photoData );
    //move uploaded file to public dir
      $imageFile->move( public_path().'/images/uploads/', $filename . $mime );
  
    //associate the image with the user
      $event->photo_id = $photo->id;
      $event->photo()->associate( $photo );
      $event->save();
    }

    // Create QR code.
    $code = QrCode::format('png')->size(350)->generate(url('/events') . '/' . $event->id);
    $qrPath = public_path(). '/images/qrcodes/' . $event->id . '.png';
    File::put($qrPath, $code); 

    // show all events
    return Redirect::route('events.show', $event);
  }



  /**
   * Update the event's details
   * 
   * @param  EventEditRequest $request The User's Request
   * @param  [type]           $id      id of the event
   */
  public function update(EventEditRequest $request, $id){

    $event = Event::find($id);

    // Parse date and time into a carbon instance 
    $dateTime = $request['event_date'] . " " . $request['event_time'];
    $carbon = Carbon::createFromFormat('d F, Y H:i', $dateTime);
    $request['event_time'] = $carbon;

  // remove all old tags before attaching new ones
    $event->tags()->detach($event->tags()->lists('id')->toArray());
  // trim custom tags for whitespace and make array
    $trimmedTags = preg_replace('/\s+/', '', $request['customTags']);
    $tags = explode(',', $trimmedTags);
    
    if($tags) {
      foreach($tags as $tag) { 
        $event->tags()->attach(Tag::firstOrCreate(array('name' => $tag)));
      }  
    }


    if($request['tags']) {
      foreach($request['tags'] as $tag) {
        $event->tags()->attach(Tag::find($tag));
      }  
    }

    if( $request->file('image') ){
      $imageFile = $request->file('image');

    //make timestamp and append event name for filename
      $timestamp = str_replace([' ', ':'], '-', Carbon::now()->toDateTimeString());
      $filename = $timestamp . '-' . $event->name;
    //remove 'image/' from MIME type
      $mime = "." . substr($imageFile->getMimeType(), 6);
    //move file to /public/images/
      $filename = $timestamp . '-' . $event->name;
    
      $photoData = array('fileName' => $filename, 'mime' => $mime);
      $photo = Photo::create( $photoData );
    //move uploaded file to public dir
      $imageFile->move( public_path().'/images/uploads/', $filename . $mime );


      $event->photo()->dissociate($event->photo_id);  

    //associate the image with the user
      $event->photo_id = $photo->id;
      $event->photo()->associate( $photo );
      $event->save();
    }
    
    $attributes = $request->only('name', 'event_time', 'event_type', 'gmaps_id', 'description');

  // Update the fields shown in the form 
    Event::find($id)->update($attributes);
    
  // Show updated event
    $event = Event::find($id);
    return view('events.show', compact('event'));
  }
  
  public function addTag(Event $event)
  {
    return "wowo";
  }

  public function attend(Request $request, Event $event, AppMailer $mailer)
  {
    \Stripe\Stripe::setApiKey( env("STRIPE_SK") );

    try {
      $token = $request->stripeToken;
      $charge = \Stripe\Charge::create(array(
        "amount" => $event->ticket_price * 100,
        "currency" => "eur",
        "source" => $token,
        "description" => $event->name) );
      $numTickets = isset($request->num_tickets) ? $request->num_tickets : 1;
      $event->attendees()->attach( Auth::user(), 
                    ['num_tickets' => $numTickets ] );
      $event->ticket_left = $event->ticket_left - $numTickets;
      $event->save();
    } catch(Stripe_CardError $e) {
      // Since it's a decline, Stripe_CardError will be caught
      $body = $e->getJsonBody();
      $err  = $body['error'];
    }

    // Send ticket email.
    $mailer->sendTicket(Auth::user(), $event);
    
    return Redirect::route('events.show', compact('event') );
  }

}