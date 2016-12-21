<?php
/******************************************
****AuThor:rubbish.boy@163.com
****Title :购物车
*******************************************/
namespace App\Http\Controllers\Api\V1\Xcx;

use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DB;
use Cache;
use App\Http\Model\Xcxshoppingcart;

class ShoppingcartController extends PublicController
{
	/******************************************
	****AuThor:rubbish.boy@163.com
	****Title :添加接口
	*******************************************/
	public function api_add(Request $request)  
	{
		$request_token=$this->request_token;
		if($request_token['status']==1)
		{
			$param=$request_token['request'];
			$xcxmp=$request_token['data'];
			$appid=$xcxmp['appid'];
			$appsecret=$xcxmp['appsecret'];
			$session_id=@$param['session_id'];
			if(@$session_id)
			{
				$session_openid=Cache::store('redis')->get($session_id);
				if(@$session_openid)
				{
					$openid=substr($session_openid, -28);
					$condition['openid']=$openid;
					$xcxuser=object_array(DB::table('xcxusers')->where($condition)->first());
					if($xcxuser)
					{
						$formdata=$param['formdata'];
						//判断是否存在购物车
						$info_condition['item_id']=$formdata['item_id'];
						$info_condition['status']=0;
						$info_condition['xcxuser_id']=$xcxuser['id'];
						$info=object_array(DB::table('xcxshoppingcarts')->where($info_condition)->first());
						if($info)
						{
							$qty=$formdata['qty']?$formdata['qty']:1;
							$updateres=DB::table('xcxshoppingcarts')->where($info_condition)->increment('qty',$qty);
							if($updateres)
							{
								$msg_array['status']='1';
								$msg_array['info']=trans('api.message_add_success');
								$msg_array['curl']='';
								$msg_array['resource']="";
							}
							else
							{
								$msg_array['status']='0';
								$msg_array['info']=trans('api.message_add_failure');
								$msg_array['curl']='';
								$msg_array['resource']="4";
							}
						}
						else
						{
							$params = new Xcxshoppingcart;
							$params->item_id 			=$formdata['item_id'];
							$params->qty 				=$formdata['qty'];
							$params->status 			=0;					
							$params->xcxuser_id 		=$xcxuser['id'];

							if($params->save())
							{
								$msg_array['status']='1';
								$msg_array['info']=trans('api.message_add_success');
								$msg_array['curl']='';
								$msg_array['resource']="";
							}
							else
							{
								$msg_array['status']='0';
								$msg_array['info']=trans('api.message_add_failure');
								$msg_array['curl']='';
								$msg_array['resource']="4";	
							}
						}
						
					}
					else
					{
						$msg_array['status']='0';
						$msg_array['info']=trans('api.message_sessionid_failure');
						$msg_array['curl']='';
						$msg_array['resource']="3";	
					}
				}
				else
				{
					$msg_array['status']='0';
					$msg_array['info']=trans('api.message_sessionid_failure');
					$msg_array['curl']='';
					$msg_array['resource']="2";	
				}
			}
			else
			{
				$msg_array['status']='0';
				$msg_array['info']=trans('api.message_get_empty');
				$msg_array['curl']='';
				$msg_array['resource']="1";
			}
		}
		else
		{
			$msg_array['status']='0';
			$msg_array['info']=$request_token['info'];
			$msg_array['curl']='';
			$msg_array['resource']="0";
		}
		
        return $msg_array;
	}
	/******************************************
	****AuThor:rubbish.boy@163.com
	****Title :列表接口
	*******************************************/
	public function api_list(Request $request)  
	{
		$request_token=$this->request_token;
		if($request_token['status']==1)
		{
			$param=$request_token['request'];
			$xcxmp=$request_token['data'];
			$appid=$xcxmp['appid'];
			$appsecret=$xcxmp['appsecret'];
			$session_id=@$param['session_id'];
			if(@$session_id)
			{
				$session_openid=Cache::store('redis')->get($session_id);
				if(@$session_openid)
				{
					$openid=substr($session_openid, -28);
					$condition['openid']=$openid;
					$xcxuser=object_array(DB::table('xcxusers')->where($condition)->first());
					if($xcxuser)
					{	
						$keyword=@$param['search_keyword'];
						if($keyword)
						{
							$list_condition['xcxuser_id']=$xcxuser['id'];
							$list_condition['status']=0;
							$list=Xcxshoppingcart::where($list_condition)->where('name','like',"%".$keyword.'%')->orderBy('updated_at','desc')->get()->toArray();
							if($list)
							{
								foreach($list as $key=>$val)
								{
									$info_condition['id']=$val['item_id'];
									$list[$key]['info']=object_array(DB::table('products')->where($info_condition)->first());
								}
								$msg_array['status']='1';
								$msg_array['info']=trans('api.message_get_success');
								$msg_array['curl']='';
								$msg_array['resource']=$list;
							}
							else
							{
								$msg_array['status']='1';
								$msg_array['info']=trans('api.message_get_empty');
								$msg_array['curl']='';
								$msg_array['resource']="";
							}
						}
						else
						{
							$list_condition['xcxuser_id']=$xcxuser['id'];
							$list_condition['status']=0;
							$list=Xcxshoppingcart::where($list_condition)->orderBy('updated_at','desc')->get()->toArray();
							if($list)
							{
								foreach($list as $key=>$val)
								{
									$info_condition['id']=$val['item_id'];
									$list[$key]['info']=object_array(DB::table('products')->where($info_condition)->first());
								}
								$msg_array['status']='1';
								$msg_array['info']=trans('api.message_get_success');
								$msg_array['curl']='';
								$msg_array['resource']=$list;
							}
							else
							{
								$msg_array['status']='1';
								$msg_array['info']=trans('api.message_get_empty');
								$msg_array['curl']='';
								$msg_array['resource']="";
							}
						}
					}
					else
					{
						$msg_array['status']='0';
						$msg_array['info']=trans('api.message_sessionid_failure');
						$msg_array['curl']='';
						$msg_array['resource']="3";	
					}
				}
				else
				{
					$msg_array['status']='0';
					$msg_array['info']=trans('api.message_sessionid_failure');
					$msg_array['curl']='';
					$msg_array['resource']="2";	
				}
			}
			else
			{
				$msg_array['status']='0';
				$msg_array['info']=trans('api.message_get_empty');
				$msg_array['curl']='';
				$msg_array['resource']="1";
			}
		}
		else
		{
			$msg_array['status']='0';
			$msg_array['info']=$request_token['info'];
			$msg_array['curl']='';
			$msg_array['resource']="0";
		}
		
        return $msg_array;
	}
}
