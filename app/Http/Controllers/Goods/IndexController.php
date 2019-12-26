<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
<<<<<<< HEAD

class IndexController extends Controller
{
    /**
     * 商品详情页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail()
    {
        return view('goods.detail');
=======
use App\Model\GoodsModel;
class IndexController extends Controller
{
    public function detail(Request $request)
    {
        $goods_id = $request->input('id');
        $goods = GoodsModel::find($goods_id);
        // echo "<pre>";print_r($goods->toArray());echo "</pre>";
        $data = [
            'goods' => $goods
        ];
        return view('goods.detail',$data);
>>>>>>> store
    }
}
