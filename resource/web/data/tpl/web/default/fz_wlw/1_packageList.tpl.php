<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
 
 <div class="alert alert-success" role="alert" style="text-align: center;">当前设备:<?php  echo $dev['province'];?><?php  echo $dev['city'];?><?php  echo $dev['area'];?><?php  echo $dev['address'];?><?php  echo $dev['devname'];?></div>
 
<ul class="nav nav-tabs">
	<li ><a href="<?php  echo url('site/entry/devList', array('m' => 'fz_wlw'));?>" class="fa fa-reply-all">返回设备列表</a></li>
	<li ><a href="<?php  echo url('site/entry/devAdd', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">设备信息</a></li> 
	<li  ><a href="<?php  echo url('site/entry/yedetail', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">钱包管理</a></li> 
	 <li ><a href="<?php  echo url('site/entry/orderList', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">订单管理</a></li>
 	<li class="active"><a href="<?php  echo url('site/entry/packageList', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">套餐管理</a></li>
 	<li ><a href="<?php  echo url('site/entry/notice', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>">设备通知</a></li>
  </ul>

<div class="clearfix">
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">查询</h3>
  </div>
  <div class="panel-body">
     <form class="form-horizontal" action="" method="post" id="frmSave" onsubmit="return funQuerySubmit()"> 
		 
		 <!-- 
		  <div class="form-group">  
		    <label for="lab" class="col-sm-2 control-label">套餐类别</label>
		    <div class="col-sm-10">
		      
		       <select id="typeid" name="typeid" class="form-control"> 	
		       	 <option value="">所有</option>
		    		<?php  if(is_array($types)) { foreach($types as $key => $item) { ?> 
		       		 <option value="<?php  echo $item['id'];?>"><?php  echo $item['typename'];?></option>
		        	<?php  } } ?>  
		    	</select> 
		      
		    </div>
		  </div>
		  -->
		
		  <div class="form-group">  
		    <label for="lab" class="col-sm-2 control-label">套餐名称</label>
		    <div class="col-sm-10">
		      <input type="text" class="form-control" value="<?php  echo $_GPC['tcname'];?>" id="query_tcname" name="tcname" placeholder="套餐名称">
		    </div>
		  </div>
		  
		  <div class="form-group"> 
		    <div class="col-sm-offset-2 col-sm-10"> 
		      <input type="hidden" name="devid" value="<?php  echo $_GPC['devid'];?>">
		      <input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
		      <input type="submit" name="query" class="btn" value="查询"> 
		      <input type="button" class="btn btn-success" value="添加套餐" onclick="funAdd()"> 
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
					<th>套餐名称</th> 
                    <th>价格</th> 
                    <th>信号数</th>  
                    <th>类型</th>  
                    <th>已售出</th> 
                    <th>每日限购</th>   
                    <th>是否上架</th> 
                    <th >操作</th>
				</tr>
			</thead>
			<tbody>
				<?php  if(is_array($res)) { foreach($res as $key => $item) { ?>
					<tr>
					 	<td><?php  echo $item['tcname'];?></td> 
					  
					 	<td><?php  echo $item['tcprice'];?>元</td> 
					 	<td><?php  echo $item['signnum'];?>次</td>
					 	<td>
					 		<?php  echo $item['countdown'];?>秒
					 		/
					 		<?php  if($item['ptype']=='1' || $item['ptype']=='') { ?>
					 			脉冲
					 		<?php  } ?>
					 		<?php  if($item['ptype']=='2' ) { ?>
					 			定时器
					 		<?php  } ?>
					 	</td> 
					 	<td><?php  echo $item['sellnum'];?></td> 
					 	<td><?php  echo $item['quota'];?></td> 
					 	<td>  
					 		<?php  if($item['issend']=='1' ) { ?>
					 			上架
					 		<?php  } ?>
					 		<?php  if($item['issend']=='0' ) { ?>
					 			下架
					 		<?php  } ?>
					 	</td>  
					 	<td>
					 		<a class="btn btn-default" href="<?php  echo url('site/entry/packageAdd', array('m' => 'fz_wlw','packageid'=>$item['id'],'devid'=>$_GPC['devid']));?>" role="button">
					 			<span class="glyphicon glyphicon-edit"></span> 
					 		</a>
					 		<a class="btn btn-default" onclick="funDelete('<?php  echo $item['id'];?>')" role="button">
					 			<span class="glyphicon glyphicon-remove"></span> 
					 		</a>
					   </td>
					</tr> 
			    <?php  } } ?>
				
			
			</tbody>
						
	 </table>
						
  </div>
</div> 
</div>
<form class="form-horizontal" action="" method="post" id="frmdel"> 
	 <input type="hidden" name="delId" id="delId" value="">
     <input type="hidden" name="devid" value="<?php  echo $_GPC['devid'];?>">
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
    function funAdd(){
		var url="<?php  echo url('site/entry/packageAdd', array('m' => 'fz_wlw','devid'=>$_GPC['devid']));?>";
		window.location=url;
		
    }

    $(function(){

    	  
    });
      
</script>

  

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>