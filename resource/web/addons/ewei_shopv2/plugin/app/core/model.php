<?php


if (!defined('IN_IA')) {
	exit('Access Denied');
}
if (!class_exists('AppModel')) {
	class AppModel extends PluginModel
	{
		private $staticurl = array('index', 'shop', 'goods', 'member', 'sale', 'account', 'commission');
		private $loginPage = array('member', 'order', 'commission', 'sale.coupon', 'groups.orders', 'groups.team');
		public function createAuth()
		{
			global $_W;
			global $_GPC;
			$domain = trim(preg_replace('/http(s)?:\\/\\//', '', rtrim($_W['siteroot'], '/')));
			$ip = gethostbyname($_SERVER['HTTP_HOST']);
			$setting = setting_load('site');
			$id = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
			$auth = get_auth();
			load()->func('communication');
			$resp = ihttp_request(EWEI_SHOPV2_AUTH_URL . 'app', array('ip' => $ip, 'id' => $id, 'code' => $auth['code'], 'domain' => $domain, 'appid' => $_W['account']['key']), NULL, 1);
			$result = @json_decode($resp['content'], true);
			if ($result['status'] && !empty($result['result']['ak']) && !empty($result['result']['sk'])) {
				return array('ak' => $result['result']['ak'], 'sk' => $result['result']['sk']);
			}
			return error(-1, $result['result']['message']);
		}
		public function diyMenu($menuid)
		{
			global $_W;
			global $_GPC;
			$set = m('common')->getPluginset('diypage');
			$menuset = $set['menu'];
			$id = intval($menuid);
			if ($id == 0) {
				$id = $menuset[$menuid];
			}
			if (empty($id)) {
				return $this->defaultMenu($menuid);
			}
			if (empty($data['params']['navstyle'])) {
				$iconfont = $this->getIconUnicode();
			}
			$menu = pdo_fetch('select * from ' . tablename('ewei_shop_diypage_menu') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
			if (empty($menu)) {
				return $this->defaultMenu($menuid);
			}
			$data = base64_decode($menu['data']);
			$data = json_decode($data, true);
			$items = $data['data'];
			if (empty($items) || !is_array($items)) {
				return NULL;
			}
			$loginPage = $this->loginPage;
			$newMenu = array();
			foreach ($items as $id => $item) {
				if (empty($item['linkurl'])) {
					continue;
				}
				if (empty($data['params']['navstyle'])) {
					if (empty($item['text'])) {
						continue;
					}
				} else {
					if (empty($item['imgurl'])) {
						continue;
					}
				}
				$newUrl = $this->getUrl($item['linkurl']);
				$newItem = array('url' => $newUrl['url']);
				if (!empty($newUrl['vars'])) {
					$newItem['url_vars'] = $newUrl['vars'];
				}
				if (!empty($data['params']['navstyle'])) {
					$newItem['image'] = tomedia($item['imgurl']);
				} else {
					$newItem['icon'] = $iconfont[$item['iconclass']];
					$newItem['text'] = $item['text'];
				}
				if (!empty($loginPage)) {
					foreach ($loginPage as $lp) {
						if (strexists($newUrl['url'], $lp)) {
							$newItem['needlogin'] = 1;
							break;
						}
					}
				}
				if (!empty($item['child'])) {
					foreach ($item['child'] as $id => $child) {
						if (empty($child['linkurl']) || empty($child['text'])) {
							continue;
						}
						$childNewUrl = $this->getUrl($child['linkurl']);
						$newChild = array('text' => $child['text'], 'url' => $childNewUrl['url']);
						if (!empty($childNewUrl['vars'])) {
							$newChild['url_vars'] = $childNewUrl['vars'];
						}
						if (!empty($loginPage)) {
							foreach ($loginPage as $lp) {
								if (strexists($childNewUrl['url'], $lp)) {
									$newChild['needlogin'] = 1;
									break;
								}
							}
						}
						if (!empty($newChild)) {
							$newItem['child'][] = $newChild;
						}
					}
				}
				$newMenu[] = $newItem;
			}
			return array('type' => $data['params']['navstyle'], 'navfloat' => $data['params']['navfloat'], 'style' => $data['style'], 'items' => $newMenu);
		}
		public function defaultMenu($page)
		{
			global $_W;
			if ($page == 'shop') {
				$menu = array(array('url' => 'index', 'icon' => 'e647', 'text' => '首页'), array('url' => 'shop.category', 'icon' => 'e62c', 'text' => '全部分类'), array('url' => 'commission', 'icon' => 'e647', 'text' => '分销中心'), array('url' => 'member.cart', 'icon' => 'e642', 'text' => '购物车', 'needlogin' => 1), array('url' => 'member', 'icon' => 'e724', 'text' => '会员中心', 'needlogin' => 1));
				if (p('commission') && 0 < $_W['shopset']['commission']['level']) {
					$member = m('member')->getMember($_W['openid']);
					if ($member['isagent'] == 1 && $member['status'] == 1) {
						$menu[2]['text'] = empty($_W['shopset']['commission']['texts']['center']) ? '分销中心' : $_W['shopset']['commission']['texts']['center'];
					} else {
						$menu[2]['text'] = empty($_W['shopset']['commission']['texts']['become']) ? '成为分销商' : $_W['shopset']['commission']['texts']['become'];
						$menu[2]['url'] = 'commission.register';
					}
				} else {
					array_splice($menu, 2, 1);
				}
			} else {
				if ($page == 'commission') {
					$menu = array(array('url' => 'commission', 'icon' => 'e647', 'text' => '分销中心', 'needlogin' => 1), array('url' => 'commission.withdraw', 'icon' => 'e74c', 'text' => '分销佣金', 'needlogin' => 1), array('url' => 'commission.order', 'icon' => 'e62c', 'text' => '佣金明细', 'needlogin' => 1), array('url' => 'commission.down', 'icon' => 'e6c8', 'text' => '我的下线', 'needlogin' => 1), array('url' => 'commission.myshop', 'icon' => 'e627', 'text' => '我的小店', 'needlogin' => 1));
				} else {
					return '';
				}
			}
			return array('type' => 0, 'navfloat' => 'top', 'style' => array('pagebgcolor' => '#f9f9f9', 'bgcolor' => '#ffffff', 'bgcoloron' => '#ffffff', 'iconcolor' => '#999999', 'iconcoloron' => '#ff0000', 'textcolor' => '#666666', 'textcoloron' => '#ff0000', 'bordercolor' => '#ffffff', 'bordercoloron' => '#ffffff', 'childtextcolor' => '#666666', 'childbgcolor' => '#f4f4f4', 'childbordercolor' => '#eeeeee'), 'items' => $menu);
		}
		public function getUrl($url)
		{
			global $_W;
			if (empty($url)) {
				return array();
			}
			if (strexists($url, './index.php?') && strexists($url, 'ewei_shopv2') && strexists($url, 'mobile')) {
				$parse = parse_url($url);
				$parse_query = $parse['query'];
				if (empty($parse_query)) {
					return array();
				}
				$vars = explode('&', $parse_query);
				$newVars = array();
				foreach ($vars as $i => $var) {
					$vararr = explode('=', $var);
					$newVars[$vararr[0]] = $vararr[1];
				}
				if ($newVars['m'] != 'ewei_shopv2' || $newVars['do'] != 'mobile') {
					return array('url' => $url);
				}
				$route = $newVars['r'] = !empty($newVars['r']) ? $newVars['r'] : 'index';
				unset($newVars['i']);
				unset($newVars['c']);
				unset($newVars['m']);
				unset($newVars['do']);
				unset($newVars['r']);
				$newUrl = array('url' => $route, 'vars' => $newVars);
				$routes = explode('.', $route);
				if (!in_array($routes[0], $this->staticurl)) {
					$newUrl['url'] = $_W['siteroot'] . 'app/' . str_replace('./', '', $url);
					unset($newUrl['vars']);
				}
				return $newUrl;
			}
			return array('url' => $url);
		}
		public function getIconUnicode($class = NULL)
		{
			$file = EWEI_SHOPV2_PLUGIN . 'app/static/iconfont.json';
			if (!file_exists($file)) {
				return '';
			}
			$json = file_get_contents($file);
			if (empty($json)) {
				return '';
			}
			$arr = json_decode($json, true);
			if (empty($arr) || !is_array($arr)) {
				return '';
			}
			$newArr = array();
			foreach ($arr as $i => $item) {
				if (!empty($item['code']) && !empty($item['class'])) {
					$newArr[$item['class']] = $item['code'];
				}
			}
			return $newArr;
		}
		public function wxpay($params, $type = 0)
		{
			global $_W;
			$data = m('common')->getSysset('app');
			$openid = empty($params['openid']) ? $_W['openid'] : $params['openid'];
			if (isset($openid) && strexists($openid, 'sns_wa_')) {
				$openid = str_replace('sns_wa_', '', $openid);
			}
			$sec = m('common')->getSec();
			$sec = iunserializer($sec['sec']);
			$package = array();
			$package['appid'] = $data['appid'];
			$package['mch_id'] = $sec['wxapp']['mchid'];
			$package['nonce_str'] = random(32);
			$package['body'] = $params['title'];
			$package['device_info'] = 'ewei_shopv2';
			$package['attach'] = $_W['uniacid'] . ':' . $type;
			$package['out_trade_no'] = $params['tid'];
			$package['total_fee'] = $params['fee'] * 100;
			$package['spbill_create_ip'] = CLIENT_IP;
			if (!empty($params['goods_tag'])) {
				$package['goods_tag'] = $params['goods_tag'];
			}
			$package['notify_url'] = $_W['siteroot'] . 'addons/ewei_shopv2/payment/wechat/notify.php';
			$package['trade_type'] = 'JSAPI';
			$package['openid'] = $openid;
			ksort($package, SORT_STRING);
			$string1 = '';
			foreach ($package as $key => $v) {
				if (empty($v)) {
					continue;
				}
				$string1 .= $key . '=' . $v . '&';
			}
			$string1 .= 'key=' . $sec['wxapp']['apikey'];
			$package['sign'] = strtoupper(md5($string1));
			$dat = array2xml($package);
			load()->func('communication');
			$response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
			if (is_error($response)) {
				return error(-1, $response['message']);
			}
			$xml = @simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
			if (strval($xml->return_code) == 'FAIL') {
				return error(-2, strval($xml->return_msg));
			}
			if (strval($xml->result_code) == 'FAIL') {
				return error(-3, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
			}
			$prepayid = $xml->prepay_id;
			$wOpt['appId'] = $data['appid'];
			$wOpt['timeStamp'] = TIMESTAMP . '';
			$wOpt['nonceStr'] = random(32);
			$wOpt['package'] = 'prepay_id=' . $prepayid;
			$wOpt['signType'] = 'MD5';
			ksort($wOpt, SORT_STRING);
			$string = '';
			foreach ($wOpt as $key => $v) {
				$string .= $key . '=' . $v . '&';
			}
			$string .= 'key=' . $sec['wxapp']['apikey'];
			$wOpt['paySign'] = strtoupper(md5($string));
			unset($wOpt['appId']);
			return $wOpt;
		}
		public function isWeixinPay($out_trade_no, $money = 0)
		{
			global $_W;
			global $_GPC;
			$data = m('common')->getSysset('app');
			$sec = m('common')->getSec();
			$sec = iunserializer($sec['sec']);
			$url = 'https://api.mch.weixin.qq.com/pay/orderquery';
			$pars = array();
			$pars['appid'] = $data['appid'];
			$pars['mch_id'] = $sec['wxapp']['mchid'];
			$pars['nonce_str'] = random(32);
			$pars['out_trade_no'] = $out_trade_no;
			ksort($pars, SORT_STRING);
			$string1 = '';
			foreach ($pars as $k => $v) {
				$string1 .= $k . '=' . $v . '&';
			}
			$string1 .= 'key=' . $sec['wxapp']['apikey'];
			$pars['sign'] = strtoupper(md5($string1));
			$xml = array2xml($pars);
			load()->func('communication');
			$resp = ihttp_post($url, $xml);
			if (is_error($resp)) {
				return error(-2, $resp['message']);
			}
			if (empty($resp['content'])) {
				return error(-2, '网络错误');
			}
			$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
			$dom = new DOMDocument();
			if ($dom->loadXML($xml)) {
				$xpath = new DOMXPath($dom);
				$code = $xpath->evaluate('string(//xml/return_code)');
				$ret = $xpath->evaluate('string(//xml/result_code)');
				$trade_state = $xpath->evaluate('string(//xml/trade_state)');
				if (strtolower($code) == 'success' && strtolower($ret) == 'success' && strtolower($trade_state) == 'success') {
					$total_fee = intval($xpath->evaluate('string(//xml/total_fee)')) / 100;
					if ($total_fee != $money) {
						return error(-1, '金额出错');
					}
					return true;
				}
				if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
					$error = $xpath->evaluate('string(//xml/return_msg)');
				} else {
					$error = $xpath->evaluate('string(//xml/return_msg)') . ' | ' . $xpath->evaluate('string(//xml/err_code_des)');
				}
				return error(-2, $error);
			}
			return error(-1, '未知错误');
		}
		public function alipay_build($params, $config = array())
		{
			global $_W;
			$arr = array('app_id' => $config['appid'], 'method' => 'alipay.trade.app.pay', 'format' => 'JSON', 'charset' => 'utf-8', 'sign_type' => 'RSA2', 'timestamp' => date('Y-m-d H:i:s', time()), 'version' => '1.0', 'notify_url' => $_W['siteroot'] . 'addons/ewei_shopv2/payment/alipay/notify.php', 'biz_content' => json_encode(array('timeout_express' => '90m', 'product_code' => 'QUICK_MSECURITY_PAY', 'total_amount' => $params['total_amount'], 'subject' => $params['subject'], 'body' => $params['body'], 'out_trade_no' => $params['out_trade_no'])));
			ksort($arr);
			$string1 = '';
			foreach ($arr as $key => $v) {
				if (empty($v)) {
					continue;
				}
				$string1 .= $key . '=' . $v . '&';
			}
			$string1 = rtrim($string1, '&');
			$pkeyid = openssl_pkey_get_private(m('common')->chackKey($config['private_key'], false));
			if ($pkeyid === false) {
				return error(-1, '提供的私钥格式不对');
			}
			$signature = '';
			openssl_sign($string1, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
			openssl_free_key($pkeyid);
			$signature = base64_encode($signature);
			$arr['sign'] = $signature;
			return http_build_query($arr);
		}
		/**

		 * 获取微信小程序accessToken

		 * @return string

		 */
		public function getAccessToken()
		{
			global $_W;
			$appset = $_W['shopset']['app'];
			$cacheKey = 'eweishop:wxapp:accesstoken:' . $_W['uniacid'];
			$accessToken = m('cache')->get($cacheKey, $_W['uniacid']);
			if (!empty($accessToken) && !empty($accessToken['token']) && TIMESTAMP < $accessToken['expire']) {
				return $accessToken['token'];
			}
			if (empty($appset['appid']) || empty($appset['secret'])) {
				return error(-1, '未填写小程序的 appid 或 appsecret！');
			}
			load()->func('communication');
			$content = ihttp_get('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appset['appid'] . '&secret=' . $appset['secret']);
			if (is_error($content)) {
				return error(-1, '获取微信公众号授权失败, 请稍后重试！错误详情: ' . $content['message']);
			}
			$result = @json_decode($content['content'], true);
			if (empty($result) || !is_array($result) || empty($result['access_token']) || empty($result['expires_in'])) {
				$errorinfo = substr($content['meta'], strpos($content['meta'], '{'));
				$errorinfo = @json_decode($errorinfo, true);
				return error(-1, '获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: 错误代码-' . $errorinfo['errcode'] . '，错误信息-' . $errorinfo['errmsg']);
			}
			$record['token'] = $result['access_token'];
			$record['expire'] = TIMESTAMP + $result['expires_in'] - 200;
			m('cache')->set($cacheKey, $record, $_W['uniacid']);
			return $result['access_token'];
		}
		/**

		 * 发送模板消息

		 * @param null $openid  发送人openid

		 * @param array $datas   发送数据

		 * @param string $type  发送类型

		 * @return bool

		 */
		public function sendNotice($openid = NULL, $datas = array(), $prepay_id = NULL, $orderid = 0, $type = 'pay')
		{
			global $_W;
			if (empty($openid) || empty($datas) || empty($prepay_id)) {
				return error(-1, 'openid或datas或prepay_id为空');
			}
			$openid = str_replace('sns_wa_', '', $openid);
			$appset = m('common')->getSysset('app');
			if (empty($appset)) {
				return error(-1, '未读取到小程序设置');
			}
			$tempateid = $appset['tmessage_' . $type];
			if (empty($tempateid)) {
				return error(-1, '未选择消息模板');
			}
			$tempate = $this->getTMessage($tempateid);
			if (empty($tempate) || empty($tempate['templateid']) || empty($tempate['datas'])) {
				return error(-1, '消息模板未开启或不存在');
			}
			$data = array();
			$emphasis_keyword = '';
			foreach ($tempate['datas'] as $index => $item) {
				$key = str_replace(array('{{', '.DATA}}'), '', trim($item['key']));
				if (empty($key)) {
					continue;
				}
				$data[$key] = array('value' => $this->replaceTemplate($item['value'], $datas), 'color' => $item['color']);
				if ($index == $tempate['emphasis_keyword']) {
					$emphasis_keyword = $key;
				}
			}
			unset($index);
			unset($item);
			$page = 'pages/order/detail/index?id=' . $orderid;
			if (empty($orderid)) {
				$page = '';
			}
			$obj = json_encode(array('touser' => $openid, 'template_id' => $tempate['templateid'], 'page' => $page, 'form_id' => $prepay_id, 'data' => $data, 'emphasis_keyword' => $emphasis_keyword . '.DATA'));
			$accessToken = $this->getAccessToken();
			if (is_error($accessToken)) {
				return error(-1, 'accessToken获取失败');
			}
			load()->func('communication');
			$result = ihttp_post('https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $accessToken, $obj);
			return $result;
		}
		/**

		 * 替换数据

		 * @param $str

		 * @param array $datas

		 * @return mixed

		 */
		protected function replaceTemplate($str, $datas = array())
		{
			foreach ($datas as $d) {
				$str = str_replace('[' . $d['name'] . ']', $d['value'], $str);
			}
			return $str;
		}
		/**

		 * 获取单个消息模板

		 * @param $id

		 * @return bool

		 */
		public function getTMessage($id)
		{
			global $_W;
			$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_wxapp_tmessage') . ' WHERE id=:id AND uniacid=:uniacid AND status=1 LIMIT 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
			if (!empty($item)) {
				$item['datas'] = iunserializer($item['datas']);
			}
			return $item;
		}
	}
}