<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Information;
use App\About;
use App\CategoryRoom;
use App\Description;
use App\Slide;
use App\CategoryFood;
use App\Review;
use App\Event;
use App\Reservation;
use App\Room;
use App\User;
use App\DetailBill;
use App\Food;
use App\Repositories\CategoryFood\CategoryFoodInterface;
use App\Builder\PageBuilder;
class PageController extends Controller
{
    
    function __construct(
        CategoryFoodInterface $category_food
        // Information $information, 
        // About $about,
        // Description $description,
        // Slide $slide,
        // Event $event,
        // CategoryRoom $category
    ) {   
        $this->category_food=$category_food;

        $builder = new PageBuilder($this);
        $this->builder=$builder->setInfor()
                               ->setAbout()  
                               ->setDescription()
                               ->setSlide()
                               ->setEvent()
                               ->setCategory(); 
        $this->shareView();                       

    	//$this->infor=$information->find(0);
    	// $this->about=$about->find(1);
    	// $this->description=$description->find(1);
    	// $this->slide=$slide->all();
    	// $this->event=$event->all();
    	// $this->category=$event->all();
                               
    	// view()->share('infor', $this->infor);
    	// view()->share('about', $this->about);
    	// view()->share('description', $this->description);
    	// view()->share('slide', $this->slide);
    	// view()->share('event', $this->event);
    	// view()->share('category', $this->category);
    }

    public function shareView()
    {
        view()->share('infor', $this->builder->getInfor());
        view()->share('about', $this->builder->getAbout());
        view()->share('description', $this->builder->getDescription());
        view()->share('slide', $this->builder->getSlide());
        view()->share('event', $this->builder->getEvent());
        view()->share('category', $this->builder->getCategory());
    }
    // public function adduser()
    // {
    //     $user=new User;
    //     $user->name='duy';
    //      $user->password=bcrypt('123');
    //     $user->save();
    //     return redirect('admin/login')->with('annoucement','them thanh cong');
    // }
    public function Home()
    {
        $food_category=CategoryFood::all();
        $food_category=$this->category_food->GetAll();
        //return $this->builder->getInfor();
    	$review=Review::all();
        // $food=new Food();
        // $food_data=$food->GetById(3);
        // var_dump($food_data);return;
    	return view('pages.Home',['food_category'=>$food_category,'review'=>$review]);
    }
    public function About()
    {
    	return view('pages.About');
    }
    public function Event()
    {
    	return view('pages.Events');
    }
    public function Rooms()
    {
    	return view('pages.Rooms');
    }
    public function Reservation($idCate)
    {
        
        return view('pages.Reservation',['idCate'=>$idCate]);
    }
    public function postReservation(Request $request)
    {
        $room=Room::where('Status',1)->where('idCategory',$request->room)->get();
        if (count($room)>0) 
        {
            $roomtaken=Room::where('Status',1)->where('idCategory',$request->room)->take(1)->get();
            
            $this->validate($request,
            [
                'name'=>'required',
                'email'=>'required',
                'phone'=>'required',
                'datein'=>'required',
                'dateout'=>'required',
                'numbers'=>'required'
            ],
            [
                'name.required'=>"B???n ch??a nh???p t??n",
                'email.required'=>"B???n ch??a nh???p email",
                'phone.required'=>"B???n ch??a nh???p s??? ??i???n tho???i",
                'datein.required'=>"B???n ch??a nh???p ng??y ?????n",
                'dateout.required'=>"B???n ch??a nh???p ng??y ??i",
                'numbers.required'=>"B???n ch??a nh???p s??? l?????ng",

            ]);
            $reservation=new Reservation;
            $reservation->name=$request->name;    
            $reservation->email=$request->email; 
            $reservation->phone=$request->phone; 
            $reservation->DateIn=$request->datein; 
            $reservation->DateOut=$request->dateout; 
            $reservation->Numbers=$request->numbers; 
            $reservation->Notes=$request->notes; 
            $reservation->idRoom=$roomtaken[0]->id; 
            $roomtaken[0]->Status=0;
            $roomtaken[0]->save();

            $reservation->save();

            $r=Room::find($reservation->idRoom);
            $cate=CategoryRoom::find($r->idCategory);
           
            $day= (strtotime($reservation->DateOut) - strtotime($reservation->DateIn))/60/60/24;
            $bill=new DetailBill;
            $bill->content='Ti???n ph??ng';
            $bill->price= $cate->price*$day;
            $bill->idReservation=$reservation->id;
            $bill->created_at=$request->dateout;
            $bill->save();  
            return redirect('reservation/{1}')->with('annoucement','?????t ch??? th??nh c??ng.Ph??ng c???a b???n l?? '.$roomtaken[0]->name .'  .See you soon !');
        }
        else return redirect('reservation/{1}')->with('annoucement','Lo???i ph??ng b???n ?????t ???? h???t. Vui l??ng tham kh???o c??c lo???i ph??ng c??n l???i trong h??? th???ng kh??ch s???n. Xin c???m ??n !');
    }

}
