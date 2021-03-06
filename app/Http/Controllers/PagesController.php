<?php

namespace App\Http\Controllers;

use App\Event;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Mailers\AppMailer;
use App\User;
use Illuminate\Http\Request;
use View;
use QrCode;
use File;

class PagesController extends Controller
{
  public function homepage(){
    $events = Event::orderBy('event_time')->get();
    return View::make('home', compact('events'));
  }

  public function contact(){
    return View::make('pages.contact');
  }

  public function sendContactMessage(Request $request, AppMailer $mailer) {
    // Validate request.
    $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required'
    ]);

    // Send message.
    $mailer->sendContactMessage($request);

    // Send message has been recieved.
    $mailer->sendRecievedContactMessage($request);
    
    // Flash message.
    session()->flash('message', 'Your message has been sent!  We will get back to you as soon as possible.');
    
    // Redirect back.
    return redirect('contact');
  }
  
  public function faq() {
    return View::make('pages.faq');
  }

  public function about() {
    return View::make('pages.about');
  }

  
}
