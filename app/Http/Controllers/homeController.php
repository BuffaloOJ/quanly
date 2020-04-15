<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller;
use App\Repositories\UserRepository;
use Session;
use Auth;
use App\User;
use App\giangvien;
use App\sinhvien;
use App\khoa;
use App\lop;
use App\detai;
class homeController extends sharecontroller
{
    function __construct() { 
        view::share('stt','1');

        $detai = detai::get();
        view::share('detai',$detai);

        $user = user::get();
        view::share('user',$user);

        $danhsachdt = detai::join('sinhvien','sinhvien.id','detais.idsinhvien')
        ->where('daduyet','1')->where('thamkhao','0')->get();
        view::share('danhsachdt',$danhsachdt);

        $thamkhao = detai::join('sinhvien','sinhvien.id', 'detais.idsinhvien')
        ->where('daduyet','1')->where('thamkhao','1')->get();
        view::share('thamkhao',$thamkhao);

        $duyet = sinhvien::join('detais','detais.idsinhvien', 'sinhvien.id')
        ->where('daduyet','0')->get();
        view::share('duyet',$duyet);

        $gvlist = giangvien::get();
        view::share('gvlist',$gvlist);

        $svlist = sinhvien::get();
        view::share('svlist',$svlist);
        
        // $this->middleware('auth')->except('logout');
        // if(auth::check()){
        $this->middleware(function ($request, $next) {
        $this->id = Auth::user();
        if($this->id!=null){
            $id = Auth::user()->id;
            $sinhvien = sinhvien::where('idusers',$id)->get();
            $giangvien = giangvien::where('idusers',$id)->get();    
            view::share('sinhvien',$sinhvien);
            view::share('giangvien',$giangvien);
            view::share('iduser',$id);
            $dkdt = detai::join('sinhvien','sinhvien.id', 'detais.idsinhvien')
            ->where('idusers',$id)->get();
            view::share('dkdt',$dkdt); 
            return $next($request);
        }else{
            return $next($request);
        }
    });
    }
    public function gethome(){
        return view('pages.home');
    }
    //đề tài//
    public function alldt(){
        return view('pages.danhsachdetai');
    }

    public function getdkdetai(){
        if(Auth::check())
        {   
            return view('pages.dangkydetai');
        }
        else{
            return redirect()->route('getlogin');
        }
    }

    public function dkdetai(Request $request){
        $this->validate($request,[
            'idsinhvien'=> 'required',

            'tendt'=> 'required'
        ],[
            'tendt.required'=>'Bạn chưa nhập tên đề tài'
        ]);
        $sinhvien = sinhvien::find($request->idsinhvien);
        $sinhvien->gvhd = $request->gv;
        $sinhvien->save();
        $detai = new detai;
        $detai->idsinhvien = $request->idsinhvien;
        $detai->tendetai = $request->tendt;
        $detai->mota = $request->mota;
        $detai->daduyet = 0;
        $detai->thamkhao = 0;
        $detai->save();
        return redirect()->route('getdkdetai')->with('status',"Đăng ký đề tài thành công");
    }
    public function getduyetdt(){
        return view('pages.duyetdetai');
    }
    public function duyetdt(Request $request){
        $iddetai = $request->iddetai;
        $svdt = $request->svdt;
        $this->validate($request,[]);
        $duyetdt = detai::where('id',$iddetai);
        if ($request->duyet == 'duyet'){
            $daduyet = $duyetdt->update(['daduyet'=>1]);
            if($daduyet){
                return redirect()->route('getduyetdt')
                ->with('status',"Đã duyệt đề tài của sinh viên $svdt");
            }
            else{
            return redirect()->route('getduyetdt')
            ->with('status',"Duyệt thất bại");
            } 
        }
        if ($request->xoa == 'xoa'){
            $xoadt = $duyetdt->delete();
            if($xoadt){
                return redirect()->route('getduyetdt')
                ->with('status',"Đã xóa đề tài của sinh viên $svdt");
            }
            else{
            return redirect()->route('getduyetdt')
            ->with('status',"Xãy ra lỗi trong quá trình xóa");
            }
        }
    }
    public function thamkhao(){
        return view('pages.thamkhao');
    }
    function quanlyUser() {
        return view('admin');
    }
    public function editUser(Request $request){
        $user = User::find($request->id);
        if($user->email == $request->email){
            $this->validate($request,[
                    'email'=>'required|email',
                ],[
                    'email.required'=>'Chưa nhập email',
                    'email.email'=>'Email không đúng định dạng'
                ]);
        }else{
            $this->validate($request,[
                'email'=>'required|email|unique:users,email',
            ],[
                'email.required'=>'Chưa nhập email',
                'email.email'=>'Email không đúng định dạng',
                'email.unique'=>'Email đã có người đăng ký'
            ]);
        }
            $user->email = $request->email;
            $user->level = $request->level;
            $user->save();
            if($user->save()){
                return redirect()->route('quanly')->with('status','Đã sửa thành công');
            } else{
                return redirect()->route('quanly')
                ->with('status',"Xãy ra lỗi trong quá trình sửa");
            }
    }
    public function delUser(Request $request){
        $delUser = user::where('id',$request->id)->delete();
    }
}
