<?php

namespace App;

use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;


class Event extends Model
{

  // use AlgoliaEloquentTrait;

  // public $indices = ['dev_events'];


  // public $algoliaSettings = [
  //     'attributesToIndex' => [
  //         'event_type', 
  //         'name',
  //         'event_time', 
  //         'description',
  //         'ticket_price',
  //         'ticket_left',
  //         'place_name',
  //     ]
  // ];

  // public static $autoIndex = true;

  protected $table = "events";
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'name', 'event_type', 'event_time', 'host_id', 'description', 'ticket_cap', 'ticket_left', 'venue_id', 'place_name', 'location_id', 'photo_id', 'gmaps_id', 'ticket_price'
  ];

  /**
   *  The attributes to be converted into Carbon instances.
   *
   *  @var array
   */
  protected $dates = [ 'created_at', 'updated_at', 'event_time' ];

  /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
  protected $hidden = [ 'password', 'remember_token' ];

  public function getAlgoliaRecord()
  {
    return array_merge($this->toArray(), [
      'hostname' => $this->host->name
    ]);
  }

  /* Event Relations */
 
  public function host(){
    return $this->belongsTo('App\User', 'host_id', 'id');
  }

  public function attendees()
  {
    return $this->belongsToMany('App\User', 'attending', 'event_id', 'user_id')->withPivot('id', 'num_tickets');
  }

  public function tags(){
    return $this->belongsToMany('App\Tag');
  }

  public function photo(){
    return $this->belongsTo('App\Photo');
  }


  /* Event Scopes */ 

  public function scopePopular($query)
  {
    return $query->where('tickets', '>', 100);
  }


  public function scopeUpcoming($query)
  {
    return $query->where('event_time', '>', Carbon::now())->orderBy('event_time');
  }

  public function scopeNextWeek($query, $amount)
  {
    return $query->where('event_time', '<', new Carbon('next week'))->take($amount);
  }

  public function scopeSuggested($query) {
    //Same as upcoming, just a place holder
    $subs = Auth::user()->subs->lists('id');
    return $query->where('host_id', 'in', $subs);
  }

  public function scopePast($query, User $user = null)
  {
    // if($user == null) {
    //   return $query->where('event_time', '<', Carbon::now())->orderBy('event_time');
    // }
    // else if($user->hasType('normal')) {
    //   $eventIds = DB::table('attending')->where('user_id', $user->id)->lists('event_id');
    //   return $query->whereIn('id', $eventIds)->where('event_time', '<', Carbon::now());
    // }
    // else {
    //   return $query->where('host_id', $user->id)->where('event_time', '<', Carbon::now())->orderBy('event_time');
    // }

    return $query->where('event_time', '<', Carbon::now());

  }


}
