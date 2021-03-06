<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
 
 <div class="alert alert-success" role="alert" style="text-align: center;">当前设备:<?php  echo $dev['province'];?><?php  echo $dev['city'];?><?php  echo $dev['area'];?><?php  echo $dev['address'];?><?php  echo $dev['devname'];?></div>
 
<ul class="nav nav-tabs">
	<li ><a href="<?php  echo url('site/entry/devList', array('m' => 'fz_wlw'));?>" class="fa fa-reply-all">返回设备列表</a></li>
	<li ><a href="<?php  echo url('site/entry/devAdd', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">设备信息</a></li> 
	<li  ><a href="<?php  echo url('site/entry/yedetail', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">钱包管理</a></li> 
	 <li class="active"><a href="<?php  echo url('site/entry/orderList', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">订单管理</a></li>
 	<li ><a href="<?php  echo url('site/entry/packageList', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">套餐管理</a></li>
 	<li ><a href="<?php  echo url('site/entry/notice', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">设备通知</a></li>
</ul>

<div class="clearfix">
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">查询</h3>
  </div>
  <div class="panel-body">
     <form class="form-horizontal" action="" method="post" id="frmSave" onsubmit="return funQuerySubmit()"> 
		 
		 <div class="col-sm-6"> 
	 		<div class="form-group">  
			    <label for="lab" class="col-sm-5 control-label">订单状态</label>
			    <div class="col-sm-7">
			       <select id="paystate" name="paystate" class="form-control"> 	
			       	 	<option value="">所有</option>
			    		<option value="0">未支付</option>
			    		<option value="1">已支付</option>
			    		<option value="2">已退款</option>
			    	</select>
			    </div>
			  </div>
		 </div>
		 
		 <div class="col-sm-6"> 
		 		<div class="form-group">  
				    <label for="lab" class="col-sm-5 control-label">订单状态</label>
				    <div class="col-sm-7"> 
				      <select id="paysend" name="paysend" class="form-control"> 	
				       	 	<option value="">所有</option>
				    		<option value="0">未触发</option>
				    		<option value="1">已触发</option>
				    		<option value="2">触发成功</option>
				    		<option value="3">触发失败</option>
				    	</select> 
				    </div>
				  </div>
		 </div>
		 
		  <div class="col-sm-6"> 
		  		<div class="form-group">  
			    <label for="lab" class="col-sm-5 control-label">时间查询</label>
			    <div class="col-sm-7">
			    	<?php echo tpl_form_field_daterange('time', array('starttime'=>($starttime ? date('Y-m-d', $starttime) : false),'endtime'=> ($endtime ? date('Y-m-d', $endtime) : false)));?>
			    </div>
			  </div>
		  </div>
		  
		  <div class="col-sm-6"> 
		  		<div class="form-group">  
			    <label for="lab" class="col-sm-5 control-label">订单号</label>
			    <div class="col-sm-7">
			      <input type="text" class="form-control" value="<?php  echo $_GPC['query_id'];?>" id="query_id" name="query_id" placeholder="订单号">
			    </div>
			  </div>
		  </div>
		  
		
		  
		  
		  <div class="form-group"> 
		    <div class="col-sm-offset-2 col-sm-10"> 
		      <input type="hidden" name="devid" value="<?php  echo $_GPC['devid'];?>">
		      <input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
		      <input type="submit" name="query" id="btn_query" class="btn" value="查询"> 
		       <input type="hidden" name="page" id="page" value="1">
		    </div> 
		  </div>
		</form> 
  </div>
</div>

<div class="panel panel-default"> 
  <div class="panel-body table-responsive">
    	<table class="table table-bordered table-hover">
    		<thead>
				<tr>
					<th>订单号</th>
					<th width="160px;">收款用户/设备号</th>
                    <th>购买用户</th> 
                    <th>金额(元)</th>  
                    <th>信号数</th>  
                    <th>订单状态</th>  
                    <th>触发状态</th> 
                    <th>下单时间</th> 
                    <th>支付信息</th> 
                    <th>设备描述</th>
                    
				</tr>
			</thead>
			<tbody>
				<?php  if(is_array($res)) { foreach($res as $key => $item) { ?>
					<tr>
					 	<td><?php  echo $item['id'];?></td>
					 	<td>
					 	<?php  echo $item['username'];?><br/>
					 	<?php  echo $item['devNum'];?>
					 	</td> 
					 	<td><?php  echo $item['buyuser'];?></td> 
					 	<td><?php  echo $item['paymoney'];?></td>
					 	<td><?php  echo $item['paynum'];?></td>  
					 	<td>  
					 		<?php  if($item['paystate']=='0' ) { ?>
					 			<span style="color: black;">未支付</span>
					 		<?php  } ?>
					 		<?php  if($item['paystate']=='1' ) { ?>
					 			<span style="color:green;">已支付</span>
					 		<?php  } ?>
					 		<?php  if($item['paystate']=='2' ) { ?>
					 			<span style="color:red;">已退款</span>
					 		<?php  } ?>
					 	</td>  
					 	<td>  
					 		<?php  if($item['paysend']=='0' ) { ?>
					 			<span style="color: black;">未触发</span>
					 		<?php  } ?>
					 		<?php  if($item['paysend']=='1' ) { ?>
					 			<span style="color:blue;">已触发</span>
					 		<?php  } ?>
					 		<?php  if($item['paysend']=='2' ) { ?>
					 			<span style="color:green;">触发成功</span>
					 		<?php  } ?>
					 		<?php  if($item['paysend']=='3' ) { ?>
					 			<span style="color:red;">触发失败</span>
					 		<?php  } ?>
					 		<br/>
					 		<span><?php  echo $item['remark'];?></span>
					 	</td>  
					 	<td style="white-space:normal;"><?php  echo date("Y-m-d H:i:s",$item['addtime'])?></td>   
					 	<td style="white-space:normal;width: 120px;">
					 		 <?php  if($item['paytime']!='') { ?> 
					 			<?php  echo date("Y-m-d H:i:s",$item['paytime'])?><br/>
					 		<?php  } ?>
					 		<?php  echo $item['payordernum'];?> 
					 	</td>   
					 	<td style="white-space:normal;"><?php  echo $item['devname'];?></td>
					</tr> 
			    <?php  } } ?> 
			
			</tbody> 
	 </table>  
	 
	 <!-- 分页 -->
	 <div style="text-align: center;margin-top: 10px;">
	 <?php  echo $pager;?>
     </div>	
	 <!-- 分页end -->
	 
  </div>
</div> 
</div>
<form class="form-horizontal" action="" method="post" id="frmdel">  
     <input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
</form>
<script type="text/javascript">
	function funQuerySubmit(){
		 
		 return true; 
	}
    
	function funDelete(id){
		if(id==""){
			return;
		}
		var agr="温馨提示：您确定要删除吗？";
		if(window.confirm(agr)){
			$("#delId").val(id);
			$("#frmdel").submit();
		}
     }

    $(function(){
		var paystate="<?php  echo $_GPC['paystate'];?>";
		$("#paystate").val(paystate);

		var paysend="<?php  echo $_GPC['paysend'];?>";
		$("#paysend").val(paysend);
    	  
    });

    function setPageIndex(page){
		$("#page").val(page); 
		$("#btn_query").click();
    }
      
</script>

  

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>